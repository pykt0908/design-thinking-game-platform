<template>
  <div>
    <!-- Page Header -->
    <div class="d-flex flex-wrap justify-space-between align-center gap-3 mb-6">
      <div>
        <h1 class="text-h5 font-weight-bold text-slate-800 d-flex align-center">
          <v-icon icon="mdi-google-classroom" color="primary" class="mr-2"></v-icon>
          จัดการห้องเรียน (Classrooms)
        </h1>
        <p class="text-body-2 text-grey">สร้างห้องเรียน กิจกรรม มอบหมายเกม และแจกรหัสเข้าร่วมให้นักเรียน</p>
      </div>

      <v-btn
        color="primary"
        class="font-weight-bold px-5 elevation-2"
        prepend-icon="mdi-plus"
        rounded="lg"
        @click="openCreateDialog"
      >
        สร้างห้องเรียนใหม่
      </v-btn>
    </div>

    <!-- Classrooms Grid (4 Columns on Desktop per Google Classroom style) -->
    <v-row v-if="classrooms.length > 0">
      <v-col
        v-for="c in classrooms"
        :key="c.id"
        cols="12"
        sm="6"
        md="4"
        lg="3"
      >
        <v-card class="classroom-card rounded-2xl overflow-hidden h-100 d-flex flex-column justify-space-between elevation-1 border">
          <!-- Top Cover Banner (Google Classroom style) -->
          <div
            class="classroom-cover position-relative"
            :style="getCoverStyle(c)"
          >
            <!-- Overlay Gradient for contrast -->
            <div class="cover-overlay pa-4 d-flex flex-column justify-space-between h-100">
              <!-- Top Row: Badge & 3-Dots Menu -->
              <div class="d-flex justify-space-between align-start">
                <v-chip
                  size="x-small"
                  class="font-weight-bold text-white cover-pill"
                >
                  ปี {{ c.academic_year || '2569' }}/{{ c.semester || '1' }}
                </v-chip>

                <!-- Options Menu -->
                <v-menu location="bottom end">
                  <template #activator="{ props }">
                    <v-btn
                      v-bind="props"
                      icon="mdi-dots-vertical"
                      variant="text"
                      size="small"
                      color="white"
                      class="cover-menu-btn"
                    ></v-btn>
                  </template>

                  <v-card min-width="190" rounded="xl" class="pa-1 elevation-4">
                    <v-list density="compact" nav>
                      <v-list-item
                        prepend-icon="mdi-pencil-outline"
                        title="แก้ไขห้องเรียน & ปก"
                        rounded="lg"
                        @click="openEditDialog(c)"
                      ></v-list-item>
                      <v-list-item
                        prepend-icon="mdi-content-copy"
                        title="คัดลอกรหัสเข้าเรียน"
                        rounded="lg"
                        @click="copyJoinCode(c.code)"
                      ></v-list-item>
                      <v-divider class="my-1"></v-divider>
                      <v-list-item
                        prepend-icon="mdi-trash-can-outline"
                        title="ลบห้องเรียน"
                        color="error"
                        rounded="lg"
                        @click="handleDeleteClassroom(c)"
                      ></v-list-item>
                    </v-list>
                  </v-card>
                </v-menu>
              </div>

              <!-- Bottom Row: Classroom Title & Info -->
              <div>
                <h2
                  class="text-subtitle-1 font-weight-bold text-white text-truncate mb-0"
                  :title="c.name"
                >
                  {{ c.name }}
                </h2>
                <div class="text-caption text-white text-truncate" style="opacity: 0.9;">
                  สถานะ: {{ c.status === 'active' ? 'เปิดการสอน' : 'เสร็จสิ้น' }}
                </div>
              </div>
            </div>
          </div>

          <!-- Card Body -->
          <div class="pa-4 flex-grow-1 d-flex flex-column justify-space-between">
            <div>
              <!-- Description -->
              <p
                class="text-caption text-slate-600 mb-3 text-clamp-2"
                :title="c.description || 'ไม่มีคำอธิบาย'"
                style="min-height: 36px;"
              >
                {{ c.description || 'ไม่มีรายละเอียดเพิ่มเติม' }}
              </p>

              <!-- Join Code Box -->
              <div class="join-code-box rounded-xl pa-2 px-3 mb-3 d-flex justify-space-between align-center">
                <div>
                  <div class="text-caption text-grey-darken-1 font-weight-medium" style="font-size: 11px;">
                    รหัสห้องเรียน (Join Code)
                  </div>
                  <div class="text-subtitle-2 font-weight-bold text-primary tracking-wider">
                    {{ c.code }}
                  </div>
                </div>
                <v-btn
                  icon="mdi-content-copy"
                  size="x-small"
                  variant="flat"
                  color="purple-lighten-5"
                  class="text-primary"
                  @click="copyJoinCode(c.code)"
                  title="คัดลอกรหัส"
                ></v-btn>
              </div>

              <!-- Stats Pill Bar -->
              <div class="d-flex align-center justify-space-between text-caption text-slate-600 mb-2 px-1">
                <span class="d-flex align-center">
                  <v-icon icon="mdi-account-school" size="16" color="primary" class="mr-1"></v-icon>
                  <strong>{{ c.students?.length || 0 }}</strong> คน
                </span>
                <span class="d-flex align-center">
                  <v-icon icon="mdi-gamepad-variant" size="16" color="secondary" class="mr-1"></v-icon>
                  <strong>{{ c.assignments?.length || 0 }}</strong> เกม
                </span>
              </div>
            </div>

            <!-- Card Actions Footer -->
            <div class="d-flex justify-space-between align-center pt-3 border-t gap-2 mt-2">
              <v-btn
                variant="outlined"
                size="small"
                color="primary"
                rounded="lg"
                class="flex-1-1 font-weight-medium px-2"
                prepend-icon="mdi-plus"
                @click="openAssignDialog(c)"
              >
                มอบหมายเกม
              </v-btn>

              <v-btn
                variant="tonal"
                size="small"
                color="primary"
                rounded="lg"
                class="flex-1-1 font-weight-bold px-2"
                prepend-icon="mdi-account-group"
                @click="openStudentsDialog(c)"
              >
                นักเรียน ({{ c.students?.length || 0 }})
              </v-btn>
            </div>
          </div>
        </v-card>
      </v-col>
    </v-row>

    <!-- Empty State -->
    <div v-else class="text-center pa-12 border-card rounded-2xl bg-white mt-4">
      <v-avatar size="64" color="purple-lighten-5" class="mb-3">
        <v-icon icon="mdi-google-classroom" size="36" color="primary"></v-icon>
      </v-avatar>
      <h3 class="text-h6 font-weight-bold text-slate-800 mb-1">ยังไม่มีห้องเรียนในระบบ</h3>
      <p class="text-body-2 text-grey mb-4">สร้างห้องเรียนแรกของคุณเพื่อเริ่มมอบหมายเกมการเรียนรู้และติดตามผลคะแนน</p>
      <v-btn color="primary" rounded="lg" prepend-icon="mdi-plus" @click="openCreateDialog">
        สร้างห้องเรียนตอนนี้
      </v-btn>
    </div>

    <!-- Create Classroom Dialog with Cover Selector -->
    <v-dialog v-model="showCreateDialog" max-width="600" scrollable>
      <v-card class="rounded-2xl overflow-hidden">
        <v-card-title class="pa-5 bg-purple-lighten-5 border-b d-flex justify-space-between align-center">
          <div class="d-flex align-center">
            <v-icon icon="mdi-google-classroom" color="primary" class="mr-2"></v-icon>
            <span class="text-h6 font-weight-bold text-slate-900">สร้างห้องเรียนใหม่</span>
          </div>
          <v-btn icon="mdi-close" variant="text" size="small" @click="showCreateDialog = false"></v-btn>
        </v-card-title>

        <v-card-text class="pa-5">
          <v-form @submit.prevent="handleCreateClassroom">
            <!-- Cover Preview -->
            <div class="mb-4">
              <label class="text-caption font-weight-bold text-slate-700 mb-1 d-block">
                ภาพหน้าปกห้องเรียน (Cover Image)
              </label>
              <div
                class="cover-dialog-preview rounded-xl overflow-hidden position-relative mb-3 d-flex align-end pa-4"
                :style="formCoverPreviewStyle"
              >
                <div class="position-relative" style="z-index: 2;">
                  <div class="text-h6 font-weight-bold text-white text-truncate">
                    {{ formClassName || 'ชื่อห้องเรียนของคุณ' }}
                  </div>
                  <div class="text-caption text-white opacity-90">
                    ปีการศึกษา {{ formAcademicYear }}/{{ formSemester }} &bull; ตัวอย่างหน้าปก
                  </div>
                </div>
              </div>

              <!-- Cover Selection Tabs -->
              <v-tabs v-model="coverTab" density="compact" color="primary" class="mb-3">
                <v-tab value="presets">ภาพมาตรฐาน (Presets)</v-tab>
                <v-tab value="upload">อัปโหลดภาพ (Upload)</v-tab>
                <v-tab value="url">ใส่ลิงก์รูป (URL)</v-tab>
              </v-tabs>

              <v-window v-model="coverTab">
                <!-- Preset Covers Grid -->
                <v-window-item value="presets">
                  <div class="preset-covers-grid">
                    <div
                      v-for="(preset, idx) in presetCovers"
                      :key="idx"
                      class="preset-item rounded-lg overflow-hidden position-relative"
                      :class="{ 'preset-selected': formCoverImage === preset.url }"
                      @click="selectPresetCover(preset.url, preset.color)"
                    >
                      <img :src="preset.url" :alt="preset.title" class="w-100 h-100 object-cover" />
                      <div class="preset-label">{{ preset.title }}</div>
                      <v-icon
                        v-if="formCoverImage === preset.url"
                        icon="mdi-check-circle"
                        color="white"
                        size="20"
                        class="preset-check-icon"
                      ></v-icon>
                    </div>
                  </div>
                </v-window-item>

                <!-- Upload File -->
                <v-window-item value="upload">
                  <v-file-input
                    v-model="formCoverFile"
                    accept="image/*"
                    label="เลือกไฟล์รูปภาพจากเครื่อง (JPG, PNG, WebP)"
                    prepend-icon="mdi-camera"
                    variant="outlined"
                    density="comfortable"
                    rounded="lg"
                    @update:model-value="onCoverFileChange"
                  ></v-file-input>
                </v-window-item>

                <!-- Custom URL -->
                <v-window-item value="url">
                  <v-text-field
                    v-model="formCoverImage"
                    label="URL ลิงก์รูปภาพภาพหน้าปก"
                    placeholder="https://example.com/cover.jpg"
                    prepend-inner-icon="mdi-link"
                    variant="outlined"
                    density="comfortable"
                    rounded="lg"
                  ></v-text-field>
                </v-window-item>
              </v-window>
            </div>

            <!-- Classroom Details Form -->
            <v-text-field
              v-model="formClassName"
              label="ชื่อห้องเรียน *"
              placeholder="เช่น วิทยาศาสตร์ ม.1/3, นวัตกรคิดสร้างสรรค์"
              required
              variant="outlined"
              density="comfortable"
              rounded="lg"
              class="mb-3"
            ></v-text-field>

            <v-row>
              <v-col cols="6">
                <v-text-field
                  v-model="formAcademicYear"
                  label="ปีการศึกษา"
                  placeholder="2569"
                  variant="outlined"
                  density="comfortable"
                  rounded="lg"
                ></v-text-field>
              </v-col>
              <v-col cols="6">
                <v-text-field
                  v-model="formSemester"
                  label="ภาคเรียน"
                  placeholder="1"
                  variant="outlined"
                  density="comfortable"
                  rounded="lg"
                ></v-text-field>
              </v-col>
            </v-row>

            <v-textarea
              v-model="formClassDesc"
              label="คำอธิบายห้องเรียน (ไม่บังคับ)"
              placeholder="เป้าหมายการเรียนรู้, คำแนะนำสำหรับนักเรียน..."
              rows="2"
              variant="outlined"
              density="comfortable"
              rounded="lg"
              class="mb-2"
            ></v-textarea>

            <div class="d-flex justify-end gap-2 pt-3 border-t">
              <v-btn variant="text" rounded="lg" @click="showCreateDialog = false">ยกเลิก</v-btn>
              <v-btn color="primary" rounded="lg" type="submit" :loading="saving" class="font-weight-bold px-5">
                สร้างห้องเรียน
              </v-btn>
            </div>
          </v-form>
        </v-card-text>
      </v-card>
    </v-dialog>

    <!-- Edit Classroom Dialog with Cover Selector -->
    <v-dialog v-model="showEditDialog" max-width="600" scrollable>
      <v-card class="rounded-2xl overflow-hidden">
        <v-card-title class="pa-5 bg-purple-lighten-5 border-b d-flex justify-space-between align-center">
          <div class="d-flex align-center">
            <v-icon icon="mdi-pencil-box-outline" color="primary" class="mr-2"></v-icon>
            <span class="text-h6 font-weight-bold text-slate-900">แก้ไขห้องเรียน & ภาพหน้าปก</span>
          </div>
          <v-btn icon="mdi-close" variant="text" size="small" @click="showEditDialog = false"></v-btn>
        </v-card-title>

        <v-card-text class="pa-5">
          <v-form @submit.prevent="handleUpdateClassroom">
            <!-- Cover Preview -->
            <div class="mb-4">
              <label class="text-caption font-weight-bold text-slate-700 mb-1 d-block">
                ภาพหน้าปกห้องเรียน (Cover Image)
              </label>
              <div
                class="cover-dialog-preview rounded-xl overflow-hidden position-relative mb-3 d-flex align-end pa-4"
                :style="formCoverPreviewStyle"
              >
                <div class="position-relative" style="z-index: 2;">
                  <div class="text-h6 font-weight-bold text-white text-truncate">
                    {{ formClassName }}
                  </div>
                  <div class="text-caption text-white opacity-90">
                    ปีการศึกษา {{ formAcademicYear }}/{{ formSemester }} &bull; ตัวอย่างหน้าปก
                  </div>
                </div>
              </div>

              <!-- Cover Selection Tabs -->
              <v-tabs v-model="coverTab" density="compact" color="primary" class="mb-3">
                <v-tab value="presets">ภาพมาตรฐาน (Presets)</v-tab>
                <v-tab value="upload">อัปโหลดภาพ (Upload)</v-tab>
                <v-tab value="url">ใส่ลิงก์รูป (URL)</v-tab>
              </v-tabs>

              <v-window v-model="coverTab">
                <!-- Preset Covers Grid -->
                <v-window-item value="presets">
                  <div class="preset-covers-grid">
                    <div
                      v-for="(preset, idx) in presetCovers"
                      :key="idx"
                      class="preset-item rounded-lg overflow-hidden position-relative"
                      :class="{ 'preset-selected': formCoverImage === preset.url }"
                      @click="selectPresetCover(preset.url, preset.color)"
                    >
                      <img :src="preset.url" :alt="preset.title" class="w-100 h-100 object-cover" />
                      <div class="preset-label">{{ preset.title }}</div>
                      <v-icon
                        v-if="formCoverImage === preset.url"
                        icon="mdi-check-circle"
                        color="white"
                        size="20"
                        class="preset-check-icon"
                      ></v-icon>
                    </div>
                  </div>
                </v-window-item>

                <!-- Upload File -->
                <v-window-item value="upload">
                  <v-file-input
                    v-model="formCoverFile"
                    accept="image/*"
                    label="เลือกไฟล์รูปภาพจากเครื่อง (JPG, PNG, WebP)"
                    prepend-icon="mdi-camera"
                    variant="outlined"
                    density="comfortable"
                    rounded="lg"
                    @update:model-value="onCoverFileChange"
                  ></v-file-input>
                </v-window-item>

                <!-- Custom URL -->
                <v-window-item value="url">
                  <v-text-field
                    v-model="formCoverImage"
                    label="URL ลิงก์รูปภาพภาพหน้าปก"
                    placeholder="https://example.com/cover.jpg"
                    prepend-inner-icon="mdi-link"
                    variant="outlined"
                    density="comfortable"
                    rounded="lg"
                  ></v-text-field>
                </v-window-item>
              </v-window>
            </div>

            <!-- Classroom Details Form -->
            <v-text-field
              v-model="formClassName"
              label="ชื่อห้องเรียน *"
              required
              variant="outlined"
              density="comfortable"
              rounded="lg"
              class="mb-3"
            ></v-text-field>

            <v-row>
              <v-col cols="6">
                <v-text-field
                  v-model="formAcademicYear"
                  label="ปีการศึกษา"
                  variant="outlined"
                  density="comfortable"
                  rounded="lg"
                ></v-text-field>
              </v-col>
              <v-col cols="6">
                <v-text-field
                  v-model="formSemester"
                  label="ภาคเรียน"
                  variant="outlined"
                  density="comfortable"
                  rounded="lg"
                ></v-text-field>
              </v-col>
            </v-row>

            <v-textarea
              v-model="formClassDesc"
              label="คำอธิบายห้องเรียน"
              rows="2"
              variant="outlined"
              density="comfortable"
              rounded="lg"
              class="mb-2"
            ></v-textarea>

            <div class="d-flex justify-end gap-2 pt-3 border-t">
              <v-btn variant="text" rounded="lg" @click="showEditDialog = false">ยกเลิก</v-btn>
              <v-btn color="primary" rounded="lg" type="submit" :loading="saving" class="font-weight-bold px-5">
                บันทึกการเปลี่ยนแปลง
              </v-btn>
            </div>
          </v-form>
        </v-card-text>
      </v-card>
    </v-dialog>

    <!-- Assign Game Dialog -->
    <v-dialog v-model="showAssignDialog" max-width="500">
      <v-card class="pa-6 rounded-2xl">
        <h2 class="text-h6 font-weight-bold mb-1">มอบหมายเกมการเรียนรู้</h2>
        <p class="text-caption text-grey mb-4">ห้องเรียน: <strong>{{ targetClassroom?.name }}</strong></p>

        <v-form @submit.prevent="handleAssignGame">
          <v-select
            v-model="assignGameId"
            :items="availableGames"
            item-title="title"
            item-value="id"
            label="เลือกเกมการเรียนรู้ *"
            required
            variant="outlined"
            density="comfortable"
            rounded="lg"
            class="mb-3"
          ></v-select>

          <v-text-field
            v-model.number="assignPassingScore"
            type="number"
            label="คะแนนผ่านเกณฑ์ (%)"
            variant="outlined"
            density="comfortable"
            rounded="lg"
            class="mb-4"
          ></v-text-field>

          <div class="d-flex justify-end gap-2">
            <v-btn variant="text" rounded="lg" @click="showAssignDialog = false">ยกเลิก</v-btn>
            <v-btn color="primary" rounded="lg" type="submit" :loading="assigning" class="font-weight-bold px-5">
              ยืนยันมอบหมาย
            </v-btn>
          </div>
        </v-form>
      </v-card>
    </v-dialog>

    <!-- View Classroom Students Dialog -->
    <v-dialog v-model="showStudentsDialog" max-width="680" scrollable>
      <v-card v-if="selectedClassroomForStudents" class="rounded-2xl overflow-hidden">
        <!-- Header -->
        <div class="pa-5 bg-purple-lighten-5 border-b d-flex justify-space-between align-center">
          <div class="d-flex align-center">
            <v-avatar size="44" color="white" class="mr-3 elevation-1">
              <v-icon icon="mdi-account-school" color="primary" size="24"></v-icon>
            </v-avatar>
            <div>
              <div class="d-flex align-center gap-2">
                <h2 class="text-h6 font-weight-bold text-slate-900 mb-0">
                  {{ selectedClassroomForStudents.name }}
                </h2>
                <v-chip size="x-small" color="primary" variant="flat" class="font-weight-bold">
                  {{ selectedClassroomForStudents.students?.length || 0 }} คน
                </v-chip>
              </div>
              <div class="text-caption text-grey-darken-1">
                ปีการศึกษา {{ selectedClassroomForStudents.academic_year || '2569' }}/{{ selectedClassroomForStudents.semester || '1' }}
                &bull; รหัสเข้าร่วม: <strong class="text-primary">{{ selectedClassroomForStudents.code }}</strong>
              </div>
            </div>
          </div>
          <v-btn icon="mdi-close" variant="text" size="small" @click="showStudentsDialog = false"></v-btn>
        </div>

        <!-- Body -->
        <v-card-text class="pa-5" style="max-height: 480px;">
          <!-- Search & Filter -->
          <div class="mb-4">
            <v-text-field
              v-model="studentSearchQuery"
              placeholder="ค้นหาตามชื่อ, รหัสนักเรียน หรืออีเมล..."
              prepend-inner-icon="mdi-magnify"
              density="compact"
              hide-details
              rounded="lg"
              variant="outlined"
              class="bg-white"
              clearable
            ></v-text-field>
          </div>

          <!-- Students List -->
          <div v-if="filteredStudents.length > 0" class="d-flex flex-column gap-2">
            <v-card
              v-for="s in filteredStudents"
              :key="s.id"
              class="pa-3 border rounded-xl d-flex justify-space-between align-center bg-white hover-card"
            >
              <div class="d-flex align-center">
                <v-avatar size="40" color="purple-lighten-4" class="mr-3 text-primary font-weight-bold">
                  {{ s.name?.charAt(0) || 'S' }}
                </v-avatar>
                <div>
                  <div class="font-weight-bold text-slate-800 text-subtitle-2 d-flex align-center">
                    <span>{{ s.name }}</span>
                    <v-chip
                      v-if="s.student_id"
                      size="x-small"
                      color="secondary"
                      variant="tonal"
                      class="ml-2 font-weight-bold"
                    >
                      รหัส: {{ s.student_id }}
                    </v-chip>
                  </div>
                  <div class="text-caption text-grey">
                    {{ s.email }}
                    <span v-if="(s as any)?.pivot?.joined_at">
                      &bull; เข้าร่วมเมื่อ {{ formatDate((s as any).pivot.joined_at) }}
                    </span>
                  </div>
                </div>
              </div>

              <div class="d-flex align-center">
                <v-chip size="small" color="success" variant="tonal" class="font-weight-bold">
                  <v-icon icon="mdi-check-circle-outline" size="14" class="mr-1"></v-icon>
                  เข้าเรียนแล้ว
                </v-chip>
              </div>
            </v-card>
          </div>

          <!-- Search No Result -->
          <div v-else-if="studentSearchQuery" class="text-center pa-8 text-grey">
            <v-icon icon="mdi-account-search" size="48" color="grey-lighten-1" class="mb-2"></v-icon>
            <div>ไม่พบนักเรียนที่ตรงกับคำค้นหา "{{ studentSearchQuery }}"</div>
          </div>

          <!-- Empty State: No students joined yet -->
          <div v-else class="text-center pa-8 border rounded-2xl bg-purple-lighten-5">
            <v-avatar size="56" color="white" class="mb-3 elevation-2">
              <v-icon icon="mdi-account-plus-outline" size="32" color="primary"></v-icon>
            </v-avatar>
            <h3 class="text-subtitle-1 font-weight-bold text-slate-800 mb-1">
              ยังไม่มีนักเรียนเข้าร่วมห้องเรียนนี้
            </h3>
            <p class="text-caption text-slate-600 mb-4 max-w-md mx-auto">
              ส่งรหัสห้องเรียนด้านล่างให้นักเรียน เพื่อใช้กรอกเข้าร่วมผ่านหน้าแรกของนักเรียน
            </p>
            <v-sheet color="white" rounded="xl" class="pa-3 d-inline-flex align-center gap-3 border elevation-1">
              <div class="text-left px-2">
                <div class="text-caption text-grey">รหัสห้องเรียน (Join Code)</div>
                <div class="text-h6 font-weight-bold text-primary tracking-wider">
                  {{ selectedClassroomForStudents.code }}
                </div>
              </div>
              <v-btn
                color="primary"
                rounded="lg"
                size="small"
                class="font-weight-bold"
                prepend-icon="mdi-content-copy"
                @click="copyJoinCode(selectedClassroomForStudents.code)"
              >
                คัดลอกรหัส
              </v-btn>
            </v-sheet>
          </div>
        </v-card-text>

        <!-- Footer -->
        <div class="pa-4 border-t d-flex justify-end bg-grey-lighten-5">
          <v-btn variant="outlined" rounded="lg" color="grey-darken-1" @click="showStudentsDialog = false">
            ปิด
          </v-btn>
        </div>
      </v-card>
    </v-dialog>
  </div>
</template>

<script setup lang="ts">
import { ref, computed, onMounted } from 'vue'
import apiClient from '@/api/client'
import { useAlertStore } from '@/stores/alert'
import type { Classroom, Game } from '@/types'

const alertStore = useAlertStore()

const classrooms = ref<Classroom[]>([])
const availableGames = ref<Game[]>([])

// Curated Educational Preset Covers
const presetCovers = [
  {
    title: 'Design Thinking',
    url: 'https://images.unsplash.com/photo-1531403009284-440f080d1e12?auto=format&fit=crop&w=600&q=80',
    color: '#3d0066',
  },
  {
    title: 'วิทยาศาสตร์ & ทดลอง',
    url: 'https://images.unsplash.com/photo-1532094349884-543bc11b234d?auto=format&fit=crop&w=600&q=80',
    color: '#0284c7',
  },
  {
    title: 'เทคโนโลยี & โค้ดดิ้ง',
    url: 'https://images.unsplash.com/photo-1517694712202-14dd9538aa97?auto=format&fit=crop&w=600&q=80',
    color: '#1e1b4b',
  },
  {
    title: 'คณิตศาสตร์ & ตรรกะ',
    url: 'https://images.unsplash.com/photo-1635070041078-e363dbe005cb?auto=format&fit=crop&w=600&q=80',
    color: '#d97706',
  },
  {
    title: 'ศิลปะ & ความคิดสร้างสรรค์',
    url: 'https://images.unsplash.com/photo-1513364776144-60967b0f800f?auto=format&fit=crop&w=600&q=80',
    color: '#c026d3',
  },
  {
    title: 'ภาษาและการสื่อสาร',
    url: 'https://images.unsplash.com/photo-1457369804613-52c61a468e7d?auto=format&fit=crop&w=600&q=80',
    color: '#059669',
  },
]

// Modal & Form States
const showCreateDialog = ref(false)
const showEditDialog = ref(false)
const editingClassId = ref<number | null>(null)
const saving = ref(false)

const formClassName = ref('')
const formAcademicYear = ref('2569')
const formSemester = ref('1')
const formClassDesc = ref('')
const formCoverImage = ref(presetCovers[0].url)
const formThemeColor = ref(presetCovers[0].color)
const formCoverFile = ref<File | null>(null)
const coverTab = ref('presets')
const localCoverPreview = ref<string | null>(null)

// Assign Dialog
const showAssignDialog = ref(false)
const targetClassroom = ref<Classroom | null>(null)
const assignGameId = ref<number | null>(null)
const assignPassingScore = ref(60)
const assigning = ref(false)

// Students Dialog
const showStudentsDialog = ref(false)
const selectedClassroomForStudents = ref<Classroom | null>(null)
const studentSearchQuery = ref('')

const filteredStudents = computed(() => {
  const students = selectedClassroomForStudents.value?.students || []
  if (!studentSearchQuery.value) return students
  const q = studentSearchQuery.value.toLowerCase().trim()
  return students.filter(s =>
    s.name?.toLowerCase().includes(q) ||
    s.email?.toLowerCase().includes(q) ||
    (s.student_id && s.student_id.toLowerCase().includes(q))
  )
})

const formCoverPreviewStyle = computed(() => {
  const bgImg = localCoverPreview.value || formCoverImage.value
  if (bgImg) {
    return {
      backgroundImage: `linear-gradient(to bottom, rgba(0,0,0,0.3) 0%, rgba(0,0,0,0.85) 100%), url('${bgImg}')`,
      backgroundSize: 'cover',
      backgroundPosition: 'center',
      minHeight: '130px',
    }
  }
  return {
    background: `linear-gradient(135deg, ${formThemeColor.value || '#3d0066'} 0%, #1f0036 100%)`,
    minHeight: '130px',
  }
})

function getCoverStyle(c: Classroom) {
  if (c.cover_image) {
    return {
      backgroundImage: `url('${c.cover_image}')`,
      backgroundSize: 'cover',
      backgroundPosition: 'center',
    }
  }
  const color = c.theme_color || '#3d0066'
  return {
    background: `linear-gradient(135deg, ${color} 0%, #1f0036 100%)`,
  }
}

function selectPresetCover(url: string, color: string) {
  formCoverImage.value = url
  formThemeColor.value = color
  localCoverPreview.value = null
  formCoverFile.value = null
}

function onCoverFileChange(file: any) {
  const f = Array.isArray(file) ? file[0] : file
  if (f && f instanceof File) {
    localCoverPreview.value = URL.createObjectURL(f)
  } else {
    localCoverPreview.value = null
  }
}

function openCreateDialog() {
  editingClassId.value = null
  formClassName.value = ''
  formAcademicYear.value = '2569'
  formSemester.value = '1'
  formClassDesc.value = ''
  formCoverImage.value = presetCovers[0].url
  formThemeColor.value = presetCovers[0].color
  formCoverFile.value = null
  localCoverPreview.value = null
  coverTab.value = 'presets'
  showCreateDialog.value = true
}

function openEditDialog(c: Classroom) {
  editingClassId.value = c.id
  formClassName.value = c.name
  formAcademicYear.value = c.academic_year || '2569'
  formSemester.value = c.semester || '1'
  formClassDesc.value = c.description || ''
  formCoverImage.value = c.cover_image || presetCovers[0].url
  formThemeColor.value = c.theme_color || '#3d0066'
  formCoverFile.value = null
  localCoverPreview.value = null
  coverTab.value = 'presets'
  showEditDialog.value = true
}

async function handleCreateClassroom() {
  if (!formClassName.value) return
  saving.value = true
  try {
    const formData = new FormData()
    formData.append('name', formClassName.value)
    formData.append('academic_year', formAcademicYear.value)
    formData.append('semester', formSemester.value)
    formData.append('description', formClassDesc.value || '')
    formData.append('theme_color', formThemeColor.value || '#3d0066')

    if (formCoverFile.value) {
      formData.append('cover_file', formCoverFile.value)
    } else if (formCoverImage.value) {
      formData.append('cover_image', formCoverImage.value)
    }

    await apiClient.post('/classrooms', formData, {
      headers: { 'Content-Type': 'multipart/form-data' },
    })

    showCreateDialog.value = false
    alertStore.success('สร้างห้องเรียนเรียบร้อยแล้ว!', 'สำเร็จ')
    await loadData()
  } catch (err: any) {
    alertStore.error(err.response?.data?.message || 'ไม่สามารถสร้างห้องเรียนได้', 'เกิดข้อผิดพลาด')
  } finally {
    saving.value = false
  }
}

async function handleUpdateClassroom() {
  if (!editingClassId.value || !formClassName.value) return
  saving.value = true
  try {
    const formData = new FormData()
    formData.append('_method', 'PUT')
    formData.append('name', formClassName.value)
    formData.append('academic_year', formAcademicYear.value)
    formData.append('semester', formSemester.value)
    formData.append('description', formClassDesc.value || '')
    formData.append('theme_color', formThemeColor.value || '#3d0066')

    if (formCoverFile.value) {
      formData.append('cover_file', formCoverFile.value)
    } else if (formCoverImage.value) {
      formData.append('cover_image', formCoverImage.value)
    }

    await apiClient.post(`/classrooms/${editingClassId.value}`, formData, {
      headers: { 'Content-Type': 'multipart/form-data' },
    })

    showEditDialog.value = false
    alertStore.success('อัปเดตข้อมูลห้องเรียนเรียบร้อยแล้ว!', 'สำเร็จ')
    await loadData()
  } catch (err: any) {
    alertStore.error(err.response?.data?.message || 'ไม่สามารถแก้ไขห้องเรียนได้', 'เกิดข้อผิดพลาด')
  } finally {
    saving.value = false
  }
}

async function handleDeleteClassroom(c: Classroom) {
  const confirmed = await alertStore.confirm(
    `คุณต้องการลบห้องเรียน "${c.name}" ใช่หรือไม่? ข้อมูลการมอบหมายและประวัตินักเรียนจะถูกลบด้วย`,
    'ยืนยันการลบห้องเรียน',
    { confirmText: 'ลบห้องเรียน', cancelText: 'ยกเลิก', type: 'error' }
  )

  if (!confirmed) return

  try {
    await apiClient.delete(`/classrooms/${c.id}`)
    alertStore.success(`ลบห้องเรียน "${c.name}" เรียบร้อยแล้ว`, 'สำเร็จ')
    await loadData()
  } catch (err: any) {
    alertStore.error(err.response?.data?.message || 'ไม่สามารถลบห้องเรียนได้', 'เกิดข้อผิดพลาด')
  }
}

async function openStudentsDialog(c: Classroom) {
  selectedClassroomForStudents.value = c
  studentSearchQuery.value = ''
  showStudentsDialog.value = true
  try {
    const { data } = await apiClient.get(`/classrooms/${c.id}`)
    selectedClassroomForStudents.value = data
    const idx = classrooms.value.findIndex(item => item.id === c.id)
    if (idx !== -1) {
      classrooms.value[idx] = data
    }
  } catch (err) {
    console.error('Failed to load classroom details', err)
  }
}

function formatDate(dateStr?: string) {
  if (!dateStr) return '-'
  try {
    const d = new Date(dateStr)
    return d.toLocaleDateString('th-TH', {
      year: 'numeric',
      month: 'short',
      day: 'numeric',
    })
  } catch {
    return dateStr
  }
}

async function loadData() {
  const [cRes, gRes] = await Promise.all([
    apiClient.get('/classrooms'),
    apiClient.get('/games'),
  ])
  classrooms.value = cRes.data
  availableGames.value = gRes.data.data || gRes.data
}

function openAssignDialog(c: Classroom) {
  targetClassroom.value = c
  if (availableGames.value.length > 0) {
    assignGameId.value = availableGames.value[0].id
  }
  showAssignDialog.value = true
}

async function handleAssignGame() {
  if (!targetClassroom.value || !assignGameId.value) return
  assigning.value = true
  try {
    await apiClient.post(`/classrooms/${targetClassroom.value.id}/assign`, {
      game_id: assignGameId.value,
      passing_score: assignPassingScore.value,
    })
    showAssignDialog.value = false
    alertStore.success('มอบหมายเกมให้ห้องเรียนสำเร็จ!')
    await loadData()
  } finally {
    assigning.value = false
  }
}

function copyJoinCode(code: string) {
  navigator.clipboard.writeText(code)
  alertStore.toast(`คัดลอกรหัสห้องเรียน "${code}" เรียบร้อยแล้ว!`, 'info')
}

onMounted(loadData)
</script>

<style scoped>
.gap-2 { gap: 8px; }
.gap-3 { gap: 12px; }
.gap-4 { gap: 16px; }
.tracking-wider { letter-spacing: 0.08em; }

/* Classroom Card & Cover */
.classroom-card {
  transition: transform 0.25s cubic-bezier(0.16, 1, 0.3, 1), box-shadow 0.25s ease;
  background-color: #ffffff;
  border-color: #edd4f8 !important;
}

.classroom-card:hover {
  transform: translateY(-4px);
  box-shadow: 0 14px 28px -6px rgba(61, 0, 102, 0.15), 0 4px 12px -2px rgba(198, 112, 255, 0.2) !important;
}

.classroom-cover {
  height: 140px;
  background-color: #3d0066;
  border-bottom: 1px solid rgba(0, 0, 0, 0.08);
}

.cover-overlay {
  background: linear-gradient(to bottom, rgba(0, 0, 0, 0.45) 0%, rgba(0, 0, 0, 0.1) 40%, rgba(0, 0, 0, 0.85) 100%);
}

.cover-pill {
  background: rgba(255, 255, 255, 0.25) !important;
  backdrop-filter: blur(8px);
}

.cover-menu-btn {
  background: rgba(0, 0, 0, 0.2);
  backdrop-filter: blur(4px);
}

.cover-menu-btn:hover {
  background: rgba(0, 0, 0, 0.4);
}

/* Join Code Box */
.join-code-box {
  background: #faf4fe;
  border: 1px dashed #d8b4fe;
}

/* Clamped text */
.text-clamp-2 {
  display: -webkit-box;
  -webkit-line-clamp: 2;
  -webkit-box-orient: vertical;
  overflow: hidden;
  text-overflow: ellipsis;
}

/* Preset Covers Grid */
.preset-covers-grid {
  display: grid;
  grid-template-columns: repeat(3, 1fr);
  gap: 10px;
}

.preset-item {
  height: 72px;
  cursor: pointer;
  border: 2px solid transparent;
  transition: all 0.2s ease;
}

.preset-item:hover {
  transform: scale(1.02);
  border-color: #c670ff;
}

.preset-selected {
  border-color: #3d0066 !important;
  box-shadow: 0 0 0 2px #ffe047;
}

.preset-label {
  position: absolute;
  bottom: 0;
  left: 0;
  right: 0;
  padding: 2px 6px;
  font-size: 10px;
  font-weight: 600;
  color: #ffffff;
  background: linear-gradient(to top, rgba(0, 0, 0, 0.8), transparent);
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}

.preset-check-icon {
  position: absolute;
  top: 4px;
  right: 4px;
  background: #3d0066;
  border-radius: 50%;
}

.object-cover {
  object-fit: cover;
}
</style>
