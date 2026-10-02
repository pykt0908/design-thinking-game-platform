<template>
  <div>
    <!-- Header -->
    <div class="d-flex flex-wrap justify-space-between align-center gap-3 mb-6">
      <div>
        <h1 class="text-h5 font-weight-bold text-slate-800 d-flex align-center">
          <v-icon icon="mdi-gamepad-variant" color="primary" class="mr-2"></v-icon>
          เกมการเรียนรู้ของฉัน (My Games)
        </h1>
        <p class="text-body-2 text-grey">จัดการ แก้ไข ทดลองเล่น และมอบหมายเกมให้กับห้องเรียน</p>
      </div>

      <v-btn
        to="/projects/new"
        color="primary"
        rounded="lg"
        prepend-icon="mdi-plus"
        class="font-weight-bold px-5 elevation-2"
      >
        + สร้างเกมใหม่
      </v-btn>
    </div>

    <!-- Loading Skeleton (5 columns) -->
    <div v-if="loading" class="game-grid-5">
      <div v-for="i in 5" :key="i">
        <v-skeleton-loader type="image, article" class="border-card rounded-2xl"></v-skeleton-loader>
      </div>
    </div>

    <!-- Games Grid (5 Columns on Desktop) -->
    <div v-else-if="games.length > 0" class="game-grid-5">
      <div v-for="g in games" :key="g.id" class="game-grid-item">
        <v-card class="border-card rounded-2xl overflow-hidden h-100 d-flex flex-column justify-space-between hover-card bg-white elevation-1">
          <!-- Game Cover Banner -->
          <div class="position-relative game-cover-wrapper">
            <v-img
              :src="g.cover_image || 'https://images.unsplash.com/photo-1509062522246-3755977927d7?w=600&auto=format&fit=crop&q=80'"
              height="135"
              cover
              class="bg-grey-lighten-2"
            >
              <div class="pa-2 d-flex justify-space-between align-start">
                <v-chip
                  size="x-small"
                  :color="g.status === 'published' ? 'success' : 'grey-darken-3'"
                  variant="flat"
                  class="font-weight-bold text-white shadow-sm"
                >
                  {{ g.status === 'published' ? 'เผยแพร่แล้ว' : 'ฉบับร่าง' }}
                </v-chip>
                <v-chip size="x-small" color="black" variant="flat" class="font-weight-bold text-white shadow-sm">
                  v{{ g.current_version?.version_number || '1.0' }}
                </v-chip>
              </div>
            </v-img>
          </div>

          <!-- Game Card Body -->
          <div class="pa-3 flex-grow-1 d-flex flex-column justify-space-between">
            <div>
              <h2 class="text-subtitle-2 font-weight-bold text-slate-800 mb-1 text-truncate" :title="g.title">
                {{ g.title }}
              </h2>
              <p class="text-caption text-grey-darken-1 mb-2 line-clamp-2" :title="g.description" style="min-height: 32px;">
                {{ g.description || 'เกมการเรียนรู้แบบโต้ตอบ' }}
              </p>
            </div>

            <!-- Public Code Pill -->
            <div class="bg-purple-lighten-5 rounded-lg px-2 py-1 mb-2 d-flex justify-space-between align-center">
              <span class="text-caption text-grey-darken-2" style="font-size: 11px;">รหัสเกม:</span>
              <strong class="text-caption font-weight-bold text-primary">{{ g.public_id }}</strong>
            </div>
          </div>

          <!-- Card Actions Footer -->
          <div class="px-3 pb-3 pt-1 d-flex justify-space-between align-center border-t gap-2">
            <v-btn
              :to="`/play/${g.public_id}?preview=true`"
              target="_blank"
              variant="outlined"
              color="success"
              size="small"
              rounded="lg"
              class="flex-1-1 px-1 font-weight-bold text-caption"
              prepend-icon="mdi-play"
            >
              ทดลองเล่น
            </v-btn>

            <v-btn
              :to="`/games/${g.id}/edit`"
              color="primary"
              variant="flat"
              size="small"
              rounded="lg"
              class="flex-1-1 px-1 font-weight-bold text-caption"
              prepend-icon="mdi-pencil"
            >
              แก้ไข
            </v-btn>

            <v-btn
              icon="mdi-trash-can-outline"
              color="error"
              variant="text"
              size="small"
              rounded="lg"
              class="delete-game-btn"
              title="ลบเกม"
              @click.stop="handleDeleteGame(g)"
            ></v-btn>
          </div>
        </v-card>
      </div>
    </div>

    <!-- Empty State -->
    <div v-else class="text-center pa-12 border-card rounded-2xl hero-gradient-card bg-white">
      <div class="mb-4">
        <img
          src="@/assets/images/ai_game_wizard.jpg"
          alt="Game Library"
          class="floating-asset rounded-2xl elevation-4"
          style="width: 140px; height: 140px; object-fit: cover; border: 3px solid rgba(198, 112, 255, 0.4);"
        />
      </div>
      <h3 class="text-h6 font-weight-bold text-slate-800 mb-1">ยังไม่มีเกมในคลังของคุณ</h3>
      <p class="text-body-2 text-grey mb-5">สร้างเกมได้ง่ายๆ ผ่านกระบวนการ Design Thinking 5 ขั้นตอน หรือปล่อยให้ AI ช่วยสร้างเกมอัตโนมัติ</p>
      <v-btn to="/projects/new" color="primary" rounded="lg" size="large" class="font-weight-bold elevation-2">
        + สร้างโปรเจกต์เพื่อสร้างเกม
      </v-btn>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, onMounted } from 'vue'
import apiClient from '@/api/client'
import { useAlertStore } from '@/stores/alert'
import type { Game } from '@/types'

const alertStore = useAlertStore()
const games = ref<Game[]>([])
const loading = ref(true)

async function fetchGames() {
  loading.value = true
  try {
    const { data } = await apiClient.get('/games')
    games.value = data.data || data
  } finally {
    loading.value = false
  }
}

async function handleDeleteGame(game: Game) {
  const confirmed = await alertStore.confirm(
    `คุณต้องการลบเกม "${game.title}" (รหัส: ${game.public_id}) ใช่หรือไม่? ข้อมูลการเล่นทั้งหมดของเกมนี้จะถูกลบและไม่สามารถกู้คืนได้`,
    'ยืนยันการลบเกม',
    {
      confirmText: 'ลบเกมทันที',
      cancelText: 'ยกเลิก',
      type: 'error',
    }
  )

  if (!confirmed) return

  try {
    await apiClient.delete(`/games/${game.id}`)
    games.value = games.value.filter((g) => g.id !== game.id)
    alertStore.success('ลบเกมเรียบร้อยแล้ว')
  } catch (err: any) {
    console.error('Failed to delete game', err)
    alertStore.error(err.response?.data?.message || 'ไม่สามารถลบเกมได้')
  }
}

onMounted(() => {
  fetchGames()
})
</script>

<style scoped>
.gap-2 { gap: 8px; }
.gap-3 { gap: 12px; }

/* 5 Column Grid System */
.game-grid-5 {
  display: grid;
  grid-template-columns: repeat(5, minmax(0, 1fr));
  gap: 16px;
}

@media (max-width: 1279px) {
  .game-grid-5 {
    grid-template-columns: repeat(4, minmax(0, 1fr));
  }
}

@media (max-width: 960px) {
  .game-grid-5 {
    grid-template-columns: repeat(3, minmax(0, 1fr));
  }
}

@media (max-width: 640px) {
  .game-grid-5 {
    grid-template-columns: repeat(2, minmax(0, 1fr));
    gap: 12px;
  }
}

@media (max-width: 420px) {
  .game-grid-5 {
    grid-template-columns: 1fr;
  }
}

.hover-card {
  transition: transform 0.25s cubic-bezier(0.16, 1, 0.3, 1), box-shadow 0.25s ease;
  border-color: #edd4f8 !important;
}

.hover-card:hover {
  transform: translateY(-4px);
  box-shadow: 0 12px 24px -6px rgba(61, 0, 102, 0.15), 0 4px 10px -2px rgba(198, 112, 255, 0.2) !important;
}

.game-cover-wrapper {
  overflow: hidden;
}

.line-clamp-2 {
  display: -webkit-box;
  -webkit-line-clamp: 2;
  -webkit-box-orient: vertical;
  overflow: hidden;
  text-overflow: ellipsis;
}
</style>
