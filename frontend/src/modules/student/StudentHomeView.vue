<template>
  <div class="max-w-5xl mx-auto">
    <!-- Hero Greeting per spec Section 29 -->
    <v-card class="pa-8 border-card rounded-2xl mb-6 bg-white overflow-hidden">
      <v-row align="center">
        <v-col cols="12" md="8">
          <h1 class="text-h4 font-weight-bold text-slate-800 mb-2">
            สวัสดี, {{ authStore.user?.name }} 👋
          </h1>
          <p class="text-body-1 text-grey mb-6">
            พร้อมสำหรับภารกิจการเรียนรู้วันนี้หรือยัง? เล่นเกมเพื่อสะสมคะแนนและเหรียญรางวัล!
          </p>

          <!-- Join Classroom with Code per spec Section 7 -->
          <div class="d-flex align-center gap-2 max-w-md">
            <v-text-field
              v-model="joinCodeInput"
              label="กรอกรหัสห้องเรียน (Join Code)"
              placeholder="เช่น DTG-SCI01"
              density="compact"
              hide-details
              rounded="lg"
            ></v-text-field>
            <v-btn
              color="primary"
              rounded="lg"
              class="font-weight-bold px-4"
              :loading="joining"
              @click="handleJoinClassroom"
            >
              เข้าร่วม
            </v-btn>
          </div>
        </v-col>
        <v-col cols="12" md="4" class="text-right d-none d-md-block">
          <v-icon icon="mdi-gamepad-variant" size="140" color="purple-lighten-4" class="opacity-40"></v-icon>
        </v-col>
      </v-row>
    </v-card>

    <!-- Assigned Games per spec Section 27 -->
    <div class="mb-6">
      <div class="d-flex align-center mb-4">
        <v-icon icon="mdi-controller" color="primary" class="mr-2"></v-icon>
        <h2 class="text-h6 font-weight-bold text-slate-800">เกมที่ได้รับมอบหมาย (Assigned Games)</h2>
      </div>

      <v-row v-if="assignedGames.length > 0">
        <v-col v-for="item in assignedGames" :key="item.id" cols="12" sm="6" md="4">
          <v-card class="border-card rounded-2xl overflow-hidden h-100 d-flex flex-column justify-space-between hover-card">
            <div>
              <v-img
                :src="item.game?.cover_image || 'https://images.unsplash.com/photo-1509062522246-3755977927d7?w=600&auto=format&fit=crop&q=80'"
                height="150"
                cover
              >
                <div class="pa-2 text-right">
                  <v-chip size="x-small" color="primary" variant="flat" class="font-weight-bold">
                    เกณฑ์ผ่าน {{ item.passing_score }}%
                  </v-chip>
                </div>
              </v-img>

              <div class="pa-4">
                <div class="text-caption text-primary font-weight-bold mb-1">
                  {{ item.classroom?.name || 'ห้องเรียน' }}
                </div>
                <h3 class="text-subtitle-1 font-weight-bold text-slate-800 mb-2">
                  {{ item.game?.title }}
                </h3>
                <p class="text-caption text-grey mb-3">
                  {{ item.game?.description || 'ภารกิจเกมการเรียนรู้' }}
                </p>
              </div>
            </div>

            <div class="pa-4 pt-0">
              <v-btn
                :to="`/play/${item.game?.public_id}`"
                target="_blank"
                class="ai-gradient-bg text-white font-weight-bold"
                block
                rounded="lg"
                prepend-icon="mdi-play"
              >
                เริ่มเล่นเกม (Play Game) &rarr;
              </v-btn>
            </div>
          </v-card>
        </v-col>
      </v-row>

      <div v-else class="text-center pa-8 border-card rounded-2xl bg-white">
        <v-icon icon="mdi-emoticon-happy-outline" size="48" color="grey" class="mb-2"></v-icon>
        <div class="text-subtitle-1 font-weight-medium text-slate-800">ยังไม่มีเกมใหม่ที่ได้รับมอบหมาย</div>
        <p class="text-caption text-grey">ใส่รหัสห้องเรียนจากคุณครูเพื่อเข้าร่วมห้องเรียนและรับภารกิจเกม</p>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, onMounted } from 'vue'
import { useAuthStore } from '@/stores/auth'
import apiClient from '@/api/client'
import type { GameAssignment } from '@/types'

const authStore = useAuthStore()
const joinCodeInput = ref('')
const joining = ref(false)
const assignedGames = ref<GameAssignment[]>([])

async function loadStudentData() {
  try {
    const { data: classrooms } = await apiClient.get('/classrooms')
    const list: GameAssignment[] = []
    classrooms.forEach((c: any) => {
      if (c.assignments) {
        c.assignments.forEach((a: any) => {
          list.push({ ...a, classroom: c })
        })
      }
    })
    assignedGames.value = list
  } catch (err) {
    console.error('Failed to load student data', err)
  }
}

async function handleJoinClassroom() {
  if (!joinCodeInput.value) return
  joining.value = true
  try {
    const { data } = await apiClient.post('/classrooms/join', {
      code: joinCodeInput.value,
    })
    alert(data.message)
    joinCodeInput.value = ''
    await loadStudentData()
  } catch (err: any) {
    alert(err.response?.data?.message || 'ไม่สามารถเข้าร่วมห้องเรียนได้')
  } finally {
    joining.value = false
  }
}

onMounted(loadStudentData)
</script>

<style scoped>
.hover-card {
  transition: transform 0.2s ease;
}
.hover-card:hover {
  transform: translateY(-2px);
}
.max-w-5xl { max-width: 1024px; }
.max-w-md { max-width: 380px; }
.gap-2 { gap: 8px; }
</style>
