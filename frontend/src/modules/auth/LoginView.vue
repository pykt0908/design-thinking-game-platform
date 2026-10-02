<template>
  <div class="login-page d-flex align-center justify-center min-h-screen pa-4 pa-md-8">
    <v-card width="100%" max-width="960" class="border-card rounded-2xl elevation-6 overflow-hidden bg-white">
      <v-row no-gutters>
        <!-- Left Hero Showcase Column -->
        <v-col cols="12" md="6" class="hero-brand-panel pa-6 pa-md-10 d-flex flex-column justify-space-between text-white position-relative">
          <div class="hero-glow-circle"></div>

          <div class="position-relative z-10">
            <div class="d-flex align-center mb-4">
              <img src="/logo.png" alt="DTG Logo" class="rounded-circle mr-3 elevation-3" style="width: 44px; height: 44px; object-fit: contain;" />
              <div>
                <div class="text-subtitle-1 font-weight-bold">Design Thinking Studio</div>
                <div class="text-caption text-purple-lighten-4">Educational Web Game Platform</div>
              </div>
            </div>

            <div class="hero-image-wrapper my-6 text-center">
              <img
                src="@/assets/images/studio_hero.jpg"
                alt="Game Studio 3D Artwork"
                class="floating-asset rounded-2xl hero-artwork elevation-4"
              />
            </div>

            <h2 class="text-h5 font-weight-bold mb-2">
              สร้างเกมการเรียนรู้ <br />
              <span class="text-amber-lighten-2">ด้วยกระบวนการคิดเชิงออกแบบ</span>
            </h2>
            <p class="text-caption text-purple-lighten-4 mb-4">
              เปลี่ยนแผนการสอนสู่เว็บเกมเชิงโต้ตอบภายใน 5 ขั้นตอน พร้อมระบบ AI อัจฉริยะช่วยคิดเนื้อหาและเกมเพลย์
            </p>
          </div>

          <!-- Feature badges -->
          <div class="d-flex flex-wrap gap-2 position-relative z-10 pt-2 border-t border-purple-lighten-3">
            <v-chip size="small" color="amber-lighten-3" variant="flat" class="font-weight-bold text-slate-900">
              <v-icon icon="mdi-lightbulb-on" size="14" class="mr-1"></v-icon>
              5-Step Studio
            </v-chip>
            <v-chip size="small" color="purple-lighten-4" variant="flat" class="font-weight-bold text-slate-900">
              <v-icon icon="mdi-creation" size="14" class="mr-1"></v-icon>
              AI Game Generator
            </v-chip>
            <v-chip size="small" color="white" variant="flat" class="font-weight-bold text-slate-900">
              <v-icon icon="mdi-chart-line" size="14" class="mr-1"></v-icon>
              Learning Analytics
            </v-chip>
          </div>
        </v-col>

        <!-- Right Login Form Column -->
        <v-col cols="12" md="6" class="pa-6 pa-md-10 d-flex flex-column justify-center bg-white">
          <div class="mb-6">
            <h1 class="text-h5 font-weight-bold text-slate-900 mb-1">ยินดีต้อนรับกลับมา</h1>
            <p class="text-body-2 text-grey">กรุณาเข้าสู่ระบบเพื่อเริ่มใช้งานแพลตฟอร์ม</p>
          </div>

          <!-- Quick Demo Login Switcher -->
          <v-sheet rounded="xl" color="purple-lighten-5" class="pa-4 mb-6 border-card">
            <div class="text-caption font-weight-bold text-primary mb-2 text-center d-flex align-center justify-center">
              <v-icon icon="mdi-flash" color="warning" size="16" class="mr-1"></v-icon>
              ทดลองใช้งานด่วน (Quick Demo Accounts)
            </div>
            <div class="d-flex flex-wrap gap-2 justify-center">
              <v-btn
                size="small"
                color="primary"
                variant="flat"
                rounded="lg"
                prepend-icon="mdi-account-tie"
                @click="quickLogin('teacher@example.com', 'password')"
                :loading="loading"
                class="font-weight-bold"
              >
                คุณครู
              </v-btn>
              <v-btn
                size="small"
                color="secondary"
                variant="flat"
                rounded="lg"
                prepend-icon="mdi-account-school"
                @click="quickLogin('student@example.com', 'password')"
                :loading="loading"
                class="font-weight-bold"
              >
                นักเรียน
              </v-btn>
              <v-btn
                size="small"
                color="grey-darken-3"
                variant="flat"
                rounded="lg"
                prepend-icon="mdi-shield-crown"
                @click="quickLogin('admin@example.com', 'password')"
                :loading="loading"
                class="font-weight-bold"
              >
                ผู้ดูแลระบบ
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
              rounded="lg"
            ></v-text-field>

            <v-text-field
              v-model="password"
              label="รหัสผ่าน"
              type="password"
              prepend-inner-icon="mdi-lock-outline"
              placeholder="••••••••"
              required
              class="mb-4"
              rounded="lg"
            ></v-text-field>

            <v-alert v-if="errorMessage" type="error" variant="tonal" density="compact" class="mb-4" rounded="lg">
              {{ errorMessage }}
            </v-alert>

            <v-btn
              type="submit"
              block
              size="large"
              class="ai-gradient-bg text-white font-weight-bold mb-4 elevation-2"
              :loading="loading"
              rounded="lg"
            >
              เข้าสู่ระบบ (Sign In)
            </v-btn>
          </v-form>

          <div class="text-center text-caption text-grey mt-2">
            Design Thinking First &bull; AI Assisted &bull; Safe Game Engine
          </div>
        </v-col>
      </v-row>
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
  background: radial-gradient(circle at 10% 20%, rgba(198, 112, 255, 0.12) 0%, rgba(61, 0, 102, 0.04) 50%, rgba(250, 247, 253, 1) 100%);
  min-height: 100vh;
}
.hero-brand-panel {
  background: linear-gradient(145deg, #3d0066 0%, #520982 50%, #c670ff 100%);
  overflow: hidden;
}
.hero-glow-circle {
  position: absolute;
  top: -60px;
  right: -60px;
  width: 240px;
  height: 240px;
  background: radial-gradient(circle, rgba(255, 224, 71, 0.25) 0%, rgba(198, 112, 255, 0) 70%);
  border-radius: 50%;
  pointer-events: none;
}
.hero-artwork {
  max-width: 240px;
  width: 100%;
  height: auto;
  border: 3px solid rgba(255, 255, 255, 0.2);
}
.z-10 {
  z-index: 10;
}
.gap-2 {
  gap: 8px;
}
</style>
