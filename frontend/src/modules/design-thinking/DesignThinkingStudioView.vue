<template>
  <div v-if="project" class="dt-luxury-studio">
    <!-- Top Executive Bar: Project title, Game Mode, and Auto-save status -->
    <v-card class="pa-4 mb-5 luxury-top-card rounded-2xl elevation-2">
      <div class="d-flex align-center justify-space-between flex-wrap gap-3">
        <div class="d-flex align-center">
          <v-btn
            icon="mdi-arrow-left"
            variant="tonal"
            color="white"
            to="/projects"
            class="mr-3 glass-btn"
            density="comfortable"
          ></v-btn>
          <div>
            <div class="d-flex align-center flex-wrap gap-2 mb-1">
              <h1 class="text-h6 font-weight-bold text-white mb-0 d-flex align-center">
                {{ project.title }}
              </h1>
              <v-chip size="x-small" class="luxury-step-badge font-weight-bold">
                ขั้นตอนที่ {{ currentStepIndex + 1 }} จาก 5: {{ steps[currentStepIndex].title }}
              </v-chip>
              <v-chip
                v-if="project.game_mode"
                size="x-small"
                class="mode-chip font-weight-medium"
              >
                <v-icon
                  :icon="project.game_mode === 'multiplayer_live' ? 'mdi-account-group' : 'mdi-account-star'"
                  size="13"
                  class="mr-1"
                ></v-icon>
                {{ project.game_mode === 'multiplayer_live' ? 'ห้องเรียนถ่ายทอดสด (Kahoot)' : 'ผจญภัยเล่นเดี่ยว (RPG)' }}
              </v-chip>
            </div>
            <div class="d-flex align-center flex-wrap gap-2 text-caption text-purple-lighten-4">
              <span><v-icon icon="mdi-book-open-outline" size="14" class="mr-1"></v-icon>วิชา: {{ project.subject || 'ทั่วไป' }}</span>
              <span>&bull;</span>
              <span><v-icon icon="mdi-school-outline" size="14" class="mr-1"></v-icon>ระดับชั้น: {{ project.grade_level || 'ทุกระดับ' }}</span>
              <template v-if="project.theme_pack">
                <span>&bull;</span>
                <span><v-icon icon="mdi-palette-outline" size="14" class="mr-1"></v-icon>ธีม: {{ project.theme_pack }}</span>
              </template>
            </div>
          </div>
        </div>

        <div class="d-flex align-center gap-2 flex-wrap">
          <!-- Auto-save status indicator with glowing pulse -->
          <div class="autosave-pill px-3 py-1 rounded-pill d-flex align-center text-caption font-weight-medium">
            <template v-if="projectStore.saveStatus === 'saving'">
              <v-progress-circular indeterminate size="12" width="2" color="amber-accent-2" class="mr-2"></v-progress-circular>
              <span class="text-amber-accent-2">กำลังบันทึก...</span>
            </template>
            <template v-else-if="projectStore.saveStatus === 'saved'">
              <span class="pulse-dot green mr-2"></span>
              <span class="text-green-accent-2">บันทึกอัตโนมัติแล้ว</span>
            </template>
            <template v-else-if="projectStore.saveStatus === 'error'">
              <v-icon icon="mdi-alert-circle" color="red-lighten-2" size="14" class="mr-1"></v-icon>
              <span class="text-red-lighten-2">บันทึกล้มเหลว</span>
            </template>
            <template v-else>
              <span class="pulse-dot grey mr-2"></span>
              <span class="text-grey-lighten-2">พร้อมบันทึกอัตโนมัติ</span>
            </template>
          </div>

          <!-- AI Assistant General Drawer -->
          <v-btn
            class="luxury-ai-btn text-white font-weight-bold"
            prepend-icon="mdi-creation"
            rounded="xl"
            @click="openAiAssistant"
            size="small"
          >
            AI ที่ปรึกษา
          </v-btn>

          <!-- Delete Project -->
          <v-btn
            icon="mdi-trash-can-outline"
            variant="text"
            color="red-lighten-3"
            size="small"
            @click="handleDeleteProject"
            title="ลบโปรเจกต์นี้"
          ></v-btn>

          <!-- Generate Game Ready Button in header -->
          <v-btn
            color="amber-accent-3"
            class="text-slate-900 font-weight-bold generate-nav-btn px-4"
            rounded="xl"
            size="small"
            prepend-icon="mdi-rocket-launch"
            @click="showGenerateModal = true"
          >
            สร้างเกม (Generate Game)
          </v-btn>
        </div>
      </div>
    </v-card>

    <!-- Main Stepper & Workspace Layout -->
    <v-row>
      <!-- Left Step Navigation (Luxury Executive Stepper) -->
      <v-col cols="12" md="3">
        <v-card class="pa-3 luxury-nav-card rounded-2xl mb-4">
          <div class="d-flex align-center justify-space-between px-3 py-2 mb-2">
            <span class="text-caption font-weight-bold text-uppercase tracking-wider text-purple-lighten-2">
              กระบวนการ 5 ขั้นตอน
            </span>
            <span class="text-caption text-grey-darken-1 font-weight-bold">
              {{ completedStepsCount }}/5 เสร็จสมบูรณ์
            </span>
          </div>

          <div class="steps-nav-list">
            <div
              v-for="(s, idx) in steps"
              :key="s.id"
              class="step-nav-item mb-2 rounded-xl pa-3 cursor-pointer transition-all"
              :class="{
                'step-active': currentStepIndex === idx,
                'step-completed': isStepCompleted(s.id),
              }"
              @click="goToStep(idx)"
            >
              <div class="d-flex align-center justify-space-between">
                <div class="d-flex align-center">
                  <div class="step-num-badge mr-3">
                    0{{ idx + 1 }}
                  </div>
                  <div>
                    <div class="font-weight-bold text-subtitle-2 step-title d-flex align-center">
                      <v-icon :icon="s.icon" size="16" class="mr-1.5 step-icon"></v-icon>
                      {{ s.title }}
                    </div>
                    <div class="text-caption step-subtitle text-truncate">
                      {{ s.thaiName }}
                    </div>
                  </div>
                </div>

                <div>
                  <v-icon
                    v-if="isStepCompleted(s.id)"
                    icon="mdi-check-circle"
                    color="success"
                    size="18"
                  ></v-icon>
                  <v-icon
                    v-else-if="currentStepIndex === idx"
                    icon="mdi-chevron-right"
                    color="primary"
                    size="20"
                  ></v-icon>
                  <v-icon
                    v-else
                    icon="mdi-circle-outline"
                    color="grey-lighten-1"
                    size="16"
                  ></v-icon>
                </div>
              </div>
            </div>
          </div>

          <!-- AI Supercharge Tip in Sidebar -->
          <v-sheet class="pa-3 mt-3 rounded-xl ai-sidebar-tip">
            <div class="d-flex align-center mb-1">
              <v-icon icon="mdi-auto-fix" size="16" color="primary" class="mr-1.5"></v-icon>
              <span class="text-caption font-weight-bold text-slate-800">AI Co-Pilot พร้อมช่วยคิด</span>
            </div>
            <p class="text-caption text-slate-600 mb-0 leading-tight">
              กดปุ่ม <span class="font-weight-bold text-primary">✨ AI ช่วยคิด</span> ในแต่ละช่อง หรือกดปุ่ม <span class="font-weight-bold text-purple-darken-1">✨ เติมทั้งขั้นตอน</span> ด้านบน เพื่อสร้างข้อความแนะนำทันที
            </p>
          </v-sheet>
        </v-card>
      </v-col>

      <!-- Center Active Step Workspace -->
      <v-col cols="12" md="9">
        <v-card class="pa-6 luxury-workspace-card rounded-2xl">
          <!-- Step Hero Header Banner with Auto-Fill Action -->
          <div class="step-hero-banner pa-5 rounded-2xl mb-6 d-flex align-center justify-space-between flex-wrap gap-3">
            <div class="d-flex align-center">
              <div class="step-hero-icon-box mr-4">
                <v-icon :icon="steps[currentStepIndex].icon" size="30" color="white"></v-icon>
              </div>
              <div>
                <div class="d-flex align-center gap-2 mb-1">
                  <h2 class="text-h6 font-weight-bold text-slate-900 mb-0">
                    {{ currentStepIndex + 1 }}. {{ steps[currentStepIndex].title }} ({{ steps[currentStepIndex].thaiName }})
                  </h2>
                  <v-chip size="x-small" color="primary" variant="flat" class="font-weight-bold">
                    STEP 0{{ currentStepIndex + 1 }}
                  </v-chip>
                </div>
                <div class="text-caption text-slate-600">
                  {{ steps[currentStepIndex].description }}
                </div>
              </div>
            </div>

            <!-- "✨ ให้ AI เติมให้ทั้งขั้นตอนนี้" Button -->
            <v-btn
              class="ai-autofill-btn text-white font-weight-bold px-4"
              rounded="xl"
              size="small"
              prepend-icon="mdi-auto-fix"
              :loading="stepAutoFillLoading"
              @click="handleStepAutoFill(steps[currentStepIndex].id)"
            >
              ✨ ให้ AI เติมให้ทั้งขั้นตอนนี้
            </v-btn>
          </div>

          <!-- ================= STEP 1: EMPATHIZE ================= -->
          <div v-if="currentStepIndex === 0" class="step-form-body">
            <v-row>
              <!-- Field 1: Target Learner -->
              <v-col cols="12" sm="6">
                <div class="field-container mb-1">
                  <div class="field-header d-flex align-center justify-space-between mb-1.5">
                    <label class="field-label">
                      <v-icon icon="mdi-account-group-outline" size="16" class="mr-1 text-primary"></v-icon>
                      กลุ่มผู้เรียนเป้าหมาย (Target Learner)
                    </label>
                    <v-btn
                      size="x-small"
                      variant="tonal"
                      class="ai-field-btn"
                      :loading="activeFieldLoading === 'target_learner'"
                      @click="requestFieldAi('empathize', 'target_learner', 'กลุ่มผู้เรียนเป้าหมาย')"
                    >
                      <v-icon icon="mdi-creation" size="13" class="mr-1 text-primary"></v-icon>
                      ✨ AI ช่วยคิด
                    </v-btn>
                  </div>
                  <v-text-field
                    v-model="empathizeData.target_learner"
                    placeholder="เช่น นักเรียนชั้น ม.1 ที่ต้องการพัฒนาความรู้เรื่องสิ่งแวดล้อม"
                    variant="outlined"
                    density="comfortable"
                    rounded="lg"
                    @update:model-value="onFieldChange('empathize', empathizeData)"
                  ></v-text-field>

                  <!-- Inline AI Suggestion Box -->
                  <div v-if="fieldSuggestions['target_learner']" class="ai-suggestion-card pa-3 rounded-xl mb-3">
                    <div class="d-flex align-center justify-space-between mb-2">
                      <span class="text-caption font-weight-bold text-primary d-flex align-center">
                        <v-icon icon="mdi-sparkles" size="14" class="mr-1"></v-icon>
                        คำแนะนำจาก AI
                      </span>
                      <v-btn icon="mdi-close" variant="text" size="x-small" @click="dismissSuggestion('target_learner')"></v-btn>
                    </div>
                    <p class="text-body-2 mb-2 text-slate-800">{{ fieldSuggestions['target_learner'].suggestion }}</p>
                    <div class="d-flex align-center gap-2 flex-wrap">
                      <v-btn size="x-small" color="primary" class="font-weight-bold" rounded="lg" @click="applyFieldSuggestion('empathize', 'target_learner')">
                        นำไปใช้ทันที
                      </v-btn>
                      <v-btn v-if="empathizeData.target_learner" size="x-small" variant="outlined" rounded="lg" @click="appendFieldSuggestion('empathize', 'target_learner')">
                        ต่อท้ายข้อความเดิม
                      </v-btn>
                      <v-btn size="x-small" variant="text" icon="mdi-refresh" @click="requestFieldAi('empathize', 'target_learner', 'กลุ่มผู้เรียนเป้าหมาย')" title="สุ่มใหม่"></v-btn>
                    </div>
                  </div>
                </div>
              </v-col>

              <!-- Field 2: Age Group -->
              <v-col cols="12" sm="6">
                <div class="field-container mb-1">
                  <div class="field-header d-flex align-center justify-space-between mb-1.5">
                    <label class="field-label">
                      <v-icon icon="mdi-calendar-range" size="16" class="mr-1 text-primary"></v-icon>
                      ช่วงอายุ (Age Group)
                    </label>
                    <v-btn
                      size="x-small"
                      variant="tonal"
                      class="ai-field-btn"
                      :loading="activeFieldLoading === 'age_group'"
                      @click="requestFieldAi('empathize', 'age_group', 'ช่วงอายุ')"
                    >
                      <v-icon icon="mdi-creation" size="13" class="mr-1 text-primary"></v-icon>
                      ✨ AI ช่วยคิด
                    </v-btn>
                  </div>
                  <v-text-field
                    v-model="empathizeData.age_group"
                    placeholder="เช่น 12-13 ปี (วัยรุ่นตอนต้น)"
                    variant="outlined"
                    density="comfortable"
                    rounded="lg"
                    @update:model-value="onFieldChange('empathize', empathizeData)"
                  ></v-text-field>

                  <!-- Inline AI Suggestion Box -->
                  <div v-if="fieldSuggestions['age_group']" class="ai-suggestion-card pa-3 rounded-xl mb-3">
                    <div class="d-flex align-center justify-space-between mb-2">
                      <span class="text-caption font-weight-bold text-primary d-flex align-center">
                        <v-icon icon="mdi-sparkles" size="14" class="mr-1"></v-icon>
                        คำแนะนำจาก AI
                      </span>
                      <v-btn icon="mdi-close" variant="text" size="x-small" @click="dismissSuggestion('age_group')"></v-btn>
                    </div>
                    <p class="text-body-2 mb-2 text-slate-800">{{ fieldSuggestions['age_group'].suggestion }}</p>
                    <div class="d-flex align-center gap-2 flex-wrap">
                      <v-btn size="x-small" color="primary" class="font-weight-bold" rounded="lg" @click="applyFieldSuggestion('empathize', 'age_group')">
                        นำไปใช้ทันที
                      </v-btn>
                    </div>
                  </div>
                </div>
              </v-col>

              <!-- Field 3: Learner Characteristics -->
              <v-col cols="12">
                <div class="field-container mb-1">
                  <div class="field-header d-flex align-center justify-space-between mb-1.5">
                    <label class="field-label">
                      <v-icon icon="mdi-heart-pulse" size="16" class="mr-1 text-primary"></v-icon>
                      ลักษณะนิสัยและความชอบของผู้เรียน (Learner Characteristics)
                    </label>
                    <v-btn
                      size="x-small"
                      variant="tonal"
                      class="ai-field-btn"
                      :loading="activeFieldLoading === 'learner_characteristics'"
                      @click="requestFieldAi('empathize', 'learner_characteristics', 'ลักษณะนิสัยและความชอบ')"
                    >
                      <v-icon icon="mdi-creation" size="13" class="mr-1 text-primary"></v-icon>
                      ✨ AI ช่วยคิด
                    </v-btn>
                  </div>
                  <v-textarea
                    v-model="empathizeData.learner_characteristics"
                    placeholder="เช่น ชอบการแข่งขัน มีสมาธิสูงเมื่อมีภาพประกอบ สนุกกับการตอบคำถามเพื่อปลดล็อกไอเทม"
                    variant="outlined"
                    rows="2"
                    density="comfortable"
                    rounded="lg"
                    @update:model-value="onFieldChange('empathize', empathizeData)"
                  ></v-textarea>

                  <!-- Inline AI Suggestion Box -->
                  <div v-if="fieldSuggestions['learner_characteristics']" class="ai-suggestion-card pa-3 rounded-xl mb-3">
                    <div class="d-flex align-center justify-space-between mb-2">
                      <span class="text-caption font-weight-bold text-primary d-flex align-center">
                        <v-icon icon="mdi-sparkles" size="14" class="mr-1"></v-icon>
                        คำแนะนำจาก AI
                      </span>
                      <v-btn icon="mdi-close" variant="text" size="x-small" @click="dismissSuggestion('learner_characteristics')"></v-btn>
                    </div>
                    <p class="text-body-2 mb-2 text-slate-800">{{ fieldSuggestions['learner_characteristics'].suggestion }}</p>
                    <div class="d-flex align-center gap-2 flex-wrap">
                      <v-btn size="x-small" color="primary" class="font-weight-bold" rounded="lg" @click="applyFieldSuggestion('empathize', 'learner_characteristics')">
                        นำไปใช้ทันที
                      </v-btn>
                      <v-btn v-if="empathizeData.learner_characteristics" size="x-small" variant="outlined" rounded="lg" @click="appendFieldSuggestion('empathize', 'learner_characteristics')">
                        ต่อท้ายข้อความเดิม
                      </v-btn>
                    </div>
                  </div>
                </div>
              </v-col>

              <!-- Field 4: Pain Points -->
              <v-col cols="12">
                <div class="field-container mb-1">
                  <div class="field-header d-flex align-center justify-space-between mb-1.5">
                    <label class="field-label">
                      <v-icon icon="mdi-alert-decagram-outline" size="16" class="mr-1 text-amber-darken-2"></v-icon>
                      Pain Points / อุปสรรคในการเรียนรู้ที่พบ
                    </label>
                    <v-btn
                      size="x-small"
                      variant="tonal"
                      class="ai-field-btn"
                      :loading="activeFieldLoading === 'pain_points'"
                      @click="requestFieldAi('empathize', 'pain_points', 'Pain Points อุปสรรค')"
                    >
                      <v-icon icon="mdi-creation" size="13" class="mr-1 text-primary"></v-icon>
                      ✨ AI ช่วยคิด
                    </v-btn>
                  </div>
                  <v-textarea
                    v-model="empathizeData.pain_points"
                    placeholder="เช่น จำเนื้อหาทฤษฎียาวๆ ไม่ได้ เบื่อการทำใบงานแบบเดิมๆ เมื่อตอบผิดไม่มีคำอธิบายทันที"
                    variant="outlined"
                    rows="3"
                    density="comfortable"
                    rounded="lg"
                    @update:model-value="onFieldChange('empathize', empathizeData)"
                  ></v-textarea>

                  <!-- Inline AI Suggestion Box -->
                  <div v-if="fieldSuggestions['pain_points']" class="ai-suggestion-card pa-3 rounded-xl mb-3">
                    <div class="d-flex align-center justify-space-between mb-2">
                      <span class="text-caption font-weight-bold text-primary d-flex align-center">
                        <v-icon icon="mdi-sparkles" size="14" class="mr-1"></v-icon>
                        คำแนะนำจาก AI
                      </span>
                      <v-btn icon="mdi-close" variant="text" size="x-small" @click="dismissSuggestion('pain_points')"></v-btn>
                    </div>
                    <p class="text-body-2 mb-2 text-slate-800">{{ fieldSuggestions['pain_points'].suggestion }}</p>
                    <div class="d-flex align-center gap-2 flex-wrap">
                      <v-btn size="x-small" color="primary" class="font-weight-bold" rounded="lg" @click="applyFieldSuggestion('empathize', 'pain_points')">
                        นำไปใช้ทันที
                      </v-btn>
                      <v-btn v-if="empathizeData.pain_points" size="x-small" variant="outlined" rounded="lg" @click="appendFieldSuggestion('empathize', 'pain_points')">
                        ต่อท้ายข้อความเดิม
                      </v-btn>
                    </div>
                  </div>
                </div>
              </v-col>

              <!-- Field 5: Device Availability -->
              <v-col cols="12" sm="6">
                <div class="field-container mb-1">
                  <div class="field-header d-flex align-center justify-space-between mb-1.5">
                    <label class="field-label">
                      <v-icon icon="mdi-devices" size="16" class="mr-1 text-primary"></v-icon>
                      อุปกรณ์หลักที่นักเรียนใช้เล่น
                    </label>
                    <v-btn
                      size="x-small"
                      variant="tonal"
                      class="ai-field-btn"
                      :loading="activeFieldLoading === 'device_availability'"
                      @click="requestFieldAi('empathize', 'device_availability', 'อุปกรณ์หลัก')"
                    >
                      <v-icon icon="mdi-creation" size="13" class="mr-1 text-primary"></v-icon>
                      ✨ AI ช่วยคิด
                    </v-btn>
                  </div>
                  <v-select
                    v-model="empathizeData.device_availability"
                    :items="['Mobile (สมาร์ตโฟน)', 'Desktop/Laptop', 'Tablet', 'ทุกอุปกรณ์']"
                    variant="outlined"
                    density="comfortable"
                    rounded="lg"
                    @update:model-value="onFieldChange('empathize', empathizeData)"
                  ></v-select>
                </div>
              </v-col>

              <!-- Field 6: Learning Environment -->
              <v-col cols="12" sm="6">
                <div class="field-container mb-1">
                  <div class="field-header d-flex align-center justify-space-between mb-1.5">
                    <label class="field-label">
                      <v-icon icon="mdi-home-city-outline" size="16" class="mr-1 text-primary"></v-icon>
                      บริบทการเรียนรู้ (Environment)
                    </label>
                    <v-btn
                      size="x-small"
                      variant="tonal"
                      class="ai-field-btn"
                      :loading="activeFieldLoading === 'learning_environment'"
                      @click="requestFieldAi('empathize', 'learning_environment', 'บริบทการเรียนรู้')"
                    >
                      <v-icon icon="mdi-creation" size="13" class="mr-1 text-primary"></v-icon>
                      ✨ AI ช่วยคิด
                    </v-btn>
                  </div>
                  <v-text-field
                    v-model="empathizeData.learning_environment"
                    placeholder="เช่น ในห้องเรียนคอมพิวเตอร์ หรือการบ้านทบทวนด้วยตนเอง"
                    variant="outlined"
                    density="comfortable"
                    rounded="lg"
                    @update:model-value="onFieldChange('empathize', empathizeData)"
                  ></v-text-field>

                  <!-- Inline AI Suggestion Box -->
                  <div v-if="fieldSuggestions['learning_environment']" class="ai-suggestion-card pa-3 rounded-xl mb-3">
                    <div class="d-flex align-center justify-space-between mb-2">
                      <span class="text-caption font-weight-bold text-primary d-flex align-center">
                        <v-icon icon="mdi-sparkles" size="14" class="mr-1"></v-icon>
                        คำแนะนำจาก AI
                      </span>
                      <v-btn icon="mdi-close" variant="text" size="x-small" @click="dismissSuggestion('learning_environment')"></v-btn>
                    </div>
                    <p class="text-body-2 mb-2 text-slate-800">{{ fieldSuggestions['learning_environment'].suggestion }}</p>
                    <div class="d-flex align-center gap-2 flex-wrap">
                      <v-btn size="x-small" color="primary" class="font-weight-bold" rounded="lg" @click="applyFieldSuggestion('empathize', 'learning_environment')">
                        นำไปใช้ทันที
                      </v-btn>
                    </div>
                  </div>
                </div>
              </v-col>
            </v-row>
          </div>

          <!-- ================= STEP 2: DEFINE ================= -->
          <div v-else-if="currentStepIndex === 1" class="step-form-body">
            <v-row>
              <!-- Field 1: Problem Statement -->
              <v-col cols="12">
                <div class="field-container mb-1">
                  <div class="field-header d-flex align-center justify-space-between mb-1.5">
                    <label class="field-label">
                      <v-icon icon="mdi-target" size="16" class="mr-1 text-primary"></v-icon>
                      โจทย์ปัญหาหลัก (Problem Statement)
                    </label>
                    <v-btn
                      size="x-small"
                      variant="tonal"
                      class="ai-field-btn"
                      :loading="activeFieldLoading === 'problem_statement'"
                      @click="requestFieldAi('define', 'problem_statement', 'โจทย์ปัญหาหลัก')"
                    >
                      <v-icon icon="mdi-creation" size="13" class="mr-1 text-primary"></v-icon>
                      ✨ AI ช่วยคิด
                    </v-btn>
                  </div>
                  <v-textarea
                    v-model="defineData.problem_statement"
                    placeholder="เช่น ผู้เรียนขาดความเข้าใจและไม่สามารถเชื่อมโยงทฤษฎีสู่การปฏิบัติจริงในชีวิตประจำวัน จึงต้องการสถานการณ์จำลองที่ท้าทาย"
                    variant="outlined"
                    rows="3"
                    density="comfortable"
                    rounded="lg"
                    @update:model-value="onFieldChange('define', defineData)"
                  ></v-textarea>

                  <!-- Inline AI Suggestion Box -->
                  <div v-if="fieldSuggestions['problem_statement']" class="ai-suggestion-card pa-3 rounded-xl mb-3">
                    <div class="d-flex align-center justify-space-between mb-2">
                      <span class="text-caption font-weight-bold text-primary d-flex align-center">
                        <v-icon icon="mdi-sparkles" size="14" class="mr-1"></v-icon>
                        คำแนะนำจาก AI
                      </span>
                      <v-btn icon="mdi-close" variant="text" size="x-small" @click="dismissSuggestion('problem_statement')"></v-btn>
                    </div>
                    <p class="text-body-2 mb-2 text-slate-800">{{ fieldSuggestions['problem_statement'].suggestion }}</p>
                    <div class="d-flex align-center gap-2 flex-wrap">
                      <v-btn size="x-small" color="primary" class="font-weight-bold" rounded="lg" @click="applyFieldSuggestion('define', 'problem_statement')">
                        นำไปใช้ทันที
                      </v-btn>
                      <v-btn v-if="defineData.problem_statement" size="x-small" variant="outlined" rounded="lg" @click="appendFieldSuggestion('define', 'problem_statement')">
                        ต่อท้ายข้อความเดิม
                      </v-btn>
                    </div>
                  </div>
                </div>
              </v-col>

              <!-- Field 2: Expected Outcomes -->
              <v-col cols="12">
                <div class="field-container mb-1">
                  <div class="field-header d-flex align-center justify-space-between mb-1.5">
                    <label class="field-label">
                      <v-icon icon="mdi-flag-checkered" size="16" class="mr-1 text-success"></v-icon>
                      ผลลัพธ์การเรียนรู้ที่คาดหวัง (Expected Outcomes)
                    </label>
                    <v-btn
                      size="x-small"
                      variant="tonal"
                      class="ai-field-btn"
                      :loading="activeFieldLoading === 'expected_outcomes'"
                      @click="requestFieldAi('define', 'expected_outcomes', 'ผลลัพธ์การเรียนรู้')"
                    >
                      <v-icon icon="mdi-creation" size="13" class="mr-1 text-primary"></v-icon>
                      ✨ AI ช่วยคิด
                    </v-btn>
                  </div>
                  <v-textarea
                    v-model="defineData.expected_outcomes"
                    placeholder="เช่น ผู้เรียนสามารถอธิบายหลักการและตอบคำถามในสถานการณ์จำลองได้ถูกต้องไม่น้อยกว่า 80% หลังเล่นจบภารกิจ"
                    variant="outlined"
                    rows="2"
                    density="comfortable"
                    rounded="lg"
                    @update:model-value="onFieldChange('define', defineData)"
                  ></v-textarea>

                  <!-- Inline AI Suggestion Box -->
                  <div v-if="fieldSuggestions['expected_outcomes']" class="ai-suggestion-card pa-3 rounded-xl mb-3">
                    <div class="d-flex align-center justify-space-between mb-2">
                      <span class="text-caption font-weight-bold text-primary d-flex align-center">
                        <v-icon icon="mdi-sparkles" size="14" class="mr-1"></v-icon>
                        คำแนะนำจาก AI
                      </span>
                      <v-btn icon="mdi-close" variant="text" size="x-small" @click="dismissSuggestion('expected_outcomes')"></v-btn>
                    </div>
                    <p class="text-body-2 mb-2 text-slate-800">{{ fieldSuggestions['expected_outcomes'].suggestion }}</p>
                    <div class="d-flex align-center gap-2 flex-wrap">
                      <v-btn size="x-small" color="primary" class="font-weight-bold" rounded="lg" @click="applyFieldSuggestion('define', 'expected_outcomes')">
                        นำไปใช้ทันที
                      </v-btn>
                    </div>
                  </div>
                </div>
              </v-col>

              <!-- Field 3: Knowledge Goals -->
              <v-col cols="12" sm="4">
                <div class="field-container mb-1">
                  <div class="field-header d-flex align-center justify-space-between mb-1.5">
                    <label class="field-label">
                      <v-icon icon="mdi-brain" size="16" class="mr-1 text-primary"></v-icon>
                      ด้านความรู้ (Knowledge)
                    </label>
                    <v-btn
                      size="x-small"
                      variant="tonal"
                      class="ai-field-btn"
                      :loading="activeFieldLoading === 'knowledge_goals'"
                      @click="requestFieldAi('define', 'knowledge_goals', 'เป้าหมายด้านความรู้')"
                    >
                      <v-icon icon="mdi-creation" size="13" class="mr-1 text-primary"></v-icon>
                      ✨ AI ช่วยคิด
                    </v-btn>
                  </div>
                  <v-textarea
                    v-model="defineData.knowledge_goals"
                    placeholder="เช่น ความหมายและหลักการสำคัญของเนื้อหา"
                    variant="outlined"
                    rows="2"
                    density="comfortable"
                    rounded="lg"
                    @update:model-value="onFieldChange('define', defineData)"
                  ></v-textarea>

                  <div v-if="fieldSuggestions['knowledge_goals']" class="ai-suggestion-card pa-3 rounded-xl mb-3">
                    <p class="text-body-2 mb-2 text-slate-800">{{ fieldSuggestions['knowledge_goals'].suggestion }}</p>
                    <v-btn size="x-small" color="primary" rounded="lg" @click="applyFieldSuggestion('define', 'knowledge_goals')">นำไปใช้</v-btn>
                  </div>
                </div>
              </v-col>

              <!-- Field 4: Skill Goals -->
              <v-col cols="12" sm="4">
                <div class="field-container mb-1">
                  <div class="field-header d-flex align-center justify-space-between mb-1.5">
                    <label class="field-label">
                      <v-icon icon="mdi-arm-flex-outline" size="16" class="mr-1 text-primary"></v-icon>
                      ด้านทักษะ (Skills)
                    </label>
                    <v-btn
                      size="x-small"
                      variant="tonal"
                      class="ai-field-btn"
                      :loading="activeFieldLoading === 'skill_goals'"
                      @click="requestFieldAi('define', 'skill_goals', 'เป้าหมายด้านทักษะ')"
                    >
                      <v-icon icon="mdi-creation" size="13" class="mr-1 text-primary"></v-icon>
                      ✨ AI ช่วยคิด
                    </v-btn>
                  </div>
                  <v-textarea
                    v-model="defineData.skill_goals"
                    placeholder="เช่น การคิดวิเคราะห์และตัดสินใจอย่างรวดเร็ว"
                    variant="outlined"
                    rows="2"
                    density="comfortable"
                    rounded="lg"
                    @update:model-value="onFieldChange('define', defineData)"
                  ></v-textarea>

                  <div v-if="fieldSuggestions['skill_goals']" class="ai-suggestion-card pa-3 rounded-xl mb-3">
                    <p class="text-body-2 mb-2 text-slate-800">{{ fieldSuggestions['skill_goals'].suggestion }}</p>
                    <v-btn size="x-small" color="primary" rounded="lg" @click="applyFieldSuggestion('define', 'skill_goals')">นำไปใช้</v-btn>
                  </div>
                </div>
              </v-col>

              <!-- Field 5: Attitude Goals -->
              <v-col cols="12" sm="4">
                <div class="field-container mb-1">
                  <div class="field-header d-flex align-center justify-space-between mb-1.5">
                    <label class="field-label">
                      <v-icon icon="mdi-emoticon-happy-outline" size="16" class="mr-1 text-primary"></v-icon>
                      ด้านเจตคติ (Attitude)
                    </label>
                    <v-btn
                      size="x-small"
                      variant="tonal"
                      class="ai-field-btn"
                      :loading="activeFieldLoading === 'attitude_goals'"
                      @click="requestFieldAi('define', 'attitude_goals', 'เป้าหมายด้านเจตคติ')"
                    >
                      <v-icon icon="mdi-creation" size="13" class="mr-1 text-primary"></v-icon>
                      ✨ AI ช่วยคิด
                    </v-btn>
                  </div>
                  <v-textarea
                    v-model="defineData.attitude_goals"
                    placeholder="เช่น ตระหนักถึงความสำคัญและสนุกกับการเรียนรู้"
                    variant="outlined"
                    rows="2"
                    density="comfortable"
                    rounded="lg"
                    @update:model-value="onFieldChange('define', defineData)"
                  ></v-textarea>

                  <div v-if="fieldSuggestions['attitude_goals']" class="ai-suggestion-card pa-3 rounded-xl mb-3">
                    <p class="text-body-2 mb-2 text-slate-800">{{ fieldSuggestions['attitude_goals'].suggestion }}</p>
                    <v-btn size="x-small" color="primary" rounded="lg" @click="applyFieldSuggestion('define', 'attitude_goals')">นำไปใช้</v-btn>
                  </div>
                </div>
              </v-col>
            </v-row>
          </div>

          <!-- ================= STEP 3: IDEATE ================= -->
          <div v-else-if="currentStepIndex === 2" class="step-form-body">
            <v-row>
              <!-- Field 1: Game Concept -->
              <v-col cols="12">
                <div class="field-container mb-1">
                  <div class="field-header d-flex align-center justify-space-between mb-1.5">
                    <label class="field-label">
                      <v-icon icon="mdi-lightbulb-on-outline" size="16" class="mr-1 text-amber-darken-2"></v-icon>
                      แนวคิดของเกม (Game Concept)
                    </label>
                    <v-btn
                      size="x-small"
                      variant="tonal"
                      class="ai-field-btn"
                      :loading="activeFieldLoading === 'game_concept'"
                      @click="requestFieldAi('ideate', 'game_concept', 'แนวคิดของเกม')"
                    >
                      <v-icon icon="mdi-creation" size="13" class="mr-1 text-primary"></v-icon>
                      ✨ AI ช่วยคิด
                    </v-btn>
                  </div>
                  <v-textarea
                    v-model="ideateData.game_concept"
                    placeholder="เช่น ภารกิจกอบกู้ดินแดนความรู้ โดยสวมบทบาทเป็นผู้กล้าออกเดินทางไขปริศนาผ่านการตอบคำถามเพื่อปลดล็อกพลัง"
                    variant="outlined"
                    rows="2"
                    density="comfortable"
                    rounded="lg"
                    @update:model-value="onFieldChange('ideate', ideateData)"
                  ></v-textarea>

                  <div v-if="fieldSuggestions['game_concept']" class="ai-suggestion-card pa-3 rounded-xl mb-3">
                    <div class="d-flex align-center justify-space-between mb-2">
                      <span class="text-caption font-weight-bold text-primary">คำแนะนำจาก AI</span>
                      <v-btn icon="mdi-close" variant="text" size="x-small" @click="dismissSuggestion('game_concept')"></v-btn>
                    </div>
                    <p class="text-body-2 mb-2 text-slate-800">{{ fieldSuggestions['game_concept'].suggestion }}</p>
                    <div class="d-flex align-center gap-2 flex-wrap">
                      <v-btn size="x-small" color="primary" rounded="lg" @click="applyFieldSuggestion('ideate', 'game_concept')">นำไปใช้ทันที</v-btn>
                    </div>
                  </div>
                </div>
              </v-col>

              <!-- Field 2: Story & Missions -->
              <v-col cols="12">
                <div class="field-container mb-1">
                  <div class="field-header d-flex align-center justify-space-between mb-1.5">
                    <label class="field-label">
                      <v-icon icon="mdi-book-open-page-variant" size="16" class="mr-1 text-primary"></v-icon>
                      เรื่องราวและเนื้อเรื่อง (Story & Narrative)
                    </label>
                    <v-btn
                      size="x-small"
                      variant="tonal"
                      class="ai-field-btn"
                      :loading="activeFieldLoading === 'story'"
                      @click="requestFieldAi('ideate', 'story', 'เรื่องราวและเนื้อเรื่อง')"
                    >
                      <v-icon icon="mdi-creation" size="13" class="mr-1 text-primary"></v-icon>
                      ✨ AI ช่วยคิด
                    </v-btn>
                  </div>
                  <v-textarea
                    v-model="ideateData.story"
                    placeholder="เช่น ตัวละครเอกได้รับสารปริศนาจากปราชญ์ผู้พิทักษ์ ขอให้ออกตามหาผลึกความรู้ 4 ชิ้นที่ซ่อนอยู่ในแต่ละด่านเพื่อคลี่คลายวิกฤต"
                    variant="outlined"
                    rows="3"
                    density="comfortable"
                    rounded="lg"
                    @update:model-value="onFieldChange('ideate', ideateData)"
                  ></v-textarea>

                  <div v-if="fieldSuggestions['story']" class="ai-suggestion-card pa-3 rounded-xl mb-3">
                    <p class="text-body-2 mb-2 text-slate-800">{{ fieldSuggestions['story'].suggestion }}</p>
                    <v-btn size="x-small" color="primary" rounded="lg" @click="applyFieldSuggestion('ideate', 'story')">นำไปใช้ทันที</v-btn>
                  </div>
                </div>
              </v-col>

              <!-- Field 3: Missions -->
              <v-col cols="12" sm="6">
                <div class="field-container mb-1">
                  <div class="field-header d-flex align-center justify-space-between mb-1.5">
                    <label class="field-label">
                      <v-icon icon="mdi-sword-cross" size="16" class="mr-1 text-primary"></v-icon>
                      ภารกิจและด่านการเรียนรู้ (Missions)
                    </label>
                    <v-btn
                      size="x-small"
                      variant="tonal"
                      class="ai-field-btn"
                      :loading="activeFieldLoading === 'missions'"
                      @click="requestFieldAi('ideate', 'missions', 'ภารกิจย่อยในเกม')"
                    >
                      <v-icon icon="mdi-creation" size="13" class="mr-1 text-primary"></v-icon>
                      ✨ AI ช่วยคิด
                    </v-btn>
                  </div>
                  <v-textarea
                    v-model="ideateData.missions"
                    placeholder="เช่น ด่านที่ 1 ความรู้พื้นฐาน &rarr; ด่านที่ 2 วิเคราะห์สถานการณ์ &rarr; ด่านที่ 3 เผชิญหน้าบอสใหญ่"
                    variant="outlined"
                    rows="2"
                    density="comfortable"
                    rounded="lg"
                    @update:model-value="onFieldChange('ideate', ideateData)"
                  ></v-textarea>

                  <div v-if="fieldSuggestions['missions']" class="ai-suggestion-card pa-3 rounded-xl mb-3">
                    <p class="text-body-2 mb-2 text-slate-800">{{ fieldSuggestions['missions'].suggestion }}</p>
                    <v-btn size="x-small" color="primary" rounded="lg" @click="applyFieldSuggestion('ideate', 'missions')">นำไปใช้ทันที</v-btn>
                  </div>
                </div>
              </v-col>

              <!-- Field 4: Challenges -->
              <v-col cols="12" sm="6">
                <div class="field-container mb-1">
                  <div class="field-header d-flex align-center justify-space-between mb-1.5">
                    <label class="field-label">
                      <v-icon icon="mdi-timer-sand" size="16" class="mr-1 text-warning"></v-icon>
                      ความท้าทายและอุปสรรค (Challenges)
                    </label>
                    <v-btn
                      size="x-small"
                      variant="tonal"
                      class="ai-field-btn"
                      :loading="activeFieldLoading === 'challenges'"
                      @click="requestFieldAi('ideate', 'challenges', 'ความท้าทาย')"
                    >
                      <v-icon icon="mdi-creation" size="13" class="mr-1 text-primary"></v-icon>
                      ✨ AI ช่วยคิด
                    </v-btn>
                  </div>
                  <v-textarea
                    v-model="ideateData.challenges"
                    placeholder="เช่น เวลาถอยหลังในแต่ละข้อ ตัวเลือกลวงที่ท้าทาย และเกณฑ์คะแนนผ่าน 60%"
                    variant="outlined"
                    rows="2"
                    density="comfortable"
                    rounded="lg"
                    @update:model-value="onFieldChange('ideate', ideateData)"
                  ></v-textarea>

                  <div v-if="fieldSuggestions['challenges']" class="ai-suggestion-card pa-3 rounded-xl mb-3">
                    <p class="text-body-2 mb-2 text-slate-800">{{ fieldSuggestions['challenges'].suggestion }}</p>
                    <v-btn size="x-small" color="primary" rounded="lg" @click="applyFieldSuggestion('ideate', 'challenges')">นำไปใช้ทันที</v-btn>
                  </div>
                </div>
              </v-col>

              <!-- Field 5: Rewards -->
              <v-col cols="12" sm="6">
                <div class="field-container mb-1">
                  <div class="field-header d-flex align-center justify-space-between mb-1.5">
                    <label class="field-label">
                      <v-icon icon="mdi-medal-outline" size="16" class="mr-1 text-amber"></v-icon>
                      รางวัลและการเสริมแรง (Rewards & Badges)
                    </label>
                    <v-btn
                      size="x-small"
                      variant="tonal"
                      class="ai-field-btn"
                      :loading="activeFieldLoading === 'rewards'"
                      @click="requestFieldAi('ideate', 'rewards', 'รางวัลและการเสริมแรง')"
                    >
                      <v-icon icon="mdi-creation" size="13" class="mr-1 text-primary"></v-icon>
                      ✨ AI ช่วยคิด
                    </v-btn>
                  </div>
                  <v-text-field
                    v-model="ideateData.rewards"
                    placeholder="เช่น เหรียญตรา Master Badge, คะแนน EXP, อันดับ Leaderboard"
                    variant="outlined"
                    density="comfortable"
                    rounded="lg"
                    @update:model-value="onFieldChange('ideate', ideateData)"
                  ></v-text-field>

                  <div v-if="fieldSuggestions['rewards']" class="ai-suggestion-card pa-3 rounded-xl mb-3">
                    <p class="text-body-2 mb-2 text-slate-800">{{ fieldSuggestions['rewards'].suggestion }}</p>
                    <v-btn size="x-small" color="primary" rounded="lg" @click="applyFieldSuggestion('ideate', 'rewards')">นำไปใช้ทันที</v-btn>
                  </div>
                </div>
              </v-col>

              <!-- Field 6: Duration -->
              <v-col cols="12" sm="6">
                <div class="field-container mb-1">
                  <div class="field-header d-flex align-center justify-space-between mb-1.5">
                    <label class="field-label">
                      <v-icon icon="mdi-clock-outline" size="16" class="mr-1 text-primary"></v-icon>
                      ระยะเวลาเล่นโดยประมาณ (นาที)
                    </label>
                    <v-btn
                      size="x-small"
                      variant="tonal"
                      class="ai-field-btn"
                      :loading="activeFieldLoading === 'duration_minutes'"
                      @click="requestFieldAi('ideate', 'duration_minutes', 'ระยะเวลาเล่น')"
                    >
                      <v-icon icon="mdi-creation" size="13" class="mr-1 text-primary"></v-icon>
                      ✨ AI แนะนำ
                    </v-btn>
                  </div>
                  <v-text-field
                    v-model.number="ideateData.duration_minutes"
                    type="number"
                    variant="outlined"
                    density="comfortable"
                    rounded="lg"
                    @update:model-value="onFieldChange('ideate', ideateData)"
                  ></v-text-field>
                </div>
              </v-col>
            </v-row>
          </div>

          <!-- ================= STEP 4: PROTOTYPE ================= -->
          <div v-else-if="currentStepIndex === 3" class="step-form-body">
            <!-- Prototype Info Banner -->
            <v-sheet class="pa-4 rounded-xl mb-5 prototype-info-banner d-flex align-center">
              <v-avatar color="primary" class="mr-3 text-white" size="40">
                <v-icon icon="mdi-gamepad-variant" size="22"></v-icon>
              </v-avatar>
              <div>
                <div class="font-weight-bold text-subtitle-2 text-slate-900">
                  โครงสร้างเกมอัตโนมัติ (Automated Game Architecture)
                </div>
                <div class="text-caption text-slate-600">
                  คุณครูไม่ต้องเขียนโค้ดหรือแต่งเกมด้วยตนเอง! ระบบจะนำข้อมูลข้างต้นไปประกอบเป็นฉาก ตัวละคร และแบบทดสอบใน Game Editor โดยอัตโนมัติ
                </div>
              </div>
            </v-sheet>

            <v-row>
              <!-- Field 1: Feedback Mechanisms -->
              <v-col cols="12">
                <div class="field-container mb-1">
                  <div class="field-header d-flex align-center justify-space-between mb-1.5">
                    <label class="field-label">
                      <v-icon icon="mdi-comment-check-outline" size="16" class="mr-1 text-primary"></v-icon>
                      กลไกผลตอบรับเมื่อตอบถูก/ผิด (Feedback Mechanisms)
                    </label>
                    <v-btn
                      size="x-small"
                      variant="tonal"
                      class="ai-field-btn"
                      :loading="activeFieldLoading === 'feedback_mechanisms'"
                      @click="requestFieldAi('prototype', 'feedback_mechanisms', 'กลไกผลตอบรับ')"
                    >
                      <v-icon icon="mdi-creation" size="13" class="mr-1 text-primary"></v-icon>
                      ✨ AI ช่วยคิด
                    </v-btn>
                  </div>
                  <v-textarea
                    v-model="prototypeData.feedback_mechanisms"
                    placeholder="เช่น แสดงเฉลยพร้อมคำอธิบายทันทีเมื่อตอบแต่ละข้อ หากตอบถูกจะได้รับเอฟเฟกต์เสียงแห่งชัยชนะ หากตอบผิดจะมีคำใบ้คอยชี้แนะ"
                    variant="outlined"
                    rows="3"
                    density="comfortable"
                    rounded="lg"
                    @update:model-value="onFieldChange('prototype', prototypeData)"
                  ></v-textarea>

                  <div v-if="fieldSuggestions['feedback_mechanisms']" class="ai-suggestion-card pa-3 rounded-xl mb-3">
                    <p class="text-body-2 mb-2 text-slate-800">{{ fieldSuggestions['feedback_mechanisms'].suggestion }}</p>
                    <v-btn size="x-small" color="primary" rounded="lg" @click="applyFieldSuggestion('prototype', 'feedback_mechanisms')">นำไปใช้ทันที</v-btn>
                  </div>
                </div>
              </v-col>

              <!-- Field 2: Core Rules -->
              <v-col cols="12">
                <div class="field-container mb-1">
                  <div class="field-header d-flex align-center justify-space-between mb-1.5">
                    <label class="field-label">
                      <v-icon icon="mdi-scale-balance" size="16" class="mr-1 text-primary"></v-icon>
                      กติกาหลักของเกมและการผ่านด่าน (Core Rules)
                    </label>
                    <v-btn
                      size="x-small"
                      variant="tonal"
                      class="ai-field-btn"
                      :loading="activeFieldLoading === 'core_rules'"
                      @click="requestFieldAi('prototype', 'core_rules', 'กติกาหลักของเกม')"
                    >
                      <v-icon icon="mdi-creation" size="13" class="mr-1 text-primary"></v-icon>
                      ✨ AI ช่วยคิด
                    </v-btn>
                  </div>
                  <v-textarea
                    v-model="prototypeData.core_rules"
                    placeholder="เช่น ตอบคำถามให้ถูกต้องภายในเวลาที่กำหนด แต่ละข้อมีคะแนนตามระดับความยาก สะสมคะแนนให้ผ่านเกณฑ์ 60% เพื่อพิชิตเกม"
                    variant="outlined"
                    rows="3"
                    density="comfortable"
                    rounded="lg"
                    @update:model-value="onFieldChange('prototype', prototypeData)"
                  ></v-textarea>

                  <div v-if="fieldSuggestions['core_rules']" class="ai-suggestion-card pa-3 rounded-xl mb-3">
                    <p class="text-body-2 mb-2 text-slate-800">{{ fieldSuggestions['core_rules'].suggestion }}</p>
                    <v-btn size="x-small" color="primary" rounded="lg" @click="applyFieldSuggestion('prototype', 'core_rules')">นำไปใช้ทันที</v-btn>
                  </div>
                </div>
              </v-col>
            </v-row>
          </div>

          <!-- ================= STEP 5: TEST ================= -->
          <div v-else-if="currentStepIndex === 4" class="step-form-body">
            <v-row>
              <!-- Field 1: Test Users Count -->
              <v-col cols="12" sm="6">
                <div class="field-container mb-1">
                  <div class="field-header d-flex align-center justify-space-between mb-1.5">
                    <label class="field-label">
                      <v-icon icon="mdi-account-multiple-check" size="16" class="mr-1 text-primary"></v-icon>
                      จำนวนผู้ทดสอบ (คน)
                    </label>
                    <v-btn
                      size="x-small"
                      variant="tonal"
                      class="ai-field-btn"
                      :loading="activeFieldLoading === 'test_users_count'"
                      @click="requestFieldAi('test', 'test_users_count', 'จำนวนผู้ทดสอบ')"
                    >
                      <v-icon icon="mdi-creation" size="13" class="mr-1 text-primary"></v-icon>
                      ✨ AI แนะนำ
                    </v-btn>
                  </div>
                  <v-text-field
                    v-model.number="testData.test_users_count"
                    type="number"
                    variant="outlined"
                    density="comfortable"
                    rounded="lg"
                    @update:model-value="onFieldChange('test', testData)"
                  ></v-text-field>
                </div>
              </v-col>

              <!-- Field 2: Difficulty Rating -->
              <v-col cols="12" sm="6">
                <div class="field-container mb-1">
                  <div class="field-header d-flex align-center justify-space-between mb-1.5">
                    <label class="field-label">
                      <v-icon icon="mdi-speedometer" size="16" class="mr-1 text-primary"></v-icon>
                      ระดับความยากที่ประเมิน
                    </label>
                  </div>
                  <v-select
                    v-model.number="testData.difficulty_rating"
                    :items="[
                      { title: 'ระดับ 1 - ง่ายมาก (1/5)', value: 1 },
                      { title: 'ระดับ 2 - ง่าย (2/5)', value: 2 },
                      { title: 'ระดับ 3 - พอดี เหมาะสม (3/5)', value: 3 },
                      { title: 'ระดับ 4 - ค่อนข้างยาก (4/5)', value: 4 },
                      { title: 'ระดับ 5 - ท้าทายมาก (5/5)', value: 5 },
                    ]"
                    variant="outlined"
                    density="comfortable"
                    rounded="lg"
                    @update:model-value="onFieldChange('test', testData)"
                  ></v-select>
                </div>
              </v-col>

              <!-- Field 3: Observations -->
              <v-col cols="12">
                <div class="field-container mb-1">
                  <div class="field-header d-flex align-center justify-space-between mb-1.5">
                    <label class="field-label">
                      <v-icon icon="mdi-eye-check-outline" size="16" class="mr-1 text-primary"></v-icon>
                      ข้อสังเกตระหว่างผู้เรียนเล่นเกม (Observations)
                    </label>
                    <v-btn
                      size="x-small"
                      variant="tonal"
                      class="ai-field-btn"
                      :loading="activeFieldLoading === 'observations'"
                      @click="requestFieldAi('test', 'observations', 'ข้อสังเกต')"
                    >
                      <v-icon icon="mdi-creation" size="13" class="mr-1 text-primary"></v-icon>
                      ✨ AI ช่วยคิด
                    </v-btn>
                  </div>
                  <v-textarea
                    v-model="testData.observations"
                    placeholder="เช่น ผู้เรียนมีความตื่นตัวและตั้งใจทำภารกิจอย่างมาก ชอบระบบสะสมเหรียญรางวัล แต่บางข้อควรอ่านเวลาให้เพียงพอ"
                    variant="outlined"
                    rows="2"
                    density="comfortable"
                    rounded="lg"
                    @update:model-value="onFieldChange('test', testData)"
                  ></v-textarea>

                  <div v-if="fieldSuggestions['observations']" class="ai-suggestion-card pa-3 rounded-xl mb-3">
                    <p class="text-body-2 mb-2 text-slate-800">{{ fieldSuggestions['observations'].suggestion }}</p>
                    <v-btn size="x-small" color="primary" rounded="lg" @click="applyFieldSuggestion('test', 'observations')">นำไปใช้ทันที</v-btn>
                  </div>
                </div>
              </v-col>

              <!-- Field 4: Feedback Summary -->
              <v-col cols="12">
                <div class="field-container mb-1">
                  <div class="field-header d-flex align-center justify-space-between mb-1.5">
                    <label class="field-label">
                      <v-icon icon="mdi-comment-quote-outline" size="16" class="mr-1 text-primary"></v-icon>
                      สรุปข้อเสนอแนะเพื่อนำไปปรับเกม (Feedback Summary)
                    </label>
                    <v-btn
                      size="x-small"
                      variant="tonal"
                      class="ai-field-btn"
                      :loading="activeFieldLoading === 'feedback_summary'"
                      @click="requestFieldAi('test', 'feedback_summary', 'สรุปข้อเสนอแนะ')"
                    >
                      <v-icon icon="mdi-creation" size="13" class="mr-1 text-primary"></v-icon>
                      ✨ AI ช่วยคิด
                    </v-btn>
                  </div>
                  <v-textarea
                    v-model="testData.feedback_summary"
                    placeholder="เช่น ปรับเวลาอ่านคำถามให้เหมาะสม และเพิ่มการสรุปเนื้อหาสำคัญสั้นๆ หลังจบด่าน"
                    variant="outlined"
                    rows="2"
                    density="comfortable"
                    rounded="lg"
                    @update:model-value="onFieldChange('test', testData)"
                  ></v-textarea>

                  <div v-if="fieldSuggestions['feedback_summary']" class="ai-suggestion-card pa-3 rounded-xl mb-3">
                    <p class="text-body-2 mb-2 text-slate-800">{{ fieldSuggestions['feedback_summary'].suggestion }}</p>
                    <v-btn size="x-small" color="primary" rounded="lg" @click="applyFieldSuggestion('test', 'feedback_summary')">นำไปใช้ทันที</v-btn>
                  </div>
                </div>
              </v-col>
            </v-row>
          </div>

          <!-- Bottom Navigation Buttons -->
          <div class="d-flex justify-space-between align-center mt-6 pt-5 border-t">
            <v-btn
              :disabled="currentStepIndex === 0"
              variant="outlined"
              prepend-icon="mdi-arrow-left"
              rounded="xl"
              class="px-5 font-weight-medium"
              @click="goToStep(currentStepIndex - 1)"
            >
              ย้อนกลับ
            </v-btn>

            <div class="d-flex align-center gap-2">
              <v-btn
                v-if="currentStepIndex < 4"
                color="primary"
                append-icon="mdi-arrow-right"
                rounded="xl"
                class="px-6 font-weight-bold luxury-next-btn elevation-2"
                @click="goToStep(currentStepIndex + 1)"
              >
                บันทึกและไปต่อ &rarr;
              </v-btn>

              <v-btn
                v-else
                color="amber-accent-3"
                class="text-slate-900 font-weight-bold px-7 luxury-launch-btn elevation-3"
                prepend-icon="mdi-rocket-launch"
                rounded="xl"
                size="large"
                @click="showGenerateModal = true"
              >
                สร้างเกมการเรียนรู้ด้วย AI
              </v-btn>
            </div>
          </div>
        </v-card>
      </v-col>
    </v-row>

    <!-- AI Assistant General Drawer -->
    <v-navigation-drawer
      v-model="aiDrawer"
      location="right"
      temporary
      width="400"
      class="pa-4 luxury-ai-drawer"
    >
      <div class="d-flex justify-space-between align-center mb-4">
        <div class="d-flex align-center">
          <v-avatar size="36" class="luxury-avatar mr-2">
            <v-icon icon="mdi-creation" color="white" size="20"></v-icon>
          </v-avatar>
          <div>
            <div class="font-weight-bold text-subtitle-1 text-slate-900">AI Co-Pilot Advisor</div>
            <div class="text-caption text-grey">ที่ปรึกษาการออกแบบกระบวนการเรียนรู้</div>
          </div>
        </div>
        <v-btn icon="mdi-close" variant="text" size="small" @click="aiDrawer = false"></v-btn>
      </div>

      <div v-if="aiLoading" class="text-center pa-8">
        <v-progress-circular indeterminate color="primary" class="mb-3"></v-progress-circular>
        <div class="text-caption text-grey">AI กำลังวิเคราะห์ข้อมูลของคุณครู...</div>
      </div>

      <div v-else-if="aiSuggestion">
        <v-card class="pa-4 rounded-2xl mb-4 ai-drawer-card elevation-1">
          <div class="font-weight-bold text-subtitle-2 text-primary mb-2">
            {{ aiSuggestion.title }}
          </div>
          <p v-if="aiSuggestion.problem_statement" class="text-body-2 mb-3 text-slate-800">
            {{ aiSuggestion.problem_statement }}
          </p>

          <v-list v-if="aiSuggestion.suggestions" density="compact" class="bg-transparent pa-0">
            <v-list-item v-for="(item, i) in aiSuggestion.suggestions" :key="i" class="pa-0 mb-2">
              <div class="d-flex align-start text-caption text-slate-700">
                <v-icon icon="mdi-check-circle" size="14" color="primary" class="mr-2 mt-0.5"></v-icon>
                <span>{{ item }}</span>
              </div>
            </v-list-item>
          </v-list>
        </v-card>
      </div>
    </v-navigation-drawer>

    <!-- Generate Game Progress Modal -->
    <v-dialog v-model="showGenerateModal" max-width="540" persistent>
      <v-card class="pa-6 rounded-2xl luxury-modal-card">
        <div class="text-center mb-4">
          <div class="d-inline-block position-relative mb-3">
            <img
              src="@/assets/images/ai_game_wizard.jpg"
              alt="AI Engine Artwork"
              class="floating-asset rounded-2xl elevation-6"
              style="width: 105px; height: 105px; object-fit: cover; border: 3px solid rgba(198, 112, 255, 0.6);"
            />
          </div>
          <h2 class="text-h5 font-weight-bold text-slate-900 mb-1">
            {{ isGenerating ? 'AI กำลังสร้างเกมการเรียนรู้ของคุณครู' : 'พร้อมสร้างเกมการเรียนรู้แล้ว!' }}
          </h2>
          <p class="text-caption text-slate-600 mb-0">
            แปลงข้อมูลจาก Design Thinking เข้าสู่ Web Game อัตโนมัติ โดยที่คุณครูไม่ต้องแต่งเกมหรือเขียนโค้ดเอง
          </p>
        </div>

        <div v-if="!isGenerating" class="mb-5">
          <v-sheet color="purple-lighten-5" rounded="xl" class="pa-4 border-card">
            <div class="font-weight-bold text-subtitle-2 mb-2 text-slate-900 d-flex align-center">
              <v-icon icon="mdi-information-outline" size="16" class="mr-1 text-primary"></v-icon>
              สรุปข้อมูลการสร้างเกม
            </div>
            <div class="text-caption text-slate-700 mb-1 d-flex align-center">
              <v-icon icon="mdi-gamepad-variant-outline" size="14" class="mr-1.5 text-primary"></v-icon>
              <span>โหมดและประเภท: <strong>{{ project.game_mode === 'multiplayer_live' ? 'ถ่ายทอดสดห้องเรียน' : 'ผจญภัยคนเดียว' }} &bull; {{ project.game_genre || '2D RPG' }}</strong></span>
            </div>
            <div class="text-caption text-slate-700 mb-1 d-flex align-center">
              <v-icon icon="mdi-book-education-outline" size="14" class="mr-1.5 text-primary"></v-icon>
              <span>วิชาและเนื้อหา: <strong>{{ project.subject }} &bull; {{ project.title }}</strong></span>
            </div>
            <div class="text-caption text-slate-700 d-flex align-center">
              <v-icon icon="mdi-account-school" size="14" class="mr-1.5 text-primary"></v-icon>
              <span>กลุ่มผู้เรียน: <strong>{{ empathizeData.target_learner || project.grade_level || 'ทั่วไป' }}</strong></span>
            </div>
          </v-sheet>
        </div>

        <!-- Multi-step Generator Pipeline -->
        <div v-else class="mb-5">
          <v-list density="compact" class="pa-0 mb-4 bg-transparent">
            <v-list-item v-for="(p, i) in genSteps" :key="i" class="pa-0 py-1">
              <div class="d-flex align-center text-body-2">
                <v-icon
                  v-if="genProgress > (i + 1) * 20"
                  icon="mdi-check-circle"
                  color="success"
                  size="18"
                  class="mr-2"
                ></v-icon>
                <v-progress-circular
                  v-else-if="genProgress >= i * 20"
                  indeterminate
                  size="16"
                  width="2"
                  color="primary"
                  class="mr-2"
                ></v-progress-circular>
                <v-icon v-else icon="mdi-circle-outline" color="grey" size="18" class="mr-2"></v-icon>
                <span :class="{ 'font-weight-bold text-primary': genProgress >= i * 20 }">
                  {{ p }}
                </span>
              </div>
            </v-list-item>
          </v-list>

          <v-progress-linear
            v-model="genProgress"
            color="primary"
            height="8"
            rounded
          ></v-progress-linear>
          <div class="text-right text-caption text-primary mt-1 font-weight-bold">
            {{ genProgress }}%
          </div>
        </div>

        <div class="d-flex justify-end gap-2">
          <v-btn
            v-if="!isGenerating"
            variant="text"
            @click="showGenerateModal = false"
            rounded="xl"
          >
            ยกเลิก
          </v-btn>
          <v-btn
            v-if="!isGenerating"
            class="luxury-launch-btn text-slate-900 font-weight-bold px-6"
            color="amber-accent-3"
            rounded="xl"
            prepend-icon="mdi-creation"
            @click="startGeneration"
          >
            เริ่มสร้างเกมทันที &rarr;
          </v-btn>
        </div>
      </v-card>
    </v-dialog>
  </div>
</template>

<script setup lang="ts">
import { ref, computed, onMounted } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { useProjectStore } from '@/stores/project'
import { useAlertStore } from '@/stores/alert'
import apiClient from '@/api/client'
import type { DesignProject } from '@/types'

const route = useRoute()
const router = useRouter()
const projectStore = useProjectStore()
const alertStore = useAlertStore()

const project = ref<DesignProject | null>(null)
const currentStepIndex = ref(0)

const steps = [
  { id: 'empathize', title: 'Empathize', thaiName: 'ทำความเข้าใจผู้เรียน', description: 'วิเคราะห์คุณลักษณะ ความชอบ บริบท และอุปสรรคของผู้เรียนเพื่อเป็นฐานในการออกแบบ', icon: 'mdi-heart-multiple' },
  { id: 'define', title: 'Define', thaiName: 'กำหนดปัญหาและเป้าหมาย', description: 'สรุป Learning Problem Statement และเป้าหมายการเรียนรู้ทั้งด้านความรู้ ทักษะ เจตคติ', icon: 'mdi-target' },
  { id: 'ideate', title: 'Ideate', thaiName: 'ออกแบบแนวคิดเกม', description: 'สร้างสรรค์ Concept, Story, รูปแบบด่าน และความท้าทายในเกมการเรียนรู้', icon: 'mdi-lightbulb-on' },
  { id: 'prototype', title: 'Prototype', thaiName: 'ร่างโครงสร้างฉาก', description: 'วางโครงสร้างแบบทดสอบและกลไกผลตอบรับ พร้อมส่งต่อเข้าสู่ Game Editor อัตโนมัติ', icon: 'mdi-palette-swatch' },
  { id: 'test', title: 'Test', thaiName: 'ทดสอบและปรับปรุง', description: 'บันทึกผลการทดสอบกับผู้เรียนจริง และเตรียมกดสร้างเกมการเรียนรู้ฉบับสมบูรณ์', icon: 'mdi-flask-round-bottom' },
]

// Step reactive form data
const empathizeData = ref<any>({})
const defineData = ref<any>({})
const ideateData = ref<any>({})
const prototypeData = ref<any>({})
const testData = ref<any>({})

// Per-field AI state
const activeFieldLoading = ref<string | null>(null)
const fieldSuggestions = ref<Record<string, { suggestion: string; alternatives?: string[] }>>({})
const stepAutoFillLoading = ref(false)

// AI Assistant Drawer
const aiDrawer = ref(false)
const aiLoading = ref(false)
const aiSuggestion = ref<any>(null)

// Generation Modal
const showGenerateModal = ref(false)
const isGenerating = ref(false)
const genProgress = ref(0)
const genSteps = [
  'วิเคราะห์ข้อมูลผู้เรียนและบริบท',
  'กำหนดวัตถุประสงค์และ Problem Statement',
  'ออกแบบ Missions และคำถามการเรียนรู้',
  'เลือก Assets และสร้าง Game Schema v1.0',
  'ตรวจสอบ Schema และเตรียม Game Editor',
]

// Step completion calculation
function isStepCompleted(stepId: string): boolean {
  if (stepId === 'empathize') {
    return Boolean(empathizeData.value.target_learner || empathizeData.value.pain_points)
  }
  if (stepId === 'define') {
    return Boolean(defineData.value.problem_statement || defineData.value.expected_outcomes)
  }
  if (stepId === 'ideate') {
    return Boolean(ideateData.value.game_concept || ideateData.value.story)
  }
  if (stepId === 'prototype') {
    return Boolean(prototypeData.value.feedback_mechanisms || prototypeData.value.core_rules)
  }
  if (stepId === 'test') {
    return Boolean(testData.value.observations || testData.value.feedback_summary)
  }
  return false
}

const completedStepsCount = computed(() => {
  return steps.filter(s => isStepCompleted(s.id)).length
})

function onFieldChange(stepName: string, data: any) {
  projectStore.queueAutoSave(stepName, data)
}

function goToStep(idx: number) {
  currentStepIndex.value = idx
}

// Request AI assistance for a specific field
async function requestFieldAi(step: string, field: string, _fieldLabel: string) {
  if (!project.value) return
  activeFieldLoading.value = field

  let currentVal = ''
  if (step === 'empathize') currentVal = empathizeData.value[field] || ''
  else if (step === 'define') currentVal = defineData.value[field] || ''
  else if (step === 'ideate') currentVal = ideateData.value[field] || ''
  else if (step === 'prototype') currentVal = prototypeData.value[field] || ''
  else if (step === 'test') currentVal = testData.value[field] || ''

  try {
    const { data } = await apiClient.post(`/projects/${project.value.id}/ai-field-assist`, {
      step,
      field,
      current_value: currentVal,
    })

    fieldSuggestions.value[field] = {
      suggestion: data.suggestion,
      alternatives: data.alternatives || [],
    }
  } catch (err) {
    console.error('Failed to get AI field assist', err)
    alertStore.error('ไม่สามารถเรียกข้อมูล AI ได้ในขณะนี้ กรุณาลองใหม่อีกครั้ง')
  } finally {
    activeFieldLoading.value = null
  }
}

// Dismiss an inline suggestion box
function dismissSuggestion(field: string) {
  delete fieldSuggestions.value[field]
}

// Apply AI suggestion directly into field
function applyFieldSuggestion(step: string, field: string) {
  const item = fieldSuggestions.value[field]
  if (!item) return

  if (step === 'empathize') {
    empathizeData.value[field] = item.suggestion
    onFieldChange('empathize', empathizeData.value)
  } else if (step === 'define') {
    defineData.value[field] = item.suggestion
    onFieldChange('define', defineData.value)
  } else if (step === 'ideate') {
    ideateData.value[field] = item.suggestion
    onFieldChange('ideate', ideateData.value)
  } else if (step === 'prototype') {
    prototypeData.value[field] = item.suggestion
    onFieldChange('prototype', prototypeData.value)
  } else if (step === 'test') {
    testData.value[field] = item.suggestion
    onFieldChange('test', testData.value)
  }

  delete fieldSuggestions.value[field]
  alertStore.success('นำข้อความจาก AI ไปใช้เรียบร้อยแล้ว')
}

// Append suggestion to existing content
function appendFieldSuggestion(step: string, field: string) {
  const item = fieldSuggestions.value[field]
  if (!item) return

  let targetObj: any = null
  if (step === 'empathize') targetObj = empathizeData.value
  else if (step === 'define') targetObj = defineData.value
  else if (step === 'ideate') targetObj = ideateData.value
  else if (step === 'prototype') targetObj = prototypeData.value
  else if (step === 'test') targetObj = testData.value

  if (targetObj) {
    const existing = targetObj[field] || ''
    targetObj[field] = existing ? `${existing} ${item.suggestion}` : item.suggestion
    onFieldChange(step, targetObj)
  }

  delete fieldSuggestions.value[field]
  alertStore.success('เพิ่มข้อความต่อท้ายเรียบร้อยแล้ว')
}

// Auto-fill an entire step using AI
async function handleStepAutoFill(step: string) {
  if (!project.value) return
  stepAutoFillLoading.value = true

  try {
    const { data } = await apiClient.post(`/projects/${project.value.id}/ai-step-autofill`, {
      step,
    })

    if (step === 'empathize') {
      empathizeData.value = { ...empathizeData.value, ...data }
      onFieldChange('empathize', empathizeData.value)
    } else if (step === 'define') {
      defineData.value = { ...defineData.value, ...data }
      onFieldChange('define', defineData.value)
    } else if (step === 'ideate') {
      ideateData.value = { ...ideateData.value, ...data }
      onFieldChange('ideate', ideateData.value)
    } else if (step === 'prototype') {
      prototypeData.value = { ...prototypeData.value, ...data }
      onFieldChange('prototype', prototypeData.value)
    } else if (step === 'test') {
      testData.value = { ...testData.value, ...data }
      onFieldChange('test', testData.value)
    }

    alertStore.success('AI ได้ช่วยเติมข้อมูลครบทุกช่องในขั้นตอนนี้แล้ว! คุณครูสามารถปรับแต่งต่อได้เลย')
  } catch (err: any) {
    alertStore.error('ไม่สามารถเรียก AI เติมข้อมูลทั้งขั้นตอนได้')
  } finally {
    stepAutoFillLoading.value = false
  }
}

// Open general AI co-pilot drawer
async function openAiAssistant() {
  aiDrawer.value = true
  aiLoading.value = true
  try {
    const currentStepName = steps[currentStepIndex.value].id
    const { data } = await apiClient.post(`/projects/${project.value!.id}/ai-assist`, {
      step: currentStepName,
    })
    aiSuggestion.value = data
  } catch (err) {
    console.error('Failed to load AI assist', err)
  } finally {
    aiLoading.value = false
  }
}

// Start Game Generation
async function startGeneration() {
  isGenerating.value = true
  genProgress.value = 15

  const interval = setInterval(() => {
    if (genProgress.value < 85) {
      genProgress.value += 15
    }
  }, 400)

  try {
    const { data } = await apiClient.post(`/projects/${project.value!.id}/generate-game`)
    clearInterval(interval)
    genProgress.value = 100

    setTimeout(() => {
      showGenerateModal.value = false
      router.push(`/games/${data.game.id}/edit`)
    }, 600)
  } catch (err: any) {
    clearInterval(interval)
    isGenerating.value = false
    alertStore.error(err.response?.data?.message || 'เกิดข้อผิดพลาดในการสร้างเกม', 'สร้างเกมไม่สำเร็จ')
  }
}

// Delete Project
async function handleDeleteProject() {
  if (!project.value) return
  const confirmed = await alertStore.confirm(
    `คุณต้องการลบโปรเจกต์ "${project.value.title}" ใช่หรือไม่? ข้อมูลขั้นตอน Design Thinking ทั้งหมดจะถูกลบอย่างถาวร`,
    'ยืนยันการลบโปรเจกต์',
    { confirmText: 'ลบโปรเจกต์', cancelText: 'ยกเลิก', type: 'error' }
  )

  if (!confirmed) return

  try {
    await apiClient.delete(`/projects/${project.value.id}`)
    alertStore.success('ลบโปรเจกต์เรียบร้อยแล้ว', 'สำเร็จ')
    router.push('/projects')
  } catch (err: any) {
    alertStore.error(err.response?.data?.message || 'ไม่สามารถลบโปรเจกต์ได้', 'เกิดข้อผิดพลาด')
  }
}

onMounted(async () => {
  const projectId = Number(route.params.id)
  try {
    const loaded = await projectStore.loadProject(projectId)
    project.value = loaded
    empathizeData.value = loaded.empathize || {}
    defineData.value = loaded.define || {}
    ideateData.value = loaded.ideate || {}
    prototypeData.value = loaded.prototype || {}
    testData.value = loaded.test || {}
  } catch (err) {
    console.error('Failed to load project', err)
  }
})
</script>

<style scoped>
.dt-luxury-studio {
  font-family: 'LINESeedSansTH', sans-serif;
  max-width: 1400px;
  margin: 0 auto;
}

/* Executive Top Bar */
.luxury-top-card {
  background: linear-gradient(135deg, #1b0330 0%, #3d0066 60%, #52008a 100%);
  border: 1px solid rgba(198, 112, 255, 0.35);
  box-shadow: 0 10px 25px -5px rgba(61, 0, 102, 0.3);
}

.luxury-step-badge {
  background: rgba(255, 255, 255, 0.15) !important;
  color: #fce7f3 !important;
  border: 1px solid rgba(255, 255, 255, 0.3);
}

.mode-chip {
  background: rgba(255, 224, 71, 0.18) !important;
  color: #ffe047 !important;
  border: 1px solid rgba(255, 224, 71, 0.4);
}

.autosave-pill {
  background: rgba(0, 0, 0, 0.25);
  border: 1px solid rgba(255, 255, 255, 0.15);
}

.pulse-dot {
  width: 8px;
  height: 8px;
  border-radius: 50%;
  display: inline-block;
}

.pulse-dot.green {
  background-color: #4ade80;
  box-shadow: 0 0 8px #4ade80;
}

.pulse-dot.grey {
  background-color: #94a3b8;
}

.luxury-ai-btn {
  background: linear-gradient(135deg, #c670ff 0%, #8b00e8 100%) !important;
  box-shadow: 0 4px 14px rgba(198, 112, 255, 0.4);
}

.generate-nav-btn {
  box-shadow: 0 4px 14px rgba(255, 224, 71, 0.35);
}

/* Stepper Navigation on Left - Sticky */
.luxury-nav-card {
  background: #ffffff;
  border: 1px solid rgba(198, 112, 255, 0.2);
  box-shadow: 0 4px 20px rgba(61, 0, 102, 0.04);
  position: sticky;
  top: 80px;
  z-index: 10;
  max-height: calc(100vh - 100px);
  overflow-y: auto;
}

.step-nav-item {
  border: 1px solid #f1f5f9;
  background: #ffffff;
  transition: all 0.2s ease-in-out;
}

.step-nav-item:hover {
  background: #faf5ff;
  border-color: rgba(198, 112, 255, 0.3);
  transform: translateX(2px);
}

.step-nav-item.step-active {
  background: linear-gradient(135deg, #faf5ff 0%, #f3e8ff 100%);
  border: 1.5px solid #c670ff;
  box-shadow: 0 4px 16px rgba(198, 112, 255, 0.18);
}

.step-num-badge {
  font-size: 0.75rem;
  font-weight: 800;
  color: #64748b;
  background: #f1f5f9;
  padding: 4px 8px;
  border-radius: 8px;
}

.step-active .step-num-badge {
  background: #3d0066;
  color: #ffffff;
}

.step-title {
  color: #1e293b;
}

.step-active .step-title {
  color: #3d0066;
  font-weight: 800 !important;
}

.step-subtitle {
  color: #64748b;
  font-size: 0.75rem;
}

.ai-sidebar-tip {
  background: linear-gradient(135deg, #faf5ff 0%, #f3e8ff 100%);
  border: 1px dashed rgba(198, 112, 255, 0.5);
}

/* Main Workspace */
.luxury-workspace-card {
  background: #ffffff;
  border: 1px solid rgba(198, 112, 255, 0.2);
  box-shadow: 0 10px 30px rgba(61, 0, 102, 0.04);
}

.step-hero-banner {
  background: linear-gradient(135deg, #fbf7ff 0%, #f5e8ff 100%);
  border: 1px solid rgba(198, 112, 255, 0.25);
}

.step-hero-icon-box {
  width: 52px;
  height: 52px;
  border-radius: 16px;
  background: linear-gradient(135deg, #3d0066 0%, #8b00e8 100%);
  display: flex;
  align-items: center;
  justify-content: center;
  box-shadow: 0 6px 16px rgba(61, 0, 102, 0.25);
}

.ai-autofill-btn {
  background: linear-gradient(135deg, #8b00e8 0%, #c670ff 100%) !important;
  box-shadow: 0 4px 14px rgba(198, 112, 255, 0.35);
}

/* Field Container & AI Buttons */
.field-container {
  position: relative;
}

.field-label {
  font-size: 0.85rem;
  font-weight: 700;
  color: #334155;
  display: flex;
  align-items: center;
}

.ai-field-btn {
  background: #faf5ff !important;
  color: #8b00e8 !important;
  border: 1px solid rgba(198, 112, 255, 0.35) !important;
  font-weight: 700 !important;
  border-radius: 12px;
  transition: all 0.2s ease;
}

.ai-field-btn:hover {
  background: #f3e8ff !important;
  border-color: #8b00e8 !important;
  transform: translateY(-1px);
}

/* Inline AI Suggestion Card */
.ai-suggestion-card {
  background: linear-gradient(135deg, #fdfaff 0%, #f7edff 100%);
  border: 1.5px solid #c670ff;
  box-shadow: 0 4px 18px rgba(198, 112, 255, 0.15);
  animation: fadeInDown 0.25s ease-out;
}

@keyframes fadeInDown {
  from {
    opacity: 0;
    transform: translateY(-6px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}

.prototype-info-banner {
  background: #f8fafc;
  border: 1px solid #e2e8f0;
}

.luxury-next-btn {
  background: linear-gradient(135deg, #3d0066 0%, #5b0099 100%) !important;
}

.luxury-launch-btn {
  background: linear-gradient(135deg, #ffe047 0%, #ffce1f 100%) !important;
}

.luxury-avatar {
  background: linear-gradient(135deg, #3d0066 0%, #c670ff 100%);
}

.ai-drawer-card {
  background: #fbf7ff;
  border: 1px solid rgba(198, 112, 255, 0.3);
}

.luxury-modal-card {
  border: 1.5px solid rgba(198, 112, 255, 0.4);
  box-shadow: 0 20px 40px rgba(61, 0, 102, 0.2);
}

.cursor-pointer {
  cursor: pointer;
}

.gap-2 {
  gap: 8px;
}

.gap-3 {
  gap: 12px;
}
</style>
