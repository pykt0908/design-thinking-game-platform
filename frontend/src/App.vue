<template>
  <v-app>
    <router-view />
    <AppAlert />
  </v-app>
</template>

<script setup lang="ts">
import AppAlert from '@/components/common/AppAlert.vue'
</script>
