<script setup lang="ts">
import ChangeStatusDialog from '@/components/ChangeStatusDialog.vue';
import { Button } from '@/components/ui/button';
import { router } from '@inertiajs/vue3';
import { Eye, FlaskConical, Pencil, RefreshCw } from 'lucide-vue-next';
import { inject, ref } from 'vue';
import type { SalivaSample } from './columns';

const props = defineProps<{
    sample: SalivaSample;
}>();

const handleEdit = inject<(sample: SalivaSample) => void>('handleEdit');
const handleView = inject<(sample: SalivaSample) => void>('handleView');
const handleResults = inject<(sample: SalivaSample) => void>('handleResults');
const showChangeStatus = ref(false);
</script>

<template>
    <div class="flex gap-2">
        <Button size="sm" variant="outline" @click="handleEdit?.(sample)" title="Editar">
            <Pencil class="h-4 w-4" />
        </Button>
        <Button size="sm" variant="outline" @click="handleView?.(sample)" title="Ver">
            <Eye class="h-4 w-4" />
        </Button>
        <Button size="sm" variant="outline" @click="handleResults?.(sample)" title="Resultados">
            <FlaskConical class="h-4 w-4" />
        </Button>
        <Button size="sm" variant="outline" @click="showChangeStatus = true" title="Cambiar Estado">
            <RefreshCw class="h-4 w-4" />
        </Button>
    </div>

    <ChangeStatusDialog v-model:open="showChangeStatus" :sample-id="sample.sample_id" :current-status="sample.status_id"
        route-name="booksalivasample.updateStatus" @success="() => router.reload({ only: ['salivaSamples'] })" />
</template>
