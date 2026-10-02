<template>
  <v-app>
    <!-- Top Bar (64px) - Prominent Royal Purple for Students -->
    <v-app-bar flat height="64" class="student-navbar-prominent">
      <v-container class="d-flex align-center py-0">
        <div class="d-flex align-center mr-8">
          <img
            src="/logo.png"
            alt="DTG Logo"
            class="mr-3 brand-logo-glow rounded-circle"
            style="width: 38px; height: 38px; object-fit: contain;"
          />
          <span class="font-weight-bold text-subtitle-1 text-white">
            DTG <span class="gold-gradient-text font-weight-bold">Student</span>
          </span>
        </div>

        <div class="d-none d-sm-flex align-center gap-2 nav-links">
          <v-btn
            to="/student/home"
            variant="flat"
            prepend-icon="mdi-home-outline"
            rounded="lg"
            class="student-nav-btn"
          >
            หน้าแรก
          </v-btn>
          <v-btn
            to="/student/classes"
            variant="flat"
            prepend-icon="mdi-google-classroom"
            rounded="lg"
            class="student-nav-btn"
          >
            ห้องเรียนของฉัน
          </v-btn>
          <v-btn
            to="/student/games"
            variant="flat"
            prepend-icon="mdi-controller"
            rounded="lg"
            class="student-nav-btn"
          >
            เกมที่ได้รับมอบหมาย
          </v-btn>
        </div>

        <v-spacer></v-spacer>

        <div class="d-flex align-center">
          <v-chip
            variant="flat"
            class="mr-3 font-weight-bold student-chip elevation-1"
          >
            <v-icon start icon="mdi-star" color="#3d0066"></v-icon>
            {{ authStore.user?.name }}
          </v-chip>

          <v-btn
            icon="mdi-logout"
            variant="text"
            color="#eec7fc"
            @click="handleLogout"
            title="ออกจากระบบ"
            class="logout-btn"
          ></v-btn>
        </div>
      </v-container>
    </v-app-bar>

    <v-main style="background-color: #faf7fd; min-height: 100vh;">
      <v-container class="py-6">
        <router-view></router-view>
      </v-container>
    </v-main>
  </v-app>
</template>

<script setup lang="ts">
import { useRouter } from 'vue-router'
import { useAuthStore } from '@/stores/auth'

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

.student-navbar-prominent {
  background: linear-gradient(90deg, #24003d 0%, #3d0066 45%, #500085 100%) !important;
  border-bottom: 1px solid rgba(198, 112, 255, 0.25) !important;
  box-shadow: 0 4px 20px rgba(36, 0, 61, 0.25) !important;
}

.brand-logo-glow {
  border: 2px solid #ffe047;
  box-shadow: 0 0 12px rgba(255, 224, 71, 0.45);
}

.gold-gradient-text {
  background: linear-gradient(135deg, #ffe047 0%, #ffce1f 100%);
  -webkit-background-clip: text;
  -webkit-text-fill-color: transparent;
}

.student-nav-btn {
  background: transparent !important;
  color: #f3e8ff !important;
  font-weight: 500;
  transition: all 0.2s ease;
}

.student-nav-btn:hover {
  background: rgba(198, 112, 255, 0.18) !important;
  color: #ffffff !important;
}

.student-nav-btn.v-btn--active {
  background: linear-gradient(135deg, #c670ff 0%, #9016e8 100%) !important;
  color: #ffffff !important;
  font-weight: 700;
  box-shadow: 0 4px 14px rgba(198, 112, 255, 0.45);
}

.student-chip {
  background: linear-gradient(135deg, #ffe047 0%, #ffce1f 100%) !important;
  color: #3d0066 !important;
}

.logout-btn:hover {
  color: #ffffff !important;
  background: rgba(255, 255, 255, 0.1) !important;
}
</style>
