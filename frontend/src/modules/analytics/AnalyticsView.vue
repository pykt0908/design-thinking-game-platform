<template>
  <div>
    <div class="d-flex justify-space-between align-center mb-6">
      <div>
        <h1 class="text-h5 font-weight-bold text-slate-800">วิเคราะห์ผลการเรียนรู้ (Learning Analytics)</h1>
        <p class="text-body-2 text-grey">สถิติการเล่น อัตราการตอบถูก และข้อเสนอแนะจาก AI เพื่อปรับปรุงเกม</p>
      </div>

      <div class="max-w-xs">
        <v-select
          v-model="selectedGameId"
          :items="games"
          item-title="title"
          item-value="id"
          label="เลือกเกมการเรียนรู้"
          density="compact"
          hide-details
          rounded="lg"
          @update:model-value="fetchGameAnalytics"
        ></v-select>
      </div>
    </div>

    <div v-if="analytics">
      <!-- Performance Metrics per spec Section 33 -->
      <v-row class="mb-6">
        <v-col cols="12" sm="6" lg="3">
          <v-card class="pa-4 border-card rounded-xl">
            <div class="text-caption text-grey font-weight-bold mb-1">⭐ คะแนนเฉลี่ย</div>
            <div class="text-h4 font-weight-bold text-slate-800">
              {{ analytics.average_score }} <span class="text-caption text-grey">/ 100</span>
            </div>
          </v-card>
        </v-col>

        <v-col cols="12" sm="6" lg="3">
          <v-card class="pa-4 border-card rounded-xl">
            <div class="text-caption text-grey font-weight-bold mb-1">🏁 อัตราเล่นจบ (Completion)</div>
            <div class="text-h4 font-weight-bold text-primary">
              {{ analytics.completion_rate }}%
            </div>
          </v-card>
        </v-col>

        <v-col cols="12" sm="6" lg="3">
          <v-card class="pa-4 border-card rounded-xl">
            <div class="text-caption text-grey font-weight-bold mb-1">⏱️ เวลาเล่นเฉลี่ย</div>
            <div class="text-h4 font-weight-bold text-slate-800">
              {{ formatDuration(analytics.average_duration_seconds) }}
            </div>
          </v-card>
        </v-col>

        <v-col cols="12" sm="6" lg="3">
          <v-card class="pa-4 border-card rounded-xl">
            <div class="text-caption text-grey font-weight-bold mb-1">👥 จำนวนผู้เรียนที่เล่น</div>
            <div class="text-h4 font-weight-bold text-slate-800">
              {{ analytics.total_players }} <span class="text-caption text-grey">คน ({{ analytics.total_plays }} ครั้ง)</span>
            </div>
          </v-card>
        </v-col>
      </v-row>

      <v-row>
        <!-- Difficult Areas per spec Section 34 -->
        <v-col cols="12" md="7">
          <v-card class="pa-5 border-card rounded-xl mb-6">
            <div class="font-weight-bold text-subtitle-1 text-slate-800 mb-4 d-flex align-center">
              <v-icon icon="mdi-alert-octagon-outline" color="warning" class="mr-2"></v-icon>
              <span>วิเคราะห์ความยากรายข้อ (Question Difficulty Analysis)</span>
            </div>

            <div v-if="analytics.questions && analytics.questions.length > 0" class="d-flex flex-column gap-3">
              <div
                v-for="q in analytics.questions"
                :key="q.question_id"
                class="pa-4 border rounded-xl"
                :class="q.is_difficult ? 'bg-amber-lighten-5 border-warning' : 'bg-white'"
              >
                <div class="d-flex justify-space-between align-center mb-2">
                  <span class="font-weight-bold text-body-2 text-slate-800">{{ q.question_text }}</span>
                  <v-chip size="x-small" :color="q.is_difficult ? 'warning' : 'success'" class="font-weight-bold">
                    ตอบถูก {{ q.correct_rate }}%
                  </v-chip>
                </div>
                <v-progress-linear
                  :model-value="q.correct_rate"
                  :color="q.is_difficult ? 'warning' : 'success'"
                  height="6"
                  rounded
                ></v-progress-linear>
                <div class="text-caption text-grey mt-1">
                  ตอบถูก {{ q.correct_answers }} จากทั้งหมด {{ q.total_answers }} ครั้ง
                </div>
              </div>
            </div>

            <div v-else class="text-center pa-6 text-grey">
              ยังไม่มีข้อมูลการตอบคำถามในเกมนี้
            </div>
          </v-card>
        </v-col>

        <!-- AI Improvement Recommendations per spec Section 35 -->
        <v-col cols="12" md="5">
          <v-card class="pa-5 border-card rounded-xl mb-6 bg-indigo-lighten-5">
            <div class="font-weight-bold text-subtitle-1 text-primary mb-3 d-flex align-center">
              <v-avatar size="28" class="ai-gradient-bg mr-2">
                <v-icon icon="mdi-creation" color="white" size="16"></v-icon>
              </v-avatar>
              <span>✨ AI Improvement Recommendations</span>
            </div>

            <div v-if="analytics.ai_improvements && analytics.ai_improvements.length > 0">
              <v-card
                v-for="(imp, i) in analytics.ai_improvements"
                :key="i"
                class="pa-4 border-card rounded-xl mb-3 bg-white"
              >
                <div class="font-weight-bold text-subtitle-2 text-slate-800 mb-1">
                  {{ imp.title }}
                </div>
                <p class="text-caption text-slate-600 mb-3">
                  {{ imp.description }}
                </p>
                <v-btn
                  size="small"
                  color="primary"
                  variant="outlined"
                  rounded="lg"
                  class="font-weight-medium"
                  :to="`/games/${selectedGameId}/edit`"
                >
                  นำไปปรับในเวอร์ชันใหม่ (Apply to New Version) &rarr;
                </v-btn>
              </v-card>
            </div>

            <div v-else class="text-center pa-6 text-grey">
              เมื่อมีนักเรียนเข้าเล่น AI จะวิเคราะห์จุดติดขัดและเสนอแนะให้ที่นี่
            </div>
          </v-card>
        </v-col>
      </v-row>
    </div>

    <div v-else class="text-center pa-12 border-card rounded-xl bg-white">
      <v-progress-circular indeterminate color="primary"></v-progress-circular>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, onMounted } from 'vue'
import apiClient from '@/api/client'
import type { Game } from '@/types'

const games = ref<Game[]>([])
const selectedGameId = ref<number | null>(null)
const analytics = ref<any>(null)

function formatDuration(sec: number) {
  const m = Math.floor(sec / 60)
  const s = sec % 60
  return `${m} นาที ${s} วินาที`
}

async function fetchGameAnalytics(gameId: number) {
  try {
    const { data } = await apiClient.get(`/analytics/games/${gameId}`)
    analytics.value = data.analytics
  } catch (err) {
    console.error('Failed to load game analytics', err)
  }
}

onMounted(async () => {
  try {
    const { data } = await apiClient.get('/games')
    games.value = data.data || data
    if (games.value.length > 0) {
      selectedGameId.value = games.value[0].id
      await fetchGameAnalytics(games.value[0].id)
    }
  } catch (err) {
    console.error('Failed to load games', err)
  }
})
</script>

<style scoped>
.gap-3 { gap: 12px; }
.max-w-xs { max-width: 260px; }
</style>
