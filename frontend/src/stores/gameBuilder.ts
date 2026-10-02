import { defineStore } from 'pinia'
import { ref, computed } from 'vue'
import type { Game, GameSchema, GameScene, GameElement } from '@/types'
import apiClient from '@/api/client'

export const useGameBuilderStore = defineStore('gameBuilder', () => {
  const game = ref<Game | null>(null)
  const schema = ref<GameSchema | null>(null)
  const activeSceneId = ref<string>('')
  const selectedElementId = ref<string | null>(null)
  const aspectRatio = ref<'16:9' | '4:3' | '9:16'>('16:9')
  const isSaving = ref(false)
  const saveSuccess = ref(false)
  const undoStack = ref<string[]>([])
  const redoStack = ref<string[]>([])

  const activeScene = computed(() => {
    return schema.value?.scenes.find((s: GameScene) => s.id === activeSceneId.value) || schema.value?.scenes[0] || null
  })

  const selectedElement = computed(() => {
    if (!activeScene.value || !selectedElementId.value) return null
    return activeScene.value.elements.find((el: GameElement) => el.id === selectedElementId.value) || null
  })

  function recordHistory() {
    if (!schema.value) return
    undoStack.value.push(JSON.stringify(schema.value))
    if (undoStack.value.length > 20) undoStack.value.shift()
    redoStack.value = []
  }

  function undo() {
    if (undoStack.value.length === 0 || !schema.value) return
    redoStack.value.push(JSON.stringify(schema.value))
    const prev = undoStack.value.pop()!
    schema.value = JSON.parse(prev)
  }

  function redo() {
    if (redoStack.value.length === 0 || !schema.value) return
    undoStack.value.push(JSON.stringify(schema.value))
    const next = redoStack.value.pop()!
    schema.value = JSON.parse(next)
  }

  async function loadGame(gameId: number) {
    const { data } = await apiClient.get(`/games/${gameId}`)
    game.value = data
    if (data.current_version?.schema_data) {
      const raw = data.current_version.schema_data
      schema.value = typeof raw === 'string' ? JSON.parse(raw) : raw
    } else {
      schema.value = {
        version: '1.0',
        title: data.title,
        description: data.description || '',
        theme: data.theme || 'school',
        settings: { duration: 300, maxAttempts: 3 },
        scenes: [
          {
            id: 'scene_01',
            title: 'ฉากที่ 1',
            elements: [],
          },
        ],
        scoring: { initialScore: 0, maxScore: 100, passingScore: 60 },
        completion: { type: 'mission_complete' },
      }
    }

    if (schema.value?.scenes && schema.value.scenes.length > 0) {
      activeSceneId.value = schema.value.scenes[0].id
    }
  }

  function addScene(title?: string) {
    if (!schema.value) return
    if (!schema.value.scenes) schema.value.scenes = []
    recordHistory()
    const newId = 'scene_' + Date.now().toString(36)
    const newScene: GameScene = {
      id: newId,
      title: title || `ฉากที่ ${schema.value.scenes.length + 1}`,
      elements: [],
    }
    schema.value.scenes.push(newScene)
    activeSceneId.value = newId
  }

  function deleteScene(sceneId: string) {
    if (!schema.value || !schema.value.scenes || schema.value.scenes.length <= 1) return
    recordHistory()
    schema.value.scenes = schema.value.scenes.filter((s: GameScene) => s.id !== sceneId)
    if (activeSceneId.value === sceneId) {
      activeSceneId.value = schema.value.scenes[0].id
    }
  }

  function addElementToActiveScene(element: GameElement) {
    if (!activeScene.value) return
    recordHistory()
    activeScene.value.elements.push(element)
    selectedElementId.value = element.id
  }

  function removeElement(elementId: string) {
    if (!activeScene.value) return
    recordHistory()
    activeScene.value.elements = activeScene.value.elements.filter((el: GameElement) => el.id !== elementId)
    if (selectedElementId.value === elementId) {
      selectedElementId.value = null
    }
  }

  async function saveGame(publishAfter = false) {
    if (!game.value || !schema.value) return
    isSaving.value = true
    try {
      await apiClient.post(`/games/${game.value.id}/save-schema`, {
        schema_data: schema.value,
        title: schema.value.title,
      })
      if (publishAfter) {
        await apiClient.post(`/games/${game.value.id}/publish`)
        game.value.status = 'published'
      }
      saveSuccess.value = true
      setTimeout(() => {
        saveSuccess.value = false
      }, 2500)
    } finally {
      isSaving.value = false
    }
  }

  return {
    game,
    schema,
    activeSceneId,
    activeScene,
    selectedElementId,
    selectedElement,
    aspectRatio,
    isSaving,
    saveSuccess,
    undoStack,
    redoStack,
    loadGame,
    addScene,
    deleteScene,
    addElementToActiveScene,
    removeElement,
    saveGame,
    undo,
    redo,
    recordHistory,
  }
})
