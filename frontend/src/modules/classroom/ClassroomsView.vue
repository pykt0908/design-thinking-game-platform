<template>
  <div>
    <div class="d-flex justify-space-between align-center mb-6">
      <div>
        <h1 class="text-h5 font-weight-bold text-slate-800">จัดการห้องเรียน (Classrooms)</h1>
        <p class="text-body-2 text-grey">สร้างห้องเรียน แจกรหัสเข้าร่วม และมอบหมายเกมให้นักเรียน</p>
      </div>

      <v-btn
        color="primary"
        class="font-weight-bold px-5"
        prepend-icon="mdi-plus"
        rounded="lg"
        @click="showCreateDialog = true"
      >
        + สร้างห้องเรียนใหม่
      </v-btn>
    </div>

    <!-- Classrooms Grid per spec Section 26 -->
    <v-row v-if="classrooms.length > 0">
      <v-col v-for="c in classrooms" :key="c.id" cols="12" md="6" lg="4">
        <v-card class="pa-5 border-card rounded-xl h-100 d-flex flex-column justify-space-between">
          <div>
            <div class="d-flex justify-space-between align-center mb-3">
              <v-chip size="small" color="primary" variant="flat" class="font-weight-bold">
                ปีการศึกษา {{ c.academic_year || '2569' }}/{{ c.semester || '1' }}
              </v-chip>
              <v-chip size="x-small" color="success">
                {{ c.status === 'active' ? 'เปิดการสอน' : 'เสร็จสิ้น' }}
              </v-chip>
            </div>

            <h2 class="text-h6 font-weight-bold text-slate-800 mb-2">
              {{ c.name }}
            </h2>

            <p class="text-body-2 text-grey-darken-1 mb-4">
              {{ c.description || 'ไม่มีคำอธิบาย' }}
            </p>

            <div class="d-flex align-center gap-4 mb-4 text-caption text-grey">
              <span class="d-flex align-center"><v-icon icon="mdi-account-school" size="16" class="mr-1"></v-icon> นักเรียน: <strong class="ml-1">{{ c.students?.length || 0 }}</strong> คน</span>
              <span class="d-flex align-center"><v-icon icon="mdi-gamepad-variant" size="16" class="mr-1"></v-icon> เกมที่มอบหมาย: <strong class="ml-1">{{ c.assignments?.length || 0 }}</strong> เกม</span>
            </div>

            <!-- Join Code Card per spec Section 7 & 26 -->
            <v-sheet color="grey-lighten-4" rounded="lg" class="pa-3 mb-4 d-flex justify-space-between align-center">
              <div>
                <div class="text-caption text-grey">รหัสเข้าร่วมห้องเรียน (Join Code)</div>
                <div class="text-h6 font-weight-bold text-primary tracking-wider">{{ c.code }}</div>
              </div>
              <v-btn
                icon="mdi-content-copy"
                size="small"
                variant="text"
                color="primary"
                @click="copyJoinCode(c.code)"
                title="คัดลอกรหัส"
              ></v-btn>
            </v-sheet>
          </div>

          <div class="d-flex justify-space-between align-center pt-3 border-t">
            <v-btn
              variant="outlined"
              size="small"
              color="primary"
              rounded="lg"
              prepend-icon="mdi-plus"
              @click="openAssignDialog(c)"
            >
              มอบหมายเกม
            </v-btn>

            <v-btn
              variant="text"
              size="small"
              color="grey-darken-2"
              rounded="lg"
              append-icon="mdi-arrow-right"
            >
              รายชื่อนักเรียน
            </v-btn>
          </div>
        </v-card>
      </v-col>
    </v-row>

    <!-- Create Classroom Dialog -->
    <v-dialog v-model="showCreateDialog" max-width="500">
      <v-card class="pa-6 rounded-xl">
        <h2 class="text-h6 font-weight-bold mb-4">สร้างห้องเรียนใหม่</h2>
        <v-form @submit.prevent="handleCreateClassroom">
          <v-text-field
            v-model="newClassName"
            label="ชื่อห้องเรียน *"
            placeholder="เช่น วิทยาศาสตร์ ม.1/3"
            required
            class="mb-3"
          ></v-text-field>

          <v-row>
            <v-col cols="6">
              <v-text-field
                v-model="newAcademicYear"
                label="ปีการศึกษา"
                placeholder="2569"
              ></v-text-field>
            </v-col>
            <v-col cols="6">
              <v-text-field
                v-model="newSemester"
                label="ภาคเรียน"
                placeholder="1"
              ></v-text-field>
            </v-col>
          </v-row>

          <v-textarea
            v-model="newClassDesc"
            label="คำอธิบายห้องเรียน"
            rows="2"
            class="mb-4"
          ></v-textarea>

          <div class="d-flex justify-end gap-2">
            <v-btn variant="text" rounded="lg" @click="showCreateDialog = false">ยกเลิก</v-btn>
            <v-btn color="primary" rounded="lg" type="submit" :loading="creating">สร้างห้องเรียน</v-btn>
          </div>
        </v-form>
      </v-card>
    </v-dialog>

    <!-- Assign Game Dialog -->
    <v-dialog v-model="showAssignDialog" max-width="500">
      <v-card class="pa-6 rounded-xl">
        <h2 class="text-h6 font-weight-bold mb-1">มอบหมายเกม</h2>
        <p class="text-caption text-grey mb-4">ห้องเรียน: {{ targetClassroom?.name }}</p>

        <v-form @submit.prevent="handleAssignGame">
          <v-select
            v-model="assignGameId"
            :items="availableGames"
            item-title="title"
            item-value="id"
            label="เลือกเกมการเรียนรู้ *"
            required
            class="mb-3"
          ></v-select>

          <v-text-field
            v-model.number="assignPassingScore"
            type="number"
            label="คะแนนผ่านเกณฑ์ (%)"
            class="mb-4"
          ></v-text-field>

          <div class="d-flex justify-end gap-2">
            <v-btn variant="text" rounded="lg" @click="showAssignDialog = false">ยกเลิก</v-btn>
            <v-btn color="primary" rounded="lg" type="submit" :loading="assigning">ยืนยันมอบหมาย</v-btn>
          </div>
        </v-form>
      </v-card>
    </v-dialog>
  </div>
</template>

<script setup lang="ts">
import { ref, onMounted } from 'vue'
import apiClient from '@/api/client'
import type { Classroom, Game } from '@/types'

const classrooms = ref<Classroom[]>([])
const availableGames = ref<Game[]>([])

// Create Dialog
const showCreateDialog = ref(false)
const newClassName = ref('')
const newAcademicYear = ref('2569')
const newSemester = ref('1')
const newClassDesc = ref('')
const creating = ref(false)

// Assign Dialog
const showAssignDialog = ref(false)
const targetClassroom = ref<Classroom | null>(null)
const assignGameId = ref<number | null>(null)
const assignPassingScore = ref(60)
const assigning = ref(false)

async function loadData() {
  const [cRes, gRes] = await Promise.all([
    apiClient.get('/classrooms'),
    apiClient.get('/games'),
  ])
  classrooms.value = cRes.data
  availableGames.value = gRes.data.data || gRes.data
}

async function handleCreateClassroom() {
  if (!newClassName.value) return
  creating.value = true
  try {
    await apiClient.post('/classrooms', {
      name: newClassName.value,
      academic_year: newAcademicYear.value,
      semester: newSemester.value,
      description: newClassDesc.value,
    })
    showCreateDialog.value = false
    newClassName.value = ''
    newClassDesc.value = ''
    await loadData()
  } finally {
    creating.value = false
  }
}

function openAssignDialog(c: Classroom) {
  targetClassroom.value = c
  if (availableGames.value.length > 0) {
    assignGameId.value = availableGames.value[0].id
  }
  showAssignDialog.value = true
}

async function handleAssignGame() {
  if (!targetClassroom.value || !assignGameId.value) return
  assigning.value = true
  try {
    await apiClient.post(`/classrooms/${targetClassroom.value.id}/assign`, {
      game_id: assignGameId.value,
      passing_score: assignPassingScore.value,
    })
    showAssignDialog.value = false
    alert('มอบหมายเกมให้ห้องเรียนสำเร็จ!')
    await loadData()
  } finally {
    assigning.value = false
  }
}

function copyJoinCode(code: string) {
  navigator.clipboard.writeText(code)
  alert(`คัดลอกรหัสห้องเรียน "${code}" เรียบร้อยแล้ว! นำไปส่งให้นักเรียนเพื่อเข้าร่วมได้เลย`)
}

onMounted(loadData)
</script>

<style scoped>
.gap-2 { gap: 8px; }
.gap-4 { gap: 16px; }
.tracking-wider { letter-spacing: 0.1em; }
</style>
