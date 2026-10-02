<template>
  <v-app>
    <!-- Top Bar (64px) - Prominent Royal Purple Gradient -->
    <v-app-bar flat height="64" class="top-navbar-prominent">
      <v-app-bar-nav-icon
        @click="drawer = !drawer"
        color="white"
        class="nav-toggle-btn mr-1"
      ></v-app-bar-nav-icon>

      <div class="d-flex align-center ml-1">
        <img
          src="/logo.png"
          alt="DTG Studio Logo"
          class="mr-3 brand-logo-glow rounded-circle"
          style="width: 38px; height: 38px; object-fit: contain;"
        />
        <span class="font-weight-bold text-subtitle-1 text-white d-flex align-center">
          Design Thinking
          <span class="gold-gradient-text font-weight-bold ml-2">Game Studio</span>
        </span>
      </div>

      <v-spacer></v-spacer>

      <div class="d-flex align-center gap-2">
        <v-menu location="bottom end" transition="slide-y-transition">
          <template #activator="{ props }">
            <v-btn
              v-bind="props"
              variant="flat"
              rounded="lg"
              class="user-profile-btn px-3 py-1 d-flex align-center"
            >
              <v-avatar size="28" class="mr-2 user-avatar-gold font-weight-bold">
                <span class="text-caption">
                  {{ authStore.user?.name?.charAt(0) || 'ค' }}
                </span>
              </v-avatar>
              <span class="font-weight-bold text-body-2 text-white mr-1 text-truncate" style="max-width: 180px;">
                {{ authStore.user?.name || 'คุณครู' }}
              </span>
              <v-icon icon="mdi-chevron-down" size="18" color="#ffe047"></v-icon>
            </v-btn>
          </template>

          <v-card min-width="230" rounded="xl" class="pa-2 border-card elevation-6 bg-white">
            <div class="px-3 py-2">
              <div class="font-weight-bold text-subtitle-2 text-slate-900">{{ authStore.user?.name }}</div>
              <div class="text-caption text-grey">{{ authStore.user?.email }}</div>
              <v-chip size="x-small" color="primary" class="mt-1 font-weight-medium">
                {{ authStore.user?.role === 'admin' ? 'ผู้ดูแลระบบ' : 'คุณครู' }}
              </v-chip>
            </div>
            <v-divider class="my-1"></v-divider>
            <v-list density="compact" nav>
              <v-list-item
                to="/settings/ai"
                prepend-icon="mdi-cog-outline"
                title="ตั้งค่า AI Provider"
                rounded="lg"
              ></v-list-item>
              <v-list-item
                @click="handleLogout"
                prepend-icon="mdi-logout"
                title="ออกจากระบบ"
                color="error"
                rounded="lg"
              ></v-list-item>
            </v-list>
          </v-card>
        </v-menu>
      </div>
    </v-app-bar>

    <!-- Sidebar Navigation (260px) - Prominent Deep Purple -->
    <v-navigation-drawer
      v-model="drawer"
      width="260"
      class="sidebar-prominent"
    >
      <div class="pa-4 pb-2">
        <div class="sidebar-section-title">
          WORKSPACE
        </div>
      </div>

      <v-list nav density="comfortable" class="px-3 sidebar-nav-list">
        <v-list-item
          to="/dashboard"
          prepend-icon="mdi-view-dashboard-outline"
          title="แดชบอร์ด"
          rounded="lg"
          class="sidebar-item"
        ></v-list-item>

        <v-list-item
          to="/projects"
          prepend-icon="mdi-lightbulb-on-outline"
          title="Design Projects"
          rounded="lg"
          class="sidebar-item"
        ></v-list-item>

        <v-list-item
          to="/games"
          prepend-icon="mdi-gamepad-variant"
          title="เกมของฉัน (My Games)"
          rounded="lg"
          class="sidebar-item"
        ></v-list-item>

        <v-list-item
          to="/assets"
          prepend-icon="mdi-folder-image"
          title="Asset Library"
          rounded="lg"
          class="sidebar-item"
        ></v-list-item>

        <div class="sidebar-section-title mt-4 mb-1 px-3">
          CLASSROOM
        </div>

        <v-list-item
          to="/classrooms"
          prepend-icon="mdi-google-classroom"
          title="ห้องเรียน (My Classes)"
          rounded="lg"
          class="sidebar-item"
        ></v-list-item>

        <div class="sidebar-section-title mt-4 mb-1 px-3">
          INSIGHTS
        </div>

        <v-list-item
          to="/analytics"
          prepend-icon="mdi-chart-box-outline"
          title="ผลลัพธ์ & วิเคราะห์"
          rounded="lg"
          class="sidebar-item"
        ></v-list-item>

        <div class="sidebar-section-title mt-4 mb-1 px-3">
          SYSTEM
        </div>

        <v-list-item
          to="/settings/ai"
          prepend-icon="mdi-creation"
          title="ตั้งค่า AI Provider"
          rounded="lg"
          class="sidebar-item"
        ></v-list-item>
      </v-list>

      <template #append>
        <div class="pa-4 sidebar-footer">
          <div class="d-flex align-center pa-2 rounded-xl footer-badge">
            <v-avatar size="32" class="mr-2" color="rgba(255, 224, 71, 0.15)">
              <v-icon icon="mdi-shield-check" color="#ffe047" size="18"></v-icon>
            </v-avatar>
            <div class="text-truncate">
              <div class="text-caption font-weight-bold text-white">Game Engine v1.0</div>
              <div class="text-caption text-purple-lighten-4" style="opacity: 0.85;">AI Assisted &bull; Safe</div>
            </div>
          </div>
        </div>
      </template>
    </v-navigation-drawer>

    <!-- Main Content -->
    <v-main style="background-color: #faf7fd; min-height: 100vh; padding-top: 64px !important;">
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

/* Top Navbar Prominent Styling - Sticky Fixed */
.top-navbar-prominent {
  background: linear-gradient(90deg, #24003d 0%, #3d0066 45%, #500085 100%) !important;
  border-bottom: 1px solid rgba(198, 112, 255, 0.25) !important;
  box-shadow: 0 4px 20px rgba(36, 0, 61, 0.25) !important;
  position: fixed !important;
  top: 0 !important;
  left: 0 !important;
  right: 0 !important;
  z-index: 1000 !important;
}

.brand-logo-glow {
  border: 2px solid #ffe047;
  box-shadow: 0 0 12px rgba(255, 224, 71, 0.45);
}

.gold-gradient-text {
  background: linear-gradient(135deg, #ffe047 0%, #ffce1f 100%);
  -webkit-background-clip: text;
  -webkit-text-fill-color: transparent;
  text-shadow: 0 2px 10px rgba(255, 206, 31, 0.25);
}

.user-profile-btn {
  background: rgba(255, 255, 255, 0.12) !important;
  backdrop-filter: blur(10px);
  border: 1px solid rgba(238, 199, 252, 0.35) !important;
  transition: all 0.2s ease;
}

.user-profile-btn:hover {
  background: rgba(255, 255, 255, 0.22) !important;
  border-color: #ffe047 !important;
}

.user-avatar-gold {
  background: linear-gradient(135deg, #ffe047 0%, #ffce1f 100%) !important;
  color: #3d0066 !important;
}

/* Sidebar Prominent Styling - Sticky Fixed */
.sidebar-prominent {
  background: linear-gradient(180deg, #1f0036 0%, #290048 50%, #36005c 100%) !important;
  border-right: 1px solid rgba(198, 112, 255, 0.2) !important;
  position: fixed !important;
  top: 64px !important;
  height: calc(100vh - 64px) !important;
  z-index: 99 !important;
  overflow-y: auto !important;
}

.sidebar-section-title {
  color: #eec7fc;
  font-size: 0.6875rem;
  font-weight: 800;
  letter-spacing: 0.1em;
  opacity: 0.9;
  text-transform: uppercase;
}

/* Sidebar Navigation Items */
.sidebar-nav-list :deep(.v-list-item) {
  color: #f3e8ff !important;
  font-weight: 500;
  margin-bottom: 4px;
  transition: all 0.2s cubic-bezier(0.16, 1, 0.3, 1);
}

.sidebar-nav-list :deep(.v-list-item .v-icon) {
  color: #c670ff !important;
  transition: all 0.2s ease;
}

.sidebar-nav-list :deep(.v-list-item:hover) {
  background: rgba(198, 112, 255, 0.18) !important;
  color: #ffffff !important;
  transform: translateX(3px);
}

.sidebar-nav-list :deep(.v-list-item:hover .v-icon) {
  color: #ffe047 !important;
}

/* Active Item Glow & Gradient */
.sidebar-nav-list :deep(.v-list-item--active) {
  background: linear-gradient(135deg, #c670ff 0%, #9016e8 100%) !important;
  color: #ffffff !important;
  font-weight: 700 !important;
  box-shadow: 0 4px 18px rgba(198, 112, 255, 0.45) !important;
}

.sidebar-nav-list :deep(.v-list-item--active .v-icon) {
  color: #ffe047 !important;
}

/* Footer Badge */
.sidebar-footer {
  border-top: 1px solid rgba(198, 112, 255, 0.15);
}

.footer-badge {
  background: rgba(255, 255, 255, 0.08);
  backdrop-filter: blur(8px);
  border: 1px solid rgba(238, 199, 252, 0.2);
}
</style>
