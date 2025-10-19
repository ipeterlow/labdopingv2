<script setup lang="ts">
import { Alert, AlertTitle, AlertDescription } from '@/components/ui/alert'
import { usePage } from '@inertiajs/vue3'
import { CheckCircle, XCircle } from 'lucide-vue-next'
import { ref, watchEffect } from 'vue'

const showAlert = ref(false)
const alertType = ref<'success' | 'error'>('success')
const alertMessage = ref('')

// Obtenemos flash messages de Inertia
const page = usePage()

watchEffect(() => {
  const flash = (page.props as any).flash
  if (!flash) return

  if (flash.success) {
    alertType.value = 'success'
    alertMessage.value = flash.success
    showAlert.value = true
    setTimeout(() => (showAlert.value = false), 3500)
  } else if (flash.error) {
    alertType.value = 'error'
    alertMessage.value = flash.error
    showAlert.value = true
    setTimeout(() => (showAlert.value = false), 3500)
  }
})
</script>

<template>
  <div class="fixed top-4 right-4 z-50 w-96">
    <transition name="fade">
      <Alert
        v-if="showAlert"
        :variant="alertType === 'success' ? 'default' : 'destructive'"
        class="shadow-md"
      >
        <div class="flex items-center gap-2">
          <component :is="alertType === 'success' ? CheckCircle : XCircle" class="h-5 w-5" />
          <AlertTitle>{{ alertType === 'success' ? 'Éxito' : 'Error' }}</AlertTitle>
        </div>
        <AlertDescription>{{ alertMessage }}</AlertDescription>
      </Alert>
    </transition>
  </div>
</template>

<style>
.fade-enter-active,
.fade-leave-active {
  transition: all 0.5s ease;
}
.fade-enter-from,
.fade-leave-to {
  opacity: 0;
  transform: translateY(-1rem);
}
.fade-enter-to,
.fade-leave-from {
  opacity: 1;
  transform: translateY(0);
}
</style>
