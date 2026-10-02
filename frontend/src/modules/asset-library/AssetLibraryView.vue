<template>
  <div>
    <!-- Page Header -->
    <div class="d-flex flex-wrap justify-space-between align-center gap-3 mb-6">
      <div>
        <h1 class="text-h5 font-weight-bold text-slate-800 d-flex align-center">
          <v-icon icon="mdi-folder-image" color="primary" class="mr-2"></v-icon>
          คลังสื่อและรูปภาพ (Asset Library)
        </h1>
        <p class="text-body-2 text-grey">รวบรวมตัวละคร ฉากหลัง วัตถุ และไอคอนสำหรับใช้ในเกมการเรียนรู้</p>
      </div>

      <v-btn
        color="primary"
        prepend-icon="mdi-upload"
        rounded="lg"
        class="font-weight-bold px-5 elevation-2"
        @click="showUploadDialog = true"
      >
        + อัปโหลด Asset
      </v-btn>
    </div>

    <!-- Filters per spec Section 23 -->
    <v-card class="pa-4 mb-6 border-card rounded-xl bg-white elevation-1">
      <!-- Pack Chips -->
      <div class="d-flex align-center mb-3 flex-wrap gap-1">
        <span class="text-caption font-weight-bold text-slate-700 mr-2">คลังแพ็กเกจ Kenney:</span>
        <v-chip
          v-for="pack in kenneyPacks"
          :key="pack.slug"
          size="small"
          :color="selectedPack === pack.slug ? 'primary' : undefined"
          :variant="selectedPack === pack.slug ? 'flat' : 'outlined'"
          class="cursor-pointer font-weight-medium"
          @click="selectPack(pack.slug)"
        >
          {{ pack.name }}
        </v-chip>
      </div>

      <v-row align="center">
        <v-col cols="12" md="5">
          <v-text-field
            v-model="searchQuery"
            label="ค้นหา Asset..."
            prepend-inner-icon="mdi-magnify"
            density="compact"
            hide-details
            rounded="lg"
            variant="outlined"
            @update:model-value="fetchAssets(1)"
          ></v-text-field>
        </v-col>

        <v-col cols="12" md="7">
          <v-btn-toggle
            v-model="selectedType"
            density="compact"
            rounded="lg"
            color="primary"
            mandatory
            class="flex-wrap"
            @update:model-value="fetchAssets(1)"
          >
            <v-btn value="all" size="small">ทั้งหมด</v-btn>
            <v-btn value="character" size="small">👤 ตัวละคร</v-btn>
            <v-btn value="item" size="small">🥕 ไอเทม/อาหาร</v-btn>
            <v-btn value="object" size="small">📦 วัตถุ/ยาน</v-btn>
            <v-btn value="background" size="small">🧱 ฉาก/พื้นดิน</v-btn>
            <v-btn value="ui" size="small">🎮 ปุ่ม/UI</v-btn>
          </v-btn-toggle>
        </v-col>
      </v-row>
    </v-card>

    <!-- Assets Grid (5 Columns on Desktop) -->
    <div v-if="assets.length > 0">
      <div class="asset-grid-5 mb-6">
        <div v-for="a in assets" :key="a.id" class="asset-grid-item">
          <v-card class="border-card rounded-2xl overflow-hidden hover-card pa-3 text-center h-100 d-flex flex-column justify-space-between bg-white elevation-1">
            <div>
              <div
                class="asset-preview-box rounded-xl pa-3 mb-3 d-flex align-center justify-center bg-slate-50 border cursor-pointer position-relative overflow-hidden"
                style="height: 120px;"
                @click="previewAsset(a)"
              >
                <img :src="a.file_path" :alt="a.name" class="asset-img object-contain pixel-art-img" style="max-height: 70px; max-width: 70px;" />
                <div class="preview-overlay d-flex align-center justify-center">
                  <v-icon icon="mdi-magnify-plus" color="white" size="24"></v-icon>
                </div>
              </div>

              <div class="font-weight-bold text-subtitle-2 text-slate-800 text-truncate mb-1" :title="a.name">
                {{ a.name }}
              </div>
            </div>

            <div class="d-flex align-center justify-center gap-1 mt-2 flex-wrap">
              <v-chip size="x-small" color="primary" variant="tonal" class="font-weight-medium">
                {{ formatType(a.type) }}
              </v-chip>
              <v-chip v-if="a.license" size="x-small" color="teal" variant="outlined" class="font-weight-medium">
                CC0
              </v-chip>
            </div>
          </v-card>
        </div>
      </div>

      <!-- Pagination -->
      <div v-if="totalPages > 1" class="d-flex justify-center my-4">
        <v-pagination
          v-model="currentPage"
          :length="totalPages"
          rounded="circle"
          color="primary"
          @update:model-value="fetchAssets"
        ></v-pagination>
      </div>
    </div>

    <!-- Empty State -->
    <div v-else class="text-center pa-12 border-card rounded-2xl bg-white">
      <v-avatar size="64" color="purple-lighten-5" class="mb-3">
        <v-icon icon="mdi-image-search-outline" size="36" color="primary"></v-icon>
      </v-avatar>
      <div class="text-subtitle-1 font-weight-bold text-slate-800 mb-1">ไม่พบ Asset ที่ค้นหา</div>
      <p class="text-caption text-grey mb-4">ลองเปลี่ยนคำค้นหาหรือเลือกดูแพ็กเกจ Kenney 2D ด้านบน</p>
      <v-btn color="primary" rounded="lg" prepend-icon="mdi-refresh" @click="resetFilters">
        รีเซ็ตการค้นหา
      </v-btn>
    </div>

    <!-- Image Preview Modal -->
    <v-dialog v-model="showPreviewModal" max-width="500">
      <v-card v-if="selectedAsset" class="rounded-2xl pa-4 text-center bg-white">
        <div class="d-flex justify-space-between align-center mb-3">
          <span class="font-weight-bold text-subtitle-1 text-slate-900">{{ selectedAsset.name }}</span>
          <v-btn icon="mdi-close" variant="text" size="small" @click="showPreviewModal = false"></v-btn>
        </div>
        <div class="pa-4 bg-slate-100 rounded-xl mb-3 d-flex align-center justify-center" style="max-height: 380px;">
          <img :src="selectedAsset.file_path" class="max-h-full max-w-full object-contain" />
        </div>
        <div class="text-caption text-grey">
          ประเภท: {{ formatType(selectedAsset.type) }} &bull; ธีม: {{ selectedAsset.theme || '-' }}
        </div>
      </v-card>
    </v-dialog>

    <!-- Upload Dialog -->
    <v-dialog v-model="showUploadDialog" max-width="500">
      <v-card class="pa-6 rounded-2xl">
        <div class="d-flex justify-space-between align-center mb-4">
          <h2 class="text-h6 font-weight-bold">อัปโหลด Asset เข้าสู่ระบบ</h2>
          <v-btn icon="mdi-close" variant="text" size="small" @click="showUploadDialog = false"></v-btn>
        </div>
        <v-form @submit.prevent="handleUpload">
          <v-text-field
            v-model="uploadName"
            label="ชื่อ Asset *"
            placeholder="เช่น หุ่นยนต์ช่วยสอน, ถ้ำผลึกแก้ว"
            required
            variant="outlined"
            density="comfortable"
            rounded="lg"
            class="mb-3"
          ></v-text-field>

          <v-select
            v-model="uploadType"
            :items="[
              { title: 'ตัวละคร (Character)', value: 'character' },
              { title: 'วัตถุ (Object)', value: 'object' },
              { title: 'ฉากหลัง (Background)', value: 'background' },
              { title: 'ไอคอน & UI', value: 'icon' },
            ]"
            item-title="title"
            item-value="value"
            label="ประเภท Asset *"
            required
            variant="outlined"
            density="comfortable"
            rounded="lg"
            class="mb-3"
          ></v-select>

          <v-file-input
            v-model="uploadFile"
            label="เลือกไฟล์รูปภาพ (PNG, JPG, SVG, WebP) *"
            accept="image/*"
            prepend-icon=""
            prepend-inner-icon="mdi-paperclip"
            required
            variant="outlined"
            density="comfortable"
            rounded="lg"
            class="mb-4"
          ></v-file-input>

          <div class="d-flex justify-end gap-2">
            <v-btn variant="text" rounded="lg" @click="showUploadDialog = false">ยกเลิก</v-btn>
            <v-btn color="primary" rounded="lg" type="submit" :loading="uploading" class="font-weight-bold px-5">
              อัปโหลด
            </v-btn>
          </div>
        </v-form>
      </v-card>
    </v-dialog>
  </div>
</template>

<script setup lang="ts">
import { ref, onMounted } from 'vue'
import apiClient from '@/api/client'
import { useAlertStore } from '@/stores/alert'
import type { Asset } from '@/types'

const alertStore = useAlertStore()

const assets = ref<Asset[]>([])
const searchQuery = ref('')
const selectedType = ref('all')
const selectedPack = ref('all')
const currentPage = ref(1)
const totalPages = ref(1)

const kenneyPacks = [
  { slug: 'all', name: '🌟 ทั้งหมด' },
  { slug: 'kenney-tiny-dungeon', name: '⚔️ Tiny Dungeon' },
  { slug: 'kenney-pixel-platformer-food', name: '🥕 Food & Crops' },
  { slug: 'kenney-pixel-platformer', name: '🎮 Pixel Platformer' },
  { slug: 'kenney-tiny-town', name: '🏘️ Tiny Town' },
  { slug: 'kenney-simple-space', name: '🚀 Simple Space' },
  { slug: 'kenney-ui-pack', name: '🎨 UI Pack' },
]

function selectPack(slug: string) {
  selectedPack.value = slug
  fetchAssets(1)
}

function resetFilters() {
  selectedPack.value = 'all'
  selectedType.value = 'all'
  searchQuery.value = ''
  fetchAssets(1)
}

const showUploadDialog = ref(false)
const uploadName = ref('')
const uploadType = ref('character')
const uploadFile = ref<File | null>(null)
const uploading = ref(false)

const showPreviewModal = ref(false)
const selectedAsset = ref<Asset | null>(null)

function previewAsset(a: Asset) {
  selectedAsset.value = a
  showPreviewModal.value = true
}

function formatType(type: string) {
  switch (type) {
    case 'character': return 'ตัวละคร'
    case 'item': return 'ไอเทม/อาหาร'
    case 'object': return 'วัตถุ/ยาน'
    case 'background': return 'ฉากหลัง'
    case 'ui': return 'UI'
    case 'icon': return 'ไอคอน'
    default: return type
  }
}

async function fetchAssets(page = 1) {
  currentPage.value = page
  const params: any = { page, per_page: 30 }
  if (selectedType.value !== 'all') params.type = selectedType.value
  if (selectedPack.value !== 'all') params.pack = selectedPack.value
  if (searchQuery.value) params.search = searchQuery.value

  try {
    const { data } = await apiClient.get('/assets', { params })
    assets.value = data.data || data
    if (data.last_page) {
      totalPages.value = data.last_page
    } else {
      totalPages.value = 1
    }
  } catch (err) {
    console.error('Failed to fetch assets', err)
  }
}

async function handleUpload() {
  if (!uploadName.value || !uploadFile.value) return
  uploading.value = true

  const formData = new FormData()
  formData.append('name', uploadName.value)
  formData.append('type', uploadType.value)
  const fileToUpload = Array.isArray(uploadFile.value) ? uploadFile.value[0] : uploadFile.value
  formData.append('file', fileToUpload)

  try {
    await apiClient.post('/assets/upload', formData, {
      headers: { 'Content-Type': 'multipart/form-data' },
    })
    showUploadDialog.value = false
    uploadName.value = ''
    uploadFile.value = null
    alertStore.success('อัปโหลด Asset เข้าสู่คลังเรียบร้อยแล้ว!', 'สำเร็จ')
    await fetchAssets()
  } catch (err: any) {
    alertStore.error(err.response?.data?.message || 'ไม่สามารถอัปโหลด Asset ได้', 'เกิดข้อผิดพลาด')
  } finally {
    uploading.value = false
  }
}

onMounted(fetchAssets)
</script>

<style scoped>
.gap-1 { gap: 4px; }
.gap-2 { gap: 8px; }
.gap-3 { gap: 12px; }

/* 5 Column Grid System */
.asset-grid-5 {
  display: grid;
  grid-template-columns: repeat(5, minmax(0, 1fr));
  gap: 16px;
}

@media (max-width: 1279px) {
  .asset-grid-5 {
    grid-template-columns: repeat(4, minmax(0, 1fr));
  }
}

@media (max-width: 960px) {
  .asset-grid-5 {
    grid-template-columns: repeat(3, minmax(0, 1fr));
  }
}

@media (max-width: 600px) {
  .asset-grid-5 {
    grid-template-columns: repeat(2, minmax(0, 1fr));
    gap: 12px;
  }
}

@media (max-width: 420px) {
  .asset-grid-5 {
    grid-template-columns: 1fr;
  }
}

.hover-card {
  transition: transform 0.25s cubic-bezier(0.16, 1, 0.3, 1), box-shadow 0.25s ease;
  border-color: #edd4f8 !important;
}

.hover-card:hover {
  transform: translateY(-4px);
  box-shadow: 0 12px 24px -6px rgba(61, 0, 102, 0.15) !important;
}

.asset-preview-box {
  background-color: #f8fafc;
  background-image: 
    linear-gradient(45deg, #f1f5f9 25%, transparent 25%), 
    linear-gradient(-45deg, #f1f5f9 25%, transparent 25%), 
    linear-gradient(45deg, transparent 75%, #f1f5f9 75%), 
    linear-gradient(-45deg, transparent 75%, #f1f5f9 75%);
  background-size: 16px 16px;
  background-position: 0 0, 0 8px, 8px -8px, -8px 0px;
}

.asset-img {
  max-height: 100%;
  max-width: 100%;
  transition: transform 0.3s ease;
}

.pixel-art-img {
  image-rendering: pixelated;
  image-rendering: -moz-crisp-edges;
  image-rendering: crisp-edges;
}

.hover-card:hover .asset-img {
  transform: scale(1.08);
}

.preview-overlay {
  position: absolute;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background: rgba(61, 0, 102, 0.4);
  opacity: 0;
  transition: opacity 0.2s ease;
  border-radius: 12px;
}

.hover-card:hover .preview-overlay {
  opacity: 1;
}

.cursor-pointer {
  cursor: pointer;
}

.object-contain {
  object-fit: contain;
}

.max-h-full {
  max-height: 100%;
}

.max-w-full {
  max-width: 100%;
}
</style>
