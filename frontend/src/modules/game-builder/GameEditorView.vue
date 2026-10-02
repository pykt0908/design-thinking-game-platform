<template>
  <div v-if="gameBuilderStore.schema" class="game-builder-workspace">
    <!-- Top Toolbar per spec Section 19 -->
    <header class="builder-header px-4 py-2 bg-white border-b d-flex align-center justify-space-between flex-wrap gap-2">
      <div class="d-flex align-center">
        <v-btn icon="mdi-arrow-left" variant="text" size="small" to="/games" class="mr-2"></v-btn>
        <div>
          <div class="d-flex align-center">
            <input
              v-model="gameBuilderStore.schema.title"
              class="font-weight-bold text-subtitle-1 border-0 bg-transparent px-1 focus-outline"
              style="outline: none;"
            />
            <v-chip size="x-small" :color="gameBuilderStore.game?.status === 'published' ? 'success' : 'grey'" class="ml-2">
              {{ gameBuilderStore.game?.status === 'published' ? 'เผยแพร่แล้ว (v' + (gameBuilderStore.game?.current_version?.version_number || '1.0') + ')' : 'ฉบับร่าง (Draft)' }}
            </v-chip>
          </div>
          <div class="text-caption text-grey d-flex align-center gap-2">
            <span>ID: {{ gameBuilderStore.game?.public_id }}</span>
            <span>&bull;</span>
            <span class="font-weight-bold text-primary">🛠️ Studio Editor (หน้าจอแก้ไขเกม)</span>
          </div>
        </div>
      </div>

      <!-- Center Controls: Undo / Redo & Aspect Ratio presets -->
      <div class="d-flex align-center gap-2">
        <v-btn
          icon="mdi-undo"
          variant="text"
          size="small"
          :disabled="gameBuilderStore.undoStack.length === 0"
          @click="gameBuilderStore.undo()"
          title="ย้อนกลับ (Undo)"
        ></v-btn>
        <v-btn
          icon="mdi-redo"
          variant="text"
          size="small"
          :disabled="gameBuilderStore.redoStack.length === 0"
          @click="gameBuilderStore.redo()"
          title="ทำซ้ำ (Redo)"
        ></v-btn>

        <v-divider vertical class="mx-2 my-1"></v-divider>

        <!-- Aspect Ratio presets per spec Section 20 -->
        <v-btn-toggle
          v-model="gameBuilderStore.aspectRatio"
          mandatory
          density="compact"
          rounded="lg"
          color="primary"
        >
          <v-btn value="16:9" size="small">16:9</v-btn>
          <v-btn value="4:3" size="small">4:3</v-btn>
          <v-btn value="9:16" size="small">9:16</v-btn>
        </v-btn-toggle>
      </div>

      <!-- Right Actions: Save, Play Test, Publish -->
      <div class="d-flex align-center gap-2">
        <div v-if="gameBuilderStore.saveSuccess" class="text-caption text-success font-weight-bold mr-2 d-flex align-center">
          <v-icon icon="mdi-check-circle" size="14" class="mr-1"></v-icon> บันทึกเรียบร้อย
        </div>

        <v-btn
          variant="outlined"
          size="small"
          rounded="lg"
          :loading="gameBuilderStore.isSaving"
          @click="gameBuilderStore.saveGame(false)"
          prepend-icon="mdi-content-save-outline"
        >
          บันทึก
        </v-btn>

        <v-btn
          variant="flat"
          color="accent"
          size="small"
          rounded="lg"
          prepend-icon="mdi-play"
          @click="openPreview"
        >
          ทดสอบเล่น (Play)
        </v-btn>

        <v-btn
          class="ai-gradient-bg text-white font-weight-bold"
          size="small"
          rounded="lg"
          prepend-icon="mdi-publish"
          @click="publishGame"
        >
          เผยแพร่เกม (Publish)
        </v-btn>

        <v-divider vertical class="mx-1 my-1"></v-divider>

        <v-btn
          icon="mdi-trash-can-outline"
          color="error"
          variant="text"
          size="small"
          rounded="lg"
          title="ลบเกมนี้"
          @click="deleteCurrentGame"
        ></v-btn>
      </div>
    </header>

    <!-- IF HTML5 GAME: Interactive Live Studio with Sandbox Runner, AI Refiner, and Code Viewer -->
    <div v-if="isHtml5Game" class="builder-body d-flex">
      <!-- Left Panel: AI Refiner & Features Control -->
      <aside class="builder-tools border-r bg-white pa-4 d-flex flex-column justify-space-between" style="width: 380px;">
        <div class="overflow-y-auto pr-1">
          <!-- Game Info Badge -->
          <div class="d-flex align-center justify-space-between mb-3">
            <span class="text-caption font-weight-bold text-uppercase text-purple-darken-2">
              🎮 HTML5 Real Game Engine
            </span>
            <v-chip size="x-small" color="primary" variant="flat">
              v{{ gameBuilderStore.game?.current_version?.version_number || '1.0' }}
            </v-chip>
          </div>

          <!-- AI Refiner Box -->
          <v-card class="pa-4 mb-4 border-card rounded-xl bg-purple-lighten-5 elevation-1">
            <div class="d-flex align-center mb-2">
              <v-icon icon="mdi-creation" color="primary" class="mr-2" size="20"></v-icon>
              <span class="font-weight-bold text-body-2 text-slate-900">สั่ง AI ปรับแก้เกม (AI Refiner)</span>
            </div>
            <p class="text-caption text-grey mb-3">
              บอกให้ AI ปรับปรุงโค้ดเกม เช่น เพิ่มความเร็ว เพิ่มมอนสเตอร์ หรือเปลี่ยนกลไก
            </p>
            <v-textarea
              v-model="refinePrompt"
              placeholder="เช่น 'เพิ่มความเร็วการเดิน 2 เท่า', 'เพิ่มมอนสเตอร์บอส HP 150', 'เพิ่มแปลงผักเป็น 9 แปลง'..."
              variant="outlined"
              density="compact"
              rounded="lg"
              rows="3"
              auto-grow
              class="mb-2 bg-white"
            ></v-textarea>
            
            <!-- Quick Chips -->
            <div class="d-flex flex-wrap gap-1 mb-3">
              <v-chip size="x-small" variant="outlined" color="primary" @click="refinePrompt = 'เพิ่มความเร็วตัวละครและเพิ่มเวลาเป็น 3 นาที'">⚡ เพิ่มความเร็ว</v-chip>
              <v-chip size="x-small" variant="outlined" color="primary" @click="refinePrompt = 'เพิ่มมอนสเตอร์และศัตรูอีก 3 ตัว'">👾 เพิ่มมอนสเตอร์</v-chip>
              <v-chip size="x-small" variant="outlined" color="primary" @click="refinePrompt = 'เพิ่มพลังชีวิตเริ่มต้นเป็น 200 HP'">❤️ เพิ่มเลือด 200</v-chip>
            </div>

            <v-btn
              block
              class="ai-gradient-bg text-white font-weight-bold"
              rounded="lg"
              size="small"
              :loading="isRefining"
              :disabled="!refinePrompt"
              prepend-icon="mdi-auto-fix"
              @click="handleRefineGame"
            >
              สั่ง AI ปรับแก้โค้ดเกมทันที
            </v-btn>
          </v-card>

          <!-- Feature Toggles -->
          <div class="text-caption font-weight-bold text-slate-800 mb-2">ระบบในเกมที่เปิดใช้งาน (Enabled Systems):</div>
          <div class="d-flex flex-wrap gap-1 mb-4">
            <v-chip
              v-for="feat in (gameBuilderStore.schema?.features || ['map', 'health_bar', 'scoreboard', 'timer', 'inventory', 'dialogue', 'controls', 'sound_fx'])"
              :key="feat"
              size="x-small"
              color="purple-darken-1"
              variant="tonal"
              class="font-weight-medium"
            >
              ✓ {{ formatFeatureName(feat) }}
            </v-chip>
          </div>
        </div>

        <!-- Bottom Actions -->
        <div class="border-t pt-3 d-flex flex-column gap-2">
          <v-btn
            variant="outlined"
            color="primary"
            size="small"
            rounded="lg"
            prepend-icon="mdi-code-tags"
            @click="showCodeModal = true"
          >
            ดูโค้ด HTML5 Bundle
          </v-btn>
          <v-btn
            variant="tonal"
            color="purple"
            size="small"
            rounded="lg"
            prepend-icon="mdi-download"
            @click="downloadHtmlFile"
          >
            ดาวน์โหลดไฟล์ .html
          </v-btn>
        </div>
      </aside>

      <!-- Center Runner Frame -->
      <main class="builder-canvas-wrapper d-flex flex-column align-center justify-center pa-4">
        <!-- Live Runner Header Bar -->
        <div class="d-flex justify-space-between align-center w-100 max-w-4xl mb-2 text-white">
          <div class="d-flex align-center gap-2">
            <span class="text-caption font-weight-bold">🎮 Live Interactive Sandbox</span>
            <span class="text-caption text-grey">(คลิกบนจอเพื่อเล่นและทดสอบได้ทันที)</span>
          </div>
          <div class="d-flex gap-2">
            <v-btn size="x-small" variant="outlined" color="white" prepend-icon="mdi-reload" @click="reloadIframe">
              รีโหลดเกม
            </v-btn>
            <v-btn
              size="x-small"
              color="success"
              variant="flat"
              prepend-icon="mdi-play"
              :to="`/play/${gameBuilderStore.game?.public_id}?preview=true`"
              target="_blank"
            >
              เปิดเล่นเต็มจอ
            </v-btn>
          </div>
        </div>

        <!-- Sandboxed Game Iframe -->
        <div class="game-runner-card w-100 max-w-4xl elevation-4 rounded-2xl overflow-hidden" style="height: 78vh; border: 2px solid rgba(198, 112, 255, 0.4); background: #000;">
          <iframe
            ref="editorIframeRef"
            :srcdoc="gameBuilderStore.schema.bundle || gameBuilderStore.schema.html"
            class="w-100 h-100 border-0"
            sandbox="allow-scripts allow-same-origin allow-modals"
            allow="fullscreen; autoplay"
          ></iframe>
        </div>
      </main>

      <!-- Code Inspector Modal -->
      <v-dialog v-model="showCodeModal" max-width="800">
        <v-card class="pa-5 rounded-2xl">
          <div class="d-flex justify-space-between align-center mb-3">
            <h3 class="text-subtitle-1 font-weight-bold d-flex align-center">
              <v-icon icon="mdi-code-braces" color="primary" class="mr-2"></v-icon>
              โค้ดเกม HTML5 Standalone Bundle
            </h3>
            <v-btn size="small" variant="text" color="primary" prepend-icon="mdi-content-copy" @click="copyCode">
              คัดลอกโค้ด
            </v-btn>
          </div>
          <v-textarea
            :model-value="gameBuilderStore.schema.bundle || gameBuilderStore.schema.html"
            readonly
            variant="outlined"
            density="compact"
            rows="16"
            class="font-mono text-caption"
          ></v-textarea>
          <div class="d-flex justify-end mt-3">
            <v-btn variant="text" @click="showCodeModal = false">ปิด</v-btn>
          </div>
        </v-card>
      </v-dialog>
    </div>

    <!-- Legacy Scene/Element Editor (if not HTML5) -->
    <div v-else class="builder-body d-flex">
      <!-- Left Tools Palette per spec Section 21 -->
      <aside class="builder-tools border-r bg-white pa-3">
        <div class="text-caption font-weight-bold text-grey text-uppercase mb-2">
          องค์ประกอบเกม (Components)
        </div>

        <div class="tool-list d-flex flex-column gap-2">
          <v-btn
            variant="tonal"
            color="primary"
            prepend-icon="mdi-account-cowboy-hat"
            class="justify-start text-caption"
            @click="addCharacterElement"
          >
            + ตัวละคร (Character)
          </v-btn>

          <v-btn
            variant="tonal"
            color="secondary"
            prepend-icon="mdi-comment-text-outline"
            class="justify-start text-caption"
            @click="addDialogueElement"
          >
            + กล่องสนทนา (Dialogue)
          </v-btn>

          <v-btn
            variant="tonal"
            color="indigo"
            prepend-icon="mdi-help-circle-outline"
            class="justify-start text-caption"
            @click="addMultipleChoiceQuiz"
          >
            + คำถาม 4 ตัวเลือก (Quiz)
          </v-btn>

          <v-btn
            variant="tonal"
            color="cyan"
            prepend-icon="mdi-checkbox-marked-circle-outline"
            class="justify-start text-caption"
            @click="addTrueFalseQuiz"
          >
            + ถูกหรือผิด (True/False)
          </v-btn>

          <v-btn
            variant="tonal"
            color="warning"
            prepend-icon="mdi-trophy-outline"
            class="justify-start text-caption"
            @click="addCompletionElement"
          >
            + สรุปผลภารกิจ (Finish)
          </v-btn>
        </div>

        <v-divider class="my-4"></v-divider>

        <!-- Scene Manager per spec Section 19 -->
        <div class="d-flex justify-space-between align-center mb-2">
          <span class="text-caption font-weight-bold text-grey text-uppercase">ฉากในเกม (Scenes)</span>
          <v-btn icon="mdi-plus" size="x-small" variant="text" color="primary" @click="gameBuilderStore.addScene()"></v-btn>
        </div>

        <v-list density="compact" nav class="pa-0">
          <v-list-item
            v-for="(scene, idx) in gameBuilderStore.schema.scenes"
            :key="scene.id"
            :active="gameBuilderStore.activeSceneId === scene.id"
            @click="gameBuilderStore.activeSceneId = scene.id"
            rounded="lg"
            class="mb-1"
          >
            <template #prepend>
              <span class="text-caption text-grey mr-2">{{ idx + 1 }}.</span>
            </template>
            <v-list-item-title class="text-caption font-weight-medium">
              {{ scene.title }}
            </v-list-item-title>
            <template #append>
              <v-btn
                v-if="gameBuilderStore.schema.scenes.length > 1"
                icon="mdi-delete-outline"
                size="x-small"
                variant="text"
                color="grey"
                @click.stop="gameBuilderStore.deleteScene(scene.id)"
              ></v-btn>
            </template>
          </v-list-item>
        </v-list>
      </aside>

      <!-- Center Canvas per spec Section 20 -->
      <main class="builder-canvas-wrapper d-flex flex-column align-center justify-center pa-4">
        <div
          class="game-canvas elevation-3 rounded-xl position-relative overflow-hidden"
          :class="`aspect-${gameBuilderStore.aspectRatio.replace(':', '-')}`"
        >
          <!-- Canvas Top Mission Bar Preview -->
          <div class="canvas-top-bar d-flex justify-space-between align-center px-4 py-2 text-white">
            <span class="text-caption font-weight-bold">
              {{ gameBuilderStore.activeScene?.title || 'ฉากการเล่น' }}
            </span>
            <div class="d-flex align-center gap-3">
              <v-chip size="x-small" color="white" variant="outlined">
                <v-icon start icon="mdi-timer-outline"></v-icon> 05:00
              </v-chip>
              <v-chip size="x-small" color="amber" variant="flat">
                <v-icon start icon="mdi-star" size="14"></v-icon> 100 แต้ม
              </v-chip>
            </div>
          </div>

          <!-- Canvas Active Scene Area -->
          <div class="canvas-content h-100 pa-4 d-flex flex-column justify-center align-center">
            <div
              v-for="el in gameBuilderStore.activeScene?.elements"
              :key="el.id"
              class="canvas-element pa-3 rounded-lg mb-3 cursor-pointer"
              :class="{ 'element-selected': gameBuilderStore.selectedElementId === el.id }"
              @click.stop="gameBuilderStore.selectedElementId = el.id"
            >
              <!-- Character Element -->
              <div v-if="el.type === 'character'" class="d-flex align-center">
                <v-avatar size="52" class="mr-3 bg-white elevation-1">
                  <v-img :src="el.avatar || 'https://api.dicebear.com/7.x/bottts/svg?seed=Teacher'"></v-img>
                </v-avatar>
                <div class="bg-white pa-3 rounded-xl border-card elevation-1 max-w-md">
                  <div class="text-caption font-weight-bold text-primary">{{ el.name || 'ตัวละคร' }}</div>
                  <div class="text-body-2 text-slate-800">{{ el.dialogue?.text || 'ข้อความบทสนทนา...' }}</div>
                  <v-btn size="x-small" color="primary" class="mt-2 font-weight-medium">
                    {{ el.dialogue?.actionText || 'ดำเนินการต่อ' }} &rarr;
                  </v-btn>
                </div>
              </div>

              <!-- Question Element -->
              <div v-else-if="el.type === 'question'" class="bg-white pa-4 rounded-xl border-card elevation-1 w-100 max-w-lg">
                <div class="d-flex justify-space-between align-center mb-2">
                  <v-chip size="x-small" color="primary">คำถาม: {{ el.points || 10 }} คะแนน</v-chip>
                  <span class="text-caption text-grey">{{ el.questionType === 'true_false' ? 'ถูก/ผิด' : '4 ตัวเลือก' }}</span>
                </div>
                <div class="font-weight-bold text-subtitle-2 text-slate-800 mb-3">
                  {{ el.question }}
                </div>
                <div v-if="el.image" class="mb-3 text-center">
                  <img :src="el.image" class="rounded-lg" style="max-height: 120px; object-fit: cover;" />
                </div>
                <div class="options-grid d-flex flex-column gap-2">
                  <div
                    v-for="opt in el.options"
                    :key="opt.id"
                    class="pa-2 rounded-lg border text-caption d-flex align-center justify-space-between"
                    :class="{ 'bg-green-lighten-5 border-success text-success': opt.isCorrect }"
                  >
                    <span>{{ opt.text }}</span>
                    <v-icon v-if="opt.isCorrect" icon="mdi-check-circle" size="16" color="success"></v-icon>
                  </div>
                </div>
              </div>

              <!-- Completion Element -->
              <div v-else-if="el.type === 'completion'" class="bg-white pa-6 rounded-xl border-card elevation-2 text-center max-w-md">
                <v-avatar size="56" color="amber-lighten-4" class="mb-2">
                  <v-icon icon="mdi-trophy" color="warning" size="32"></v-icon>
                </v-avatar>
                <div class="text-h6 font-weight-bold text-slate-800">{{ el.title || 'ภารกิจสำเร็จ!' }}</div>
                <div class="text-body-2 text-grey mb-3">{{ el.message }}</div>
                <v-chip color="success" class="font-weight-bold">ผ่านเกณฑ์ยอดเยี่ยม</v-chip>
              </div>
            </div>

            <!-- Empty Scene Placeholder -->
            <div
              v-if="!gameBuilderStore.activeScene?.elements || gameBuilderStore.activeScene.elements.length === 0"
              class="text-center pa-8 text-white-50"
            >
              <v-icon icon="mdi-plus-box-outline" size="40" class="mb-2 opacity-50"></v-icon>
              <div>ฉากนี้ยังไม่มีองค์ประกอบ</div>
              <div class="text-caption">คลิกเลือกเมนูด้านซ้ายเพื่อเพิ่มตัวละคร หรือคำถาม</div>
            </div>
          </div>
        </div>
      </main>

      <!-- Right Property Inspector per spec Section 22 -->
      <aside class="builder-inspector border-l bg-white pa-4">
        <div class="text-caption font-weight-bold text-grey text-uppercase mb-3">
          คุณสมบัติ (Properties)
        </div>

        <div v-if="gameBuilderStore.selectedElement">
          <div class="d-flex justify-space-between align-center mb-3">
            <span class="text-subtitle-2 font-weight-bold text-primary">
              {{ formatElementType(gameBuilderStore.selectedElement.type) }}
            </span>
            <v-btn
              icon="mdi-trash-can-outline"
              size="x-small"
              variant="text"
              color="error"
              @click="gameBuilderStore.removeElement(gameBuilderStore.selectedElement.id)"
              title="ลบองค์ประกอบนี้"
            ></v-btn>
          </div>

          <!-- Character Inspector -->
          <div v-if="gameBuilderStore.selectedElement.type === 'character'">
            <v-text-field
              v-model="gameBuilderStore.selectedElement.name"
              label="ชื่อตัวละคร"
              density="compact"
              class="mb-2"
            ></v-text-field>

            <v-textarea
              v-if="gameBuilderStore.selectedElement.dialogue"
              v-model="gameBuilderStore.selectedElement.dialogue.text"
              label="บทสนทนา"
              density="compact"
              rows="3"
              class="mb-2"
            ></v-textarea>

            <v-text-field
              v-if="gameBuilderStore.selectedElement.dialogue"
              v-model="gameBuilderStore.selectedElement.dialogue.actionText"
              label="ข้อความปุ่มกด"
              density="compact"
              class="mb-2"
            ></v-text-field>
          </div>

          <!-- Question Inspector -->
          <div v-else-if="gameBuilderStore.selectedElement.type === 'question'">
            <v-textarea
              v-model="gameBuilderStore.selectedElement.question"
              label="ข้อความคำถาม"
              density="compact"
              rows="3"
              class="mb-2"
            ></v-textarea>

            <v-text-field
              v-model.number="gameBuilderStore.selectedElement.points"
              type="number"
              label="คะแนนที่ได้รับ"
              density="compact"
              class="mb-3"
            ></v-text-field>

            <div class="text-caption font-weight-bold text-grey mb-1">ตัวเลือกและคำตอบที่ถูกต้อง:</div>
            <div v-for="(opt, i) in gameBuilderStore.selectedElement.options" :key="opt.id" class="d-flex align-center gap-1 mb-2">
              <v-checkbox-btn
                v-model="opt.isCorrect"
                color="success"
                title="ตั้งเป็นคำตอบที่ถูก"
              ></v-checkbox-btn>
              <v-text-field
                v-model="opt.text"
                density="compact"
                hide-details
                placeholder="ข้อความตัวเลือก"
              ></v-text-field>
            </div>

            <v-textarea
              v-model="gameBuilderStore.selectedElement.explanation"
              label="คำอธิบายเพื่อการเรียนรู้ (เมื่อตอบ)"
              density="compact"
              rows="2"
              class="mt-3"
            ></v-textarea>
          </div>

          <!-- Completion Inspector -->
          <div v-else-if="gameBuilderStore.selectedElement.type === 'completion'">
            <v-text-field
              v-model="gameBuilderStore.selectedElement.title"
              label="หัวข้อความสำเร็จ"
              density="compact"
              class="mb-2"
            ></v-text-field>
            <v-textarea
              v-model="gameBuilderStore.selectedElement.message"
              label="ข้อความแสดงความยินดี"
              density="compact"
              rows="3"
            ></v-textarea>
          </div>
        </div>

        <div v-else class="text-center pa-8 text-grey">
          <v-icon icon="mdi-cursor-default-click" size="32" class="mb-2 text-grey-lighten-1"></v-icon>
          <div class="text-caption">คลิกเลือกองค์ประกอบบนหน้าจอเพื่อแก้ไขคุณสมบัติ</div>
        </div>
      </aside>
    </div>

    <!-- Live Preview Modal -->
    <v-dialog v-model="showPreviewModal" fullscreen>
      <v-card color="slate-900" class="position-relative">
        <v-btn
          icon="mdi-close"
          variant="flat"
          color="white"
          class="position-absolute"
          style="top: 16px; right: 16px; z-index: 100;"
          @click="showPreviewModal = false"
        ></v-btn>
        <iframe
          v-if="showPreviewModal"
          :src="`/play/${gameBuilderStore.game?.public_id}?preview=true`"
          style="width: 100vw; height: 100vh; border: none;"
        ></iframe>
      </v-card>
    </v-dialog>
  </div>
</template>

<script setup lang="ts">
import { ref, computed, onMounted } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { useGameBuilderStore } from '@/stores/gameBuilder'
import { useAlertStore } from '@/stores/alert'
import apiClient from '@/api/client'
import type { GameElement } from '@/types'

const route = useRoute()
const router = useRouter()
const gameBuilderStore = useGameBuilderStore()
const alertStore = useAlertStore()
const showPreviewModal = ref(false)

// HTML5 Studio State
const refinePrompt = ref('')
const isRefining = ref(false)
const showCodeModal = ref(false)
const editorIframeRef = ref<HTMLIFrameElement | null>(null)

const isHtml5Game = computed(() => {
  return gameBuilderStore.schema?.type === 'html5' || !!gameBuilderStore.schema?.bundle || !(gameBuilderStore.schema?.scenes?.length)
})

function formatFeatureName(key: string) {
  const map: Record<string, string> = {
    map: 'ระบบแผนที่โลก',
    health_bar: 'หลอดเลือด HP',
    scoreboard: 'กระดานคะแนน',
    timer: 'ตัวจับเวลา',
    inventory: 'กระเป๋าเก็บไอเทม',
    dialogue: 'บทสนทนา NPC',
    controls: 'ปุ่มสัมผัสมือถือ',
    sound_fx: 'เสียงเอฟเฟกต์',
    skills: 'ท่าไม้ตาย/สตามินา',
  }
  return map[key] || key
}

function reloadIframe() {
  if (editorIframeRef.value) {
    const src = editorIframeRef.value.srcdoc
    editorIframeRef.value.srcdoc = ''
    setTimeout(() => {
      if (editorIframeRef.value) editorIframeRef.value.srcdoc = src
    }, 50)
  }
}

async function handleRefineGame() {
  if (!refinePrompt.value || !gameBuilderStore.game) return
  isRefining.value = true
  try {
    const { data } = await apiClient.post(`/games/${gameBuilderStore.game.id}/refine-html5`, {
      prompt: refinePrompt.value,
      features: gameBuilderStore.schema?.features || ['map', 'health_bar', 'scoreboard', 'controls', 'sound_fx'],
      assets: gameBuilderStore.schema?.assets || {},
      api_key: localStorage.getItem('user_gemini_api_key') || undefined,
    })
    alertStore.success('ปรับปรุงโค้ดเกมด้วย AI เรียบร้อยแล้ว!')
    await gameBuilderStore.loadGame(gameBuilderStore.game.id)
    refinePrompt.value = ''
    reloadIframe()
  } catch (err: any) {
    console.error(err)
    alertStore.error(err.response?.data?.message || 'ไม่สามารถปรับแก้เกมได้ กรุณาลองใหม่อีกครั้ง')
  } finally {
    isRefining.value = false
  }
}

function copyCode() {
  const code = gameBuilderStore.schema?.bundle || gameBuilderStore.schema?.html || ''
  navigator.clipboard.writeText(code)
  alertStore.success('คัดลอกโค้ด HTML5 เรียบร้อยแล้ว!')
}

function downloadHtmlFile() {
  const code = gameBuilderStore.schema?.bundle || gameBuilderStore.schema?.html || ''
  const blob = new Blob([code], { type: 'text/html;charset=utf-8' })
  const url = URL.createObjectURL(blob)
  const a = document.createElement('a')
  a.href = url
  a.download = (gameBuilderStore.game?.title || 'game') + '.html'
  a.click()
  URL.revokeObjectURL(url)
  alertStore.success('ดาวน์โหลดไฟล์เกมสำเร็จ!')
}

function formatElementType(type: string) {
  switch (type) {
    case 'character': return 'ตัวละคร (Character)'
    case 'question': return 'คำถาม (Quiz)'
    case 'dialogue': return 'กล่องสนทนา (Dialogue)'
    case 'completion': return 'ฉากจบภารกิจ (Finish)'
    default: return type
  }
}

function addCharacterElement() {
  const el: GameElement = {
    id: 'char_' + Date.now().toString(36),
    type: 'character',
    name: 'คุณครูผู้แนะนำ',
    avatar: 'https://api.dicebear.com/7.x/bottts/svg?seed=' + Math.random().toString(),
    dialogue: {
      text: 'ยินดีต้อนรับสู่ภารกิจการเรียนรู้! อ่านโจทย์ให้ละเอียดก่อนตอบนะ',
      actionText: 'เข้าใจแล้ว &rarr;',
    },
  }
  gameBuilderStore.addElementToActiveScene(el)
}

function addDialogueElement() {
  const el: GameElement = {
    id: 'dial_' + Date.now().toString(36),
    type: 'character',
    name: 'คำแนะนำภารกิจ',
    avatar: 'https://api.dicebear.com/7.x/bottts/svg?seed=Hint',
    dialogue: {
      text: 'คำใบ้สำคัญ: สังเกตสีของถังขยะและประเภทวัสดุให้รอบคอบ!',
      actionText: 'ลุยเลย',
    },
  }
  gameBuilderStore.addElementToActiveScene(el)
}

function addMultipleChoiceQuiz() {
  const el: GameElement = {
    id: 'q_' + Date.now().toString(36),
    type: 'question',
    questionType: 'multiple_choice',
    question: 'พิมพ์คำถามของคุณที่นี่...',
    points: 25,
    options: [
      { id: 'opt_1', text: 'ตัวเลือกที่ 1 (ถูกต้อง)', isCorrect: true },
      { id: 'opt_2', text: 'ตัวเลือกที่ 2', isCorrect: false },
      { id: 'opt_3', text: 'ตัวเลือกที่ 3', isCorrect: false },
      { id: 'opt_4', text: 'ตัวเลือกที่ 4', isCorrect: false },
    ],
    explanation: 'อธิบายว่าทำไมข้อนี้ถึงถูกต้อง เพื่อสร้างการเรียนรู้',
  }
  gameBuilderStore.addElementToActiveScene(el)
}

function addTrueFalseQuiz() {
  const el: GameElement = {
    id: 'tf_' + Date.now().toString(36),
    type: 'question',
    questionType: 'true_false',
    question: 'จริงหรือไม่? ข้อความนี้ถูกต้องตามหลักวิชาการ',
    points: 20,
    options: [
      { id: 'tf_true', text: 'จริง (ถูกต้อง)', isCorrect: true },
      { id: 'tf_false', text: 'ไม่จริง', isCorrect: false },
    ],
    explanation: 'คำอธิบายเสริมความรู้สำหรับนักเรียน',
  }
  gameBuilderStore.addElementToActiveScene(el)
}

function addCompletionElement() {
  const el: GameElement = {
    id: 'comp_' + Date.now().toString(36),
    type: 'completion',
    title: 'ภารกิจสำเร็จยอดเยี่ยม!',
    message: 'คุณได้ผ่านการทดสอบครบทุกด่านแล้ว ขอแสดงความยินดีด้วย!',
  }
  gameBuilderStore.addElementToActiveScene(el)
}

function openPreview() {
  showPreviewModal.value = true
}

async function publishGame() {
  await gameBuilderStore.saveGame(true)
  alertStore.success('เผยแพร่เกมเรียบร้อยแล้ว! นักเรียนสามารถเข้าเล่นผ่านรหัส ' + (gameBuilderStore.game?.public_id || ''), 'เผยแพร่เกมสำเร็จ')
}

async function deleteCurrentGame() {
  const g = gameBuilderStore.game
  if (!g) return

  const confirmed = await alertStore.confirm(
    `คุณต้องการลบเกม "${g.title}" (รหัส: ${g.public_id}) ใช่หรือไม่? ข้อมูลทั้งหมดจะถูกลบและไม่สามารถกู้คืนได้`,
    'ยืนยันการลบเกม',
    {
      confirmText: 'ลบเกมทันที',
      cancelText: 'ยกเลิก',
      type: 'error',
    }
  )

  if (!confirmed) return

  try {
    await apiClient.delete(`/games/${g.id}`)
    alertStore.success('ลบเกมเรียบร้อยแล้ว')
    router.push('/games')
  } catch (err: any) {
    console.error('Failed to delete game', err)
    alertStore.error(err.response?.data?.message || 'ไม่สามารถลบเกมได้')
  }
}

onMounted(async () => {
  const gameId = Number(route.params.id)
  await gameBuilderStore.loadGame(gameId)
})
</script>

<style scoped>
.game-builder-workspace {
  display: flex;
  flex-direction: column;
  height: calc(100vh - 64px);
  margin: -24px;
}
.builder-header {
  height: 58px;
  flex-shrink: 0;
}
.builder-body {
  flex: 1;
  display: flex;
  overflow: hidden;
}
.builder-tools {
  width: 240px;
  overflow-y: auto;
  flex-shrink: 0;
}
.builder-canvas-wrapper {
  flex: 1;
  background-color: #1e293b;
  overflow: auto;
}
.builder-inspector {
  width: 320px;
  overflow-y: auto;
  flex-shrink: 0;
}
.game-canvas {
  width: 800px;
  max-width: 90%;
  background: radial-gradient(circle at 50% 50%, #334155 0%, #0f172a 100%);
  display: flex;
  flex-direction: column;
  border: 2px solid rgba(255, 255, 255, 0.1);
}
.aspect-16-9 {
  aspect-ratio: 16 / 9;
}
.aspect-4-3 {
  aspect-ratio: 4 / 3;
}
.aspect-9-16 {
  aspect-ratio: 9 / 16;
  width: 420px;
}
.canvas-top-bar {
  background-color: rgba(15, 23, 42, 0.7);
  backdrop-filter: blur(8px);
}
.element-selected {
  outline: 2px solid #c670ff;
  border-radius: 12px;
}
.gap-1 { gap: 4px; }
.gap-2 { gap: 8px; }
.gap-3 { gap: 12px; }
.cursor-pointer { cursor: pointer; }
.max-w-md { max-width: 380px; }
.max-w-lg { max-width: 480px; }
</style>
