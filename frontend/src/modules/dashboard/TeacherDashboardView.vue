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

    <!-- Statistics Cards per spec Section 12 -->
    <v-row class="mb-6">
      <v-col cols="12" sm="6" lg="3">
        <v-card class="pa-4 border-card rounded-xl">
          <div class="d-flex justify-space-between align-center mb-2">
            <span class="text-caption font-weight-bold text-grey">เกมทั้งหมด</span>
            <v-avatar size="36" color="purple-lighten-5">
              <v-icon icon="mdi-gamepad-variant" color="primary" size="20"></v-icon>
            </v-avatar>
          </div>
          <div class="text-h4 font-weight-bold text-slate-800">
            {{ metrics.games_count }}
          </div>
          <div class="text-caption text-success font-weight-medium mt-1">
            <v-icon icon="mdi-check-circle-outline" size="14"></v-icon>
            เผยแพร่แล้ว {{ metrics.published_games_count }} เกม
          </div>
        </v-card>
      </v-col>

      <v-col cols="12" sm="6" lg="3">
        <v-card class="pa-4 border-card rounded-xl">
          <div class="d-flex justify-space-between align-center mb-2">
            <span class="text-caption font-weight-bold text-grey">นักเรียนในระบบ</span>
            <v-avatar size="36" color="purple-lighten-5">
              <v-icon icon="mdi-account-group" color="secondary" size="20"></v-icon>
            </v-avatar>
          </div>
          <div class="text-h4 font-weight-bold text-slate-800">
            {{ metrics.students_count }}
          </div>
          <div class="text-caption text-grey mt-1">
            ใน {{ metrics.classrooms_count }} ห้องเรียน
          </div>
        </v-card>
      </v-col>

      <v-col cols="12" sm="6" lg="3">
        <v-card class="pa-4 border-card rounded-xl">
          <div class="d-flex justify-space-between align-center mb-2">
            <span class="text-caption font-weight-bold text-grey">ครั้งที่เข้าเล่น</span>
            <v-avatar size="36" color="cyan-lighten-5">
              <v-icon icon="mdi-play-circle" color="accent" size="20"></v-icon>
            </v-avatar>
          </div>
          <div class="text-h4 font-weight-bold text-slate-800">
            {{ metrics.total_plays }}
          </div>
          <div class="text-caption text-accent font-weight-medium mt-1">
            อัตราสำเร็จ {{ metrics.completion_rate }}%
          </div>
        </v-card>
      </v-col>

      <v-col cols="12" sm="6" lg="3">
        <v-card class="pa-4 border-card rounded-xl">
          <div class="d-flex justify-space-between align-center mb-2">
            <span class="text-caption font-weight-bold text-grey">คะแนนเฉลี่ย</span>
            <v-avatar size="36" color="amber-lighten-5">
              <v-icon icon="mdi-trophy-outline" color="warning" size="20"></v-icon>
            </v-avatar>
          </div>
          <div class="text-h4 font-weight-bold text-slate-800">
            {{ metrics.average_score }} <span class="text-caption text-grey">/ 100</span>
          </div>
          <div class="text-caption text-grey mt-1">
            จากผู้เรียนที่ทำภารกิจสำเร็จ
          </div>
        </v-card>
      </v-col>
    </v-row>

    <!-- Content Sections: Recent Projects & Games -->
    <v-row>
      <v-col cols="12" lg="7">
        <v-card class="pa-5 border-card rounded-xl mb-6">
          <div class="d-flex justify-space-between align-center mb-4">
            <div class="font-weight-bold text-subtitle-1 text-slate-800 d-flex align-center">
              <v-icon icon="mdi-lightbulb-on-outline" color="primary" class="mr-2"></v-icon>
              <span>Design Thinking Projects ล่าสุด</span>
            </div>
            <v-btn variant="text" size="small" color="primary" to="/projects">
              ดูทั้งหมด &rarr;
            </v-btn>
          </div>

          <v-list v-if="projects.length > 0" lines="two" class="pa-0">
            <v-list-item
              v-for="p in projects"
              :key="p.id"
              :to="`/projects/${p.id}`"
              class="border rounded-lg mb-3 pa-3"
            >
              <template #prepend>
                <v-avatar color="purple-lighten-5" rounded="lg" class="mr-3">
                  <v-icon icon="mdi-lightbulb-on" color="primary"></v-icon>
                </v-avatar>
              </template>

              <v-list-item-title class="font-weight-bold text-slate-800">
                {{ p.title }}
              </v-list-item-title>

              <v-list-item-subtitle class="text-caption text-grey-darken-1">
                วิชา {{ p.subject || 'ทั่วไป' }} &bull; ระดับชั้น {{ p.grade_level || '-' }}
              </v-list-item-subtitle>

              <template #append>
                <div class="text-right">
                  <v-chip size="small" :color="getStepColor(p.current_step)" class="font-weight-medium">
                    ขั้นตอนที่ {{ p.current_step }}/5
                  </v-chip>
                </div>
              </template>
            </v-list-item>
          </v-list>

          <div v-else class="text-center pa-6 text-grey">
            <p>ยังไม่มีโปรเจกต์</p>
            <v-btn to="/projects/new" color="primary" size="small" class="mt-2">+ สร้างโปรเจกต์แรกของคุณ</v-btn>
          </div>
        </v-card>
      </v-col>

      <v-col cols="12" lg="5">
        <v-card class="pa-5 border-card rounded-xl mb-6">
          <div class="d-flex justify-space-between align-center mb-4">
            <div class="font-weight-bold text-subtitle-1 text-slate-800 d-flex align-center">
              <v-icon icon="mdi-gamepad-variant-outline" color="primary" class="mr-2"></v-icon>
              <span>เกมการเรียนรู้ (My Games)</span>
            </div>
            <v-btn variant="text" size="small" color="primary" to="/games">
              ดูทั้งหมด &rarr;
            </v-btn>
          </div>

          <v-list v-if="games.length > 0" class="pa-0">
            <v-list-item
              v-for="g in games"
              :key="g.id"
              class="border rounded-lg mb-3 pa-3"
            >
              <template #prepend>
                <v-avatar rounded="lg" color="grey-lighten-4" size="48" class="mr-3">
                  <v-img v-if="g.cover_image" :src="g.cover_image" cover></v-img>
                  <v-icon v-else icon="mdi-gamepad-variant" color="grey"></v-icon>
                </v-avatar>
              </template>

              <v-list-item-title class="font-weight-bold text-subtitle-2 text-slate-800">
                {{ g.title }}
              </v-list-item-title>

              <v-list-item-subtitle class="text-caption text-grey">
                รหัส: {{ g.public_id }} &bull; {{ g.status === 'published' ? 'เผยแพร่แล้ว' : 'ฉบับร่าง' }}
              </v-list-item-subtitle>

              <template #append>
                <div class="d-flex gap-1">
                  <v-btn
                    icon="mdi-pencil-outline"
                    variant="text"
                    size="small"
                    color="primary"
                    :to="`/games/${g.id}/edit`"
                    title="แก้ไขใน Game Builder"
                  ></v-btn>
                  <v-btn
                    icon="mdi-play-circle-outline"
                    variant="text"
                    size="small"
                    color="success"
                    :to="`/play/${g.public_id}`"
                    target="_blank"
                    title="ทดลองเล่นเกม"
                  ></v-btn>
                </div>
              </template>
            </v-list-item>
          </v-list>

          <div v-else class="text-center pa-6 text-grey">
            <p>ยังไม่มีเกมที่สร้าง</p>
          </div>
        </v-card>
      </v-col>
    </v-row>
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
