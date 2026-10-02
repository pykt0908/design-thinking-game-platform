<template>
  <div>
    <!-- Global Snackbar Toast -->
    <v-snackbar
      v-model="alertStore.snackbar"
      :color="alertStore.snackbarColor"
      :timeout="alertStore.snackbarTimeout"
      location="top right"
      rounded="lg"
      elevation="4"
    >
      <div class="d-flex align-center font-weight-medium text-white">
        <v-icon :icon="alertStore.snackbarIcon" color="white" class="mr-2" size="20"></v-icon>
        <span>{{ alertStore.snackbarText }}</span>
      </div>

      <template #actions>
        <v-btn
          variant="text"
          icon="mdi-close"
          size="small"
          color="white"
          @click="alertStore.snackbar = false"
        ></v-btn>
      </template>
    </v-snackbar>

    <!-- Global Alert / Confirm Modal Dialog -->
    <v-dialog v-model="alertStore.dialog" max-width="440" persistent>
      <v-card class="pa-6 rounded-2xl text-center border-card elevation-8 bg-white">
        <div class="mb-4">
          <v-avatar
            size="64"
            :color="getBgColor(alertStore.dialogType)"
            class="elevation-1"
          >
            <v-icon
              :icon="getIcon(alertStore.dialogType)"
              :color="getColor(alertStore.dialogType)"
              size="36"
            ></v-icon>
          </v-avatar>
        </div>

        <h3 class="text-h6 font-weight-bold text-slate-900 mb-2">
          {{ alertStore.dialogTitle }}
        </h3>

        <p class="text-body-2 text-slate-600 mb-6" style="white-space: pre-line;">
          {{ alertStore.dialogMessage }}
        </p>

        <!-- Actions for Confirmation Dialog -->
        <div v-if="alertStore.isConfirmDialog" class="d-flex gap-3 justify-center">
          <v-btn
            variant="outlined"
            size="large"
            rounded="lg"
            class="flex-1-1 font-weight-medium text-slate-700"
            @click="alertStore.onCancel()"
          >
            {{ alertStore.dialogCancelText }}
          </v-btn>
          <v-btn
            :color="getColor(alertStore.dialogType)"
            size="large"
            rounded="lg"
            class="flex-1-1 font-weight-bold elevation-1 text-white"
            @click="alertStore.onConfirm()"
          >
            {{ alertStore.dialogConfirmText }}
          </v-btn>
        </div>

        <!-- Actions for Simple Alert Dialog -->
        <v-btn
          v-else
          :color="getColor(alertStore.dialogType)"
          block
          size="large"
          rounded="lg"
          class="font-weight-bold elevation-1 text-white"
          @click="alertStore.dialog = false"
        >
          {{ alertStore.dialogConfirmText }}
        </v-btn>
      </v-card>
    </v-dialog>
  </div>
</template>

<script setup lang="ts">
import { useAlertStore } from '@/stores/alert'

const alertStore = useAlertStore()

function getColor(type: string) {
  switch (type) {
    case 'success': return 'success'
    case 'error': return 'error'
    case 'warning': return 'warning'
    default: return 'primary'
  }
}

function getBgColor(type: string) {
  switch (type) {
    case 'success': return 'green-lighten-5'
    case 'error': return 'red-lighten-5'
    case 'warning': return 'amber-lighten-5'
    default: return 'purple-lighten-5'
  }
}

function getIcon(type: string) {
  switch (type) {
    case 'success': return 'mdi-check-circle'
    case 'error': return 'mdi-alert-circle'
    case 'warning': return 'mdi-alert'
    default: return 'mdi-information'
  }
}
</script>
