<template>
  <div>
    <!-- Header -->
    <div class="d-flex flex-wrap justify-space-between align-center gap-3 mb-6">
      <div>
        <h1 class="text-h5 font-weight-bold text-slate-800 d-flex align-center">
          <v-icon icon="mdi-lightbulb-on-outline" color="primary" class="mr-2"></v-icon>
          Design Thinking Projects
        </h1>
        <p class="text-body-2 text-grey">โปรเจกต์ออกแบบการเรียนรู้ผ่านกระบวนการ 5 ขั้นตอนสู่เกมการเรียนรู้</p>
      </div>

      <v-btn
        to="/projects/new"
        class="ai-gradient-bg text-white font-weight-bold px-5 elevation-2"
        prepend-icon="mdi-plus"
        rounded="lg"
      >
        + สร้างโปรเจกต์ใหม่
      </v-btn>
    </div>

    <!-- Loading Skeletons -->
    <v-row v-if="loading">
      <v-col v-for="i in 3" :key="i" cols="12" md="4">
        <v-skeleton-loader type="card" class="border-card rounded-xl"></v-skeleton-loader>
      </v-col>
    </v-row>

    <!-- Projects Grid -->
    <v-row v-else-if="projects.length > 0">
      <v-col v-for="p in projects" :key="p.id" cols="12" md="6" lg="4">
        <v-card class="pa-5 border-card rounded-2xl h-100 d-flex flex-column justify-space-between hover-card bg-white elevation-1">
          <div>
            <!-- Top Status & Actions -->
            <div class="d-flex justify-space-between align-center mb-3">
              <div class="d-flex align-center gap-2">
                <v-chip size="small" :color="getStepColor(p.current_step)" class="font-weight-medium">
                  ขั้นตอนที่ {{ p.current_step }}/5
                </v-chip>
                <v-chip size="x-small" variant="tonal" color="primary">
                  {{ p.status }}
                </v-chip>
              </div>

              <!-- Delete Button -->
              <v-btn
                icon="mdi-trash-can-outline"
                size="small"
                variant="text"
                color="grey"
                class="delete-btn"
                @click="handleDeleteProject(p)"
                title="ลบโปรเจกต์"
              ></v-btn>
            </div>

            <!-- Title & Description -->
            <h2 class="text-subtitle-1 font-weight-bold text-slate-800 mb-2" :title="p.title">
              {{ p.title }}
            </h2>

            <p class="text-body-2 text-grey-darken-1 mb-4 line-clamp-2" :title="p.description">
              {{ p.description || 'ยังไม่มีคำอธิบายโปรเจกต์' }}
            </p>

            <div class="text-caption text-grey mb-4 d-flex align-center gap-2">
              <span>วิชา: <strong>{{ p.subject || '-' }}</strong></span>
              <span>&bull;</span>
              <span>ระดับชั้น: <strong>{{ p.grade_level || '-' }}</strong></span>
            </div>
          </div>

          <!-- Footer Actions -->
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
              class="font-weight-bold px-4"
            >
              เปิด Studio &rarr;
            </v-btn>
          </div>
        </v-card>
      </v-col>
    </v-row>

    <!-- Empty State -->
    <div v-else class="text-center pa-12 border-card rounded-2xl hero-gradient-card bg-white">
      <div class="mb-4">
        <img
          src="@/assets/images/studio_hero.jpg"
          alt="Create Project"
          class="floating-asset rounded-2xl elevation-4"
          style="width: 140px; height: 140px; object-fit: cover; border: 3px solid rgba(198, 112, 255, 0.4);"
        />
      </div>
      <h3 class="text-h6 font-weight-bold text-slate-800 mb-1">ยังไม่มีโปรเจกต์การเรียนรู้</h3>
      <p class="text-body-2 text-grey mb-5">เริ่มสร้างโปรเจกต์ Design Thinking เพื่อเปลี่ยนเนื้อหาบทเรียนเป็นเกมที่สนุกและได้ผลลัพธ์</p>
      <v-btn to="/projects/new" color="primary" rounded="lg" size="large" class="font-weight-bold elevation-2">
        + สร้างโปรเจกต์แรกของคุณ
      </v-btn>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, onMounted } from 'vue'
import apiClient from '@/api/client'
import { useAlertStore } from '@/stores/alert'
import type { DesignProject } from '@/types'

const alertStore = useAlertStore()

const projects = ref<DesignProject[]>([])
const loading = ref(true)

function getStepColor(step: number) {
  if (step >= 5) return 'success'
  if (step >= 3) return 'primary'
  return 'warning'
}

function formatDate(dateStr?: string) {
  if (!dateStr) return '-'
  try {
    const d = new Date(dateStr)
    return d.toLocaleDateString('th-TH', { month: 'short', day: 'numeric', year: 'numeric' })
  } catch {
    return dateStr
  }
}

async function fetchProjects() {
  loading.value = true
  try {
    const { data } = await apiClient.get('/projects')
    projects.value = data.data || data
  } finally {
    loading.value = false
  }
}

async function handleDeleteProject(p: DesignProject) {
  const confirmed = await alertStore.confirm(
    `คุณต้องการลบโปรเจกต์ "${p.title}" ใช่หรือไม่? ข้อมูลขั้นตอน Design Thinking ทั้ง 5 ขั้นตอนจะถูกลบอย่างถาวร`,
    'ยืนยันการลบโปรเจกต์',
    { confirmText: 'ลบโปรเจกต์', cancelText: 'ยกเลิก', type: 'error' }
  )

  if (!confirmed) return

  try {
    await apiClient.delete(`/projects/${p.id}`)
    alertStore.success(`ลบโปรเจกต์ "${p.title}" เรียบร้อยแล้ว`, 'สำเร็จ')
    await fetchProjects()
  } catch (err: any) {
    alertStore.error(err.response?.data?.message || 'ไม่สามารถลบโปรเจกต์ได้', 'เกิดข้อผิดพลาด')
  }
}

onMounted(fetchProjects)
</script>

<style scoped>
.gap-2 { gap: 8px; }
.gap-3 { gap: 12px; }

.hover-card {
  transition: transform 0.25s cubic-bezier(0.16, 1, 0.3, 1), box-shadow 0.25s ease;
  border-color: #edd4f8 !important;
}

.hover-card:hover {
  transform: translateY(-4px);
  box-shadow: 0 12px 24px -6px rgba(61, 0, 102, 0.15) !important;
}

.delete-btn:hover {
  color: #ef4444 !important;
  background-color: #fee2e2 !important;
}

.line-clamp-2 {
  display: -webkit-box;
  -webkit-line-clamp: 2;
  -webkit-box-orient: vertical;
  overflow: hidden;
  text-overflow: ellipsis;
}
</style>
