<template>
  <div>
    <!-- Hero Greeting per spec Section 12 -->
    <v-card class="pa-6 pa-md-8 mb-6 border-card rounded-2xl hero-gradient-card overflow-hidden position-relative elevation-1">
      <v-row align="center">
        <v-col cols="12" md="8">
          <div class="d-inline-flex align-center px-3 py-1 rounded-pill bg-purple-lighten-5 text-primary text-caption font-weight-bold mb-3 border">
            <v-icon icon="mdi-sparkles" size="14" color="secondary" class="mr-1"></v-icon>
            พื้นที่สร้างสรรค์เกมนวัตกรรมการศึกษา
          </div>
          <div class="d-flex align-center mb-2">
            <h1 class="text-h4 font-weight-bold text-slate-900">
              สวัสดี, {{ authStore.user?.name }}
            </h1>
          </div>
          <p class="text-body-1 text-slate-700 mb-6">
            วันนี้อยากสร้างประสบการณ์การเรียนรู้อะไรใหม่ๆ ให้กับผู้เรียนของคุณ? เปลี่ยนไอเดียสู่เกมได้ในไม่กี่นาที
          </p>
          <div class="d-flex flex-wrap gap-3">
            <v-btn
              color="primary"
              to="/projects/new"
              prepend-icon="mdi-plus"
              rounded="lg"
              class="px-5 font-weight-bold elevation-2"
              size="large"
            >
              + สร้างโปรเจกต์ใหม่
            </v-btn>
            <v-btn
              class="ai-gradient-bg text-white px-5 font-weight-bold elevation-2"
              to="/projects"
              prepend-icon="mdi-creation"
              rounded="lg"
              size="large"
            >
              สร้างเกมด้วย AI
            </v-btn>
            <v-btn
              variant="outlined"
              to="/classrooms"
              prepend-icon="mdi-google-classroom"
              rounded="lg"
              color="primary"
              class="font-weight-medium"
              size="large"
            >
              จัดการห้องเรียน
            </v-btn>
          </div>
        </v-col>
        <v-col cols="12" md="4" class="text-center d-none d-md-block">
          <div class="d-inline-block position-relative">
            <img
              src="@/assets/images/studio_hero.jpg"
              alt="Design Thinking Studio"
              class="floating-asset rounded-2xl elevation-6"
              style="width: 170px; height: 170px; object-fit: cover; border: 3px solid rgba(198, 112, 255, 0.4);"
            />
          </div>
        </v-col>
      </v-row>
    </v-card>

    <!-- ซ่อนส่วนการ์ดสถิติ และส่วนโปรเจกต์/เกมล่าสุด ไว้ชั่วคราวตามคำขอ -->
  </div>
</template>

<script setup lang="ts">
import { ref, onMounted } from 'vue'
import { useAuthStore } from '@/stores/auth'
import apiClient from '@/api/client'
import type { DesignProject, Game } from '@/types'

const authStore = useAuthStore()

const metrics = ref({
  games_count: 0,
  published_games_count: 0,
  students_count: 0,
  classrooms_count: 0,
  total_plays: 0,
  average_score: 0,
  completion_rate: 0,
})

const projects = ref<DesignProject[]>([])
const games = ref<Game[]>([])

function getStepColor(step: number) {
  if (step >= 5) return 'success'
  if (step >= 3) return 'primary'
  return 'warning'
}

onMounted(async () => {
  try {
    const [metricsRes, projectsRes, gamesRes] = await Promise.all([
      apiClient.get('/analytics/dashboard'),
      apiClient.get('/projects'),
      apiClient.get('/games'),
    ])
    metrics.value = metricsRes.data
    projects.value = projectsRes.data.data || projectsRes.data
    games.value = gamesRes.data.data || gamesRes.data
  } catch (err) {
    console.error('Failed to load dashboard data', err)
  }
})
</script>

<style scoped>
.gap-3 {
  gap: 12px;
}
.gap-1 {
  gap: 4px;
}
</style>
