import { defineStore } from 'pinia'
import { ref, computed } from 'vue'
import type { User } from '@/types'
import apiClient from '@/api/client'

export const useAuthStore = defineStore('auth', () => {
  const user = ref<User | null>(
    localStorage.getItem('dtg_user') ? JSON.parse(localStorage.getItem('dtg_user')!) : null
  )
  const token = ref<string | null>(localStorage.getItem('dtg_token'))
  const loading = ref(false)

  const isAuthenticated = computed(() => !!token.value && !!user.value)
  const isTeacher = computed(() => user.value?.role === 'teacher' || user.value?.role === 'admin')
  const isStudent = computed(() => user.value?.role === 'student')
  const isAdmin = computed(() => user.value?.role === 'admin')

  async function login(credentials: { login: string; password: string }) {
    loading.value = true
    try {
      const { data } = await apiClient.post('/auth/login', credentials)
      token.value = data.token
      user.value = data.user
      localStorage.setItem('dtg_token', data.token)
      localStorage.setItem('dtg_user', JSON.stringify(data.user))
      return data
    } finally {
      loading.value = false
    }
  }

  async function register(payload: any) {
    loading.value = true
    try {
      const { data } = await apiClient.post('/auth/register', payload)
      token.value = data.token
      user.value = data.user
      localStorage.setItem('dtg_token', data.token)
      localStorage.setItem('dtg_user', JSON.stringify(data.user))
      return data
    } finally {
      loading.value = false
    }
  }

  async function fetchMe() {
    if (!token.value) return null
    try {
      const { data } = await apiClient.get('/auth/me')
      user.value = data.user
      localStorage.setItem('dtg_user', JSON.stringify(data.user))
      return data.user
    } catch {
      logout()
      return null
    }
  }

  function logout() {
    if (token.value) {
      apiClient.post('/auth/logout').catch(() => {})
    }
    user.value = null
    token.value = null
    localStorage.removeItem('dtg_token')
    localStorage.removeItem('dtg_user')
  }

  return {
    user,
    token,
    loading,
    isAuthenticated,
    isTeacher,
    isStudent,
    isAdmin,
    login,
    register,
    fetchMe,
    logout,
  }
})
