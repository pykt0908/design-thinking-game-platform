<template>
  <div class="login-page d-flex align-center justify-center min-h-screen pa-4">
    <v-card width="100%" max-width="460" class="pa-8 border-card rounded-xl elevation-2">
      <div class="text-center mb-6">
        <v-avatar size="56" class="ai-gradient-bg mb-3 elevation-3">
          <v-icon icon="mdi-gamepad-variant-outline" color="white" size="32"></v-icon>
        </v-avatar>
        <h1 class="text-h5 font-weight-bold text-slate-800">Design Thinking Game Studio</h1>
        <p class="text-body-2 text-grey-darken-1 mt-1">
          แพลตฟอร์มออกแบบและสร้างเกมการเรียนรู้สำหรับคุณครู
        </p>
      </div>

      <!-- Quick Demo Login Switcher -->
      <v-sheet rounded="lg" color="grey-lighten-4" class="pa-3 mb-6">
        <div class="text-caption font-weight-bold text-grey-darken-2 mb-2 text-center">
          ⚡ เลือกล็อกอินเพื่อทดสอบระบบ (Quick Demo)
        </div>
        <div class="d-flex gap-2 justify-center">
          <v-btn
            size="small"
            color="primary"
            variant="flat"
            rounded="lg"
            @click="quickLogin('teacher@example.com', 'password')"
            :loading="loading"
          >
            👨‍🏫 คุณครู (Teacher)
          </v-btn>
          <v-btn
            size="small"
            color="secondary"
            variant="flat"
            rounded="lg"
            @click="quickLogin('student@example.com', 'password')"
            :loading="loading"
          >
            👦 นักเรียน (Student)
          </v-btn>
          <v-btn
            size="small"
            color="grey-darken-3"
            variant="flat"
            rounded="lg"
            @click="quickLogin('admin@example.com', 'password')"
            :loading="loading"
          >
            ⚙️ Admin
          </v-btn>
        </div>
      </v-sheet>

      <v-form @submit.prevent="handleLogin">
        <v-text-field
          v-model="loginInput"
          label="อีเมล / ชื่อผู้ใช้ / รหัสนักเรียน"
          prepend-inner-icon="mdi-account-outline"
          placeholder="teacher@example.com"
          required
          class="mb-3"
        ></v-text-field>

        <v-text-field
          v-model="password"
          label="รหัสผ่าน"
          type="password"
          prepend-inner-icon="mdi-lock-outline"
          placeholder="••••••••"
          required
          class="mb-4"
        ></v-text-field>

        <v-alert v-if="errorMessage" type="error" variant="tonal" density="compact" class="mb-4">
          {{ errorMessage }}
        </v-alert>

        <v-btn
          type="submit"
          block
          size="large"
          class="ai-gradient-bg text-white font-weight-bold mb-4"
          :loading="loading"
          rounded="lg"
        >
          เข้าสู่ระบบ
        </v-btn>
      </v-form>

      <div class="text-center text-caption text-grey">
        Design Thinking First &bull; AI Assisted &bull; Safe Game Engine
      </div>
    </v-card>
  </div>
</template>

<script setup lang="ts">
import { ref } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '@/stores/auth'

const router = useRouter()
const authStore = useAuthStore()

const loginInput = ref('teacher@example.com')
const password = ref('password')
const loading = ref(false)
const errorMessage = ref('')

async function handleLogin() {
  loading.value = true
  errorMessage.value = ''
  try {
    const data = await authStore.login({
      login: loginInput.value,
      password: password.value,
    })

    if (data.user.role === 'student') {
      router.push('/student/home')
    } else {
      router.push('/dashboard')
    }
  } catch (err: any) {
    errorMessage.value = err.response?.data?.message || 'ข้อมูลเข้าสู่ระบบไม่ถูกต้อง'
  } finally {
    loading.value = false
  }
}

async function quickLogin(u: string, p: string) {
  loginInput.value = u
  password.value = p
  await handleLogin()
}
</script>

<style scoped>
.login-page {
  background: radial-gradient(circle at 10% 20%, rgba(99, 102, 241, 0.08) 0%, rgba(248, 250, 252, 1) 90%);
  min-height: 100vh;
}
.gap-2 {
  gap: 8px;
}
</style>
