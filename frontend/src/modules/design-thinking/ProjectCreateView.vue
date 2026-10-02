<template>
  <div class="max-w-3xl mx-auto py-6">
    <v-card class="pa-8 border-card rounded-xl">
      <div class="d-flex align-center mb-6">
        <v-btn icon="mdi-arrow-left" variant="text" to="/projects" class="mr-3"></v-btn>
        <div>
          <h1 class="text-h5 font-weight-bold text-slate-800">สร้าง Design Thinking Project ใหม่</h1>
          <p class="text-body-2 text-grey">เริ่มต้นกระบวนการคิดเชิงออกแบบเพื่อสร้างเกมการเรียนรู้</p>
        </div>
      </div>

      <v-form @submit.prevent="handleCreate">
        <v-text-field
          v-model="title"
          label="ชื่อโปรเจกต์ / หัวข้อบทเรียน *"
          placeholder="เช่น ภารกิจโรงเรียนไร้ขยะ (School Waste Sorting Adventure)"
          required
          class="mb-3"
        ></v-text-field>

        <v-row>
          <v-col cols="12" sm="6">
            <v-text-field
              v-model="subject"
              label="กลุ่มสาระ / วิชา *"
              placeholder="เช่น วิทยาศาสตร์และเทคโนโลยี"
              required
            ></v-text-field>
          </v-col>
          <v-col cols="12" sm="6">
            <v-select
              v-model="gradeLevel"
              :items="['ประถมศึกษาตอนต้น', 'ประถมศึกษาตอนปลาย', 'มัธยมศึกษาปีที่ 1', 'มัธยมศึกษาปีที่ 2', 'มัธยมศึกษาปีที่ 3', 'มัธยมศึกษาตอนปลาย', 'อุดมศึกษา / บุคคลทั่วไป']"
              label="ระดับชั้นเป้าหมาย *"
              required
            ></v-select>
          </v-col>
        </v-row>

        <v-textarea
          v-model="description"
          label="คำอธิบายหรือเป้าหมายเบื้องต้น"
          placeholder="ระบุปัญหาในการเรียนรู้หรือสิ่งที่อยากให้นักเรียนได้รับจากการเล่นเกมนี้..."
          rows="3"
          class="mb-4"
        ></v-textarea>

        <div class="d-flex justify-end gap-3">
          <v-btn to="/projects" variant="text" rounded="lg">ยกเลิก</v-btn>
          <v-btn
            type="submit"
            class="ai-gradient-bg text-white font-weight-bold px-6"
            rounded="lg"
            :loading="loading"
          >
            สร้างและเริ่มขั้นตอนที่ 1 (Empathize) &rarr;
          </v-btn>
        </div>
      </v-form>
    </v-card>
  </div>
</template>

<script setup lang="ts">
import { ref } from 'vue'
import { useRouter } from 'vue-router'
import apiClient from '@/api/client'

const router = useRouter()
const title = ref('')
const subject = ref('')
const gradeLevel = ref('มัธยมศึกษาปีที่ 1')
const description = ref('')
const loading = ref(false)

async function handleCreate() {
  if (!title.value) return
  loading.value = true
  try {
    const { data } = await apiClient.post('/projects', {
      title: title.value,
      subject: subject.value,
      grade_level: gradeLevel.value,
      description: description.value,
    })
    router.push(`/projects/${data.id}`)
  } catch (err) {
    console.error('Failed to create project', err)
  } finally {
    loading.value = false
  }
}
</script>

<style scoped>
.gap-3 {
  gap: 12px;
}
.max-w-3xl {
  max-width: 768px;
}
</style>
