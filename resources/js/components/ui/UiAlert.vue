<script setup lang="ts">
import { Alert, AlertDescription, AlertTitle } from '@/components/ui/alert';
import { CheckCircle, XCircle, Ticket } from 'lucide-vue-next';
import { ref } from 'vue';

const props = defineProps<{
    type?: 'success' | 'error';
    message: string;
}>()

const icons = {
    success: Ticket,
    error: XCircle
}
</script>

<template>
  <Alert
    :variant="props.type === 'success' ? 'default' : 'destructive'"
    class="rounded-xl shadow-lg p-4 flex items-start gap-3 border-2 border-opacity-60"
  >
    <component :is="icons[props.type ?? 'success']" class="w-6 h-6 mt-0.5" />
    <div>
      <AlertTitle class="font-semibold text-lg capitalize">
        {{ props.type === 'success' ? 'Éxito' : 'Error' }}
      </AlertTitle>
      <AlertDescription class="text-sm">
        {{ props.message }}
      </AlertDescription>
    </div>
  </Alert>
</template>
