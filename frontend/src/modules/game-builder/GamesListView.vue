<template>
  <div>
    <div class="d-flex justify-space-between align-center mb-6">
      <div>
        <h1 class="text-h5 font-weight-bold text-slate-800">เกมการเรียนรู้ของฉัน (My Games)</h1>
        <p class="text-body-2 text-grey">จัดการ แก้ไข และมอบหมายเกมให้กับห้องเรียน</p>
      </div>
    </div>

    <v-row v-if="loading">
      <v-col v-for="i in 3" :key="i" cols="12" md="4">
        <v-skeleton-loader type="card" class="border-card rounded-xl"></v-skeleton-loader>
      </v-col>
    </v-row>

    <v-row v-else-if="games.length > 0">
      <v-col v-for="g in games" :key="g.id" cols="12" md="6" lg="4">
        <v-card class="border-card rounded-xl overflow-hidden h-100 d-flex flex-column justify-space-between hover-card">
          <div class="position-relative">
            <v-img
              :src="g.cover_image || 'https://images.unsplash.com/photo-1509062522246-3755977927d7?w=600&auto=format&fit=crop&q=80'"
              height="160"
              cover
              class="bg-grey-lighten-2"
            >
              <div class="pa-3 d-flex justify-space-between">
                <v-chip size="small" :color="g.status === 'published' ? 'success' : 'grey'" variant="flat" class="font-weight-bold">
                  {{ g.status === 'published' ? 'เผยแพร่แล้ว' : 'ฉบับร่าง' }}
                </v-chip>
                <v-chip size="small" color="black" variant="flat" class="font-weight-bold">
                  v{{ g.current_version?.version_number || '1.0' }}
                </v-chip>
              </div>
            </v-img>
          </div>

          <div class="pa-5 flex-grow-1">
            <h2 class="text-subtitle-1 font-weight-bold text-slate-800 mb-1">
              {{ g.title }}
            </h2>
            <p class="text-caption text-grey-darken-1 mb-3 line-clamp-2">
              {{ g.description || 'เกมการเรียนรู้แบบโต้ตอบ' }}
            </p>
            <div class="text-caption text-grey">
              รหัสสาธารณะ: <strong class="text-primary">{{ g.public_id }}</strong>
            </div>
          </div>

          <div class="px-5 pb-5 pt-2 d-flex justify-space-between align-center border-t gap-2">
            <v-btn
              :to="`/play/${g.public_id}`"
              target="_blank"
              variant="outlined"
              color="success"
              size="small"
              rounded="lg"
              prepend-icon="mdi-play"
            >
              เล่นเกม
            </v-btn>

            <v-btn
              :to="`/games/${g.id}/edit`"
              color="primary"
              variant="flat"
              size="small"
              rounded="lg"
              prepend-icon="mdi-pencil"
            >
              เปิด Editor
            </v-btn>
          </div>
        </v-card>
      </v-col>
    </v-row>

    <div v-else class="text-center pa-12 border-card rounded-xl bg-white">
      <v-avatar size="64" color="purple-lighten-5" class="mb-4">
        <v-icon icon="mdi-gamepad-variant-outline" color="primary" size="36"></v-icon>
      </v-avatar>
      <h3 class="text-h6 font-weight-bold text-slate-800 mb-1">ยังไม่มีเกม</h3>
      <p class="text-body-2 text-grey mb-4">สร้างเกมได้ง่ายๆ ผ่านกระบวนการ Design Thinking 5 ขั้นตอน</p>
      <v-btn to="/projects/new" color="primary" rounded="lg">+ สร้างโปรเจกต์ใหม่</v-btn>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, onMounted } from 'vue'
import apiClient from '@/api/client'
import type { Game } from '@/types'

const games = ref<Game[]>([])
const loading = ref(true)

onMounted(async () => {
  try {
    const { data } = await apiClient.get('/games')
    games.value = data.data || data
  } finally {
    loading.value = false
  }
})
</script>

<style scoped>
.hover-card {
  transition: transform 0.2s ease;
}
.hover-card:hover {
  transform: translateY(-2px);
}
.line-clamp-2 {
  display: -webkit-box;
  -webkit-line-clamp: 2;
  -webkit-box-orient: vertical;
  overflow: hidden;
}
.gap-2 { gap: 8px; }
</style>
