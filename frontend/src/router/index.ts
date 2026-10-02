import { createRouter, createWebHistory, type RouteRecordRaw } from 'vue-router'
import TeacherLayout from '@/layouts/TeacherLayout.vue'
import StudentLayout from '@/layouts/StudentLayout.vue'
import GamePlayerLayout from '@/layouts/GamePlayerLayout.vue'
import LoginView from '@/modules/auth/LoginView.vue'

const routes: RouteRecordRaw[] = [
  {
    path: '/login',
    name: 'Login',
    component: LoginView,
    meta: { guestOnly: true },
  },
  {
    path: '/play/:publicId',
    component: GamePlayerLayout,
    children: [
      {
        path: '',
        name: 'GamePlayer',
        component: () => import('@/modules/game-player/StudentGamePlayerView.vue'),
      },
    ],
  },
  {
    path: '/',
    component: TeacherLayout,
    meta: { requiresAuth: true, requiresTeacher: true },
    children: [
      {
        path: '',
        redirect: '/dashboard',
      },
      {
        path: 'dashboard',
        name: 'TeacherDashboard',
        component: () => import('@/modules/dashboard/TeacherDashboardView.vue'),
      },
      {
        path: 'projects',
        name: 'ProjectsList',
        component: () => import('@/modules/design-thinking/ProjectsListView.vue'),
      },
      {
        path: 'projects/new',
        name: 'ProjectCreate',
        component: () => import('@/modules/design-thinking/ProjectCreateView.vue'),
      },
      {
        path: 'projects/:id',
        name: 'DesignThinkingStudio',
        component: () => import('@/modules/design-thinking/DesignThinkingStudioView.vue'),
      },
      {
        path: 'games',
        name: 'GamesList',
        component: () => import('@/modules/game-builder/GamesListView.vue'),
      },
      {
        path: 'games/:id/edit',
        name: 'GameEditor',
        component: () => import('@/modules/game-builder/GameEditorView.vue'),
      },
      {
        path: 'classrooms',
        name: 'Classrooms',
        component: () => import('@/modules/classroom/ClassroomsView.vue'),
      },
      {
        path: 'assets',
        name: 'AssetLibrary',
        component: () => import('@/modules/asset-library/AssetLibraryView.vue'),
      },
      {
        path: 'analytics',
        name: 'Analytics',
        component: () => import('@/modules/analytics/AnalyticsView.vue'),
      },
      {
        path: 'settings/ai',
        name: 'AiSettings',
        component: () => import('@/modules/settings/AiSettingsView.vue'),
      },
    ],
  },
  {
    path: '/student',
    component: StudentLayout,
    meta: { requiresAuth: true },
    children: [
      {
        path: '',
        redirect: '/student/home',
      },
      {
        path: 'home',
        name: 'StudentHome',
        component: () => import('@/modules/student/StudentHomeView.vue'),
      },
      {
        path: 'classes',
        name: 'StudentClasses',
        component: () => import('@/modules/student/StudentHomeView.vue'),
      },
      {
        path: 'games',
        name: 'StudentGames',
        component: () => import('@/modules/student/StudentHomeView.vue'),
      },
    ],
  },
  {
    path: '/:pathMatch(.*)*',
    redirect: '/login',
  },
]

const router = createRouter({
  history: createWebHistory(),
  routes,
})

router.beforeEach((to, _from, next) => {
  const token = localStorage.getItem('dtg_token')
  const userRaw = localStorage.getItem('dtg_user')
  const user = userRaw ? JSON.parse(userRaw) : null

  if (to.meta.requiresAuth && !token) {
    return next('/login')
  }

  if (to.meta.guestOnly && token) {
    if (user?.role === 'student') return next('/student/home')
    return next('/dashboard')
  }

  if (to.meta.requiresTeacher && user && user.role === 'student') {
    return next('/student/home')
  }

  next()
})

export default router
