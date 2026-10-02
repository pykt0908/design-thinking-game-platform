<template>
  <div class="player-container position-relative">
    <div v-if="loading" class="d-flex flex-column align-center justify-center h-100 text-white">
      <v-progress-circular indeterminate size="48" color="primary" class="mb-4"></v-progress-circular>
      <div class="text-subtitle-1">กำลังโหลดเกม...</div>
    </div>

    <div v-else-if="error" class="d-flex flex-column align-center justify-center h-100 text-white pa-6 text-center">
      <v-icon icon="mdi-alert-circle-outline" size="64" color="error" class="mb-3"></v-icon>
      <div class="text-h6 font-weight-bold mb-2">{{ error }}</div>
      <v-btn color="primary" to="/dashboard" rounded="lg">กลับหน้าหลัก</v-btn>
    </div>

    <div v-else-if="schema" class="d-flex flex-column h-100">
      <!-- Player Top Bar per spec Section 30 -->
      <header class="player-header px-6 py-3 d-flex align-center justify-space-between text-white border-b">
        <div class="d-flex align-center">
          <v-btn icon="mdi-close" variant="text" size="small" color="white" class="mr-3" @click="confirmExit" title="ออกจากเกม"></v-btn>
          <div>
            <div class="font-weight-bold text-subtitle-1">{{ schema.title }}</div>
            <div class="text-caption text-grey-lighten-1">{{ currentScene?.title }}</div>
          </div>
        </div>

        <div class="d-flex align-center gap-3">
          <!-- Timer -->
          <v-chip color="slate-800" variant="flat" class="text-white font-weight-bold">
            <v-icon start icon="mdi-timer-outline" color="amber"></v-icon>
            {{ formatTime(timeRemaining) }}
          </v-chip>

          <!-- Score -->
          <v-chip color="amber-darken-2" variant="flat" class="text-white font-weight-bold">
            <v-icon start icon="mdi-star" color="white"></v-icon>
            {{ currentScore }} แต้ม
          </v-chip>
        </div>
      </header>

      <!-- Player Progress Bar -->
      <v-progress-linear
        :model-value="progressPercent"
        color="accent"
        height="4"
      ></v-progress-linear>

      <!-- Scene Runtime Canvas -->
      <main class="player-canvas flex-grow-1 d-flex flex-column justify-center align-center pa-4">
        <!-- Result / Completion Screen per spec Section 32 -->
        <div v-if="isGameComplete" class="victory-card text-center pa-8 rounded-2xl bg-white elevation-4 max-w-lg w-100 animate-fade-in">
          <div class="text-h3 mb-2">🎉</div>
          <h2 class="text-h4 font-weight-bold text-slate-800 mb-1">ภารกิจสำเร็จ!</h2>
          <p class="text-body-2 text-grey mb-6">คุณได้ทำแบบทดสอบและเรียนรู้ผ่านเกมเรียบร้อยแล้ว</p>

          <div class="score-badge pa-6 rounded-2xl mb-6 bg-indigo-lighten-5">
            <div class="text-h2 font-weight-bold text-primary mb-1">
              {{ currentScore }}
            </div>
            <div class="text-subtitle-2 text-grey">คะแนนเต็ม 100</div>

            <div class="stars mt-3 text-h5">
              <span v-for="s in 3" :key="s">
                {{ s <= calculateStars() ? '⭐' : '☆' }}
              </span>
            </div>
          </div>

          <div class="d-flex justify-space-around py-3 border-t border-b mb-6 text-slate-700">
            <div>
              <div class="text-caption text-grey">เวลาที่ใช้</div>
              <div class="font-weight-bold">{{ formatTime(durationSpent) }}</div>
            </div>
            <div>
              <div class="text-caption text-grey">ตอบถูก</div>
              <div class="font-weight-bold">{{ correctAnswersCount }} ข้อ</div>
            </div>
            <div>
              <div class="text-caption text-grey">ผลการประเมิน</div>
              <div class="font-weight-bold text-success">
                {{ currentScore >= (schema.scoring.passingScore || 60) ? 'ผ่านเกณฑ์ ✓' : 'ต้องปรับปรุง' }}
              </div>
            </div>
          </div>

          <div class="d-flex gap-3 justify-center">
            <v-btn variant="outlined" color="primary" rounded="lg" prepend-icon="mdi-reload" @click="restartGame">
              เล่นใหม่อีกครั้ง
            </v-btn>
            <v-btn color="primary" rounded="lg" to="/dashboard">
              กลับสู่บทเรียน
            </v-btn>
          </div>
        </div>

        <!-- Active Scene Interactive Content -->
        <div v-else-if="currentScene" class="scene-active-content w-100 max-w-xl d-flex flex-column align-center animate-fade-in">
          <!-- Elements inside current scene -->
          <template v-for="el in currentScene.elements" :key="el.id">
            <!-- 1. Dialogue / NPC Element -->
            <div v-if="el.type === 'character'" class="dialogue-box w-100 pa-6 rounded-2xl bg-white elevation-3 border-card">
              <div class="d-flex align-center mb-4">
                <v-avatar size="60" class="mr-4 elevation-2 bg-indigo-lighten-5">
                  <v-img :src="el.avatar || 'https://api.dicebear.com/7.x/bottts/svg?seed=Teacher'"></v-img>
                </v-avatar>
                <div>
                  <div class="text-subtitle-1 font-weight-bold text-primary">{{ el.name || el.dialogue?.speaker }}</div>
                  <div class="text-caption text-grey">ผู้ให้คำแนะนำประจำภารกิจ</div>
                </div>
              </div>

              <div class="text-body-1 text-slate-800 line-height-relaxed mb-6 font-weight-medium">
                {{ el.dialogue?.text }}
              </div>

              <div class="d-flex justify-end">
                <v-btn
                  class="ai-gradient-bg text-white font-weight-bold px-6 py-2"
                  rounded="lg"
                  size="large"
                  @click="goToNextScene(el.dialogue?.nextScene)"
                >
                  {{ el.dialogue?.actionText || 'ไปยังด่านถัดไป' }} &rarr;
                </v-btn>
              </div>
            </div>

            <!-- 2. Question / Quiz Element -->
            <div v-else-if="el.type === 'question'" class="quiz-box w-100 pa-6 rounded-2xl bg-white elevation-3 border-card">
              <div class="d-flex justify-space-between align-center mb-3">
                <v-chip size="small" color="primary" variant="flat" class="font-weight-medium">
                  ภารกิจคำถาม (+{{ el.points || 10 }} คะแนน)
                </v-chip>
                <span class="text-caption text-grey">เลือกคำตอบที่ถูกต้อง</span>
              </div>

              <h2 class="text-h6 font-weight-bold text-slate-800 mb-4">
                {{ el.question }}
              </h2>

              <div v-if="el.image" class="mb-4 text-center">
                <img :src="el.image" class="rounded-xl elevation-1" style="max-height: 180px; max-width: 100%; object-fit: cover;" />
              </div>

              <!-- Options List -->
              <div class="options-container d-flex flex-column gap-3 mb-4">
                <button
                  v-for="opt in el.options"
                  :key="opt.id"
                  class="option-card pa-4 rounded-xl text-left transition-all border d-flex justify-space-between align-center"
                  :class="getOptionClasses(opt)"
                  :disabled="answeredQuestions[el.id] !== undefined"
                  @click="selectOption(el, opt)"
                >
                  <span class="text-body-2 font-weight-medium">{{ opt.text }}</span>
                  <v-icon
                    v-if="answeredQuestions[el.id] !== undefined && opt.isCorrect"
                    icon="mdi-check-circle"
                    color="success"
                  ></v-icon>
                  <v-icon
                    v-else-if="selectedAnswers[el.id] === opt.id && !opt.isCorrect"
                    icon="mdi-close-circle"
                    color="error"
                  ></v-icon>
                </button>
              </div>

              <!-- Immediate Educational Feedback per spec Section 31 -->
              <div v-if="answeredQuestions[el.id]" class="feedback-card pa-4 rounded-xl mt-3 animate-fade-in" :class="answeredQuestions[el.id].is_correct ? 'bg-green-lighten-5 text-success' : 'bg-amber-lighten-5 text-warning'">
                <div class="d-flex align-center font-weight-bold mb-1">
                  <v-icon :icon="answeredQuestions[el.id].is_correct ? 'mdi-check-circle' : 'mdi-alert-circle'" class="mr-2"></v-icon>
                  <span>{{ answeredQuestions[el.id].is_correct ? '✓ ถูกต้องยอดเยี่ยม! (+ ' + (el.points || 10) + ' แต้ม)' : 'คำใบ้เพื่อการเรียนรู้:' }}</span>
                </div>
                <div class="text-caption text-slate-700">
                  {{ answeredQuestions[el.id].explanation || el.explanation || 'ขอให้เรียนรู้จากคำตอบและก้าวต่อไป!' }}
                </div>

                <div class="d-flex justify-end mt-3">
                  <v-btn
                    color="primary"
                    rounded="lg"
                    size="small"
                    class="font-weight-bold"
                    @click="goToNextScene(el.nextScene)"
                  >
                    ด่านต่อไป &rarr;
                  </v-btn>
                </div>
              </div>
            </div>

            <!-- 3. Direct Completion Trigger -->
            <div v-else-if="el.type === 'completion'" class="w-100 text-center">
              <v-btn
                class="ai-gradient-bg text-white font-weight-bold px-8 py-3"
                size="x-large"
                rounded="xl"
                @click="finishGame"
              >
                ดูสรุปผลคะแนนภารกิจ 🏆
              </v-btn>
            </div>
          </template>
        </div>
      </main>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, computed, onMounted, onUnmounted } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import apiClient from '@/api/client'
import confetti from 'canvas-confetti'
import type { GameSchema, GameScene, GameElement, GameOption } from '@/types'

const route = useRoute()
const router = useRouter()

const loading = ref(true)
const error = ref('')
const schema = ref<GameSchema | null>(null)
const sessionId = ref<number | null>(null)

// Game State
const currentSceneIndex = ref(0)
const currentScore = ref(0)
const timeRemaining = ref(300)
const durationSpent = ref(0)
const isGameComplete = ref(false)
const correctAnswersCount = ref(0)

const selectedAnswers = ref<Record<string, string>>({})
const answeredQuestions = ref<Record<string, any>>({})

let timerInterval: any = null

const currentScene = computed<GameScene | null>(() => {
  if (!schema.value || !schema.value.scenes) return null
  return schema.value.scenes[currentSceneIndex.value] || null
})

const progressPercent = computed(() => {
  if (!schema.value || schema.value.scenes.length === 0) return 0
  return Math.round(((currentSceneIndex.value + 1) / schema.value.scenes.length) * 100)
})

function formatTime(seconds: number) {
  const m = Math.floor(seconds / 60)
  const s = seconds % 60
  return `${m.toString().padStart(2, '0')}:${s.toString().padStart(2, '0')}`
}

function getOptionClasses(opt: GameOption) {
  const qId = currentScene.value?.elements[0]?.id
  if (!qId || answeredQuestions.value[qId] === undefined) {
    return 'bg-white hover-opt'
  }
  if (opt.isCorrect) {
    return 'bg-green-lighten-5 border-success text-success font-weight-bold'
  }
  if (selectedAnswers.value[qId] === opt.id) {
    return 'bg-red-lighten-5 border-error text-error'
  }
  return 'bg-grey-lighten-4 opacity-50'
}

async function selectOption(element: GameElement, option: GameOption) {
  if (answeredQuestions.value[element.id]) return

  selectedAnswers.value[element.id] = option.id

  try {
    if (sessionId.value) {
      const { data } = await apiClient.post(`/game-sessions/${sessionId.value}/answer`, {
        question_id: element.id,
        selected_answer: option.id,
        time_spent_seconds: 5,
      })

      answeredQuestions.value[element.id] = data
      currentScore.value = data.total_score
      if (data.is_correct) {
        correctAnswersCount.value++
      }
    } else {
      // Local preview mode
      const isCorrect = option.isCorrect
      const pts = isCorrect ? (element.points || 10) : 0
      currentScore.value += pts
      if (isCorrect) correctAnswersCount.value++
      answeredQuestions.value[element.id] = {
        is_correct: isCorrect,
        explanation: element.explanation,
      }
    }
  } catch (err) {
    console.error('Answer submission error', err)
  }
}

function goToNextScene(nextSceneId?: string) {
  if (nextSceneId) {
    const targetIdx = schema.value?.scenes.findIndex((s) => s.id === nextSceneId)
    if (targetIdx !== undefined && targetIdx >= 0) {
      currentSceneIndex.value = targetIdx
      return
    }
  }

  if (currentSceneIndex.value < (schema.value?.scenes.length || 1) - 1) {
    currentSceneIndex.value++
  } else {
    finishGame()
  }
}

async function finishGame() {
  isGameComplete.value = true
  if (timerInterval) clearInterval(timerInterval)

  // Fire celebratory confetti!
  confetti({
    particleCount: 100,
    spread: 70,
    origin: { y: 0.6 },
  })

  if (sessionId.value) {
    try {
      await apiClient.post(`/game-sessions/${sessionId.value}/complete`, {
        duration_seconds: durationSpent.value,
      })
    } catch (err) {
      console.error('Failed to complete session', err)
    }
  }
}

function calculateStars() {
  if (currentScore.value >= 80) return 3
  if (currentScore.value >= 50) return 2
  return 1
}

function restartGame() {
  currentSceneIndex.value = 0
  currentScore.value = 0
  durationSpent.value = 0
  correctAnswersCount.value = 0
  answeredQuestions.value = {}
  selectedAnswers.value = {}
  isGameComplete.value = false
  timeRemaining.value = schema.value?.settings.duration || 300
  startTimer()
}

function startTimer() {
  if (timerInterval) clearInterval(timerInterval)
  timerInterval = setInterval(() => {
    durationSpent.value++
    if (timeRemaining.value > 0) {
      timeRemaining.value--
    } else {
      finishGame()
    }
  }, 1000)
}

function confirmExit() {
  if (confirm('คุณต้องการออกจากเกมใช่หรือไม่? ผลการเล่นจะถูกบันทึก')) {
    router.push('/dashboard')
  }
}

onMounted(async () => {
  const publicId = route.params.publicId as string
  try {
    const { data } = await apiClient.get(`/public/games/${publicId}`)
    schema.value = data.schema
    timeRemaining.value = data.schema?.settings?.duration || 300

    // Try starting a session if authenticated
    try {
      const sessionRes = await apiClient.post('/game-sessions/start', {
        game_id: data.id,
      })
      sessionId.value = sessionRes.data.session.id
    } catch {
      // Unauthenticated preview mode
    }

    startTimer()
  } catch (err: any) {
    error.value = err.response?.data?.message || 'ไม่สามารถโหลดข้อมูลเกมได้'
  } finally {
    loading.value = false
  }
})

onUnmounted(() => {
  if (timerInterval) clearInterval(timerInterval)
})
</script>

<style scoped>
.player-container {
  width: 100vw;
  height: 100vh;
  background: radial-gradient(circle at 50% 20%, #1e1b4b 0%, #0f172a 100%);
  overflow-y: auto;
}
.player-header {
  background-color: rgba(15, 23, 42, 0.85);
  backdrop-filter: blur(12px);
  border-bottom: 1px solid rgba(255, 255, 255, 0.1);
}
.player-canvas {
  min-height: calc(100vh - 70px);
}
.line-height-relaxed {
  line-height: 1.7;
}
.option-card {
  width: 100%;
  cursor: pointer;
  border: 1.5px solid #e2e8f0;
}
.hover-opt:hover {
  border-color: #6366f1;
  background-color: #f5f3ff;
}
.gap-3 { gap: 12px; }
.max-w-xl { max-width: 620px; }
.max-w-lg { max-width: 500px; }
</style>
