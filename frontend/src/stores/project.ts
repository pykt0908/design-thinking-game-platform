import { defineStore } from 'pinia'
import { ref } from 'vue'
import type { DesignProject } from '@/types'
import apiClient from '@/api/client'

export const useProjectStore = defineStore('project', () => {
  const currentProject = ref<DesignProject | null>(null)
  const saveStatus = ref<'idle' | 'saving' | 'saved' | 'error'>('idle')
  const lastSavedAt = ref<string | null>(null)
  let debounceTimeout: any = null

  async function loadProject(id: number) {
    const { data } = await apiClient.get(`/projects/${id}`)
    currentProject.value = data
    return data
  }

  function queueAutoSave(step: string, data: any) {
    saveStatus.value = 'saving'
    if (debounceTimeout) clearTimeout(debounceTimeout)

    debounceTimeout = setTimeout(async () => {
      if (!currentProject.value) return
      try {
        const response = await apiClient.put(`/projects/${currentProject.value.id}/step/${step}`, { data })
        saveStatus.value = 'saved'
        lastSavedAt.value = response.data.updated_at
        setTimeout(() => {
          if (saveStatus.value === 'saved') saveStatus.value = 'idle'
        }, 3000)
      } catch (err) {
        saveStatus.value = 'error'
      }
    }, 1000) // 1000ms debounce per spec Section 39
  }

  return {
    currentProject,
    saveStatus,
    lastSavedAt,
    loadProject,
    queueAutoSave,
  }
})
