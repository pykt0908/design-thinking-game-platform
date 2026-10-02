<template>
  <div v-if="project" class="dt-studio">
    <!-- Top Bar: Project title & Auto-save status indicator -->
    <v-card class="pa-4 mb-4 border-card rounded-xl d-flex align-center justify-space-between flex-wrap gap-2">
      <div class="d-flex align-center">
        <v-btn icon="mdi-arrow-left" variant="text" to="/projects" class="mr-2" density="comfortable"></v-btn>
        <div>
          <h1 class="text-h6 font-weight-bold text-slate-800 d-flex align-center">
            {{ project.title }}
            <v-chip size="x-small" color="primary" variant="outlined" class="ml-2">
              ขั้นตอนที่ {{ currentStepIndex + 1 }}/5
            </v-chip>
          </h1>
          <div class="text-caption text-grey">
            วิชา: {{ project.subject || 'ทั่วไป' }} &bull; ระดับชั้น: {{ project.grade_level || 'ทุกระดับ' }}
          </div>
        </div>
      </div>

      <div class="d-flex align-center gap-3">
        <!-- Auto-save status indicator per spec Section 39 -->
        <div class="d-flex align-center text-caption font-weight-medium">
          <template v-if="projectStore.saveStatus === 'saving'">
            <v-progress-circular indeterminate size="14" width="2" color="primary" class="mr-1"></v-progress-circular>
            <span class="text-primary">กำลังบันทึก...</span>
          </template>
          <template v-else-if="projectStore.saveStatus === 'saved'">
            <v-icon icon="mdi-check-circle" color="success" size="16" class="mr-1"></v-icon>
            <span class="text-success">บันทึกเรียบร้อย</span>
          </template>
          <template v-else-if="projectStore.saveStatus === 'error'">
            <v-icon icon="mdi-alert-circle" color="error" size="16" class="mr-1"></v-icon>
            <span class="text-error">บันทึกล้มเหลว</span>
          </template>
          <template v-else>
            <v-icon icon="mdi-cloud-check-outline" color="grey" size="16" class="mr-1"></v-icon>
            <span class="text-grey">บันทึกอัตโนมัติ</span>
          </template>
        </div>

        <v-btn
          class="ai-gradient-bg text-white font-weight-medium"
          prepend-icon="mdi-creation"
          rounded="lg"
          @click="openAiAssistant"
          size="small"
        >
          AI ผู้ช่วย
        </v-btn>

        <v-btn
          icon="mdi-trash-can-outline"
          variant="text"
          color="grey-darken-1"
          size="small"
          @click="handleDeleteProject"
          title="ลบโปรเจกต์นี้"
        ></v-btn>

        <v-btn
          v-if="currentStepIndex === 4"
          color="success"
          rounded="lg"
          size="small"
          prepend-icon="mdi-rocket-launch"
          @click="showGenerateModal = true"
          class="font-weight-bold"
        >
          สร้างเกม (Generate Game)
        </v-btn>
      </div>
    </v-card>

    <!-- Main 5-Step Stepper Layout per spec Section 13 & 14 -->
    <v-row>
      <!-- Left Step Navigation (Desktop) -->
      <v-col cols="12" md="3">
        <v-card class="pa-3 border-card rounded-xl mb-4">
          <div class="text-caption font-weight-bold text-grey px-3 mb-2 text-uppercase">
            กระบวนการ DESIGN THINKING
          </div>

          <v-list nav density="comfortable">
            <v-list-item
              v-for="(s, idx) in steps"
              :key="s.id"
              :active="currentStepIndex === idx"
              @click="goToStep(idx)"
              rounded="lg"
              class="mb-1"
              :color="currentStepIndex === idx ? 'primary' : ''"
            >
              <template #prepend>
                <v-avatar
                  size="32"
                  :color="currentStepIndex === idx ? 'primary' : 'grey-lighten-3'"
                  class="mr-3"
                >
                  <v-icon
                    :icon="s.icon"
                    :color="currentStepIndex === idx ? 'white' : 'grey-darken-1'"
                    size="18"
                  ></v-icon>
                </v-avatar>
              </template>

              <v-list-item-title class="font-weight-bold text-subtitle-2">
                {{ s.title }}
              </v-list-item-title>
              <v-list-item-subtitle class="text-caption">
                {{ s.concept }}
              </v-list-item-subtitle>
            </v-list-item>
          </v-list>
        </v-card>
      </v-col>

      <!-- Center Active Step Form -->
      <v-col cols="12" md="9">
        <v-card class="pa-6 border-card rounded-xl">
          <!-- Step 1: Empathize -->
          <div v-if="currentStepIndex === 0">
            <div class="d-flex align-center mb-4">
              <v-avatar color="purple-lighten-5" class="mr-3" size="44">
                <v-icon icon="mdi-heart" color="primary" size="24"></v-icon>
              </v-avatar>
              <div>
                <h2 class="text-h6 font-weight-bold text-slate-800">1. Empathize (ทำความเข้าใจผู้เรียน)</h2>
                <div class="text-caption text-grey">วิเคราะห์คุณลักษณะ ความสนใจ และจุดติดขัดของผู้เรียน</div>
              </div>
            </div>

            <v-row>
              <v-col cols="12" sm="6">
                <v-text-field
                  v-model="empathizeData.target_learner"
                  label="กลุ่มผู้เรียนเป้าหมาย (Target Learner)"
                  placeholder="เช่น นักเรียนชั้น ม.1 อายุ 12-13 ปี"
                  @update:model-value="onFieldChange('empathize', empathizeData)"
                ></v-text-field>
              </v-col>
              <v-col cols="12" sm="6">
                <v-text-field
                  v-model="empathizeData.age_group"
                  label="ช่วงอายุ (Age Group)"
                  placeholder="เช่น 12-13 ปี"
                  @update:model-value="onFieldChange('empathize', empathizeData)"
                ></v-text-field>
              </v-col>

              <v-col cols="12">
                <v-textarea
                  v-model="empathizeData.learner_characteristics"
                  label="ลักษณะนิสัยและความชอบของผู้เรียน"
                  placeholder="เช่น ชอบภาพสีสันสดใส ชอบแข่งขันแบบกลุ่ม สนใจเรื่องสิ่งแวดล้อม"
                  rows="2"
                  @update:model-value="onFieldChange('empathize', empathizeData)"
                ></v-textarea>
              </v-col>

              <v-col cols="12">
                <v-textarea
                  v-model="empathizeData.pain_points"
                  label="Pain Points / อุปสรรคในการเรียนรู้ที่พบ"
                  placeholder="เช่น ไม่เข้าใจว่าทำไมต้องแยกขยะ จำสีถังขยะไม่ได้ สมาธิสั้นเมื่ออ่านเนื้อหาทฤษฎียาวๆ"
                  rows="3"
                  @update:model-value="onFieldChange('empathize', empathizeData)"
                ></v-textarea>
              </v-col>

              <v-col cols="12" sm="6">
                <v-select
                  v-model="empathizeData.device_availability"
                  :items="['Mobile (สมาร์ตโฟน)', 'Desktop/Laptop', 'Tablet', 'ทุกอุปกรณ์']"
                  label="อุปกรณ์หลักที่นักเรียนใช้เล่น"
                  @update:model-value="onFieldChange('empathize', empathizeData)"
                ></v-select>
              </v-col>
              <v-col cols="12" sm="6">
                <v-text-field
                  v-model="empathizeData.learning_environment"
                  label="บริบทการเรียนรู้ (Environment)"
                  placeholder="เช่น ในห้องเรียนคอมพิวเตอร์ หรือการบ้านที่บ้าน"
                  @update:model-value="onFieldChange('empathize', empathizeData)"
                ></v-text-field>
              </v-col>
            </v-row>
          </div>

          <!-- Step 2: Define -->
          <div v-else-if="currentStepIndex === 1">
            <div class="d-flex align-center mb-4">
              <v-avatar color="purple-lighten-5" class="mr-3" size="44">
                <v-icon icon="mdi-target" color="secondary" size="24"></v-icon>
              </v-avatar>
              <div>
                <h2 class="text-h6 font-weight-bold text-slate-800">2. Define (กำหนดปัญหาและเป้าหมาย)</h2>
                <div class="text-caption text-grey">กำหนด Learning Problem Statement และผลลัพธ์การเรียนรู้ที่คาดหวัง</div>
              </div>
            </div>

            <v-row>
              <v-col cols="12">
                <v-textarea
                  v-model="defineData.problem_statement"
                  label="โจทย์ปัญหาหลัก (Problem Statement)"
                  placeholder="เช่น นักเรียนไม่สามารถคัดแยกประเภทขยะในชีวิตประจำวันได้ถูกต้อง ทำให้เกิดปัญหาขยะตกค้าง"
                  rows="3"
                  @update:model-value="onFieldChange('define', defineData)"
                ></v-textarea>
              </v-col>

              <v-col cols="12">
                <v-textarea
                  v-model="defineData.expected_outcomes"
                  label="ผลลัพธ์การเรียนรู้ที่คาดหวัง (Expected Outcomes)"
                  placeholder="เช่น สามารถจำแนกขยะ 4 ประเภทได้ถูกต้องอย่างน้อย 80% หลังเล่นเกมจบ"
                  rows="2"
                  @update:model-value="onFieldChange('define', defineData)"
                ></v-textarea>
              </v-col>

              <v-col cols="12" sm="4">
                <v-textarea
                  v-model="defineData.knowledge_goals"
                  label="ด้านความรู้ (Knowledge)"
                  placeholder="เช่น ความหมายและสีถังขยะ"
                  rows="2"
                  @update:model-value="onFieldChange('define', defineData)"
                ></v-textarea>
              </v-col>
              <v-col cols="12" sm="4">
                <v-textarea
                  v-model="defineData.skill_goals"
                  label="ด้านทักษะ (Skills)"
                  placeholder="เช่น การตัดสินใจคัดแยกอย่างรวดเร็ว"
                  rows="2"
                  @update:model-value="onFieldChange('define', defineData)"
                ></v-textarea>
              </v-col>
              <v-col cols="12" sm="4">
                <v-textarea
                  v-model="defineData.attitude_goals"
                  label="ด้านเจตคติ (Attitude)"
                  placeholder="เช่น ตระหนักถึงความสะอาดในโรงเรียน"
                  rows="2"
                  @update:model-value="onFieldChange('define', defineData)"
                ></v-textarea>
              </v-col>
            </v-row>
          </div>

          <!-- Step 3: Ideate -->
          <div v-else-if="currentStepIndex === 2">
            <div class="d-flex align-center mb-4">
              <v-avatar color="amber-lighten-5" class="mr-3" size="44">
                <v-icon icon="mdi-lightbulb" color="warning" size="24"></v-icon>
              </v-avatar>
              <div>
                <h2 class="text-h6 font-weight-bold text-slate-800">3. Ideate (ออกแบบแนวคิดเกม)</h2>
                <div class="text-caption text-grey">กำหนด Concept, Theme, กติกา และความท้าทายของเกม</div>
              </div>
            </div>

            <v-row>
              <v-col cols="12">
                <v-textarea
                  v-model="ideateData.game_concept"
                  label="แนวคิดของเกม (Game Concept)"
                  placeholder="เช่น ภารกิจกอบกู้โรงเรียนสีเขียว โดยสวมบทบาทเป็นสายลับพิทักษ์สิ่งแวดล้อม"
                  rows="2"
                  @update:model-value="onFieldChange('ideate', ideateData)"
                ></v-textarea>
              </v-col>

              <v-col cols="12" sm="6">
                <v-select
                  v-model="ideateData.game_genre"
                  :items="['Scenario & Quiz', 'Adventure Simulation', 'Puzzle & Sorting', 'Decision Making Quest']"
                  label="รูปแบบเกม (Game Genre)"
                  @update:model-value="onFieldChange('ideate', ideateData)"
                ></v-select>
              </v-col>
              <v-col cols="12" sm="6">
                <v-select
                  v-model="ideateData.theme"
                  :items="['school', 'science', 'environment', 'space', 'fantasy']"
                  label="ธีมภาพของเกม (Theme)"
                  @update:model-value="onFieldChange('ideate', ideateData)"
                ></v-select>
              </v-col>

              <v-col cols="12">
                <v-textarea
                  v-model="ideateData.story"
                  label="เรื่องราวและภารกิจ (Story & Missions)"
                  placeholder="เช่น โรงเรียนกำลังจัดงานวันสิ่งแวดล้อม แต่ขยะเกลื่อนกลาด ผู้เล่นต้องเคลียร์ขยะ 3 โซนให้ทันเวลา"
                  rows="2"
                  @update:model-value="onFieldChange('ideate', ideateData)"
                ></v-textarea>
              </v-col>

              <v-col cols="12" sm="6">
                <v-text-field
                  v-model="ideateData.rewards"
                  label="รางวัลและการเสริมแรง (Rewards)"
                  placeholder="เช่น ดาว 3 ระดับ เหรียญตรา Eco-Master"
                  @update:model-value="onFieldChange('ideate', ideateData)"
                ></v-text-field>
              </v-col>
              <v-col cols="12" sm="6">
                <v-text-field
                  v-model.number="ideateData.duration_minutes"
                  type="number"
                  label="ระยะเวลาเล่นโดยประมาณ (นาที)"
                  @update:model-value="onFieldChange('ideate', ideateData)"
                ></v-text-field>
              </v-col>
            </v-row>
          </div>

          <!-- Step 4: Prototype -->
          <div v-else-if="currentStepIndex === 3">
            <div class="d-flex align-center mb-4">
              <v-avatar color="cyan-lighten-5" class="mr-3" size="44">
                <v-icon icon="mdi-palette" color="accent" size="24"></v-icon>
              </v-avatar>
              <div>
                <h2 class="text-h6 font-weight-bold text-slate-800">4. Prototype (ร่างโครงสร้างฉาก)</h2>
                <div class="text-caption text-grey">วางโครงสร้างฉาก ตัวละคร และข้อเสนอแนะในการเรียนรู้</div>
              </div>
            </div>

            <v-alert variant="tonal" color="primary" class="mb-4">
              <div class="d-flex align-center">
                <v-icon icon="mdi-information-outline" class="mr-2"></v-icon>
                <span>ระบบจะนำโครงสร้าง Prototype นี้ไปต่อยอดใน <strong>Visual Game Editor</strong> โดยอัตโนมัติ</span>
              </div>
            </v-alert>

            <v-row>
              <v-col cols="12">
                <v-textarea
                  v-model="prototypeData.feedback_mechanisms"
                  label="กลไกผลตอบรับเมื่อตอบถูก/ผิด (Feedback Mechanisms)"
                  placeholder="เช่น เมื่อตอบถูกให้แสดงการ์ดความรู้เสริม +10 แต้ม เมื่อตอบผิดให้แสดงคำใบ้ชี้แนะ"
                  rows="3"
                  @update:model-value="onFieldChange('prototype', prototypeData)"
                ></v-textarea>
              </v-col>
            </v-row>
          </div>

          <!-- Step 5: Test -->
          <div v-else-if="currentStepIndex === 4">
            <div class="d-flex align-center mb-4">
              <v-avatar color="green-lighten-5" class="mr-3" size="44">
                <v-icon icon="mdi-flask" color="success" size="24"></v-icon>
              </v-avatar>
              <div>
                <h2 class="text-h6 font-weight-bold text-slate-800">5. Test (ทดสอบและปรับปรุง)</h2>
                <div class="text-caption text-grey">บันทึกผลการทดสอบกับผู้เรียนจริงเพื่อนำข้อมูลมาพัฒนาเกม</div>
              </div>
            </div>

            <v-row>
              <v-col cols="12" sm="6">
                <v-text-field
                  v-model.number="testData.test_users_count"
                  type="number"
                  label="จำนวนผู้ทดสอบ (คน)"
                  @update:model-value="onFieldChange('test', testData)"
                ></v-text-field>
              </v-col>
              <v-col cols="12" sm="6">
                <v-select
                  v-model.number="testData.difficulty_rating"
                  :items="[
                    { title: 'ระดับ 1 - ง่ายมาก (1/5)', value: 1 },
                    { title: 'ระดับ 2 - ง่าย (2/5)', value: 2 },
                    { title: 'ระดับ 3 - พอดี เหมาะสม (3/5)', value: 3 },
                    { title: 'ระดับ 4 - ค่อนข้างยาก (4/5)', value: 4 },
                    { title: 'ระดับ 5 - ท้าทายมาก (5/5)', value: 5 },
                  ]"
                  label="ระดับความยากที่ประเมิน"
                  @update:model-value="onFieldChange('test', testData)"
                ></v-select>
              </v-col>

              <v-col cols="12">
                <v-textarea
                  v-model="testData.observations"
                  label="ข้อสังเกตระหว่างผู้เรียนเล่นเกม"
                  placeholder="เช่น นักเรียนตื่นเต้นกับฉากที่ 2 แต่อ่านคำถามแรกช้ากว่าที่คาด"
                  rows="2"
                  @update:model-value="onFieldChange('test', testData)"
                ></v-textarea>
              </v-col>

              <v-col cols="12">
                <v-textarea
                  v-model="testData.feedback_summary"
                  label="สรุปข้อเสนอแนะเพื่อนำไปปรับเกม"
                  placeholder="เช่น เพิ่มภาพประกอบขนาดใหญ่ขึ้น และปรับเวลาเป็น 10 นาที"
                  rows="2"
                  @update:model-value="onFieldChange('test', testData)"
                ></v-textarea>
              </v-col>
            </v-row>
          </div>

          <!-- Bottom Step Navigation Buttons -->
          <div class="d-flex justify-space-between mt-6 pt-4 border-t">
            <v-btn
              :disabled="currentStepIndex === 0"
              variant="outlined"
              prepend-icon="mdi-arrow-left"
              rounded="lg"
              @click="goToStep(currentStepIndex - 1)"
            >
              ย้อนกลับ
            </v-btn>

            <v-btn
              v-if="currentStepIndex < 4"
              color="primary"
              append-icon="mdi-arrow-right"
              rounded="lg"
              class="px-5 font-weight-medium"
              @click="goToStep(currentStepIndex + 1)"
            >
              บันทึกและไปต่อ &rarr;
            </v-btn>
            <v-btn
              v-else
              class="ai-gradient-bg text-white px-6 font-weight-bold"
              prepend-icon="mdi-rocket-launch"
              rounded="lg"
              @click="showGenerateModal = true"
            >
              สร้างเกมด้วย AI
            </v-btn>
          </div>
        </v-card>
      </v-col>
    </v-row>

    <!-- AI Assistant Drawer per spec Section 16 -->
    <v-navigation-drawer
      v-model="aiDrawer"
      location="right"
      temporary
      width="380"
      class="pa-4"
    >
      <div class="d-flex justify-space-between align-center mb-4">
        <div class="d-flex align-center">
          <v-avatar size="32" class="ai-gradient-bg mr-2">
            <v-icon icon="mdi-creation" color="white" size="18"></v-icon>
          </v-avatar>
          <span class="font-weight-bold text-subtitle-1">AI Suggestion</span>
        </div>
        <v-btn icon="mdi-close" variant="text" size="small" @click="aiDrawer = false"></v-btn>
      </div>

      <div v-if="aiLoading" class="text-center pa-8">
        <v-progress-circular indeterminate color="primary" class="mb-3"></v-progress-circular>
        <div class="text-caption text-grey">AI กำลังวิเคราะห์ข้อมูลของคุณ...</div>
      </div>

      <div v-else-if="aiSuggestion">
        <v-card class="pa-4 border-card rounded-xl mb-4 bg-purple-lighten-5">
          <div class="font-weight-bold text-subtitle-2 text-primary mb-2">
            {{ aiSuggestion.title }}
          </div>
          <p v-if="aiSuggestion.problem_statement" class="text-body-2 mb-3">
            {{ aiSuggestion.problem_statement }}
          </p>

          <v-list v-if="aiSuggestion.suggestions" density="compact" class="bg-transparent pa-0">
            <v-list-item v-for="(item, i) in aiSuggestion.suggestions" :key="i" class="pa-0 mb-1">
              <div class="d-flex align-start text-caption text-slate-700">
                <span class="mr-2 text-primary">&bull;</span>
                <span>{{ item }}</span>
              </div>
            </v-list-item>
          </v-list>

          <v-btn
            size="small"
            color="primary"
            class="mt-3 font-weight-medium"
            block
            rounded="lg"
            @click="applyAiSuggestion"
          >
            ใช้คำแนะนำนี้ (Use Suggestion)
          </v-btn>
        </v-card>
      </div>
    </v-navigation-drawer>

    <!-- Generate Game Progress Modal per spec Section 17 & 18 -->
    <v-dialog v-model="showGenerateModal" max-width="520" persistent>
      <v-card class="pa-6 rounded-xl">
        <div class="text-center mb-4">
          <div class="d-inline-block position-relative mb-2">
            <img
              src="@/assets/images/ai_game_wizard.jpg"
              alt="AI Engine Artwork"
              class="floating-asset rounded-2xl elevation-4"
              style="width: 100px; height: 100px; object-fit: cover; border: 2px solid rgba(198, 112, 255, 0.4);"
            />
          </div>
          <h2 class="text-h5 font-weight-bold text-slate-800">
            {{ isGenerating ? 'กำลังสร้างเกมของคุณด้วย AI' : 'พร้อมสร้างเกมการเรียนรู้' }}
          </h2>
          <p class="text-caption text-grey mt-1">
            แปลงแนวคิดจากกระบวนการ Design Thinking สู่ Web Game อัตโนมัติ
          </p>
        </div>

        <div v-if="!isGenerating" class="mb-6">
          <v-sheet color="grey-lighten-4" rounded="lg" class="pa-4">
            <div class="font-weight-bold text-subtitle-2 mb-2 text-slate-800">สรุปข้อมูลโปรเจกต์</div>
            <div class="text-caption text-grey-darken-2 mb-1 d-flex align-center">
              <v-icon icon="mdi-target" size="15" class="mr-1 text-primary"></v-icon>
              <span>วิชา: {{ project.subject }}</span>
            </div>
            <div class="text-caption text-grey-darken-2 mb-1 d-flex align-center">
              <v-icon icon="mdi-account-school" size="15" class="mr-1 text-primary"></v-icon>
              <span>ผู้เรียน: {{ empathizeData.target_learner || 'ม.1' }}</span>
            </div>
            <div class="text-caption text-grey-darken-2 mb-1 d-flex align-center">
              <v-icon icon="mdi-lightbulb" size="15" class="mr-1 text-warning"></v-icon>
              <span>ปัญหา: {{ defineData.problem_statement || '-' }}</span>
            </div>
            <div class="text-caption text-grey-darken-2 d-flex align-center">
              <v-icon icon="mdi-gamepad-variant" size="15" class="mr-1 text-success"></v-icon>
              <span>คอนเซปต์: {{ ideateData.game_concept || '-' }}</span>
            </div>
          </v-sheet>
        </div>

        <!-- Multi-step Generator Pipeline per spec Section 18 -->
        <div v-else class="mb-6">
          <v-list density="compact" class="pa-0 mb-4">
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
          <div class="text-right text-caption text-grey mt-1 font-weight-medium">
            {{ genProgress }}%
          </div>
        </div>

        <div class="d-flex justify-end gap-2">
          <v-btn
            v-if="!isGenerating"
            variant="text"
            @click="showGenerateModal = false"
            rounded="lg"
          >
            ยกเลิก
          </v-btn>
          <v-btn
            v-if="!isGenerating"
            class="ai-gradient-bg text-white font-weight-bold px-6"
            rounded="lg"
            @click="startGeneration"
          >
            เริ่มสร้างเกม &rarr;
          </v-btn>
        </div>
      </v-card>
    </v-dialog>
  </div>
</template>

<script setup lang="ts">
import { ref, onMounted } from 'vue'
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
  { id: 'empathize', title: 'Empathize', concept: 'เข้าใจผู้เรียน', icon: 'mdi-heart' },
  { id: 'define', title: 'Define', concept: 'กำหนดปัญหา', icon: 'mdi-target' },
  { id: 'ideate', title: 'Ideate', concept: 'คิดค้นไอเดีย', icon: 'mdi-lightbulb' },
  { id: 'prototype', title: 'Prototype', concept: 'ร่างต้นแบบฉาก', icon: 'mdi-palette' },
  { id: 'test', title: 'Test', concept: 'ทดลองและประเมิน', icon: 'mdi-flask' },
]

// Step reactive form data
const empathizeData = ref<any>({})
const defineData = ref<any>({})
const ideateData = ref<any>({})
const prototypeData = ref<any>({})
const testData = ref<any>({})

// AI Assistant
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

function onFieldChange(stepName: string, data: any) {
  projectStore.queueAutoSave(stepName, data)
}

function goToStep(idx: number) {
  currentStepIndex.value = idx
}

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

function applyAiSuggestion() {
  if (!aiSuggestion.value) return
  if (currentStepIndex.value === 1 && aiSuggestion.value.problem_statement) {
    defineData.value.problem_statement = aiSuggestion.value.problem_statement
    onFieldChange('define', defineData.value)
  }
  aiDrawer.value = false
}

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
.gap-2 {
  gap: 8px;
}
.gap-3 {
  gap: 12px;
}
</style>
