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
              variant="tonal"
              size="small"
              color="primary"
              rounded="lg"
              prepend-icon="mdi-account-group"
              class="font-weight-medium"
              @click="openStudentsDialog(c)"
            >
              รายชื่อนักเรียน ({{ c.students?.length || 0 }})
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

    <!-- View Classroom Students Dialog -->
    <v-dialog v-model="showStudentsDialog" max-width="680" scrollable>
      <v-card v-if="selectedClassroomForStudents" class="rounded-2xl overflow-hidden">
        <!-- Header -->
        <div class="pa-5 bg-purple-lighten-5 border-b d-flex justify-space-between align-center">
          <div class="d-flex align-center">
            <v-avatar size="44" color="white" class="mr-3 elevation-1">
              <v-icon icon="mdi-account-school" color="primary" size="24"></v-icon>
            </v-avatar>
            <div>
              <div class="d-flex align-center gap-2">
                <h2 class="text-h6 font-weight-bold text-slate-900 mb-0">
                  {{ selectedClassroomForStudents.name }}
                </h2>
                <v-chip size="x-small" color="primary" variant="flat" class="font-weight-bold">
                  {{ selectedClassroomForStudents.students?.length || 0 }} คน
                </v-chip>
              </div>
              <div class="text-caption text-grey-darken-1">
                ปีการศึกษา {{ selectedClassroomForStudents.academic_year || '2569' }}/{{ selectedClassroomForStudents.semester || '1' }}
                &bull; รหัสเข้าร่วม: <strong class="text-primary">{{ selectedClassroomForStudents.code }}</strong>
              </div>
            </div>
          </div>
          <v-btn icon="mdi-close" variant="text" size="small" @click="showStudentsDialog = false"></v-btn>
        </div>

        <!-- Body -->
        <v-card-text class="pa-5" style="max-height: 480px;">
          <!-- Search & Filter -->
          <div class="mb-4">
            <v-text-field
              v-model="studentSearchQuery"
              placeholder="ค้นหาตามชื่อ, รหัสนักเรียน หรืออีเมล..."
              prepend-inner-icon="mdi-magnify"
              density="compact"
              hide-details
              rounded="lg"
              variant="outlined"
              class="bg-white"
              clearable
            ></v-text-field>
          </div>

          <!-- Students List -->
          <div v-if="filteredStudents.length > 0" class="d-flex flex-column gap-2">
            <v-card
              v-for="s in filteredStudents"
              :key="s.id"
              class="pa-3 border rounded-xl d-flex justify-space-between align-center bg-white hover-card"
            >
              <div class="d-flex align-center">
                <v-avatar size="40" color="purple-lighten-4" class="mr-3 text-primary font-weight-bold">
                  {{ s.name?.charAt(0) || 'S' }}
                </v-avatar>
                <div>
                  <div class="font-weight-bold text-slate-800 text-subtitle-2 d-flex align-center">
                    <span>{{ s.name }}</span>
                    <v-chip
                      v-if="s.student_id"
                      size="x-small"
                      color="secondary"
                      variant="tonal"
                      class="ml-2 font-weight-bold"
                    >
                      รหัส: {{ s.student_id }}
                    </v-chip>
                  </div>
                  <div class="text-caption text-grey">
                    {{ s.email }}
                    <span v-if="(s as any)?.pivot?.joined_at">
                      &bull; เข้าร่วมเมื่อ {{ formatDate((s as any).pivot.joined_at) }}
                    </span>
                  </div>
                </div>
              </div>

              <div class="d-flex align-center">
                <v-chip size="small" color="success" variant="tonal" class="font-weight-bold">
                  <v-icon icon="mdi-check-circle-outline" size="14" class="mr-1"></v-icon>
                  เข้าเรียนแล้ว
                </v-chip>
              </div>
            </v-card>
          </div>

          <!-- Search No Result -->
          <div v-else-if="studentSearchQuery" class="text-center pa-8 text-grey">
            <v-icon icon="mdi-account-search" size="48" color="grey-lighten-1" class="mb-2"></v-icon>
            <div>ไม่พบนักเรียนที่ตรงกับคำค้นหา "{{ studentSearchQuery }}"</div>
          </div>

          <!-- Empty State: No students joined yet -->
          <div v-else class="text-center pa-8 border rounded-2xl bg-purple-lighten-5">
            <v-avatar size="56" color="white" class="mb-3 elevation-2">
              <v-icon icon="mdi-account-plus-outline" size="32" color="primary"></v-icon>
            </v-avatar>
            <h3 class="text-subtitle-1 font-weight-bold text-slate-800 mb-1">
              ยังไม่มีนักเรียนเข้าร่วมห้องเรียนนี้
            </h3>
            <p class="text-caption text-slate-600 mb-4 max-w-md mx-auto">
              ส่งรหัสห้องเรียนด้านล่างให้นักเรียน เพื่อใช้กรอกเข้าร่วมผ่านหน้าแรกของนักเรียน
            </p>
            <v-sheet color="white" rounded="xl" class="pa-3 d-inline-flex align-center gap-3 border elevation-1">
              <div class="text-left px-2">
                <div class="text-caption text-grey">รหัสห้องเรียน (Join Code)</div>
                <div class="text-h6 font-weight-bold text-primary tracking-wider">
                  {{ selectedClassroomForStudents.code }}
                </div>
              </div>
              <v-btn
                color="primary"
                rounded="lg"
                size="small"
                class="font-weight-bold"
                prepend-icon="mdi-content-copy"
                @click="copyJoinCode(selectedClassroomForStudents.code)"
              >
                คัดลอกรหัส
              </v-btn>
            </v-sheet>
          </div>
        </v-card-text>

        <!-- Footer -->
        <div class="pa-4 border-t d-flex justify-end bg-grey-lighten-5">
          <v-btn variant="outlined" rounded="lg" color="grey-darken-1" @click="showStudentsDialog = false">
            ปิด
          </v-btn>
        </div>
      </v-card>
    </v-dialog>
  </div>
</template>

<script setup lang="ts">
import { ref, computed, onMounted } from 'vue'
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

// Students Dialog
const showStudentsDialog = ref(false)
const selectedClassroomForStudents = ref<Classroom | null>(null)
const studentSearchQuery = ref('')

const filteredStudents = computed(() => {
  const students = selectedClassroomForStudents.value?.students || []
  if (!studentSearchQuery.value) return students
  const q = studentSearchQuery.value.toLowerCase().trim()
  return students.filter(s =>
    s.name?.toLowerCase().includes(q) ||
    s.email?.toLowerCase().includes(q) ||
    (s.student_id && s.student_id.toLowerCase().includes(q))
  )
})

async function openStudentsDialog(c: Classroom) {
  selectedClassroomForStudents.value = c
  studentSearchQuery.value = ''
  showStudentsDialog.value = true
  try {
    const { data } = await apiClient.get(`/classrooms/${c.id}`)
    selectedClassroomForStudents.value = data
    // Update students count in classrooms array
    const idx = classrooms.value.findIndex(item => item.id === c.id)
    if (idx !== -1) {
      classrooms.value[idx] = data
    }
  } catch (err) {
    console.error('Failed to load classroom details', err)
  }
}

function formatDate(dateStr?: string) {
  if (!dateStr) return '-'
  try {
    const d = new Date(dateStr)
    return d.toLocaleDateString('th-TH', {
      year: 'numeric',
      month: 'short',
      day: 'numeric',
    })
  } catch {
    return dateStr
  }
}

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
