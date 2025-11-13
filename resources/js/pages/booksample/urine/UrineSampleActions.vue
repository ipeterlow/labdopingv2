<script setup lang="ts">
import { Button } from '@/components/ui/button';
import { Eye, Pencil } from 'lucide-vue-next';
import { inject } from 'vue';
import type { UrineSample } from './columns';

const props = defineProps<{
    sample: UrineSample;
}>();

const handleEdit = inject<(sample: UrineSample) => void>('handleEdit');
const handleView = inject<(sample: UrineSample) => void>('handleView');
</script>

<template>
    <div class="flex gap-2">
        <Button size="sm" variant="outline" @click="handleEdit?.(sample)" title="Editar">
            <Pencil class="h-4 w-4" />
        </Button>
        <Button size="sm" variant="outline" @click="handleView?.(sample)" title="Ver">
            <Eye class="h-4 w-4" />
        </Button>
    </div>
</template>
