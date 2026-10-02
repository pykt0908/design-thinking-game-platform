<template>
  <div class="max-w-4xl mx-auto py-4">
    <!-- Header -->
    <div class="d-flex align-center justify-space-between mb-6">
      <div class="d-flex align-center">
        <v-btn icon="mdi-arrow-left" variant="outlined" to="/projects" class="mr-3 bg-white"></v-btn>
        <div>
          <h1 class="text-h5 font-weight-bold text-slate-800 d-flex align-center">
            <v-icon icon="mdi-creation" color="primary" class="mr-2"></v-icon>
            สร้าง Design Thinking Project ใหม่
          </h1>
          <p class="text-body-2 text-grey">เลือกสไตล์เกมและธีมกราฟิก จากนั้นปล่อยให้ AI ช่วยเนรมิตเกมการเรียนรู้</p>
        </div>
      </div>
    </div>

    <v-form @submit.prevent="handleCreate">
      <!-- Section 1: ข้อมูลบทเรียนพื้นฐาน -->
      <v-card class="pa-6 mb-6 border-card rounded-2xl bg-white elevation-1">
        <div class="d-flex align-center mb-4">
          <v-avatar size="36" color="purple-lighten-5" class="mr-3">
            <v-icon icon="mdi-book-open-page-variant" color="primary" size="20"></v-icon>
          </v-avatar>
          <div>
            <h2 class="text-subtitle-1 font-weight-bold text-slate-900">1. ข้อมูลบทเรียน (Learning Topic)</h2>
            <div class="text-caption text-grey">ระบุหัวข้อบทเรียนและกลุ่มผู้เรียนเป้าหมาย</div>
          </div>
        </div>

        <v-text-field
          v-model="title"
          label="ชื่อโปรเจกต์ / หัวข้อบทเรียน *"
          placeholder="เช่น ภารกิจกู้โลกโรงเรียนไร้ขยะ (Zero Waste School Mission)"
          required
          variant="outlined"
          density="comfortable"
          rounded="lg"
          class="mb-3"
        ></v-text-field>

        <!-- Quick Subject / Topic Presets -->
        <div class="mb-4">
          <span class="text-caption text-grey mr-2">ตัวอย่างหัวข้อยอดนิยม:</span>
          <div class="d-inline-flex flex-wrap gap-1 mt-1">
            <v-chip
              v-for="p in topicPresets"
              :key="p.title"
              size="x-small"
              variant="tonal"
              color="primary"
              class="cursor-pointer font-weight-medium"
              @click="applyPreset(p)"
            >
              + {{ p.title }}
            </v-chip>
          </div>
        </div>

        <v-row>
          <v-col cols="12" sm="6">
            <v-text-field
              v-model="subject"
              label="กลุ่มสาระ / รายวิชา *"
              placeholder="เช่น วิทยาศาสตร์และสิ่งแวดล้อม"
              required
              variant="outlined"
              density="comfortable"
              rounded="lg"
            ></v-text-field>
          </v-col>
          <v-col cols="12" sm="6">
            <v-select
              v-model="gradeLevel"
              :items="[
                'ประถมศึกษาตอนต้น (ป.1 - ป.3)',
                'ประถมศึกษาตอนปลาย (ป.4 - ป.6)',
                'มัธยมศึกษาปีที่ 1',
                'มัธยมศึกษาปีที่ 2',
                'มัธยมศึกษาปีที่ 3',
                'มัธยมศึกษาตอนปลาย (ม.4 - ม.6)',
                'อุดมศึกษา / บุคคลทั่วไป'
              ]"
              label="ระดับชั้นเป้าหมาย *"
              required
              variant="outlined"
              density="comfortable"
              rounded="lg"
            ></v-select>
          </v-col>
        </v-row>

        <v-textarea
          v-model="description"
          label="เป้าหมายหรือคำอธิบายเพิ่มเติม (ไม่บังคับ)"
          placeholder="สิ่งที่อยากให้เด็กเรียนรู้หรือได้ฝึกคิดแก้ปัญหา..."
          rows="2"
          variant="outlined"
          density="comfortable"
          rounded="lg"
          class="mt-2"
        ></v-textarea>
      </v-card>

      <!-- Section 2: โหมดการเล่น (Game Mode: Offline/Single vs Online/Live) -->
      <v-card class="pa-6 mb-6 border-card rounded-2xl bg-white elevation-1">
        <div class="d-flex align-center justify-space-between mb-4 flex-wrap gap-2">
          <div class="d-flex align-center">
            <v-avatar size="36" color="purple-lighten-5" class="mr-3">
              <v-icon icon="mdi-controller" color="primary" size="20"></v-icon>
            </v-avatar>
            <div>
              <h2 class="text-subtitle-1 font-weight-bold text-slate-900">2. โหมดการเล่น (Game Mode)</h2>
              <div class="text-caption text-grey">เลือกสไตล์การจัดกิจกรรม: เล่นคนเดียวตามจังหวะตนเอง หรือ แข่งขันสดในห้องเรียน</div>
            </div>
          </div>
          <v-chip size="x-small" color="primary" variant="outlined" class="font-weight-bold">
            รูปแบบเกมจะเปลี่ยนตามโหมดที่เลือกอัตโนมัติ
          </v-chip>
        </div>

        <div class="mode-grid">
          <!-- Mode 1: Single Player (Offline / Self-Paced) -->
          <div
            class="mode-card rounded-2xl pa-4 cursor-pointer border"
            :class="{ 'mode-card-active': gameMode === 'single' }"
            @click="selectGameMode('single')"
          >
            <div class="d-flex justify-space-between align-start mb-2">
              <v-avatar size="44" color="purple-lighten-5">
                <v-icon icon="mdi-account-star" color="primary" size="24"></v-icon>
              </v-avatar>
              <v-chip size="x-small" color="primary" variant="flat" class="font-weight-bold">
                Offline / Self-Paced
              </v-chip>
            </div>
            <h3 class="text-subtitle-1 font-weight-bold text-slate-900 mb-1">
              เล่นเดี่ยว (Single Player Quest)
            </h3>
            <p class="text-caption text-slate-600 mb-0">
              ผู้เรียนเล่นตามจังหวะตนเอง ผ่านด่านเนื้อเรื่อง ตอบคำถามผจญภัย มีระบบพลังชีวิต (HP) และปลดล็อกไอเทม เหมาะสำหรับการบ้านหรือฝึกฝนอิสระ
            </p>
          </div>

          <!-- Mode 2: Classroom Live (Online / Multiplayer Kahoot Style) -->
          <div
            class="mode-card rounded-2xl pa-4 cursor-pointer border"
            :class="{ 'mode-card-active': gameMode === 'multiplayer_live' }"
            @click="selectGameMode('multiplayer_live')"
          >
            <div class="d-flex justify-space-between align-start mb-2">
              <v-avatar size="44" color="amber-lighten-5">
                <v-icon icon="mdi-account-group" color="warning" size="24"></v-icon>
              </v-avatar>
              <v-chip size="x-small" color="warning" variant="flat" class="font-weight-bold text-slate-900">
                Online Live / Kahoot Style
              </v-chip>
            </div>
            <h3 class="text-subtitle-1 font-weight-bold text-slate-900 mb-1">
              แข่งขันสดในห้องเรียน (Classroom Live)
            </h3>
            <p class="text-caption text-slate-600 mb-0">
              ครูขึ้นจอใหญ่โปรเจกเตอร์ นักเรียนใส่ PIN เข้าประชันความเร็วพร้อมกันทั้งห้อง มีนับเวลาถอยหลัง โบนัสความเร็ว และตารางคะแนนสด
            </p>
          </div>
        </div>
      </v-card>

      <!-- Section 3: สไตล์ของเกม (Game Genre & Style: Distinct per mode) -->
      <v-card class="pa-6 mb-6 border-card rounded-2xl bg-white elevation-1">
        <div class="d-flex align-center justify-space-between mb-4 flex-wrap gap-2">
          <div class="d-flex align-center">
            <v-avatar size="36" :color="gameMode === 'single' ? 'purple-lighten-5' : 'amber-lighten-5'" class="mr-3">
              <v-icon :icon="gameMode === 'single' ? 'mdi-sword-cross' : 'mdi-lightning-bolt'" :color="gameMode === 'single' ? 'primary' : 'warning'" size="20"></v-icon>
            </v-avatar>
            <div>
              <h2 class="text-subtitle-1 font-weight-bold text-slate-900">
                3. รูปแบบเกมสำหรับโหมด: <span :class="gameMode === 'single' ? 'text-primary' : 'text-amber-darken-3'">{{ gameMode === 'single' ? 'เล่นเดี่ยว (Single Player)' : 'ห้องเรียนสด (Classroom Live)' }}</span>
              </h2>
              <div class="text-caption text-grey">
                {{ gameMode === 'single' ? 'กลไกเกมเฉพาะบุคคล: ระบบฉาก เควสต์ การสำรวจ และการเก็บคะแนนสะสม' : 'กลไกเกมแข่งขันหลายคน: ระบบเวลาจำกัด โบนัสความเร็ว และการปะทะคะแนนสด' }}
              </div>
            </div>
          </div>

          <v-chip
            size="small"
            :color="gameMode === 'single' ? 'primary' : 'warning'"
            class="font-weight-bold"
          >
            <v-icon :icon="gameMode === 'single' ? 'mdi-shield-check' : 'mdi-fire'" size="14" class="mr-1"></v-icon>
            {{ gameMode === 'single' ? '4 สไตล์เกมเล่นเดี่ยว' : '4 สไตล์เกมประชันสด' }}
          </v-chip>
        </div>

        <div class="genre-grid">
          <div
            v-for="g in activeGameGenres"
            :key="g.id"
            class="genre-card rounded-2xl pa-4 cursor-pointer border"
            :class="{ 'genre-card-active': gameGenre === g.id }"
            @click="gameGenre = g.id"
          >
            <div class="d-flex justify-space-between align-center mb-2">
              <v-avatar size="40" :color="g.bgColor">
                <v-icon :icon="g.icon" :color="g.iconColor" size="22"></v-icon>
              </v-avatar>
              <v-chip size="x-small" :color="g.badgeColor" class="font-weight-bold">
                {{ g.badge }}
              </v-chip>
            </div>
            <h3 class="text-subtitle-2 font-weight-bold text-slate-900 mb-1">
              {{ g.title }}
            </h3>
            <p class="text-caption text-slate-600 mb-0 line-clamp-2">
              {{ g.description }}
            </p>
          </div>
        </div>
      </v-card>

      <!-- Section 4: ธีมภาพและสื่อกราฟิก (Asset Theme Pack) -->
      <v-card class="pa-6 mb-6 border-card rounded-2xl bg-white elevation-1">
        <div class="d-flex align-center mb-4">
          <v-avatar size="36" color="purple-lighten-5" class="mr-3">
            <v-icon icon="mdi-palette" color="primary" size="20"></v-icon>
          </v-avatar>
          <div>
            <h2 class="text-subtitle-1 font-weight-bold text-slate-900">4. ธีมสื่อกราฟิก (Asset Theme Pack)</h2>
            <div class="text-caption text-grey">กำหนดโทนภาพและตัวละครที่ระบบจะนำมาใช้ประกอบเกม</div>
          </div>
        </div>

        <div class="theme-grid">
          <div
            v-for="t in themePacks"
            :key="t.id"
            class="theme-card rounded-2xl overflow-hidden cursor-pointer border"
            :class="{ 'theme-card-active': themePack === t.id }"
            @click="themePack = t.id"
          >
            <div class="theme-cover" :style="`background-image: url('${t.image}');`">
              <div class="theme-overlay pa-3 d-flex align-end">
                <div class="text-subtitle-2 font-weight-bold text-white">{{ t.name }}</div>
              </div>
            </div>
            <div class="pa-3">
              <p class="text-caption text-slate-600 mb-0">{{ t.description }}</p>
            </div>
          </div>
        </div>
      </v-card>

      <!-- Bottom Submit Bar -->
      <div class="d-flex justify-end align-center gap-3 pt-4">
        <v-btn to="/projects" variant="text" size="large" rounded="lg">
          ยกเลิก
        </v-btn>
        <v-btn
          type="submit"
          class="ai-gradient-bg text-white font-weight-bold px-8 elevation-3"
          rounded="lg"
          size="large"
          :loading="loading"
        >
          สร้างโปรเจกต์ & เริ่มต้นขั้นตอน Design Thinking &rarr;
        </v-btn>
      </div>
    </v-form>
  </div>
</template>

<script setup lang="ts">
import { ref, computed } from 'vue'
import { useRouter } from 'vue-router'
import apiClient from '@/api/client'
import { useAlertStore } from '@/stores/alert'

const router = useRouter()
const alertStore = useAlertStore()

const title = ref('')
const subject = ref('')
const gradeLevel = ref('มัธยมศึกษาปีที่ 1')
const description = ref('')
const gameMode = ref<'single' | 'multiplayer_live'>('single')
const gameGenre = ref('rpg_quest')
const themePack = ref('fantasy')
const loading = ref(false)

// Distinct game genres for Offline / Single Player
const singlePlayerGenres = [
  {
    id: 'rpg_quest',
    title: '2D RPG Turn-Based Battle',
    badge: 'ผจญภัย & ต่อสู้',
    badgeColor: 'primary',
    icon: 'mdi-sword-cross',
    iconColor: 'primary',
    bgColor: 'purple-lighten-5',
    description: 'สวมบทบาทผู้กล้า เดินสำรวจฉาก ตอบคำถามเพื่อปล่อยเวทมนตร์หรือพลังโจมตีมอนสเตอร์/บอสตามเนื้อเรื่อง มีเกจ HP และระบบเลเวล',
  },
  {
    id: 'scenario_detective',
    title: 'Detective & Mystery Quest',
    badge: 'สืบสวน & เบาะแส',
    badgeColor: 'success',
    icon: 'mdi-incognito',
    iconColor: 'success',
    bgColor: 'green-lighten-5',
    description: 'สวมบทบาทนักสืบหรือผู้เชี่ยวชาญ ค้นหาหลักฐาน สัมภาษณ์พยาน และเลือกตัดสินใจเพื่อคลี่คลายเงื่อนงำในบทเรียน',
  },
  {
    id: 'sorting_dragdrop',
    title: 'Puzzle & Category Drag-Drop',
    badge: 'คัดแยก & จัดหมวดหมู่',
    badgeColor: 'secondary',
    icon: 'mdi-drag-variant',
    iconColor: 'secondary',
    bgColor: 'cyan-lighten-5',
    description: 'ลากวางจับคู่ คัดแยกกลุ่มสิ่งของ และจัดระเบียบองค์ประกอบบทเรียนลงกล่องเป้าหมาย เล่นได้ตามจังหวะตนเองโดยไม่กดดันเรื่องเวลา',
  },
  {
    id: 'visual_novel',
    title: 'Interactive Visual Novel',
    badge: 'เนื้อเรื่อง & ตอนจบหลายแบบ',
    badgeColor: 'indigo',
    icon: 'mdi-book-open-page-variant',
    iconColor: 'indigo',
    bgColor: 'indigo-lighten-5',
    description: 'ดำเนินเรื่องราวเข้มข้น ตอบคำถามเชิงตรรกะและสถานการณ์ ทุกการตัดสินใจส่งผลต่อตอนจบหลายรูปแบบ (Multiple Endings)',
  },
]

// Distinct game genres for Online / Classroom Live (Kahoot style)
const multiplayerLiveGenres = [
  {
    id: 'live_quiz',
    title: 'Live Speed Quiz Arena (Kahoot Style)',
    badge: 'ประชันความเร็วขึ้นจอ',
    badgeColor: 'warning',
    icon: 'mdi-lightning-bolt',
    iconColor: 'warning',
    bgColor: 'amber-lighten-5',
    description: 'คำถามขึ้นจอใหญ่หน้าห้อง นักเรียนกดปุ่ม 4 สีจากมือถือเพื่อแข่งความเร็ว มีเวลานับถอยหลัง โบนัสความเร็ว และตารางคะแนนสด',
  },
  {
    id: 'team_battle',
    title: 'Classroom Team Battle (ศึกประลองแบ่งทีม)',
    badge: 'สร้างความสามัคคี',
    badgeColor: 'error',
    icon: 'mdi-shield-sword',
    iconColor: 'error',
    bgColor: 'red-lighten-5',
    description: 'ระบบแบ่งนักเรียนในห้องเป็น 2-4 ทีม ทุกคนในทีมช่วยกันตอบโจทย์เพื่อสะสมคะแนนรวมโจมตีปราสาท หรือชักเย่อแต้มไปฝั่งตนเอง',
  },
  {
    id: 'battle_royale',
    title: 'Survival Battle Royale (ศึกเอาชีวิตรอดคนสุดท้าย)',
    badge: 'ลุ้นระทึก & ท้าทาย',
    badgeColor: 'deep-orange',
    icon: 'mdi-crown',
    iconColor: 'deep-orange',
    bgColor: 'orange-lighten-5',
    description: 'แข่งขันตอบคำถามเป็นระลอก ใครตอบผิดจะเสียหัวใจและตกรอบ ผู้ที่ตอบถูกและยืนหยัดเป็นคนสุดท้ายในห้องเรียนจะเป็นแชมป์ประจำคาบ',
  },
  {
    id: 'board_game_live',
    title: 'Live Board Game / Jeopardy Challenge',
    badge: 'บอร์ดเกมเปิดป้ายชิงแต้ม',
    badgeColor: 'teal',
    icon: 'mdi-view-grid-plus',
    iconColor: 'teal',
    bgColor: 'teal-lighten-5',
    description: 'บอร์ดเกมกระดานคะแนนแบ่งตามหมวดหมู่วิชา สลับกันเลือกข้อคำถามระดับความยาก และชิงกดปุ่ม Buzz-In เพื่อตอบชิงคะแนนสูงสุด',
  },
]

// Dynamically return genres based on chosen game mode
const activeGameGenres = computed(() => {
  return gameMode.value === 'single' ? singlePlayerGenres : multiplayerLiveGenres
})

function selectGameMode(mode: 'single' | 'multiplayer_live') {
  gameMode.value = mode
  const validIds = (mode === 'single' ? singlePlayerGenres : multiplayerLiveGenres).map(g => g.id)
  if (!validIds.includes(gameGenre.value)) {
    gameGenre.value = validIds[0]
  }
}

const topicPresets = [
  { title: 'ภารกิจโรงเรียนไร้ขยะ (RPG)', subject: 'วิทยาศาสตร์และสิ่งแวดล้อม', desc: 'การคัดแยกขยะ 4 ประเภทและการรีไซเคิลแบบผจญภัย', mode: 'single' as const, genre: 'rpg_quest', theme: 'school' },
  { title: 'ประชันความเร็ววิทย์รอบตัว (Live Kahoot)', subject: 'วิทยาศาสตร์ทั่วไป', desc: 'ตอบคำถามความเร็วชิงแชมป์ประจำสัปดาห์ขึ้นจอใหญ่', mode: 'multiplayer_live' as const, genre: 'live_quiz', theme: 'scifi' },
  { title: 'ศึกชิงเมืองตรรกศาสตร์ (Team Battle)', subject: 'คณิตศาสตร์และคอมพิวเตอร์', desc: 'การคิดเชิงคำนวณและประพจน์จริง/เท็จแบบแบ่งทีม', mode: 'multiplayer_live' as const, genre: 'team_battle', theme: 'fantasy' },
  { title: 'English Detective in Town (สืบสวน)', subject: 'ภาษาต่างประเทศ', desc: 'สืบสวนหาเบาะแสและบทสนทนาสถานการณ์จริง', mode: 'single' as const, genre: 'scenario_detective', theme: 'school' },
]

function applyPreset(p: any) {
  title.value = p.title
  subject.value = p.subject
  description.value = p.desc
  selectGameMode(p.mode)
  gameGenre.value = p.genre
  themePack.value = p.theme
}

const themePacks = [
  {
    id: 'fantasy',
    name: '🏰 ดินแดนเวทมนตร์ (Fantasy & Magic)',
    description: 'ปราสาท ป่าเวทมนตร์ ผลึกแก้ว และปราชญ์ผู้พิทักษ์',
    image: 'https://images.unsplash.com/photo-1518709268805-4e9042af9f23?w=500&auto=format&fit=crop&q=80',
  },
  {
    id: 'scifi',
    name: '🔬 อวกาศ & ห้องทดลอง (Sci-Fi & Cyber)',
    description: 'สถานีอวกาศ หุ่นยนต์ AI ห้องแล็บวิจัยล้ำสมัย',
    image: 'https://images.unsplash.com/photo-1517694712202-14dd9538aa97?w=500&auto=format&fit=crop&q=80',
  },
  {
    id: 'school',
    name: '🏫 โรงเรียน & ชุมชน (School & City)',
    description: 'ห้องเรียน สนามกีฬา สวนสาธารณะ และเพื่อนร่วมชั้น',
    image: 'https://images.unsplash.com/photo-1509062522246-3755977927d7?w=500&auto=format&fit=crop&q=80',
  },
  {
    id: 'eco',
    name: '🌿 ธรรมชาติ & สิ่งแวดล้อม (Eco Nature)',
    description: 'ป่าไม้ ลำธาร สัตว์ป่า และแหล่งพลังงานสะอาด',
    image: 'https://images.unsplash.com/photo-1542601906990-b4d3fb778b09?w=500&auto=format&fit=crop&q=80',
  },
]

async function handleCreate() {
  if (!title.value) return
  loading.value = true
  try {
    const { data } = await apiClient.post('/projects', {
      title: title.value,
      subject: subject.value,
      grade_level: gradeLevel.value,
      description: description.value,
      game_mode: gameMode.value,
      game_genre: gameGenre.value,
      theme_pack: themePack.value,
    })
    alertStore.success('สร้างโปรเจกต์ใหม่เรียบร้อยแล้ว!', 'สำเร็จ')
    router.push(`/projects/${data.id}`)
  } catch (err: any) {
    alertStore.error(err.response?.data?.message || 'ไม่สามารถสร้างโปรเจกต์ได้', 'เกิดข้อผิดพลาด')
  } finally {
    loading.value = false
  }
}
</script>

<style scoped>
.gap-1 { gap: 4px; }
.gap-2 { gap: 8px; }
.gap-3 { gap: 12px; }
.cursor-pointer { cursor: pointer; }

/* Mode Grid */
.mode-grid {
  display: grid;
  grid-template-columns: repeat(2, 1fr);
  gap: 16px;
}

@media (max-width: 680px) {
  .mode-grid {
    grid-template-columns: 1fr;
  }
}

.mode-card {
  transition: all 0.25s ease;
  background-color: #faf7fd;
  border-color: #edd4f8 !important;
}

.mode-card:hover {
  border-color: #c670ff !important;
  background-color: #ffffff;
}

.mode-card-active {
  border-color: #3d0066 !important;
  border-width: 2px !important;
  background-color: #ffffff;
  box-shadow: 0 8px 24px rgba(61, 0, 102, 0.12) !important;
}

/* Genre Grid */
.genre-grid {
  display: grid;
  grid-template-columns: repeat(2, 1fr);
  gap: 16px;
}

@media (max-width: 680px) {
  .genre-grid {
    grid-template-columns: 1fr;
  }
}

.genre-card {
  transition: all 0.25s ease;
  background-color: #ffffff;
  border-color: #edd4f8 !important;
}

.genre-card:hover {
  border-color: #c670ff !important;
  transform: translateY(-2px);
}

.genre-card-active {
  border-color: #3d0066 !important;
  border-width: 2px !important;
  box-shadow: 0 8px 20px rgba(61, 0, 102, 0.12) !important;
}

/* Theme Grid */
.theme-grid {
  display: grid;
  grid-template-columns: repeat(4, 1fr);
  gap: 14px;
}

@media (max-width: 900px) {
  .theme-grid {
    grid-template-columns: repeat(2, 1fr);
  }
}

@media (max-width: 480px) {
  .theme-grid {
    grid-template-columns: 1fr;
  }
}

.theme-card {
  transition: all 0.25s ease;
  background-color: #ffffff;
  border-color: #edd4f8 !important;
}

.theme-card:hover {
  border-color: #c670ff !important;
  transform: translateY(-2px);
}

.theme-card-active {
  border-color: #3d0066 !important;
  border-width: 2px !important;
  box-shadow: 0 0 0 2px #ffe047, 0 8px 20px rgba(61, 0, 102, 0.15) !important;
}

.theme-cover {
  height: 90px;
  background-size: cover;
  background-position: center;
}

.theme-overlay {
  height: 100%;
  background: linear-gradient(to top, rgba(0, 0, 0, 0.8) 0%, transparent 80%);
}

.line-clamp-2 {
  display: -webkit-box;
  -webkit-line-clamp: 2;
  -webkit-box-orient: vertical;
  overflow: hidden;
  text-overflow: ellipsis;
}
</style>
