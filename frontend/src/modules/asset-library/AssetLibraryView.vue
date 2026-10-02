<template>
  <div>
    <div class="d-flex justify-space-between align-center mb-6">
      <div>
        <h1 class="text-h5 font-weight-bold text-slate-800">คลังสื่อและรูปภาพ (Asset Library)</h1>
        <p class="text-body-2 text-grey">รวบรวมตัวละคร ฉากหลัง วัตถุ และไอคอนสำหรับใช้ในเกม</p>
      </div>

      <v-btn
        color="primary"
        prepend-icon="mdi-upload"
        rounded="lg"
        class="font-weight-bold px-5"
        @click="showUploadDialog = true"
      >
        + อัปโหลด Asset
      </v-btn>
    </div>

    <!-- Filters per spec Section 23 -->
    <v-card class="pa-4 mb-6 border-card rounded-xl">
      <v-row align="center">
        <v-col cols="12" md="5">
          <v-text-field
            v-model="searchQuery"
            label="ค้นหา Asset..."
            prepend-inner-icon="mdi-magnify"
            density="compact"
            hide-details
            rounded="lg"
            @update:model-value="fetchAssets"
          ></v-text-field>
        </v-col>

        <v-col cols="12" md="4">
          <v-btn-toggle
            v-model="selectedType"
            density="compact"
            rounded="lg"
            color="primary"
            mandatory
            @update:model-value="fetchAssets"
          >
            <v-btn value="all" size="small">ทั้งหมด</v-btn>
            <v-btn value="character" size="small">ตัวละคร</v-btn>
            <v-btn value="object" size="small">วัตถุ</v-btn>
            <v-btn value="background" size="small">ฉากหลัง</v-btn>
          </v-btn-toggle>
        </v-col>

        <v-col cols="12" md="3">
          <v-select
            v-model="selectedTheme"
            :items="['all', 'school', 'science', 'environment']"
            label="ธีม (Theme)"
            density="compact"
            hide-details
            rounded="lg"
            @update:model-value="fetchAssets"
          ></v-select>
        </v-col>
      </v-row>
    </v-card>

    <!-- Assets Grid -->
    <v-row v-if="assets.length > 0">
      <v-col v-for="a in assets" :key="a.id" cols="12" sm="6" md="4" lg="3">
        <v-card class="border-card rounded-xl overflow-hidden hover-card pa-3 text-center">
          <div class="asset-preview-box rounded-lg pa-4 mb-3 d-flex align-center justify-center bg-grey-lighten-4" style="height: 140px;">
            <img :src="a.file_path" class="max-h-full max-w-full object-contain" />
          </div>

          <div class="font-weight-bold text-subtitle-2 text-slate-800 text-truncate">
            {{ a.name }}
          </div>
          <div class="text-caption text-grey">
            ประเภท: {{ a.type }} &bull; ธีม: {{ a.theme || '-' }}
          </div>
        </v-card>
      </v-col>
    </v-row>

    <div v-else class="text-center pa-12 border-card rounded-xl bg-white">
      <v-icon icon="mdi-image-search-outline" size="48" color="grey" class="mb-2"></v-icon>
      <div class="text-subtitle-1 text-slate-800">ไม่พบ Asset ที่ค้นหา</div>
      <p class="text-caption text-grey">ลองเปลี่ยนคำค้นหาหรืออัปโหลด Asset ใหม่</p>
    </div>

    <!-- Upload Dialog -->
    <v-dialog v-model="showUploadDialog" max-width="500">
      <v-card class="pa-6 rounded-xl">
        <h2 class="text-h6 font-weight-bold mb-4">อัปโหลด Asset เข้าสู่ระบบ</h2>
        <v-form @submit.prevent="handleUpload">
          <v-text-field
            v-model="uploadName"
            label="ชื่อ Asset *"
            required
            class="mb-3"
          ></v-text-field>

          <v-select
            v-model="uploadType"
            :items="['character', 'object', 'background', 'ui', 'icon']"
            label="ประเภท Asset *"
            required
            class="mb-3"
          ></v-select>

          <v-file-input
            v-model="uploadFile"
            label="เลือกไฟล์รูปภาพ (PNG, JPG, SVG, WebP) *"
            accept="image/*"
            prepend-icon=""
            prepend-inner-icon="mdi-paperclip"
            required
            class="mb-4"
          ></v-file-input>

          <div class="d-flex justify-end gap-2">
            <v-btn variant="text" rounded="lg" @click="showUploadDialog = false">ยกเลิก</v-btn>
            <v-btn color="primary" rounded="lg" type="submit" :loading="uploading">อัปโหลด</v-btn>
          </div>
        </v-form>
      </v-card>
    </v-dialog>
  </div>
</template>

<script setup lang="ts">
import { ref, onMounted } from 'vue'
import apiClient from '@/api/client'
import type { Asset } from '@/types'

const assets = ref<Asset[]>([])
const searchQuery = ref('')
const selectedType = ref('all')
const selectedTheme = ref('all')

const showUploadDialog = ref(false)
const uploadName = ref('')
const uploadType = ref('character')
const uploadFile = ref<File | null>(null)
const uploading = ref(false)

async function fetchAssets() {
  const params: any = {}
  if (selectedType.value !== 'all') params.type = selectedType.value
  if (selectedTheme.value !== 'all') params.theme = selectedTheme.value
  if (searchQuery.value) params.search = searchQuery.value

  const { data } = await apiClient.get('/assets', { params })
  assets.value = data.data || data
}

async function handleUpload() {
  if (!uploadName.value || !uploadFile.value) return
  uploading.value = true

  const formData = new FormData()
  formData.append('name', uploadName.value)
  formData.append('type', uploadType.value)
  formData.append('file', uploadFile.value)

  try {
    await apiClient.post('/assets/upload', formData, {
      headers: { 'Content-Type': 'multipart/form-data' },
    })
    showUploadDialog.value = false
    uploadName.value = ''
    uploadFile.value = null
    await fetchAssets()
  } finally {
    uploading.value = false
  }
}

onMounted(fetchAssets)
</script>

<style scoped>
.gap-2 { gap: 8px; }
.hover-card {
  transition: transform 0.2s ease;
}
.hover-card:hover {
  transform: translateY(-2px);
}
.max-h-full { max-height: 100%; }
.max-w-full { max-width: 100%; }
.object-contain { object-fit: contain; }
</style>
