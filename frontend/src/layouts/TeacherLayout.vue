<template>
  <v-app>
    <!-- Top Bar (64px) per spec Section 11 -->
    <v-app-bar flat height="64" class="border-b" color="surface">
      <v-app-bar-nav-icon @click="drawer = !drawer" color="on-surface-variant"></v-app-bar-nav-icon>

      <div class="d-flex align-center ml-2">
        <v-avatar size="36" class="ai-gradient-bg mr-3">
          <v-icon icon="mdi-gamepad-variant-outline" color="white" size="20"></v-icon>
        </v-avatar>
        <span class="font-weight-bold text-subtitle-1 text-slate-800">
          Design Thinking <span class="ai-gradient-text font-weight-bold">Game Studio</span>
        </span>
      </div>

      <v-spacer></v-spacer>

      <div class="d-flex align-center gap-2">
        <v-btn
          to="/projects/new"
          class="ai-gradient-bg text-white mr-3 px-4"
          prepend-icon="mdi-plus"
          rounded="lg"
        >
          สร้างโปรเจกต์ใหม่
        </v-btn>

        <v-menu location="bottom end">
          <template #activator="{ props }">
            <v-btn v-bind="props" icon variant="text">
              <v-avatar size="36" color="primary-lighten-4">
                <span class="text-subtitle-2 font-weight-bold text-primary">
                  {{ authStore.user?.name?.charAt(0) || 'U' }}
                </span>
              </v-avatar>
            </v-btn>
          </template>

          <v-card min-width="220" rounded="lg" class="pa-2">
            <div class="px-3 py-2">
              <div class="font-weight-bold text-subtitle-2">{{ authStore.user?.name }}</div>
              <div class="text-caption text-grey">{{ authStore.user?.email }}</div>
              <v-chip size="x-small" color="primary" class="mt-1">
                {{ authStore.user?.role === 'admin' ? 'ผู้ดูแลระบบ' : 'คุณครู' }}
              </v-chip>
            </div>
            <v-divider class="my-1"></v-divider>
            <v-list density="compact" nav>
              <v-list-item to="/settings/ai" prepend-icon="mdi-cog-outline" title="ตั้งค่า AI Provider"></v-list-item>
              <v-list-item @click="handleLogout" prepend-icon="mdi-logout" title="ออกจากระบบ" color="error"></v-list-item>
            </v-list>
          </v-card>
        </v-menu>
      </div>
    </v-app-bar>

    <!-- Sidebar Navigation (260px) per spec Section 9 & 10 -->
    <v-navigation-drawer v-model="drawer" width="260" class="border-r" color="surface">
      <div class="pa-4 pb-2">
        <div class="text-caption font-weight-bold text-grey-darken-1 text-uppercase tracking-wider">
          WORKSPACE
        </div>
      </div>

      <v-list nav density="comfortable" class="px-3">
        <v-list-item
          to="/dashboard"
          prepend-icon="mdi-view-dashboard-outline"
          title="แดชบอร์ด"
          rounded="lg"
          active-color="primary"
        ></v-list-item>

        <v-list-item
          to="/projects"
          prepend-icon="mdi-lightbulb-on-outline"
          title="Design Projects"
          rounded="lg"
          active-color="primary"
        ></v-list-item>

        <v-list-item
          to="/games"
          prepend-icon="mdi-gamepad-variant"
          title="เกมของฉัน (My Games)"
          rounded="lg"
          active-color="primary"
        ></v-list-item>

        <v-list-item
          to="/assets"
          prepend-icon="mdi-folder-image"
          title="Asset Library"
          rounded="lg"
          active-color="primary"
        ></v-list-item>

        <div class="text-caption font-weight-bold text-grey-darken-1 text-uppercase tracking-wider mt-4 mb-1 px-3">
          CLASSROOM
        </div>

        <v-list-item
          to="/classrooms"
          prepend-icon="mdi-google-classroom"
          title="ห้องเรียน (My Classes)"
          rounded="lg"
          active-color="primary"
        ></v-list-item>

        <div class="text-caption font-weight-bold text-grey-darken-1 text-uppercase tracking-wider mt-4 mb-1 px-3">
          INSIGHTS
        </div>

        <v-list-item
          to="/analytics"
          prepend-icon="mdi-chart-box-outline"
          title="ผลลัพธ์ & วิเคราะห์"
          rounded="lg"
          active-color="primary"
        ></v-list-item>

        <div class="text-caption font-weight-bold text-grey-darken-1 text-uppercase tracking-wider mt-4 mb-1 px-3">
          SYSTEM
        </div>

        <v-list-item
          to="/settings/ai"
          prepend-icon="mdi-creation"
          title="ตั้งค่า AI Provider"
          rounded="lg"
          active-color="primary"
        ></v-list-item>
      </v-list>

      <template #append>
        <div class="pa-4 border-t">
          <div class="d-flex align-center">
            <v-avatar size="32" class="mr-2" color="purple-lighten-5">
              <v-icon icon="mdi-shield-check" color="primary" size="18"></v-icon>
            </v-avatar>
            <div class="text-truncate">
              <div class="text-caption font-weight-medium">Game Engine v1.0</div>
              <div class="text-caption text-grey">AI Assisted &bull; Safe</div>
            </div>
          </div>
        </div>
      </template>
    </v-navigation-drawer>

    <!-- Main Content -->
    <v-main style="background-color: #f8fafc; min-height: 100vh;">
      <v-container fluid class="pa-6 max-w-7xl mx-auto">
        <router-view></router-view>
      </v-container>
    </v-main>
  </v-app>
</template>

<script setup lang="ts">
import { ref } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '@/stores/auth'

const drawer = ref(true)
const router = useRouter()
const authStore = useAuthStore()

function handleLogout() {
  authStore.logout()
  router.push('/login')
}
</script>

<style scoped>
.gap-2 {
  gap: 8px;
}
.tracking-wider {
  letter-spacing: 0.05em;
}
</style>
