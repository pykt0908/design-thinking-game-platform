<template>
  <div class="max-w-3xl mx-auto py-4">
    <div class="mb-6">
      <h1 class="text-h5 font-weight-bold text-slate-800">ตั้งค่า AI Provider (Teacher AI Settings)</h1>
      <p class="text-body-2 text-grey">กำหนด AI Provider, เลือก Model และ API Key ของคุณเองสำหรับการสร้างและวิเคราะห์เกม</p>
    </div>

    

    <v-card class="pa-6 border-card rounded-xl mb-6">
      <h2 class="text-h6 font-weight-bold text-slate-800 mb-4">กำหนดค่า AI Service</h2>

      <v-form @submit.prevent="handleSave">
        <!-- Provider Selection -->
        <v-select
          v-model="provider"
          :items="[
            { title: 'Google Gemini (แนะนำ)', value: 'gemini' },
            { title: 'OpenAI (ChatGPT)', value: 'openai' },
            { title: 'Anthropic Claude', value: 'anthropic' },
            { title: 'OpenAI-Compatible Custom API (เช่น Ollama, vLLM, DeepSeek)', value: 'custom' },
          ]"
          label="AI Provider *"
          class="mb-3"
          @update:model-value="onProviderChange"
        ></v-select>

        <!-- Base URL for Custom Provider -->
        <v-text-field
          v-if="provider === 'custom'"
          v-model="baseUrl"
          label="Base URL (สำหรับ Custom API) *"
          placeholder="เช่น https://api.deepseek.com/v1 หรือ http://localhost:11434/v1"
          class="mb-3"
        ></v-text-field>

        <!-- Model Selection Dropdown (Combobox: เลือกจากลิสต์ หรือพิมพ์เองได้) -->
        <v-combobox
          v-model="model"
          :items="modelOptions"
          item-title="title"
          item-value="value"
          :return-object="false"
          label="ชื่อ Model (เลือกจากรายการ หรือพิมพ์ระบุเองได้) *"
          placeholder="เลือกหรือพิมพ์ชื่อโมเดล..."
          prepend-inner-icon="mdi-brain"
          class="mb-3"
          hint="สามารถคลิกเลือกจากรายการที่แนะนำ หรือพิมพ์ชื่อโมเดลเฉพาะเจาะจงได้"
          persistent-hint
        >
          <template #item="{ props, item }">
            <v-list-item v-bind="props" :subtitle="(item as any)?.desc || (item as any)?.raw?.desc"></v-list-item>
          </template>
        </v-combobox>

        <!-- API Key Input -->
        <v-text-field
          v-model="apiKey"
          label="API Key *"
          type="password"
          :placeholder="existingMaskedKey || 'กรอก API Key ใหม่ของคุณที่นี่'"
          prepend-inner-icon="mdi-key-outline"
          class="mb-3 mt-2"
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
import { ref, computed, onMounted } from 'vue'
import apiClient from '@/api/client'

const provider = ref('gemini')
const model = ref('gemini-1.5-flash')
const baseUrl = ref('')
const apiKey = ref('')
const existingMaskedKey = ref('')
const testing = ref(false)
const saving = ref(false)

// Curated model list per provider
const modelPresets: Record<string, Array<{ title: string; value: string; desc: string }>> = {
  gemini: [
    { title: 'gemini-1.5-flash (แนะนำ)', value: 'gemini-1.5-flash', desc: 'เร็วมาก คุ้มค่า เหมาะกับการออกแบบเกมแบบเรียลไทม์' },
    { title: 'gemini-1.5-pro', value: 'gemini-1.5-pro', desc: 'ความฉลาดระดับสูง วิเคราะห์เนื้อหาและสรุปผลเชิงลึก' },
    { title: 'gemini-2.0-flash-exp', value: 'gemini-2.0-flash-exp', desc: 'โมเดลรุ่นใหม่ล่าสุด ความเร็วและการคิดขั้นสูง' },
    { title: 'gemini-1.0-pro', value: 'gemini-1.0-pro', desc: 'โมเดลรุ่นมาตรฐาน' },
  ],
  openai: [
    { title: 'gpt-4o-mini (แนะนำ)', value: 'gpt-4o-mini', desc: 'เร็วและประหยัดต้นทุนสูง เหมาะกับงาน Generate ทั่วไป' },
    { title: 'gpt-4o', value: 'gpt-4o', desc: 'โมเดลเรือธง ความสามารถด้านตรรกะและการศึกษาครอบคลุม' },
    { title: 'gpt-4-turbo', value: 'gpt-4-turbo', desc: 'โมเดลความจุบริบทสูง' },
    { title: 'o1-mini', value: 'o1-mini', desc: 'เน้นการคิดเชิงตรรกะและการแก้ปัญหาเชิงซ้อน' },
  ],
  anthropic: [
    { title: 'claude-3-5-sonnet-20241022 (แนะนำ)', value: 'claude-3-5-sonnet-20241022', desc: 'ฉลาดรอบด้าน ให้ภาษาไทยสละสลวย เหมาะกับการศึกษา' },
    { title: 'claude-3-5-haiku-20241022', value: 'claude-3-5-haiku-20241022', desc: 'ตอบสนองรวดเร็วและประหยัด' },
    { title: 'claude-3-opus-20240229', value: 'claude-3-opus-20240229', desc: 'โมเดลประมวลผลขนาดใหญ่สำหรับงานเชิงลึก' },
  ],
  custom: [
    { title: 'deepseek-chat', value: 'deepseek-chat', desc: 'DeepSeek-V3 LLM API' },
    { title: 'llama-3.3-70b-instruct', value: 'llama-3.3-70b-instruct', desc: 'Meta Llama 3.3 70B Open Weights' },
    { title: 'qwen-2.5-72b-instruct', value: 'qwen-2.5-72b-instruct', desc: 'Alibaba Qwen 2.5' },
    { title: 'mistral-large-latest', value: 'mistral-large-latest', desc: 'Mistral Large' },
  ],
}

const modelOptions = computed(() => {
  return modelPresets[provider.value] || modelPresets.gemini
})

function onProviderChange(newProvider: string) {
  const options = modelPresets[newProvider]
  if (options && options.length > 0) {
    model.value = options[0].value
  }
}

async function loadCredentials() {
  try {
    const { data } = await apiClient.get('/teacher/ai-credentials')
    if (data.length > 0) {
      const cred = data[0]
      provider.value = cred.provider
      model.value = cred.model || (modelPresets[cred.provider]?.[0]?.value || 'gemini-1.5-flash')
      baseUrl.value = cred.base_url || ''
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
      model: model.value,
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
      base_url: baseUrl.value || null,
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
