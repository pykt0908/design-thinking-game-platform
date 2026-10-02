import { defineStore } from 'pinia'
import { ref } from 'vue'

export interface DialogOptions {
  title?: string
  message: string
  type?: 'success' | 'error' | 'warning' | 'info'
  confirmText?: string
  cancelText?: string
  isConfirm?: boolean
}

export const useAlertStore = defineStore('alert', () => {
  // Snackbar Toast
  const snackbar = ref(false)
  const snackbarText = ref('')
  const snackbarColor = ref('success')
  const snackbarTimeout = ref(3500)
  const snackbarIcon = ref('mdi-check-circle')

  // Modal Dialog Alert / Confirm
  const dialog = ref(false)
  const dialogTitle = ref('แจ้งเตือน')
  const dialogMessage = ref('')
  const dialogType = ref<'success' | 'error' | 'warning' | 'info'>('info')
  const dialogConfirmText = ref('ตกลง')
  const dialogCancelText = ref('ยกเลิก')
  const isConfirmDialog = ref(false)

  let confirmResolver: ((value: boolean) => void) | null = null

  function toast(message: string, color: 'success' | 'error' | 'warning' | 'info' = 'success') {
    snackbarText.value = message
    snackbarColor.value = color === 'error' ? 'error' : color === 'warning' ? 'warning' : color === 'info' ? 'primary' : 'success'
    snackbarIcon.value = color === 'error' ? 'mdi-alert-circle' : color === 'warning' ? 'mdi-alert' : color === 'info' ? 'mdi-information' : 'mdi-check-circle'
    snackbar.value = true
  }

  function showAlert(options: DialogOptions | string) {
    isConfirmDialog.value = false
    confirmResolver = null
    if (typeof options === 'string') {
      dialogTitle.value = 'แจ้งเตือน'
      dialogMessage.value = options
      dialogType.value = 'info'
      dialogConfirmText.value = 'ตกลง'
    } else {
      dialogTitle.value = options.title || (options.type === 'error' ? 'เกิดข้อผิดพลาด' : options.type === 'success' ? 'สำเร็จ' : 'แจ้งเตือน')
      dialogMessage.value = options.message
      dialogType.value = options.type || 'info'
      dialogConfirmText.value = options.confirmText || 'ตกลง'
    }
    dialog.value = true
  }

  function confirm(
    message: string,
    title = 'ยืนยันการดำเนินการ',
    options?: { type?: 'success' | 'error' | 'warning' | 'info'; confirmText?: string; cancelText?: string }
  ): Promise<boolean> {
    isConfirmDialog.value = true
    dialogTitle.value = title
    dialogMessage.value = message
    dialogType.value = options?.type || 'warning'
    dialogConfirmText.value = options?.confirmText || 'ตกลง'
    dialogCancelText.value = options?.cancelText || 'ยกเลิก'
    dialog.value = true

    return new Promise((resolve) => {
      confirmResolver = resolve
    })
  }

  function onConfirm() {
    dialog.value = false
    if (confirmResolver) {
      confirmResolver(true)
      confirmResolver = null
    }
  }

  function onCancel() {
    dialog.value = false
    if (confirmResolver) {
      confirmResolver(false)
      confirmResolver = null
    }
  }

  function success(message: string, title = 'สำเร็จ') {
    showAlert({ title, message, type: 'success' })
  }

  function error(message: string, title = 'เกิดข้อผิดพลาด') {
    showAlert({ title, message, type: 'error' })
  }

  function warning(message: string, title = 'ข้อควรระวัง') {
    showAlert({ title, message, type: 'warning' })
  }

  function info(message: string, title = 'แจ้งเตือน') {
    showAlert({ title, message, type: 'info' })
  }

  return {
    snackbar,
    snackbarText,
    snackbarColor,
    snackbarTimeout,
    snackbarIcon,
    dialog,
    dialogTitle,
    dialogMessage,
    dialogType,
    dialogConfirmText,
    dialogCancelText,
    isConfirmDialog,
    toast,
    showAlert,
    confirm,
    onConfirm,
    onCancel,
    success,
    error,
    warning,
    info,
  }
})
