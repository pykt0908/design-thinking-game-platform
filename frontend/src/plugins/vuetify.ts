import 'vuetify/styles'
import '@mdi/font/css/materialdesignicons.css'
import { createVuetify } from 'vuetify'

const dtTheme = {
  dark: false,
  colors: {
    primary: '#6366F1',
    'primary-darken-1': '#4F46E5',
    secondary: '#8B5CF6',
    'secondary-darken-1': '#7C3AED',
    accent: '#06B6D4',
    success: '#22C55E',
    warning: '#F59E0B',
    error: '#EF4444',
    background: '#F8FAFC',
    surface: '#FFFFFF',
    'surface-variant': '#F1F5F9',
    'on-surface-variant': '#64748B',
  },
}

export default createVuetify({
  theme: {
    defaultTheme: 'dtTheme',
    themes: {
      dtTheme,
    },
  },
  defaults: {
    VCard: {
      elevation: 0,
      rounded: 'lg',
      class: 'border-card',
    },
    VBtn: {
      rounded: 'lg',
      elevation: 0,
      style: 'text-transform: none; font-weight: 500;',
    },
    VTextField: {
      variant: 'outlined',
      density: 'comfortable',
      rounded: 'lg',
    },
    VTextarea: {
      variant: 'outlined',
      density: 'comfortable',
      rounded: 'lg',
    },
    VSelect: {
      variant: 'outlined',
      density: 'comfortable',
      rounded: 'lg',
    },
  },
})
