import 'vuetify/styles'
import '@mdi/font/css/materialdesignicons.css'
import { createVuetify } from 'vuetify'

const dtTheme = {
  dark: false,
  colors: {
    primary: '#3d0066',
    'primary-darken-1': '#290045',
    'primary-lighten-1': '#5a0c91',
    secondary: '#c670ff',
    'secondary-darken-1': '#a941ea',
    'secondary-lighten-1': '#eec7fc',
    accent: '#ffe047',
    'accent-darken-1': '#ffce1f',
    warning: '#ffce1f',
    success: '#22c55e',
    error: '#ef4444',
    background: '#FAF7FD',
    surface: '#FFFFFF',
    'surface-variant': '#FAF4FE',
    'on-surface-variant': '#3d0066',
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
