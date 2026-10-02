<template>
  <v-app>
    <v-app-bar flat height="64" class="border-b" color="surface">
      <v-container class="d-flex align-center py-0">
        <div class="d-flex align-center mr-8">
          <img src="/logo.png" alt="DTG Logo" class="mr-3 rounded-circle" style="width: 36px; height: 36px; object-fit: contain;" />
          <span class="font-weight-bold text-subtitle-1 text-slate-800">
            DTG <span class="text-primary font-weight-bold">Student</span>
          </span>
        </div>

        <div class="d-none d-sm-flex align-center gap-2">
          <v-btn to="/student/home" variant="text" prepend-icon="mdi-home-outline" rounded="lg">
            หน้าแรก
          </v-btn>
          <v-btn to="/student/classes" variant="text" prepend-icon="mdi-google-classroom" rounded="lg">
            ห้องเรียนของฉัน
          </v-btn>
          <v-btn to="/student/games" variant="text" prepend-icon="mdi-controller" rounded="lg">
            เกมที่ได้รับมอบหมาย
          </v-btn>
        </div>

        <v-spacer></v-spacer>

        <div class="d-flex align-center">
          <v-chip color="accent" variant="flat" class="mr-3 font-weight-medium">
            <v-icon start icon="mdi-star" color="white"></v-icon>
            {{ authStore.user?.name }}
          </v-chip>

          <v-btn icon="mdi-logout" variant="text" color="grey" @click="handleLogout" title="ออกจากระบบ"></v-btn>
        </div>
      </v-container>
    </v-app-bar>

    <v-main style="background-color: #f8fafc; min-height: 100vh;">
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
</style>
