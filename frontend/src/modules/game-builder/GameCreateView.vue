<template>
  <div class="max-w-6xl mx-auto py-4 px-2">
    <!-- Header: Title & Fast Track Bar -->
    <div class="d-flex align-center justify-space-between mb-4 flex-wrap gap-3">
      <div class="d-flex align-center">
        <v-btn icon="mdi-arrow-left" variant="outlined" to="/games" class="mr-3 bg-white"></v-btn>
        <div>
          <h1 class="text-h5 font-weight-bold text-slate-800 d-flex align-center">
            <v-icon icon="mdi-creation" color="primary" class="mr-2"></v-icon>
            Design Your Game — AI HTML5 Studio
          </h1>
          <p class="text-body-2 text-grey">
            รวมกระบวนการคิดเชิงออกแบบ (Design Thinking 5 ขั้นตอน) เข้ากับการสร้างเกม HTML5 Canvas + Kenney Assets เล่นได้จริงทันที
          </p>
        </div>
      </div>

      <div class="d-flex gap-2 align-center flex-wrap">
        <v-chip
          :color="hasActiveAiKey ? 'success' : 'warning'"
          variant="tonal"
          class="font-weight-bold"
          size="small"
        >
          <v-icon :icon="hasActiveAiKey ? 'mdi-check-circle' : 'mdi-alert-circle'" start size="14"></v-icon>
          AI: {{ hasActiveAiKey ? 'Gemini พร้อมใช้งาน' : 'ยังไม่ตั้งค่า Key (ใช้ Engine จำลอง)' }}
        </v-chip>

        <v-btn
          to="/settings/ai"
          variant="outlined"
          color="primary"
          prepend-icon="mdi-cog"
          rounded="lg"
          size="small"
        >
          ตั้งค่า AI Provider
        </v-btn>

        <v-btn
          to="/projects"
          variant="tonal"
          color="purple-darken-1"
          prepend-icon="mdi-format-list-bulleted"
          rounded="lg"
          size="small"
        >
          คลังโปรเจกต์ Design Thinking
        </v-btn>
      </div>
    </div>

    <!-- ⚡ Quick 1-Click Fast Track AI Banner -->
    <v-card class="pa-4 mb-5 border-card rounded-2xl elevation-2 ai-fast-card bg-white">
      <div class="d-flex align-center justify-space-between flex-wrap gap-3">
        <div class="d-flex align-center" style="flex: 1 1 340px;">
          <v-avatar size="44" color="purple-lighten-5" class="mr-3 elevation-1">
            <span class="text-h6">⚡</span>
          </v-avatar>
          <div style="flex: 1;">
            <div class="text-subtitle-2 font-weight-bold text-slate-800 d-flex align-center">
              AI Fast-Track Studio: ป้อนหัวข้อเพื่อสร้างครบ 5 ขั้นตอนในคลิกเดียว
              <v-chip size="x-small" color="primary" variant="flat" class="ml-2 font-weight-bold">1-Click Auto</v-chip>
            </div>
            <div class="text-caption text-grey">
              ใส่ชื่อบทเรียนหรือไอเดียเกม AI จะวิเคราะห์ Empathize, กำหนด Define, ออกแบบ Ideate และเลือก Assets ให้ครบทุกขั้นตอน
            </div>
          </div>
        </div>

        <div class="d-flex align-center gap-2 flex-wrap" style="flex: 1 1 450px;">
          <v-text-field
            v-model="quickTopic"
            placeholder="เช่น ปลูกผักเกษตรอินทรีย์, สงครามคณิตศาสตร์, ตะลุยล่าคำศัพท์ภาษาอังกฤษ..."
            density="compact"
            variant="outlined"
            rounded="lg"
            hide-details
            class="bg-grey-lighten-5"
            style="min-width: 250px; flex: 1;"
            @keyup.enter="handleFastTrackAutoFill"
          ></v-text-field>

          <v-btn
            class="ai-gradient-bg text-white font-weight-bold px-4 elevation-2"
            rounded="lg"
            prepend-icon="mdi-auto-fix"
            :loading="autoFillingAll"
            @click="handleFastTrackAutoFill"
          >
            ⚡ AI เติมให้ครบ 5 ขั้นตอน
          </v-btn>
        </div>
      </div>
    </v-card>

    <!-- 5-Step Stepper Navigation -->
    <v-card class="pa-2 mb-6 border-card rounded-2xl bg-white elevation-1">
      <div class="d-flex flex-wrap stepper-tabs-row justify-space-around">
        <div
          v-for="(step, idx) in dtSteps"
          :key="step.key"
          class="stepper-tab-item px-4 py-3 rounded-xl cursor-pointer transition-all d-flex align-center"
          :class="{
            'tab-active': currentStepIndex === idx,
            'tab-completed': idx < currentStepIndex || createdGame,
          }"
          @click="currentStepIndex = idx"
        >
          <v-avatar
            size="28"
            :color="currentStepIndex === idx ? 'primary' : (idx < currentStepIndex ? 'success' : 'grey-lighten-3')"
            class="mr-2 text-caption font-weight-bold"
            :class="{ 'text-white': currentStepIndex === idx || idx < currentStepIndex }"
          >
            <v-icon v-if="idx < currentStepIndex" icon="mdi-check" size="16"></v-icon>
            <span v-else>{{ idx + 1 }}</span>
          </v-avatar>
          <div>
            <div class="text-caption font-weight-bold step-title-text">{{ step.name }}</div>
            <div class="text-caption text-grey text-truncate d-none d-sm-block" style="font-size: 11px;">
              {{ step.subtitle }}
            </div>
          </div>
        </div>
      </div>
    </v-card>

    <!-- STEP WORKSPACES -->
    <v-form @submit.prevent="handleCreate">
      <!-- ========================================== -->
      <!-- STEP 1: EMPATHIZE (เข้าใจกลุ่มผู้เรียน) -->
      <!-- ========================================== -->
      <div v-show="currentStepIndex === 0">
        <v-card class="pa-6 border-card rounded-2xl bg-white elevation-1 mb-6">
          <div class="d-flex align-center justify-space-between mb-4 flex-wrap gap-2">
            <div class="d-flex align-center">
              <v-avatar size="40" color="pink-lighten-5" class="mr-3">
                <v-icon icon="mdi-account-heart-outline" color="pink-darken-1" size="22"></v-icon>
              </v-avatar>
              <div>
                <h2 class="text-subtitle-1 font-weight-bold text-slate-900">
                  ขั้นตอนที่ 1: Empathize — ทำความเข้าใจผู้เรียนและผู้เล่น
                </h2>
                <div class="text-caption text-grey">สำรวจความต้องการ บุคลิก และจุดติดขัดในการเรียนรู้ เพื่อเป็นฐานในการออกแบบเกม</div>
              </div>
            </div>

            <v-btn
              size="small"
              color="pink-darken-1"
              variant="tonal"
              prepend-icon="mdi-creation"
              rounded="lg"
              :loading="generatingStep === 'empathize'"
              @click="generateStepWithAi('empathize')"
            >
              ✨ AI ช่วยวิเคราะห์ Empathize
            </v-btn>
          </div>

          <v-row>
            <v-col cols="12" md="6">
              <v-text-field
                v-model="form.dt.empathize.target_learner"
                label="กลุ่มผู้เรียนเป้าหมาย (Target Learners) *"
                placeholder="เช่น นักเรียนชั้น ม.1-3, นักเรียนระดับประถมปลาย, บุคคลทั่วไป"
                variant="outlined"
                density="comfortable"
                rounded="lg"
              ></v-text-field>
            </v-col>
            <v-col cols="12" md="6">
              <v-text-field
                v-model="form.dt.empathize.age_group"
                label="ช่วงอายุ (Age Group) *"
                placeholder="เช่น 10 - 15 ปี (วัยรุ่นตอนต้น)"
                variant="outlined"
                density="comfortable"
                rounded="lg"
              ></v-text-field>
            </v-col>
            <v-col cols="12">
              <v-textarea
                v-model="form.dt.empathize.learner_characteristics"
                label="พฤติกรรมและความชอบของผู้เรียน (Learner Characteristics & Interests)"
                placeholder="ชอบเกมภาพสวย มีปฏิสัมพันธ์ทันที สนุกกับการแข่งขันเก็บคะแนนหรือสะสมไอเทม..."
                variant="outlined"
                density="comfortable"
                rounded="lg"
                rows="2"
                auto-grow
              ></v-textarea>
            </v-col>
            <v-col cols="12">
              <v-textarea
                v-model="form.dt.empathize.pain_points"
                label="ปัญหาหรือจุดติดขัดในการเรียนรู้เดิม (Pain Points & Difficulties) *"
                placeholder="รู้สึกว่าเนื้อหายาก เป็นนามธรรม ท่องจำแล้วลืมเร็ว เบื่อหน่ายการทำใบงานแบบเดิมๆ..."
                variant="outlined"
                density="comfortable"
                rounded="lg"
                rows="2"
                auto-grow
              ></v-textarea>
            </v-col>
            <v-col cols="12">
              <v-text-field
                v-model="form.dt.empathize.learning_environment"
                label="สภาพแวดล้อมและอุปกรณ์ที่ใช้เล่น (Learning Environment)"
                placeholder="สมาร์ตโฟน แท็บเล็ต คอมพิวเตอร์ห้องเรียน หรือการเรียนรู้แบบผสมผสาน"
                variant="outlined"
                density="comfortable"
                rounded="lg"
              ></v-text-field>
            </v-col>
          </v-row>

          <div class="d-flex justify-end mt-4">
            <v-btn color="primary" rounded="lg" prepend-icon="mdi-arrow-right" @click="currentStepIndex = 1">
              ถัดไป: กำหนดโจทย์ (Define)
            </v-btn>
          </div>
        </v-card>
      </div>

      <!-- ========================================== -->
      <!-- STEP 2: DEFINE (ระบุโจทย์ & เป้าหมาย) -->
      <!-- ========================================== -->
      <div v-show="currentStepIndex === 1">
        <v-card class="pa-6 border-card rounded-2xl bg-white elevation-1 mb-6">
          <div class="d-flex align-center justify-space-between mb-4 flex-wrap gap-2">
            <div class="d-flex align-center">
              <v-avatar size="40" color="amber-lighten-5" class="mr-3">
                <v-icon icon="mdi-target" color="amber-darken-3" size="22"></v-icon>
              </v-avatar>
              <div>
                <h2 class="text-subtitle-1 font-weight-bold text-slate-900">
                  ขั้นตอนที่ 2: Define — ระบุโจทย์ปัญหาและเป้าหมายการเรียนรู้
                </h2>
                <div class="text-caption text-grey">กำหนดคำแถลงปัญหา (How Might We) และวัตถุประสงค์เชิงพฤติกรรมที่ต้องการให้เกิด</div>
              </div>
            </div>

            <v-btn
              size="small"
              color="amber-darken-3"
              variant="tonal"
              prepend-icon="mdi-creation"
              rounded="lg"
              :loading="generatingStep === 'define'"
              @click="generateStepWithAi('define')"
            >
              ✨ AI ช่วยกำหนดโจทย์ Define
            </v-btn>
          </div>

          <v-row>
            <v-col cols="12">
              <v-textarea
                v-model="form.dt.define.problem_statement"
                label="คำแถลงปัญหาหลัก (Problem Statement / How Might We) *"
                placeholder="ผู้เรียนขาดประสบการณ์ปฏิบัติในการเข้าใจเนื้อหา จึงต้องการเกมจำลองที่เปิดโอกาสให้ลองผิดลองถูกอย่างปลอดภัย..."
                variant="outlined"
                density="comfortable"
                rounded="lg"
                rows="3"
                auto-grow
              ></v-textarea>
            </v-col>
            <v-col cols="12">
              <v-textarea
                v-model="form.dt.define.expected_outcomes"
                label="ผลลัพธ์การเรียนรู้ที่คาดหวัง (Expected Outcomes) *"
                placeholder="ผู้เรียนสามารถอธิบายและประยุกต์ใช้ความรู้ผ่านเกณฑ์ 80% หลังจากเล่นเกมจบ..."
                variant="outlined"
                density="comfortable"
                rounded="lg"
                rows="2"
                auto-grow
              ></v-textarea>
            </v-col>
            <v-col cols="12" md="6">
              <v-text-field
                v-model="form.dt.define.knowledge_goals"
                label="เป้าหมายด้านความรู้ (Knowledge Goals - K)"
                placeholder="เข้าใจปัจจัยสำคัญและขั้นตอนสำคัญของบทเรียน"
                variant="outlined"
                density="comfortable"
                rounded="lg"
              ></v-text-field>
            </v-col>
            <v-col cols="12" md="6">
              <v-text-field
                v-model="form.dt.define.skill_goals"
                label="เป้าหมายด้านทักษะ (Skill Goals - P)"
                placeholder="ทักษะการตัดสินใจ การวางแผน และการแก้ปัญหาเฉพาะหน้า"
                variant="outlined"
                density="comfortable"
                rounded="lg"
              ></v-text-field>
            </v-col>
          </v-row>

          <div class="d-flex justify-space-between mt-4">
            <v-btn variant="text" rounded="lg" prepend-icon="mdi-arrow-left" @click="currentStepIndex = 0">
              ย้อนกลับ (Empathize)
            </v-btn>
            <v-btn color="primary" rounded="lg" prepend-icon="mdi-arrow-right" @click="currentStepIndex = 2">
              ถัดไป: ระดมไอเดียเกม (Ideate)
            </v-btn>
          </div>
        </v-card>
      </div>

      <!-- ========================================== -->
      <!-- STEP 3: IDEATE (ระดมไอเดีย & ออกแบบเกม) -->
      <!-- ========================================== -->
      <div v-show="currentStepIndex === 2">
        <v-card class="pa-6 border-card rounded-2xl bg-white elevation-1 mb-6">
          <div class="d-flex align-center justify-space-between mb-4 flex-wrap gap-2">
            <div class="d-flex align-center">
              <v-avatar size="40" color="purple-lighten-5" class="mr-3">
                <v-icon icon="mdi-lightbulb-on" color="purple-darken-1" size="22"></v-icon>
              </v-avatar>
              <div>
                <h2 class="text-subtitle-1 font-weight-bold text-slate-900">
                  ขั้นตอนที่ 3: Ideate — ระดมไอเดียและสร้างสรรค์รูปแบบเกม
                </h2>
                <div class="text-caption text-grey">กำหนดชื่อเกม แนวเกม เนื้อเรื่อง กติกา และวิธีเล่นตามจินตนาการ</div>
              </div>
            </div>

            <v-btn
              size="small"
              color="purple-darken-1"
              variant="tonal"
              prepend-icon="mdi-creation"
              rounded="lg"
              :loading="generatingStep === 'ideate'"
              @click="generateStepWithAi('ideate')"
            >
              ✨ AI ช่วยคิดไอเดีย Ideate
            </v-btn>
          </div>

          <!-- Quick Idea Chips -->
          <div class="mb-4">
            <div class="text-caption font-weight-bold text-slate-700 mb-2 d-flex align-center">
              <v-icon icon="mdi-flash" size="14" color="amber-darken-2" class="mr-1"></v-icon>
              คลิกเพื่อโหลดแนวคิดเกมยอดนิยมแบบทันที:
            </div>
            <div class="d-flex flex-wrap gap-2">
              <v-chip
                v-for="preset in ideaPresets"
                :key="preset.title"
                size="small"
                variant="outlined"
                color="primary"
                class="cursor-pointer font-weight-medium preset-chip"
                @click="applyIdeaPreset(preset)"
              >
                {{ preset.icon }} {{ preset.title }}
              </v-chip>
            </div>
          </div>

          <v-row>
            <v-col cols="12" md="8">
              <v-text-field
                v-model="form.title"
                label="ชื่อเกม (Game Title) *"
                placeholder="เช่น ฟาร์มเกษตรอินทรีย์ ปลูกผักพิทักษ์โลก, ประลองคณิตเวทมนตร์, แดนคำศัพท์พิศวง"
                variant="outlined"
                density="comfortable"
                rounded="lg"
                :rules="[v => !!v || 'กรุณาระบุชื่อเกม']"
                required
              ></v-text-field>
            </v-col>
            <v-col cols="12" md="4">
              <v-select
                v-model="form.genre"
                :items="genreOptions"
                item-title="title"
                item-value="value"
                label="แนวเกม (Genre)"
                variant="outlined"
                density="comfortable"
                rounded="lg"
              ></v-select>
            </v-col>

            <v-col cols="12">
              <v-textarea
                v-model="form.prompt"
                label="อธิบายรูปแบบเกม กติกา และวิธีเล่น (Prompt สำหรับสร้าง Canvas Game) *"
                placeholder="ระบุสิ่งที่ผู้เล่นต้องทำอย่างละเอียด เช่น เดินสำรวจแปลงดิน ปลูกผัก รดน้ำ มีศัตรูพืชมาบุก มีกระดานคะแนน หลอดเลือด ร้านค้าอัปเกรด..."
                variant="outlined"
                density="comfortable"
                rounded="lg"
                rows="4"
                auto-grow
                :rules="[v => !!v || 'กรุณาใส่คำบรรยายเกม']"
                required
              ></v-textarea>
            </v-col>

            <v-col cols="12" md="6">
              <v-textarea
                v-model="form.dt.ideate.story"
                label="เนื้อเรื่อง & บรรยากาศ (Storyline & Theme)"
                placeholder="ผู้เล่นสวมบทบาทเป็นเกษตรกรรุ่นเยาว์ที่ต้องกอบกู้ฟาร์มของคุณตา..."
                variant="outlined"
                density="comfortable"
                rounded="lg"
                rows="2"
                auto-grow
              ></v-textarea>
            </v-col>

            <v-col cols="12" md="6">
              <v-textarea
                v-model="form.dt.ideate.missions"
                label="ภารกิจและความท้าทาย (Missions & Challenges)"
                placeholder="1. ปลูกผักให้ครบแปลง 2. รดน้ำให้โต 3. ปราบศัตรูพืช 4. เก็บเกี่ยวส่งร้านค้า..."
                variant="outlined"
                density="comfortable"
                rounded="lg"
                rows="2"
                auto-grow
              ></v-textarea>
            </v-col>
          </v-row>

          <div class="d-flex justify-space-between mt-4">
            <v-btn variant="text" rounded="lg" prepend-icon="mdi-arrow-left" @click="currentStepIndex = 1">
              ย้อนกลับ (Define)
            </v-btn>
            <v-btn color="primary" rounded="lg" prepend-icon="mdi-arrow-right" @click="currentStepIndex = 3">
              ถัดไป: เลือกระบบและ Assets (Prototype)
            </v-btn>
          </div>
        </v-card>
      </div>

      <!-- ========================================== -->
      <!-- STEP 4: PROTOTYPE (กลไกเกม & KENNEY ASSETS) -->
      <!-- ========================================== -->
      <div v-show="currentStepIndex === 3">
        <!-- 4.1 Modular Features Checklist -->
        <v-card class="pa-6 border-card rounded-2xl bg-white elevation-1 mb-6">
          <div class="d-flex align-center justify-space-between mb-4 flex-wrap gap-2">
            <div class="d-flex align-center">
              <v-avatar size="40" color="teal-lighten-5" class="mr-3">
                <v-icon icon="mdi-puzzle-outline" color="teal-darken-1" size="22"></v-icon>
              </v-avatar>
              <div>
                <h2 class="text-subtitle-1 font-weight-bold text-slate-900">
                  ขั้นตอนที่ 4: Prototype — เลือกระบบ กลไกเกม และ Assets 2D
                </h2>
                <div class="text-caption text-grey">เลือกเปิด/ปิด กลไกเกมที่ต้องการ (หลอดเลือด, มินิแมพ, ร้านค้า, บอส ฯลฯ) พร้อมเลือกรูปภาพ 2D</div>
              </div>
            </div>

            <!-- Quick Preset Toggles -->
            <div class="d-flex flex-wrap gap-2">
              <v-btn size="x-small" variant="tonal" color="purple" @click="selectAllFeatures">🌟 เลือกทั้งหมด (18 ระบบ)</v-btn>
              <v-btn size="x-small" variant="tonal" color="deep-orange" @click="applyActionPack">⚔️ ชุดแอ็กชัน</v-btn>
              <v-btn size="x-small" variant="tonal" color="green-darken-1" @click="applyFarmPack">🌾 ชุดฟาร์ม</v-btn>
              <v-btn size="x-small" variant="tonal" color="blue" @click="applyQuizPack">🧩 ชุดควิซ</v-btn>
              <v-btn size="x-small" variant="text" color="grey" @click="resetFeatures">ค่าเริ่มต้น</v-btn>
            </div>
          </div>

          <!-- Feature Categories Grid -->
          <div v-for="cat in featureCategories" :key="cat.name" class="mb-5">
            <div class="d-flex align-center justify-space-between mb-2">
              <div class="text-subtitle-2 font-weight-bold text-slate-800 d-flex align-center">
                <span class="mr-2">{{ cat.icon }}</span>
                {{ cat.name }}
                <v-chip size="x-small" class="ml-2 font-weight-bold" variant="tonal" color="primary">
                  {{ getSelectedCountInCategory(cat) }} / {{ cat.features.length }}
                </v-chip>
              </div>
              <v-btn size="20" variant="text" color="primary" class="text-caption" @click="toggleCategory(cat)">
                สลับเลือกทั้งหมวด
              </v-btn>
            </div>

            <v-row dense>
              <v-col v-for="feat in cat.features" :key="feat.key" cols="12" sm="6" md="3">
                <v-card
                  class="pa-3 border-card rounded-xl cursor-pointer transition-all h-100 d-flex flex-column justify-space-between"
                  :class="{ 'feature-card-active': form.features.includes(feat.key) }"
                  @click="toggleFeature(feat.key)"
                  variant="outlined"
                >
                  <div>
                    <div class="d-flex align-center justify-space-between mb-1">
                      <div class="d-flex align-center">
                        <span class="mr-2 text-h6">{{ feat.icon }}</span>
                        <span class="font-weight-bold text-caption text-slate-900">{{ feat.name }}</span>
                      </div>
                      <v-checkbox-btn
                        :model-value="form.features.includes(feat.key)"
                        color="primary"
                        density="compact"
                        class="ma-0 pa-0"
                      ></v-checkbox-btn>
                    </div>
                    <p class="text-caption text-grey mb-0" style="font-size: 11px; line-height: 1.3;">{{ feat.desc }}</p>
                  </div>
                </v-card>
              </v-col>
            </v-row>
          </div>
        </v-card>

        <!-- 4.2 Kenney 2D Assets Selector -->
        <v-card class="pa-6 border-card rounded-2xl bg-white elevation-1 mb-6">
          <div class="d-flex align-center justify-space-between mb-4 flex-wrap gap-2">
            <div class="d-flex align-center">
              <v-avatar size="36" color="purple-lighten-5" class="mr-3">
                <v-icon icon="mdi-folder-image" color="primary" size="20"></v-icon>
              </v-avatar>
              <div>
                <h2 class="text-subtitle-1 font-weight-bold text-slate-900">คลัง Assets 2D สำหรับวาดในเกม (Kenney Sprites)</h2>
                <div class="text-caption text-grey">เลือกสไปรต์ตัวละคร ศัตรู และไอเทมจากคลัง Kenney CC0 (324 รูป) หรืออัปโหลดรูปภาพของคุณเอง</div>
              </div>
            </div>

            <div class="d-flex gap-2">
              <v-btn
                size="small"
                color="primary"
                variant="tonal"
                prepend-icon="mdi-upload"
                rounded="lg"
                @click="triggerUploadDialog"
              >
                + อัปโหลดรูปของฉัน
              </v-btn>
            </div>
          </div>

          <!-- Custom Upload Dialog -->
          <v-dialog v-model="uploadDialog" max-width="500">
            <v-card class="pa-5 rounded-2xl">
              <h3 class="text-subtitle-1 font-weight-bold mb-3 d-flex align-center">
                <v-icon icon="mdi-cloud-upload" color="primary" class="mr-2"></v-icon>
                อัปโหลด Asset รูปภาพใหม่
              </h3>
              <v-text-field v-model="uploadForm.name" label="ชื่อ Asset *" variant="outlined" density="comfortable" class="mb-3"></v-text-field>
              <v-select
                v-model="uploadForm.type"
                :items="['character', 'object', 'item', 'background']"
                label="ประเภท *"
                variant="outlined"
                density="comfortable"
                class="mb-3"
              ></v-select>
              <v-file-input
                v-model="uploadFile"
                label="เลือกไฟล์รูปภาพ (PNG, JPG, SVG, WebP) *"
                accept="image/*"
                variant="outlined"
                density="comfortable"
                prepend-icon="mdi-camera"
                class="mb-4"
              ></v-file-input>
              <div class="d-flex justify-end gap-2">
                <v-btn variant="text" @click="uploadDialog = false">ยกเลิก</v-btn>
                <v-btn color="primary" :loading="uploading" @click="handleUploadAsset">อัปโหลด</v-btn>
              </div>
            </v-card>
          </v-dialog>

          <!-- Selected Asset Slots -->
          <!-- 11-Slot Complete Kenney Asset Suite -->
          <v-row dense>
            <v-col v-for="slot in assetSlotsConfig" :key="slot.key" cols="6" sm="4" md="3" lg="2">
              <div class="pa-3 border-card rounded-xl asset-slot-card text-center h-100 d-flex flex-column justify-space-between bg-grey-lighten-5">
                <div>
                  <div class="text-caption font-weight-bold text-slate-800 text-truncate mb-1" :title="slot.label">
                    {{ slot.label }}
                  </div>
                  <div class="d-flex justify-center my-2">
                    <v-avatar size="54" rounded="lg" color="white" class="elevation-1 border">
                      <v-img :src="(form.assets as any)[slot.key]" cover class="pixel-art-img"></v-img>
                    </v-avatar>
                  </div>
                  <div class="text-caption text-grey text-truncate" style="font-size: 10px;">{{ slot.desc }}</div>
                </div>
                <v-btn size="x-small" variant="tonal" color="primary" class="mt-2 font-weight-bold" @click="openAssetPicker(slot.key)">
                  เปลี่ยนรูป
                </v-btn>
              </div>
            </v-col>
          </v-row>

          <!-- Asset Picker Modal -->
          <v-dialog v-model="pickerDialog" max-width="720">
            <v-card class="pa-5 rounded-2xl">
              <div class="d-flex justify-space-between align-center mb-3">
                <h3 class="text-subtitle-1 font-weight-bold d-flex align-center">
                  <v-icon icon="mdi-folder-star" color="primary" class="mr-2"></v-icon>
                  เลือก Asset 2D สำหรับ {{ targetSlotName }}
                </h3>
                <v-btn icon="mdi-close" variant="text" size="small" @click="pickerDialog = false"></v-btn>
              </div>

              <!-- Search and Filter Pack Chips -->
              <v-text-field
                v-model="assetSearch"
                label="ค้นหาชื่อ Asset (เช่น Knight, Slime, Carrot, Ship)..."
                prepend-inner-icon="mdi-magnify"
                variant="outlined"
                density="compact"
                rounded="lg"
                class="mb-3"
                hide-details
                @update:model-value="fetchDbAssets"
              ></v-text-field>

              <div class="d-flex flex-wrap gap-1 mb-4">
                <v-chip
                  v-for="pack in assetPacks"
                  :key="pack.slug"
                  size="small"
                  :color="selectedPack === pack.slug ? 'primary' : undefined"
                  :variant="selectedPack === pack.slug ? 'flat' : 'outlined'"
                  class="cursor-pointer font-weight-medium"
                  @click="filterByPack(pack.slug)"
                >
                  {{ pack.name }}
                </v-chip>
              </div>

              <!-- Asset Grid -->
              <div v-if="loadingAssets" class="text-center py-8">
                <v-progress-circular indeterminate color="primary"></v-progress-circular>
                <div class="text-caption text-grey mt-2">กำลังโหลดคลัง Kenney 2D Assets...</div>
              </div>

              <div v-else-if="filteredAssets.length > 0" class="asset-picker-scrollable">
                <v-row dense>
                  <v-col v-for="asset in filteredAssets" :key="asset.url" cols="3" sm="2" md="2">
                    <v-card
                      class="pa-2 text-center cursor-pointer hover-card border-card rounded-xl h-100 d-flex flex-column justify-space-between align-center"
                      @click="selectAssetForSlot(asset.url)"
                    >
                      <div class="d-flex align-center justify-center mb-1" style="width: 48px; height: 48px; background: rgba(0,0,0,0.04); border-radius: 8px;">
                        <img :src="asset.url" :alt="asset.name" class="pixel-art-img" style="max-width: 40px; max-height: 40px;" />
                      </div>
                      <div class="text-caption text-truncate w-100" style="font-size: 10px;">{{ asset.name }}</div>
                    </v-card>
                  </v-col>
                </v-row>
              </div>

              <div v-else class="text-center py-6 text-grey">
                ไม่พบ Asset ที่ตรงกับคำค้นหา
              </div>

              <div class="d-flex justify-end mt-4">
                <v-btn variant="text" rounded="lg" @click="pickerDialog = false">ปิด</v-btn>
              </div>
            </v-card>
          </v-dialog>

          <div class="d-flex justify-space-between mt-4">
            <v-btn variant="text" rounded="lg" prepend-icon="mdi-arrow-left" @click="currentStepIndex = 2">
              ย้อนกลับ (Ideate)
            </v-btn>
            <v-btn color="primary" rounded="lg" prepend-icon="mdi-arrow-right" @click="currentStepIndex = 4">
              ถัดไป: ทดสอบและเล่นเกม (Test & Play)
            </v-btn>
          </div>
        </v-card>
      </div>

      <!-- ========================================== -->
      <!-- STEP 5: TEST & PLAY (ทดสอบ & สตูดิโอรันเกม) -->
      <!-- ========================================== -->
      <div v-show="currentStepIndex === 4">
        <!-- If Game Created -> Show Live Studio Runner -->
        <div v-if="createdGame" class="mb-6">
          <v-card class="pa-5 border-card rounded-2xl bg-white elevation-2 mb-4">
            <div class="d-flex align-center justify-space-between mb-4 flex-wrap gap-2">
              <div>
                <div class="d-flex align-center gap-2">
                  <v-chip color="success" size="small" variant="flat" class="font-weight-bold">
                    🎉 เกมสร้างสำเร็จแล้ว!
                  </v-chip>
                  <h2 class="text-h6 font-weight-bold text-slate-800 mb-0">{{ createdGame.title }}</h2>
                </div>
                <div class="text-caption text-grey mt-1">
                  รหัสเกม: <strong>{{ createdGame.public_id }}</strong> | บันทึกในคลังเกมและโปรเจกต์ Design Thinking เรียบร้อย
                </div>
              </div>

              <div class="d-flex gap-2 flex-wrap">
                <v-btn
                  color="primary"
                  variant="outlined"
                  size="small"
                  prepend-icon="mdi-content-copy"
                  rounded="lg"
                  @click="copyPlayLink"
                >
                  คัดลอกลิงก์ให้นักเรียน
                </v-btn>
                <v-btn
                  color="purple"
                  variant="tonal"
                  size="small"
                  prepend-icon="mdi-fullscreen"
                  rounded="lg"
                  :href="`/play/${createdGame.public_id}?preview=true`"
                  target="_blank"
                >
                  เปิดเต็มหน้าจอ (Fullscreen)
                </v-btn>
                <v-btn
                  color="primary"
                  size="small"
                  to="/games"
                  rounded="lg"
                >
                  ไปที่เกมของฉัน
                </v-btn>
              </div>
            </div>

            <!-- Embedded Live Game Canvas Iframe -->
            <div class="game-runner-container rounded-xl overflow-hidden elevation-3 bg-black">
              <iframe
                v-if="gameEmbedUrl"
                :src="gameEmbedUrl"
                class="w-100"
                style="height: 540px; border: none;"
                allow="autoplay; fullscreen"
              ></iframe>
            </div>
          </v-card>
        </div>

        <!-- Pre-Launch Review & Launch Button -->
        <v-card class="pa-6 border-card rounded-2xl bg-white elevation-1 mb-6">
          <div class="d-flex align-center justify-space-between mb-4 flex-wrap gap-2">
            <div class="d-flex align-center">
              <v-avatar size="40" color="green-lighten-5" class="mr-3">
                <v-icon icon="mdi-check-decagram-outline" color="green-darken-1" size="22"></v-icon>
              </v-avatar>
              <div>
                <h2 class="text-subtitle-1 font-weight-bold text-slate-900">
                  ขั้นตอนที่ 5: Test & Launch — ตรวจสอบและสร้างเกมเพื่อทดสอบทันที
                </h2>
                <div class="text-caption text-grey">ตรวจสอบความสมบูรณ์ของกระบวนการ Design Thinking ทั้งหมดก่อนสร้างเป็นเกมเล่นจริง</div>
              </div>
            </div>

            <v-chip color="primary" variant="tonal" size="small" class="font-weight-medium">
              พร้อมสร้างโค้ด HTML5 Canvas
            </v-chip>
          </div>

          <!-- Summary Grid of 5 DT Steps -->
          <v-row class="mb-4">
            <v-col cols="12" md="6">
              <div class="pa-3 border-card rounded-xl bg-grey-lighten-5 h-100">
                <div class="text-caption font-weight-bold text-pink-darken-1 mb-1">
                  💡 1. ผู้เรียน (Empathize):
                </div>
                <div class="text-body-2 text-slate-800 font-weight-medium">
                  {{ form.dt.empathize.target_learner || 'นักเรียนทั่วไป' }} ({{ form.dt.empathize.age_group || '10-15 ปี' }})
                </div>
                <div class="text-caption text-grey mt-1">
                  จุดติดขัด: {{ form.dt.empathize.pain_points || 'ขาดความเข้าใจในเนื้อหา' }}
                </div>
              </div>
            </v-col>

            <v-col cols="12" md="6">
              <div class="pa-3 border-card rounded-xl bg-grey-lighten-5 h-100">
                <div class="text-caption font-weight-bold text-amber-darken-3 mb-1">
                  🎯 2. โจทย์และเป้าหมาย (Define):
                </div>
                <div class="text-body-2 text-slate-800 font-weight-medium text-truncate">
                  {{ form.dt.define.problem_statement || 'แก้ปัญหาความเข้าใจในบทเรียน' }}
                </div>
                <div class="text-caption text-grey mt-1">
                  ผลลัพธ์: {{ form.dt.define.expected_outcomes || 'ผ่านเกณฑ์ 80%' }}
                </div>
              </div>
            </v-col>

            <v-col cols="12" md="6">
              <div class="pa-3 border-card rounded-xl bg-grey-lighten-5 h-100">
                <div class="text-caption font-weight-bold text-purple-darken-1 mb-1">
                  💡 3. แนวคิดเกม (Ideate):
                </div>
                <div class="text-body-2 text-slate-800 font-weight-medium">
                  {{ form.title }} (แนว: {{ form.genre }})
                </div>
                <div class="text-caption text-grey mt-1 text-truncate">
                  {{ form.prompt }}
                </div>
              </div>
            </v-col>

            <v-col cols="12" md="6">
              <div class="pa-3 border-card rounded-xl bg-grey-lighten-5 h-100">
                <div class="text-caption font-weight-bold text-teal-darken-1 mb-1">
                  🛠️ 4. กลไก & Assets (Prototype):
                </div>
                <div class="text-body-2 text-slate-800 font-weight-medium">
                  {{ form.features.length }} ระบบเกมเปิดใช้งาน
                </div>
                <div class="d-flex align-center gap-1 mt-1 flex-wrap">
                  <img :src="form.assets.player" style="width: 22px; height: 22px;" class="pixel-art-img border rounded" title="Player" />
                  <img :src="form.assets.enemy" style="width: 22px; height: 22px;" class="pixel-art-img border rounded" title="Enemy" />
                  <img :src="form.assets.boss" style="width: 22px; height: 22px;" class="pixel-art-img border rounded" title="Boss" />
                  <img :src="form.assets.item" style="width: 22px; height: 22px;" class="pixel-art-img border rounded" title="Item" />
                  <img :src="form.assets.coin" style="width: 22px; height: 22px;" class="pixel-art-img border rounded" title="Coin" />
                  <img :src="form.assets.heart" style="width: 22px; height: 22px;" class="pixel-art-img border rounded" title="Heart" />
                  <img :src="form.assets.bullet" style="width: 22px; height: 22px;" class="pixel-art-img border rounded" title="Bullet" />
                  <span class="text-caption text-grey" style="font-size: 11px;">(11 Kenney 2D Sprites ครบชุด)</span>
                </div>
              </div>
            </v-col>
          </v-row>

          <v-textarea
            v-model="form.dt.test.observations"
            label="บันทึกข้อสังเกตและการทดสอบเกม (Test Observations)"
            placeholder="เช่น ผู้เรียนให้ความสนใจกับระบบทำฟาร์มและเสียงเอฟเฟกต์ ระบบควบคุมเข้าใจง่าย..."
            variant="outlined"
            density="comfortable"
            rounded="lg"
            rows="2"
            auto-grow
          ></v-textarea>

          <!-- Primary Submit Action Button -->
          <div class="d-flex justify-space-between align-center mt-4">
            <v-btn variant="text" rounded="lg" prepend-icon="mdi-arrow-left" @click="currentStepIndex = 3">
              ย้อนกลับ (Prototype)
            </v-btn>

            <v-btn
              type="submit"
              color="primary"
              size="large"
              rounded="lg"
              :loading="submitting"
              class="px-8 font-weight-bold text-white elevation-4 ai-gradient-bg"
              prepend-icon="mdi-rocket-launch"
            >
              🚀 บันทึกโปรเจกต์ & สร้างเกม HTML5 รันเล่นทันที
            </v-btn>
          </div>
        </v-card>
      </div>
    </v-form>
  </div>
</template>

<script setup lang="ts">
import { ref, onMounted, computed } from 'vue'
import { useRouter } from 'vue-router'
import apiClient from '@/api/client'
import { useAlertStore } from '@/stores/alert'

const router = useRouter()
const alertStore = useAlertStore()

const currentStepIndex = ref(0)
const submitting = ref(false)
const autoFillingAll = ref(false)
const generatingStep = ref<string | null>(null)
const quickTopic = ref('')

// User Gemini API Key Management
const userApiKey = ref(localStorage.getItem('user_gemini_api_key') || '')
const showApiKey = ref(false)
const testingApiKey = ref(false)

function onApiKeyChange(val: string) {
  userApiKey.value = val ? val.trim() : ''
  if (userApiKey.value) {
    localStorage.setItem('user_gemini_api_key', userApiKey.value)
  } else {
    localStorage.removeItem('user_gemini_api_key')
  }
}

async function testApiKey() {
  if (!userApiKey.value || userApiKey.value.startsWith('mock-')) {
    alertStore.toast('กรุณากรอก Google Gemini API Key จริงก่อนทดสอบ', 'warning')
    return
  }
  testingApiKey.value = true
  try {
    const res = await apiClient.post('/teacher/ai-credentials/test', {
      provider: 'gemini',
      api_key: userApiKey.value,
    })
    alertStore.toast(res.data.message || 'เชื่อมต่อ Google Gemini สำเร็จ!', 'success')
  } catch (err: any) {
    alertStore.toast(err.response?.data?.message || 'เชื่อมต่อไม่สำเร็จ กรุณาตรวจสอบ API Key', 'error')
  } finally {
    testingApiKey.value = false
  }
}

async function loadExistingTeacherApiKey() {
  try {
    const res = await apiClient.get('/teacher/ai-credentials')
    const geminiCred = (res.data || []).find((c: any) => c.provider === 'gemini' && c.is_active)
    if (geminiCred && geminiCred.encrypted_api_key && !geminiCred.encrypted_api_key.startsWith('mock-') && geminiCred.encrypted_api_key !== 'existing') {
      userApiKey.value = geminiCred.encrypted_api_key
      localStorage.setItem('user_gemini_api_key', userApiKey.value)
    }
  } catch (e) {
    // ignore
  }
}

const hasActiveAiKey = computed(() => {
  return !!userApiKey.value && !userApiKey.value.startsWith('mock-') && userApiKey.value !== 'existing'
})

const createdGame = ref<any>(null)
const gameEmbedUrl = computed(() => {
  if (!createdGame.value?.public_id) return ''
  return `/play/${createdGame.value.public_id}?preview=true`
})

const uploadDialog = ref(false)
const uploading = ref(false)
const uploadFile = ref<File | null>(null)
const uploadForm = ref({ name: '', type: 'character' })

const pickerDialog = ref(false)
const currentSlotTarget = ref<string>('player')
const targetSlotName = ref('ตัวละครหลัก (Player)')

const assetSlotsConfig = [
  { key: 'player', label: '👤 ตัวละครหลัก (Player)', desc: 'ตัวละครที่ผู้เล่นควบคุม' },
  { key: 'enemy', label: '👾 ศัตรูทั่วไป (Enemy)', desc: 'มอนสเตอร์ลูกสมุน/สิ่งขัดขวาง' },
  { key: 'boss', label: '👹 บอสใหญ่ (Boss)', desc: 'บอสประจำด่าน/มีแถบ HP' },
  { key: 'npc', label: '🧙‍♂️ ตัวละครเควสต์ (NPC)', desc: 'ผู้มอบภารกิจ/จุดเซฟ' },
  { key: 'item', label: '🍎 ไอเทมเป้าหมาย (Item)', desc: 'ภารกิจเก็บไอเทมหลัก' },
  { key: 'coin', label: '🪙 เหรียญรางวัล (Coin)', desc: 'เหรียญทองคะแนนสะสม' },
  { key: 'heart', label: '❤️ ยาฟื้นเลือด (Heart)', desc: 'ไอเทมฟื้นพลังชีวิต HP' },
  { key: 'bullet', label: '⚡ กระสุน/สกิล (Bullet)', desc: 'เอฟเฟกต์อาวุธ/เวทมนตร์' },
  { key: 'obstacle', label: '⚠️ กับดัก/หนาม (Trap)', desc: 'สิ่งกีดขวางลดพลัง' },
  { key: 'ground', label: '🟩 บล็อกพื้น (Ground)', desc: 'พื้นฉาก/แพลตฟอร์ม' },
  { key: 'wall', label: '🧱 บล็อกกำแพง (Wall)', desc: 'สิ่งกีดขวาง/ขอบฉาก' },
]

const dtSteps = [
  { key: 'empathize', name: '1. Empathize', subtitle: 'เข้าใจผู้เรียน & ปัญหา' },
  { key: 'define', name: '2. Define', subtitle: 'กำหนดโจทย์ & วัตถุประสงค์' },
  { key: 'ideate', name: '3. Ideate', subtitle: 'ออกแบบเกม & กติกา' },
  { key: 'prototype', name: '4. Prototype', subtitle: 'กลไก & Kenney Assets' },
  { key: 'test', name: '5. Test & Play', subtitle: 'ทดสอบ & รันเล่นทันที' },
]

const genreOptions = [
  { title: '🌾 ปลูกผัก / จำลองฟาร์ม (Farming Simulator)', value: 'farming' },
  { title: '⚔️ แอ็กชัน / ผจญภัยประลองยุทธ์ (Action Battle)', value: 'battle' },
  { title: '🔤 ล่าคำศัพท์ / ปริศนาภาษา (Word Hunter)', value: 'word_puzzle' },
  { title: '🏃 อาร์เคดวิ่งหลบสิ่งกีดขวาง (Endless / Runner)', value: 'runner' },
  { title: '🧩 ปริศนาตรรกะ & จับคู่ (Logic / Match)', value: 'puzzle' },
  { title: '🚀 ยานอวกาศ & ชู้ตติ้ง (Space Arcade Shooter)', value: 'shooter' },
  { title: '🏰 วางแผนป้องกันป้อมปราการ (Tower Defense)', value: 'tower_defense' },
  { title: '🍳 ทำอาหาร & เสิร์ฟด่วน (Cooking Frenzy)', value: 'cooking' },
  { title: '✨ แนวเกมอิสระตามจินตนาการ (Custom Genre)', value: 'custom' },
]

const form = ref({
  title: 'เกมการเรียนรู้และผจญภัยเชิงสร้างสรรค์',
  prompt: 'เกมผจญภัยเชิงโต้ตอบ: ผู้เล่นเคลื่อนที่สำรวจฉาก ตอบคำถามหรือเก็บไอเทมเพื่อสะสมแต้ม มีระบบหลอดเลือด กระดานคะแนน และเสียงเอฟเฟกต์',
  genre: 'battle',
  features: [
    'health_bar', 'boss_bar', 'stamina_mana', 'scoreboard', 'level_exp',
    'timer', 'combo', 'map', 'day_night', 'obstacles', 'checkpoints',
    'skills', 'inventory', 'shop', 'dialogue', 'controls', 'sound_fx', 'particles'
  ],
  assets: {
    player: '/assets/kenney/pixel-platformer/tile_0000.png',
    enemy: '/assets/kenney/pixel-platformer/tile_0021.png',
    boss: '/assets/kenney/pixel-platformer/tile_0026.png',
    item: '/assets/kenney/pixel-platformer-food/tile_0000.png',
    coin: '/assets/kenney/tiny-dungeon/tile_0117.png',
    heart: '/assets/kenney/pixel-platformer-food/tile_0002.png',
    bullet: '/assets/kenney/simple-space/effect_yellow.png',
    obstacle: '/assets/kenney/pixel-platformer/tile_0015.png',
    ground: '/assets/kenney/pixel-platformer/tile_0003.png',
    wall: '/assets/kenney/pixel-platformer/tile_0005.png',
    npc: '/assets/kenney/pixel-platformer/tile_0001.png',
  },
  dt: {
    empathize: {
      target_learner: '',
      age_group: '',
      learner_characteristics: '',
      pain_points: '',
      learning_environment: '',
    },
    define: {
      problem_statement: '',
      expected_outcomes: '',
      knowledge_goals: '',
      skill_goals: '',
    },
    ideate: {
      game_concept: '',
      story: '',
      missions: '',
      challenges: '',
    },
    test: {
      observations: '',
    }
  }
})

// Quick Idea Inspiration Presets (with 11 complete assets each)
const ideaPresets = [
  {
    title: 'เกมปลูกผักจำลองฟาร์ม (Farming)',
    icon: '🌾',
    genre: 'farming',
    features: ['health_bar', 'stamina_mana', 'scoreboard', 'level_exp', 'timer', 'map', 'day_night', 'inventory', 'shop', 'dialogue', 'controls', 'sound_fx', 'particles'],
    titleText: 'ฟาร์มเกษตรอินทรีย์ ปลูกผักพิทักษ์โลก',
    promptText: 'เกมปลูกผักจำลองฟาร์ม: ผู้เล่นเดินสำรวจแปลงดิน ปลูกเมล็ด รดน้ำ เก็บเกี่ยวผลผลิตสะสมคะแนน มีศัตรูพืชเดินมาขโมยผัก มีระบบกระเป๋าเก็บเมล็ดพืช มีร้านค้าซื้อของอัปเกรด มีหลอดเลือด และมินิแมพ',
    assets: {
      player: '/assets/kenney/tiny-town/tile_0000.png',
      enemy: '/assets/kenney/pixel-platformer/tile_0024.png',
      boss: '/assets/kenney/pixel-platformer/tile_0026.png',
      item: '/assets/kenney/pixel-platformer-food/tile_0036.png',
      coin: '/assets/kenney/tiny-dungeon/tile_0117.png',
      heart: '/assets/kenney/pixel-platformer-food/tile_0000.png',
      bullet: '/assets/kenney/pixel-platformer-food/tile_0002.png',
      obstacle: '/assets/kenney/tiny-town/tile_0044.png',
      ground: '/assets/kenney/tiny-town/tile_0001.png',
      wall: '/assets/kenney/tiny-town/tile_0015.png',
      npc: '/assets/kenney/tiny-town/tile_0027.png',
    }
  },
  {
    title: 'เกมทายคำศัพท์ภาษาอังกฤษ (Word Hunter)',
    icon: '🔤',
    genre: 'word_puzzle',
    features: ['health_bar', 'scoreboard', 'level_exp', 'timer', 'combo', 'map', 'checkpoints', 'dialogue', 'controls', 'sound_fx', 'particles'],
    titleText: 'ศึกประลองคำศัพท์เขาวงกต (Word Hunter)',
    promptText: 'เกมทายคำศัพท์ภาษาอังกฤษ: ผู้เล่นเดินค้นหาตัวอักษร D-E-S-I-G-N ในเขาวงกต มีเวลาจำกัด 2 นาที มีคอมโบสตรีคเมื่อเก็บต่อเนื่อง หลบกับดักหนาม และส่งสัญญาณแจ้งเควสต์ผ่าน NPC',
    assets: {
      player: '/assets/kenney/pixel-platformer/tile_0000.png',
      enemy: '/assets/kenney/pixel-platformer/tile_0021.png',
      boss: '/assets/kenney/pixel-platformer/tile_0026.png',
      item: '/assets/kenney/pixel-platformer-food/tile_0000.png',
      coin: '/assets/kenney/tiny-dungeon/tile_0117.png',
      heart: '/assets/kenney/pixel-platformer-food/tile_0002.png',
      bullet: '/assets/kenney/simple-space/effect_yellow.png',
      obstacle: '/assets/kenney/pixel-platformer/tile_0015.png',
      ground: '/assets/kenney/pixel-platformer/tile_0003.png',
      wall: '/assets/kenney/pixel-platformer/tile_0005.png',
      npc: '/assets/kenney/pixel-platformer/tile_0001.png',
    }
  },
  {
    title: 'เกมต่อสู้คณิตศาสตร์ (Math Gladiator)',
    icon: '⚔️',
    genre: 'battle',
    features: ['health_bar', 'boss_bar', 'stamina_mana', 'scoreboard', 'level_exp', 'skills', 'shop', 'controls', 'sound_fx', 'particles', 'victory_modal'],
    titleText: 'ลานประลองจอมเวทคณิตศาสตร์ (Math Gladiator)',
    promptText: 'เกมต่อสู้ Action Arena: ปะทะบอสใหญ่และมอนสเตอร์ ผู้เล่นปล่อยสกิลดาบสายฟ้าและคลื่นพลัง Shockwave สะสมเหรียญซื้อยาฟื้นพลังและดาบอัปเกรดในร้านค้า มีหลอดเลือดบอสขนาดใหญ่',
    assets: {
      player: '/assets/kenney/tiny-dungeon/tile_0084.png',
      enemy: '/assets/kenney/tiny-dungeon/tile_0109.png',
      boss: '/assets/kenney/tiny-dungeon/tile_0111.png',
      item: '/assets/kenney/tiny-dungeon/tile_0118.png',
      coin: '/assets/kenney/tiny-dungeon/tile_0117.png',
      heart: '/assets/kenney/tiny-dungeon/tile_0115.png',
      bullet: '/assets/kenney/tiny-dungeon/tile_0102.png',
      obstacle: '/assets/kenney/tiny-dungeon/tile_0068.png',
      ground: '/assets/kenney/tiny-dungeon/tile_0000.png',
      wall: '/assets/kenney/tiny-dungeon/tile_0012.png',
      npc: '/assets/kenney/tiny-dungeon/tile_0085.png',
    }
  },
  {
    title: 'เกมวิ่งเก็บขยะรีไซเคิล (Eco Runner)',
    icon: '🏃',
    genre: 'runner',
    features: ['health_bar', 'scoreboard', 'level_exp', 'timer', 'combo', 'obstacles', 'inventory', 'controls', 'sound_fx', 'particles'],
    titleText: 'สายลับพิทักษ์สิ่งแวดล้อม (Eco Runner)',
    promptText: 'เกมวิ่งหลบสิ่งกีดขวางและเก็บขยะรีไซเคิล: ผู้เล่นเคลื่อนที่เก็บขวดพลาสติก เศษกระดาษ และกระป๋อง หลบสิ่งกีดขวางกับดักหนาม เก็บขยะติดกันเพิ่มคอมโบคูณสอง',
    assets: {
      player: '/assets/kenney/pixel-platformer/tile_0000.png',
      enemy: '/assets/kenney/pixel-platformer/tile_0024.png',
      boss: '/assets/kenney/pixel-platformer/tile_0026.png',
      item: '/assets/kenney/pixel-platformer-food/tile_0002.png',
      coin: '/assets/kenney/tiny-dungeon/tile_0117.png',
      heart: '/assets/kenney/pixel-platformer-food/tile_0000.png',
      bullet: '/assets/kenney/pixel-platformer-food/tile_0005.png',
      obstacle: '/assets/kenney/pixel-platformer/tile_0015.png',
      ground: '/assets/kenney/pixel-platformer/tile_0003.png',
      wall: '/assets/kenney/pixel-platformer/tile_0005.png',
      npc: '/assets/kenney/pixel-platformer/tile_0001.png',
    }
  },
  {
    title: 'ยานอวกาศพิทักษ์จักรวาล (Space Defender)',
    icon: '🚀',
    genre: 'shooter',
    features: ['health_bar', 'boss_bar', 'scoreboard', 'level_exp', 'timer', 'combo', 'obstacles', 'skills', 'controls', 'sound_fx', 'particles'],
    titleText: 'ผู้พิทักษ์กาแล็กซี (Space Defender)',
    promptText: 'เกมยานอวกาศชู้ตติ้ง: ขับยานยิงเลเซอร์ทำลายอุกกาบาตและยานเอเลี่ยน เก็บชิ้นส่วนพลังงานเพิ่มค่าคะแนน ปะทะบอสยานแม่ขนาดใหญ่',
    assets: {
      player: '/assets/kenney/simple-space/ship_A.png',
      enemy: '/assets/kenney/simple-space/enemy_A.png',
      boss: '/assets/kenney/simple-space/enemy_E.png',
      item: '/assets/kenney/simple-space/star_large.png',
      coin: '/assets/kenney/simple-space/star_medium.png',
      heart: '/assets/kenney/simple-space/satellite_A.png',
      bullet: '/assets/kenney/simple-space/effect_yellow.png',
      obstacle: '/assets/kenney/simple-space/meteor_large.png',
      ground: '/assets/kenney/simple-space/star_tiny.png',
      wall: '/assets/kenney/simple-space/meteor_detailedLarge.png',
      npc: '/assets/kenney/simple-space/station_A.png',
    }
  }
]

function applyIdeaPreset(p: typeof ideaPresets[0]) {
  form.value.title = p.titleText
  form.value.genre = p.genre
  form.value.prompt = p.promptText
  form.value.features = [...p.features]
  if (p.assets) {
    form.value.assets = { ...p.assets }
  }
  quickTopic.value = p.titleText
  alertStore.toast(`โหลดแนวคิด: ${p.title} สำเร็จ!`, 'success')
}

// 18 Modular Systems Categories
const featureCategories = [
  {
    name: 'สเตตัสและการต่อสู้ (Status & Combat)',
    icon: '⚔️',
    features: [
      { key: 'health_bar', name: 'หลอดเลือด (HP Bar)', icon: '❤️', desc: 'แสดงพลังชีวิต ลดลงเมื่อโดนชนหรือตอบผิด' },
      { key: 'boss_bar', name: 'หลอดเลือดบอส (Boss HP)', icon: '👹', desc: 'แถบพลังบอสขนาดใหญ่ด้านบนจอ' },
      { key: 'stamina_mana', name: 'มานา / พลังงาน (Stamina)', icon: '⚡', desc: 'ค่าพลังสำหรับกดใช้สกิลพิเศษ' },
      { key: 'skills', name: 'ระบบสกิลกดใช้งาน (Skills)', icon: '✨', desc: 'ปุ่มสกิล เช่น คลื่นพลัง, แดชพุ่งตัว, โล่บาเรีย' },
    ]
  },
  {
    name: 'คะแนน ความก้าวหน้า & เวลา (Progression & Time)',
    icon: '🏆',
    features: [
      { key: 'scoreboard', name: 'กระดานคะแนน (Scoreboard)', icon: '📊', desc: 'แสดงแต้มสะสมและสถิติคะแนนสูงสุด' },
      { key: 'level_exp', name: 'เลเวล & หลอด EXP', icon: '⭐', desc: 'สะสมแต้มเพื่ออัปเกรดเลเวลผู้เล่น' },
      { key: 'timer', name: 'ระบบจับเวลา (Timer)', icon: '⏱️', desc: 'นับเวลาถอยหลังสร้างความตื่นเต้น' },
      { key: 'combo', name: 'คอมโบสตรีค (Combo Multiplier)', icon: '🔥', desc: 'ทำคะแนนต่อเนื่องคูณสอง/คูณสาม' },
    ]
  },
  {
    name: 'แผนที่ สภาพแวดล้อม & อุปสรรค (World & Map)',
    icon: '🗺️',
    features: [
      { key: 'map', name: 'มินิแมพ (Mini-Map Radar)', icon: '🧭', desc: 'เรดาร์แสดงตำแหน่งผู้เล่นและไอเทมในฉาก' },
      { key: 'day_night', name: 'กลางวัน / กลางคืน (Day/Night)', icon: '🌓', desc: 'แสงสว่างในฉากเปลี่ยนตามเวลา' },
      { key: 'obstacles', name: 'สิ่งกีดขวาง & กับดัก', icon: '🚧', desc: 'หนาม กับดัก หรือบล็อกสิ่งกีดขวาง' },
      { key: 'checkpoints', name: 'จุดเช็กพอยต์ (Checkpoints)', icon: '🚩', desc: 'จุดบันทึกตำแหน่งเกิดใหม่เมื่อตาย' },
    ]
  },
  {
    name: 'ไอเทม ร้านค้า & การสื่อสาร (Items & Economy)',
    icon: '🎒',
    features: [
      { key: 'inventory', name: 'กระเป๋าเก็บของ (Inventory)', icon: '🎒', desc: 'ช่องเก็บไอเทมที่เก็บได้ระหว่างทาง' },
      { key: 'shop', name: 'ร้านค้าซื้อของ (Shop / NPC)', icon: '🏪', desc: 'ใช้เหรียญซื้อไอเทมฟื้นเลือดหรืออัปสปีด' },
      { key: 'dialogue', name: 'กล่องบทสนทนา (Dialogue Box)', icon: '💬', desc: 'กล่องข้อความบอกภารกิจจาก NPC' },
      { key: 'victory_modal', name: 'หน้าต่างสรุปชัยชนะ (Victory)', icon: '🎉', desc: 'หน้าต่างสรุปผลคะแนนและมอบเหรียญรางวัล' },
    ]
  },
  {
    name: 'การควบคุม & ฟีลลิ่งเกม (Game Feel & Controls)',
    icon: '🎮',
    features: [
      { key: 'controls', name: 'ปุ่มควบคุมบนจอ (Touch D-Pad)', icon: '🕹️', desc: 'ปุ่มสัมผัสลูกศรเดินและปุ่มโจมตีบนมือถือ' },
      { key: 'sound_fx', name: 'ระบบเสียงสังเคราะห์ (Web Audio FX)', icon: '🔊', desc: 'เสียงเก็บของ โจมตี โดนดาเมจ และชัยชนะ' },
      { key: 'particles', name: 'เอฟเฟกต์อนุภาค (Particle FX)', icon: '✨', desc: 'ประกายดาว ควัน หรือระเบิดเมื่อโดนเป้าหมาย' },
    ]
  }
]

function getSelectedCountInCategory(cat: any) {
  return cat.features.filter((f: any) => form.value.features.includes(f.key)).length
}

function toggleFeature(key: string) {
  const idx = form.value.features.indexOf(key)
  if (idx > -1) {
    form.value.features.splice(idx, 1)
  } else {
    form.value.features.push(key)
  }
}

function toggleCategory(cat: any) {
  const allKeys = cat.features.map((f: any) => f.key)
  const allSelected = allKeys.every((k: string) => form.value.features.includes(k))
  if (allSelected) {
    form.value.features = form.value.features.filter((k: string) => !allKeys.includes(k))
  } else {
    for (const k of allKeys) {
      if (!form.value.features.includes(k)) form.value.features.push(k)
    }
  }
}

function selectAllFeatures() {
  const allKeys: string[] = []
  featureCategories.forEach(c => c.features.forEach(f => allKeys.push(f.key)))
  form.value.features = [...new Set(allKeys)]
}

function applyActionPack() {
  form.value.features = [
    'health_bar', 'boss_bar', 'stamina_mana', 'skills',
    'scoreboard', 'level_exp', 'combo', 'map', 'obstacles',
    'controls', 'sound_fx', 'particles', 'victory_modal'
  ]
}

function applyFarmPack() {
  form.value.features = [
    'health_bar', 'scoreboard', 'level_exp', 'timer', 'map',
    'day_night', 'inventory', 'shop', 'dialogue', 'controls',
    'sound_fx', 'particles'
  ]
}

function applyQuizPack() {
  form.value.features = [
    'health_bar', 'scoreboard', 'level_exp', 'timer', 'combo',
    'dialogue', 'controls', 'sound_fx', 'particles', 'victory_modal'
  ]
}

function resetFeatures() {
  form.value.features = ['health_bar', 'scoreboard', 'timer', 'map', 'controls', 'sound_fx', 'particles']
}

// ⚡ 1-Click Fast Track AI Auto-Fill All 5 Steps
async function handleFastTrackAutoFill() {
  const targetTopic = quickTopic.value.trim() || form.value.title.trim()
  if (!targetTopic) {
    alertStore.toast('กรุณาระบุหัวข้อบทเรียนหรือไอเดียเกมที่ต้องการสอน', 'warning')
    return
  }

  autoFillingAll.value = true
  try {
    const res = await apiClient.post('/games/ai-autofill-all', {
      title: targetTopic,
      genre: form.value.genre,
      seed: Date.now(),
      current_title: form.value.title,
      api_key: userApiKey.value || undefined,
    })

    const data = res.data.data
    if (data) {
      if (data.empathize) {
        form.value.dt.empathize = { ...form.value.dt.empathize, ...data.empathize }
      }
      if (data.define) {
        form.value.dt.define = { ...form.value.dt.define, ...data.define }
      }
      if (data.ideate) {
        form.value.dt.ideate = { ...form.value.dt.ideate, ...data.ideate }
        if (data.ideate.game_concept) form.value.title = data.ideate.game_concept
        if (data.ideate.prompt) form.value.prompt = data.ideate.prompt
        if (data.ideate.genre) form.value.genre = data.ideate.genre
      }
      if (data.prototype) {
        if (data.prototype.recommended_features) {
          form.value.features = data.prototype.recommended_features
        }
        if (data.prototype.assets) {
          form.value.assets = { ...data.prototype.assets }
        }
      }
      if (data.test) {
        form.value.dt.test = { ...form.value.dt.test, ...data.test }
      }

      alertStore.toast(`⚡ AI เติมข้อมูล 5 ขั้นตอนสำหรับ "${targetTopic}" สำเร็จแล้ว!`, 'success')
    }
  } catch (err: any) {
    alertStore.toast(err.response?.data?.message || 'เกิดข้อผิดพลาดในการสร้างข้อมูลอัตโนมัติ', 'error')
  } finally {
    autoFillingAll.value = false
  }
}

// Generate single step with AI
async function generateStepWithAi(step: string) {
  const targetTopic = form.value.title.trim() || quickTopic.value.trim() || 'เกมการเรียนรู้'
  generatingStep.value = step
  try {
    const currentVal = step === 'ideate' 
      ? form.value.dt.ideate.game_concept 
      : (step === 'define' ? form.value.dt.define.problem_statement : form.value.dt.empathize.target_learner)

    const res = await apiClient.post('/games/ai-generate-step', {
      step,
      title: targetTopic,
      genre: form.value.genre,
      prompt: form.value.prompt,
      seed: Date.now(),
      current_concept: form.value.dt.ideate.game_concept,
      current_text: currentVal,
      api_key: userApiKey.value || undefined,
    })

    const data = res.data.data
    if (data) {
      if (step === 'empathize') {
        form.value.dt.empathize = { ...form.value.dt.empathize, ...data }
      } else if (step === 'define') {
        form.value.dt.define = { ...form.value.dt.define, ...data }
      } else if (step === 'ideate') {
        form.value.dt.ideate = { ...form.value.dt.ideate, ...data }
        if (data.prompt) form.value.prompt = data.prompt
        if (data.game_concept) form.value.title = data.game_concept
      } else if (step === 'prototype') {
        if (data.recommended_features) form.value.features = data.recommended_features
        if (data.assets) form.value.assets = { ...data.assets }
      } else if (step === 'test') {
        form.value.dt.test = { ...form.value.dt.test, ...data }
      }

      alertStore.toast(`✨ AI สร้างไอเดียใหม่ขั้นตอน ${step} (${targetTopic}) เรียบร้อยแล้ว!`, 'success')
    }
  } catch (err: any) {
    alertStore.toast('ไม่สามารถดึงข้อมูล AI สำหรับขั้นตอนนี้ได้', 'error')
  } finally {
    generatingStep.value = null
  }
}

// Kenney Assets DB Picker
const dbAssets = ref<any[]>([])
const loadingAssets = ref(false)
const selectedPack = ref('all')
const assetSearch = ref('')

const assetPacks = [
  { name: 'ทั้งหมด (All Packs)', slug: 'all' },
  { name: '🏰 Tiny Dungeon', slug: 'tiny-dungeon' },
  { name: '🍎 Food & Crops', slug: 'pixel-platformer-food' },
  { name: '🏃 Platformer', slug: 'pixel-platformer' },
  { name: '🏡 Tiny Town', slug: 'tiny-town' },
  { name: '🚀 Simple Space', slug: 'simple-space' },
  { name: '🎨 UI & Badges', slug: 'ui-pack' },
]

async function fetchDbAssets() {
  loadingAssets.value = true
  try {
    const params: any = { per_page: 60 }
    if (selectedPack.value !== 'all') {
      params.pack = selectedPack.value
    }
    if (assetSearch.value.trim()) {
      params.search = assetSearch.value.trim()
    }
    const res = await apiClient.get('/assets', { params })
    dbAssets.value = (res.data.data || res.data || []).map((a: any) => ({
      name: a.name || a.filename || 'Asset',
      url: a.file_path ? (a.file_path.startsWith('/') ? a.file_path : `/${a.file_path}`) : (a.url || ''),
      pack: a.pack || 'kenney',
    }))
  } catch (err) {
    console.error('Failed to fetch DB assets', err)
  } finally {
    loadingAssets.value = false
  }
}

const filteredAssets = computed(() => dbAssets.value)

function filterByPack(slug: string) {
  selectedPack.value = slug
  fetchDbAssets()
}

function openAssetPicker(slot: string) {
  currentSlotTarget.value = slot
  const found = assetSlotsConfig.find(s => s.key === slot)
  targetSlotName.value = found ? found.label : slot
  pickerDialog.value = true
  if (dbAssets.value.length === 0) {
    fetchDbAssets()
  }
}

function selectAssetForSlot(url: string) {
  (form.value.assets as any)[currentSlotTarget.value] = url
  pickerDialog.value = false
}

// Custom Upload
function triggerUploadDialog() {
  uploadForm.value = { name: '', type: 'character' }
  uploadFile.value = null
  uploadDialog.value = true
}

async function handleUploadAsset() {
  if (!uploadFile.value || !uploadForm.value.name) {
    alertStore.toast('กรุณากรอกชื่อและเลือกไฟล์รูปภาพ', 'warning')
    return
  }

  uploading.value = true
  try {
    const formData = new FormData()
    formData.append('file', uploadFile.value)
    formData.append('name', uploadForm.value.name)
    formData.append('type', uploadForm.value.type)

    const res = await apiClient.post('/assets/upload', formData, {
      headers: { 'Content-Type': 'multipart/form-data' }
    })

    const uploaded = res.data.asset
    const url = uploaded.file_path ? (uploaded.file_path.startsWith('/') ? uploaded.file_path : `/${uploaded.file_path}`) : uploaded.url
    alertStore.toast('อัปโหลด Asset สำเร็จ!', 'success')
    uploadDialog.value = false;

    (form.value.assets as any)[currentSlotTarget.value] = url
    fetchDbAssets()
  } catch (err: any) {
    alertStore.toast(err.response?.data?.message || 'อัปโหลดล้มเหลว', 'error')
  } finally {
    uploading.value = false
  }
}

// Final Game Creation
async function handleCreate() {
  if (!form.value.title.trim()) {
    alertStore.toast('กรุณาระบุชื่อเกม', 'warning')
    currentStepIndex.value = 2
    return
  }
  if (!form.value.prompt.trim()) {
    alertStore.toast('กรุณาระบุคำบรรยายและกติกาเกม', 'warning')
    currentStepIndex.value = 2
    return
  }

  submitting.value = true
  try {
    const payload = {
      title: form.value.title,
      genre: form.value.genre,
      format: 'html5',
      prompt: form.value.prompt,
      features: form.value.features,
      assets: form.value.assets,
      api_key: userApiKey.value || undefined,
      design_thinking: {
        empathize: form.value.dt.empathize,
        define: form.value.dt.define,
        ideate: form.value.dt.ideate,
        test: form.value.dt.test,
      }
    }

    const res = await apiClient.post('/games', payload)
    createdGame.value = res.data.game
    currentStepIndex.value = 4 // Move to Test & Play tab

    if (res.data.is_ai_generated === false) {
      const reason = res.data.generation_note || 'ยังไม่มี Gemini API Key'
      alertStore.toast(
        `⚠️ สร้างเกมจาก Offline Engine Template สำเร็จ (${reason}) กรุณากรอก Gemini API Key ด้านบนเพื่อเจนโค้ดสดใหม่`,
        'warning'
      )
    } else {
      alertStore.toast('🎉 สุดยอด! Google Gemini เขียนโค้ดเกม HTML5 ใหม่ตาม Prompt ให้เรียบร้อย!', 'success')
    }
  } catch (err: any) {
    alertStore.toast(err.response?.data?.message || 'ไม่สามารถสร้างเกมได้ กรุณาลองใหม่อีกครั้ง', 'error')
  } finally {
    submitting.value = false
  }
}

function copyPlayLink() {
  if (!createdGame.value?.public_id) return
  const link = `${window.location.origin}/play/${createdGame.value.public_id}`
  navigator.clipboard.writeText(link)
  alertStore.toast('คัดลอกลิงก์เล่นเกมเรียบร้อยแล้ว!', 'success')
}

onMounted(() => {
  fetchDbAssets()
  loadExistingTeacherApiKey()
})
</script>

<style scoped>
.stepper-tabs-row {
  border-bottom: 1px solid #f1f5f9;
}
.stepper-tab-item {
  border: 1px solid transparent;
  transition: all 0.2s ease;
}
.stepper-tab-item:hover {
  background-color: #f8fafc;
}
.tab-active {
  background-color: #f3e8ff !important;
  border-color: #d8b4fe !important;
}
.tab-active .step-title-text {
  color: #7e22ce !important;
}
.tab-completed {
  border-color: #e2e8f0;
}

.ai-fast-card {
  border: 1px solid #e9d5ff !important;
  background: linear-gradient(135deg, #ffffff 0%, #faf5ff 100%);
}

.preset-chip {
  transition: all 0.15s ease-in-out;
}
.preset-chip:hover {
  transform: translateY(-1px);
  background-color: #f3e8ff;
}

.border-card {
  border: 1px solid #e2e8f0;
}

.feature-card-active {
  border-color: #7e22ce !important;
  background-color: #faf5ff !important;
}

.asset-slot-card {
  background: #f8fafc;
  border: 1px solid #e2e8f0;
  transition: all 0.2s ease;
}
.asset-slot-card:hover {
  border-color: #cbd5e1;
  background: #ffffff;
}

.pixel-art-img {
  image-rendering: pixelated;
  image-rendering: crisp-edges;
}

.asset-picker-scrollable {
  max-height: 380px;
  overflow-y: auto;
  padding-right: 4px;
}

.ai-gradient-bg {
  background: linear-gradient(135deg, #7e22ce 0%, #a855f7 50%, #ec4899 100%) !important;
}

.hover-card:hover {
  transform: translateY(-2px);
  border-color: #a855f7 !important;
  box-shadow: 0 4px 12px rgba(168, 85, 247, 0.15);
}

.game-runner-container {
  box-shadow: 0 8px 30px rgba(0, 0, 0, 0.25);
  border: 2px solid #334155;
}
</style>
