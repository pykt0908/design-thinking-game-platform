<template>
  <div class="max-w-3xl mx-auto py-4">
    <div class="mb-6">
      <h1 class="text-h5 font-weight-bold text-slate-800">ตั้งค่า AI Provider (Teacher AI Settings)</h1>
      <p class="text-body-2 text-grey">กำหนด API Key ของคุณเองสำหรับการสร้างและวิเคราะห์เกมการเรียนรู้</p>
    </div>

    <!-- Security Info Card per spec Section 23 -->
    <v-alert variant="tonal" color="primary" class="mb-6 rounded-xl">
      <div class="d-flex align-center">
        <v-icon icon="mdi-shield-lock-outline" class="mr-3" size="24"></v-icon>
        <div class="text-caption">
          <strong>ความปลอดภัยสูง:</strong> API Key ของคุณจะถูกเข้ารหัสผ่าน AES-256 ในฐานข้อมูล และจะไม่ถูกส่งกลับมายังหน้าเว็บเด็ดขาด
        </div>
      </div>
    </v-alert>

    <v-card class="pa-6 border-card rounded-xl mb-6">
      <h2 class="text-h6 font-weight-bold text-slate-800 mb-4">กำหนดค่า AI Service</h2>

      <v-form @submit.prevent="handleSave">
        <v-select
          v-model="provider"
          :items="[
            { title: 'Google Gemini (แนะนำ)', value: 'gemini' },
            { title: 'OpenAI (GPT-4o / GPT-4o-mini)', value: 'openai' },
            { title: 'Anthropic Claude', value: 'anthropic' },
            { title: 'OpenAI-Compatible Custom API', value: 'custom' },
          ]"
          label="AI Provider *"
          class="mb-3"
        ></v-select>

        <v-text-field
          v-model="model"
          label="ชื่อ Model"
          placeholder="เช่น gemini-1.5-flash, gpt-4o-mini"
          class="mb-3"
        ></v-text-field>

        <v-text-field
          v-model="apiKey"
          label="API Key *"
          type="password"
          :placeholder="existingMaskedKey || 'กรอก API Key ใหม่ของคุณที่นี่'"
          prepend-inner-icon="mdi-key-outline"
          class="mb-3"
        ></v-text-field>

        <div v-if="existingMaskedKey" class="text-caption text-grey mb-4">
          คีย์ปัจจุบันที่ใช้งานอยู่: <code>{{ existingMaskedKey }}</code>
        </div>

        <div class="d-flex justify-space-between align-center border-t pt-4">
          <!-- Test Connection button per spec Section 22 -->
          <v-btn
            variant="outlined"
            color="primary"
            rounded="lg"
            prepend-icon="mdi-connection"
            :loading="testing"
            @click="testConnection"
          >
            ทดสอบการเชื่อมต่อ (Test Connection)
          </v-btn>

          <v-btn
            type="submit"
            class="ai-gradient-bg text-white font-weight-bold px-6"
            rounded="lg"
            :loading="saving"
          >
            บันทึกการตั้งค่า
          </v-btn>
        </div>
      </v-form>
    </v-card>
  </div>
</template>

<script setup lang="ts">
import { ref, onMounted } from 'vue'
import apiClient from '@/api/client'

const provider = ref('gemini')
const model = ref('gemini-1.5-flash')
const apiKey = ref('')
const existingMaskedKey = ref('')
const testing = ref(false)
const saving = ref(false)

async function loadCredentials() {
  try {
    const { data } = await apiClient.get('/teacher/ai-credentials')
    if (data.length > 0) {
      const cred = data[0]
      provider.value = cred.provider
      model.value = cred.model || ''
      existingMaskedKey.value = cred.masked_key || ''
    }
  } catch (err) {
    console.error('Failed to load AI credentials', err)
  }
}

async function testConnection() {
  testing.value = true
  try {
    const { data } = await apiClient.post('/teacher/ai-credentials/test', {
      provider: provider.value,
    })
    alert(`${data.message} (ความเร็ว: ${data.latency_ms}ms)`)
  } catch (err: any) {
    alert('เชื่อมต่อไม่สำเร็จ กรุณาตรวจสอบ API Key')
  } finally {
    testing.value = false
  }
}

async function handleSave() {
  if (!apiKey.value && !existingMaskedKey.value) {
    alert('กรุณากรอก API Key')
    return
  }

  saving.value = true
  try {
    await apiClient.post('/teacher/ai-credentials', {
      provider: provider.value,
      model: model.value,
      api_key: apiKey.value || 'existing',
    })
    alert('บันทึกข้อมูล AI Provider สำเร็จ!')
    apiKey.value = ''
    await loadCredentials()
  } catch (err) {
    console.error('Failed to save AI credentials', err)
  } finally {
    saving.value = false
  }
}

onMounted(loadCredentials)
</script>

<style scoped>
.max-w-3xl { max-width: 768px; }
</style>
