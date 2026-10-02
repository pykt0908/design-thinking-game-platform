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

        <!-- Provider Direct Link & Guide Cards -->
        <v-card v-if="provider === 'gemini'" class="pa-4 mb-4 rounded-xl border bg-purple-lighten-5">
          <div class="d-flex flex-column flex-sm-row justify-space-between align-sm-center gap-3">
            <div class="d-flex align-center">
              <v-avatar size="40" color="white" class="mr-3 elevation-1 flex-shrink-0">
                <v-icon icon="mdi-google" color="primary" size="22"></v-icon>
              </v-avatar>
              <div>
                <div class="font-weight-bold text-subtitle-2 text-slate-800">
                  ต้องการสร้าง Project และขอ Google Gemini API Key?
                </div>
                <div class="text-caption text-slate-600">
                  สร้าง Project และขอรับ API Key ฟรีผ่าน Google AI Studio (ใช้งานฟรี ไม่จำเป็นต้องผูกบัตรเครดิต)
                </div>
              </div>
            </div>

            <v-btn
              href="https://aistudio.google.com/app/apikey"
              target="_blank"
              rel="noopener noreferrer"
              color="primary"
              rounded="lg"
              size="small"
              class="font-weight-bold px-4 flex-shrink-0 align-self-start align-self-sm-center elevation-1"
              prepend-icon="mdi-open-in-new"
            >
              เปิด Google AI Studio
            </v-btn>
          </div>
        </v-card>

        <v-card v-else-if="provider === 'openai'" class="pa-4 mb-4 rounded-xl border bg-purple-lighten-5">
          <div class="d-flex flex-column flex-sm-row justify-space-between align-sm-center gap-3">
            <div class="d-flex align-center">
              <v-avatar size="40" color="white" class="mr-3 elevation-1 flex-shrink-0">
                <v-icon icon="mdi-robot" color="secondary" size="22"></v-icon>
              </v-avatar>
              <div>
                <div class="font-weight-bold text-subtitle-2 text-slate-800">
                  ต้องการขอรับ OpenAI API Key?
                </div>
                <div class="text-caption text-slate-600">
                  เปิด OpenAI Developer Platform เพื่อสร้าง Secret Key
                </div>
              </div>
            </div>

            <v-btn
              href="https://platform.openai.com/api-keys"
              target="_blank"
              rel="noopener noreferrer"
              color="secondary"
              rounded="lg"
              size="small"
              class="font-weight-bold px-4 flex-shrink-0 align-self-start align-self-sm-center elevation-1"
              prepend-icon="mdi-open-in-new"
            >
              เปิด OpenAI API Keys
            </v-btn>
          </div>
        </v-card>

        <v-card v-else-if="provider === 'anthropic'" class="pa-4 mb-4 rounded-xl border bg-purple-lighten-5">
          <div class="d-flex flex-column flex-sm-row justify-space-between align-sm-center gap-3">
            <div class="d-flex align-center">
              <v-avatar size="40" color="white" class="mr-3 elevation-1 flex-shrink-0">
                <v-icon icon="mdi-brain" color="primary" size="22"></v-icon>
              </v-avatar>
              <div>
                <div class="font-weight-bold text-subtitle-2 text-slate-800">
                  ต้องการขอรับ Anthropic API Key?
                </div>
                <div class="text-caption text-slate-600">
                  เปิด Anthropic Console เพื่อจัดการ API Key
                </div>
              </div>
            </div>

            <v-btn
              href="https://console.anthropic.com/settings/keys"
              target="_blank"
              rel="noopener noreferrer"
              color="primary"
              rounded="lg"
              size="small"
              class="font-weight-bold px-4 flex-shrink-0 align-self-start align-self-sm-center elevation-1"
              prepend-icon="mdi-open-in-new"
            >
              เปิด Anthropic Console
            </v-btn>
          </div>
        </v-card>

        <!-- API Key Input -->
        <v-text-field
          v-model="apiKey"
          label="API Key *"
          type="password"
          :placeholder="existingMaskedKey ? `คีย์เดิมที่บันทึกแล้ว (${existingMaskedKey}) - พิมพ์ใหม่หากต้องการเปลี่ยน` : 'กรอก API Key ของคุณที่นี่'"
          prepend-inner-icon="mdi-key-outline"
          class="mb-2 mt-2"
          hint="หากมีคีย์บันทึกอยู่ในระบบแล้ว (ตามกล่องสีเขียวด้านล่าง) สามารถเว้นว่างไว้เพื่อใช้คีย์เดิม หรือพิมพ์คีย์ใหม่เพื่ออัปเดต"
          persistent-hint
        >
          <template #append-inner>
            <v-tooltip text="เปิด Google AI Studio เพื่อสร้าง Project และรับ API Key" location="top">
              <template #activator="{ props }">
                <v-btn
                  v-if="provider === 'gemini'"
                  v-bind="props"
                  icon="mdi-open-in-new"
                  variant="text"
                  size="small"
                  color="primary"
                  href="https://aistudio.google.com/app/apikey"
                  target="_blank"
                  rel="noopener noreferrer"
                  title="เปิด Google AI Studio"
                ></v-btn>
              </template>
            </v-tooltip>
          </template>
        </v-text-field>

        <div v-if="provider === 'gemini'" class="text-caption text-primary mb-3 d-flex align-center">
          <v-icon icon="mdi-information-outline" size="14" class="mr-1"></v-icon>
          <span>ยังไม่มี Key? คลิก <a href="https://aistudio.google.com/app/apikey" target="_blank" rel="noopener noreferrer" class="font-weight-bold text-decoration-underline text-primary">สร้าง Project และรับ Key ฟรีที่ Google AI Studio</a></span>
        </div>

        <div v-if="existingMaskedKey" class="d-flex align-center gap-3 mb-4 pa-3 rounded-lg border bg-green-lighten-5">
          <v-icon icon="mdi-check-decagram" color="success" size="24"></v-icon>
          <div class="flex-grow-1">
            <div class="text-caption font-weight-bold text-slate-800">
              สถานะ: บันทึกคีย์ {{ provider.toUpperCase() }} ในระบบแล้ว (<code>{{ existingMaskedKey }}</code>)
            </div>
            <div class="text-caption text-grey-darken-1">
              ระบบเชื่อมต่อและจำคีย์นี้ไว้เรียบร้อยแล้ว พร้อมใช้งานสร้างเกมได้ทันที
            </div>
          </div>
          <v-chip size="small" color="success" variant="flat" class="font-weight-bold">
            <v-icon start icon="mdi-shield-check" size="14"></v-icon> มีคีย์ในระบบ
          </v-chip>
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
import { useAlertStore } from '@/stores/alert'

const alertStore = useAlertStore()
const provider = ref('gemini')
const model = ref('gemini-flash-lite-latest')
const baseUrl = ref('')
const apiKey = ref('')
const existingMaskedKey = ref('')
const testing = ref(false)
const saving = ref(false)
const allCredentials = ref<any[]>([])

// Curated modern model list per provider (2026 updated)
const modelPresets: Record<string, Array<{ title: string; value: string; desc: string }>> = {
  gemini: [
    { title: 'gemini-flash-lite-latest (แนะนำ - เร็วและเสถียรที่สุด)', value: 'gemini-flash-lite-latest', desc: 'ตอบสนองทันที ไม่ติดคิว เหมาะกับการสร้างเกมแบบเรียลไทม์' },
    { title: 'gemini-3.5-flash (ฉลาดรอบด้าน)', value: 'gemini-3.5-flash', desc: 'โมเดลรุ่นใหม่ ความคิดสร้างสรรค์และตรรกะเกมขั้นสูง' },
    { title: 'gemini-3.8-flash (โมเดลเรือธงล่าสุด)', value: 'gemini-3.8-flash', desc: 'โมเดลความเร็วสูงรุ่นล่าสุดสำหรับตรรกะซับซ้อน' },
    { title: 'gemini-2.5-pro (การวิเคราะห์เชิงลึก)', value: 'gemini-2.5-pro', desc: 'โมเดลระดับโปรสำหรับงานวิเคราะห์และสรุปผลเชิงลึก' },
    { title: 'gemini-flash-latest', value: 'gemini-flash-latest', desc: 'โมเดล Flash เวอร์ชั่นอัปเดตล่าสุด' },
  ],
  openai: [
    { title: 'gpt-4o-mini (แนะนำ)', value: 'gpt-4o-mini', desc: 'เร็วและประหยัดต้นทุนสูง เหมาะกับงานสร้างเกมและเนื้อหาทั่วไป' },
    { title: 'gpt-4o', value: 'gpt-4o', desc: 'โมเดลเรือธง ความสามารถด้านตรรกะและการศึกษาครอบคลุม' },
    { title: 'o3-mini', value: 'o3-mini', desc: 'เน้นการคิดเชิงตรรกะและแก้โจทย์โค้ดดิ้งขั้นสูง' },
    { title: 'o1', value: 'o1', desc: 'โมเดลความสามารถการคิดวิเคราะห์เชิงลึกขั้นสุด' },
  ],
  anthropic: [
    { title: 'claude-3-5-sonnet-latest (แนะนำ)', value: 'claude-3-5-sonnet-latest', desc: 'ฉลาดรอบด้าน ให้ภาษาไทยสละสลวย โค้ดเกมแม่นยำ' },
    { title: 'claude-3-5-haiku-latest', value: 'claude-3-5-haiku-latest', desc: 'ตอบสนองรวดเร็วและประหยัด' },
    { title: 'claude-3-opus-latest', value: 'claude-3-opus-latest', desc: 'โมเดลประมวลผลขนาดใหญ่สำหรับงานเชิงลึก' },
  ],
  custom: [
    { title: 'deepseek-chat', value: 'deepseek-chat', desc: 'DeepSeek-V3 LLM API' },
    { title: 'deepseek-reasoner', value: 'deepseek-reasoner', desc: 'DeepSeek-R1 ให้เหตุผลเชิงลึก' },
    { title: 'llama-3.3-70b-instruct', value: 'llama-3.3-70b-instruct', desc: 'Meta Llama 3.3 70B Open Weights' },
    { title: 'qwen-2.5-72b-instruct', value: 'qwen-2.5-72b-instruct', desc: 'Alibaba Qwen 2.5' },
  ],
}

const modelOptions = computed(() => {
  return modelPresets[provider.value] || modelPresets.gemini
})

function applyCredToForm(cred: any) {
  if (cred) {
    model.value = cred.model || (modelPresets[cred.provider]?.[0]?.value || '')
    baseUrl.value = cred.base_url || ''
    existingMaskedKey.value = cred.masked_key || ''
  } else {
    model.value = modelPresets[provider.value]?.[0]?.value || ''
    baseUrl.value = ''
    existingMaskedKey.value = ''
  }
}

function onProviderChange(newProvider: string) {
  provider.value = newProvider
  apiKey.value = ''
  const cred = allCredentials.value.find((c: any) => c.provider === newProvider)
  applyCredToForm(cred)
}

async function loadCredentials(keepSelectedProvider = false) {
  try {
    const { data } = await apiClient.get('/teacher/ai-credentials')
    allCredentials.value = data || []
    if (allCredentials.value.length > 0) {
      let targetCred = null
      if (keepSelectedProvider) {
        targetCred = allCredentials.value.find((c: any) => c.provider === provider.value)
      } else {
        // Prefer active credential
        targetCred = allCredentials.value.find((c: any) => c.is_active) || allCredentials.value[0]
      }
      if (targetCred) {
        provider.value = targetCred.provider
        applyCredToForm(targetCred)
      }
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
      base_url: baseUrl.value || undefined,
      api_key: apiKey.value || undefined,
    })
    alertStore.showAlert({
      title: 'เชื่อมต่อ AI สำเร็จ',
      message: `${data.message} (ความเร็ว: ${data.latency_ms}ms)`,
      type: 'success',
    })
  } catch (err: any) {
    alertStore.showAlert({
      title: 'การเชื่อมต่อล้มเหลว',
      message: err.response?.data?.message || 'เชื่อมต่อไม่สำเร็จ กรุณาตรวจสอบ API Key',
      type: 'error',
    })
  } finally {
    testing.value = false
  }
}

async function handleSave() {
  if (!apiKey.value && !existingMaskedKey.value) {
    alertStore.showAlert({
      title: 'กรุณากรอก API Key',
      message: 'โปรดระบุ API Key เพื่อเปิดใช้งานระบบ AI',
      type: 'warning',
    })
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

    if (apiKey.value && provider.value === 'gemini') {
      localStorage.setItem('user_gemini_api_key', apiKey.value)
    }

    alertStore.showAlert({
      title: 'บันทึกสำเร็จ!',
      message: `ตั้งค่าผู้ให้บริการ ${provider.value.toUpperCase()} เป็น AI หลักของระบบเรียบร้อยแล้ว`,
      type: 'success',
    })
    apiKey.value = ''
    await loadCredentials(true)
  } catch (err: any) {
    console.error('Failed to save AI credentials', err)
    alertStore.showAlert({
      title: 'เกิดข้อผิดพลาด',
      message: err.response?.data?.message || 'เกิดข้อผิดพลาดในการบันทึกข้อมูล AI Provider',
      type: 'error',
    })
  } finally {
    saving.value = false
  }
}

onMounted(() => {
  loadCredentials()
})
</script>

<style scoped>
.max-w-3xl { max-width: 768px; }
</style>
