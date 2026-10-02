<template>
  <div>
    <div class="d-flex justify-space-between align-center mb-6">
      <div>
        <h1 class="text-h5 font-weight-bold text-slate-800">Design Thinking Projects</h1>
        <p class="text-body-2 text-grey">โปรเจกต์ออกแบบการเรียนรู้ผ่านกระบวนการ 5 ขั้นตอน</p>
      </div>

      <v-btn
        to="/projects/new"
        class="ai-gradient-bg text-white font-weight-bold px-5"
        prepend-icon="mdi-plus"
        rounded="lg"
      >
        + สร้างโปรเจกต์ใหม่
      </v-btn>
    </div>

    <v-row v-if="loading">
      <v-col v-for="i in 3" :key="i" cols="12" md="4">
        <v-skeleton-loader type="card" class="border-card rounded-xl"></v-skeleton-loader>
      </v-col>
    </v-row>

    <v-row v-else-if="projects.length > 0">
      <v-col v-for="p in projects" :key="p.id" cols="12" md="6" lg="4">
        <v-card class="pa-5 border-card rounded-xl h-100 d-flex flex-column justify-space-between hover-card">
          <div>
            <div class="d-flex justify-space-between align-center mb-3">
              <v-chip size="small" :color="getStepColor(p.current_step)" class="font-weight-medium">
                ขั้นตอนที่ {{ p.current_step }}/5
              </v-chip>
              <v-chip size="x-small" variant="tonal" color="primary">
                {{ p.status }}
              </v-chip>
            </div>

            <h2 class="text-subtitle-1 font-weight-bold text-slate-800 mb-2">
              {{ p.title }}
            </h2>

            <p class="text-body-2 text-grey-darken-1 mb-4 line-clamp-2">
              {{ p.description || 'ยังไม่มีคำอธิบาย' }}
            </p>

            <div class="text-caption text-grey mb-4">
              📚 วิชา: {{ p.subject || '-' }} &bull; ระดับชั้น: {{ p.grade_level || '-' }}
            </div>
          </div>

          <div class="d-flex justify-space-between align-center pt-3 border-t">
            <span class="text-caption text-grey">
              อัปเดต {{ formatDate(p.updated_at) }}
            </span>
            <v-btn
              :to="`/projects/${p.id}`"
              color="primary"
              variant="flat"
              size="small"
              rounded="lg"
              class="font-weight-medium"
            >
              เปิด Studio &rarr;
            </v-btn>
          </div>
        </v-card>
      </v-col>
    </v-row>

    <div v-else class="text-center pa-12 border-card rounded-xl bg-white">
      <v-avatar size="64" color="indigo-lighten-5" class="mb-4">
        <v-icon icon="mdi-lightbulb-outline" color="primary" size="36"></v-icon>
      </v-avatar>
      <h3 class="text-h6 font-weight-bold text-slate-800 mb-1">ยังไม่มีโปรเจกต์</h3>
      <p class="text-body-2 text-grey mb-4">เริ่มสร้างโปรเจกต์ Design Thinking เพื่อเปลี่ยนเนื้อหาบทเรียนเป็นเกม</p>
      <v-btn to="/projects/new" color="primary" rounded="lg">+ สร้างโปรเจกต์แรกของคุณ</v-btn>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, onMounted } from 'vue'
import apiClient from '@/api/client'
import type { DesignProject } from '@/types'

const projects = ref<DesignProject[]>([])
const loading = ref(true)

function getStepColor(step: number) {
  if (step >= 5) return 'success'
  if (step >= 3) return 'primary'
  return 'warning'
}

function formatDate(dateStr?: string) {
  if (!dateStr) return '-'
  const d = new Date(dateStr)
  return d.toLocaleDateString('th-TH', { month: 'short', day: 'numeric' })
}

onMounted(async () => {
  try {
    const { data } = await apiClient.get('/projects')
    projects.value = data.data || data
  } finally {
    loading.value = false
  }
})
</script>

<style scoped>
.hover-card {
  transition: transform 0.2s ease, box-shadow 0.2s ease;
}
.hover-card:hover {
  transform: translateY(-2px);
  box-shadow: 0 8px 24px rgba(99, 102, 241, 0.08);
}
.line-clamp-2 {
  display: -webkit-box;
  -webkit-line-clamp: 2;
  -webkit-box-orient: vertical;
  overflow: hidden;
}
</style>
