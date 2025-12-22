<script setup lang="ts">
import { Button } from '@/components/ui/button';
import { Dialog, DialogContent, DialogDescription, DialogFooter, DialogHeader, DialogTitle } from '@/components/ui/dialog';
import { Label } from '@/components/ui/label';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { useForm } from '@inertiajs/vue3';
import { computed, watch } from 'vue';

const props = defineProps<{
    open: boolean;
    sampleId: number;
    currentStatus: number;
    routeName: string;
}>();

const emit = defineEmits<{
    'update:open': [value: boolean];
    success: [];
}>();

const statusOptions = [
    { id: 1, name: 'Recepcionada' },
    { id: 2, name: 'Anulada' },
    { id: 3, name: 'Pendiente' },
    { id: 4, name: 'Informada' },
    { id: 5, name: 'Analisis' },
];

const form = useForm({
    status: props.currentStatus,
});

watch(
    () => props.currentStatus,
    (newStatus) => {
        form.status = newStatus;
    },
    { immediate: true },
);

const selectedStatusName = computed(() => {
    const status = statusOptions.find((s) => s.id === form.status);
    return status?.name || '';
});

const handleSubmit = () => {
    form.put(route(props.routeName, props.sampleId), {
        preserveScroll: true,
        onSuccess: () => {
            emit('update:open', false);
            emit('success');
        },
    });
};

const closeDialog = () => {
    emit('update:open', false);
    form.reset();
};
</script>

<template>
    <Dialog :open="open" @update:open="(value: boolean) => emit('update:open', value)">
        <DialogContent class="sm:max-w-[425px]">
            <DialogHeader>
                <DialogTitle>Cambiar Estado de Muestra</DialogTitle>
                <DialogDescription> Selecciona el nuevo estado para esta muestra. </DialogDescription>
            </DialogHeader>

            <form @submit.prevent="handleSubmit" class="space-y-4">
                <div class="space-y-2">
                    <Label for="status">Estado</Label>
                    <Select v-model="form.status">
                        <SelectTrigger class="w-full">
                            <SelectValue :placeholder="selectedStatusName || 'Selecciona un estado'" />
                        </SelectTrigger>
                        <SelectContent>
                            <SelectItem v-for="status in statusOptions" :key="status.id" :value="status.id">
                                {{ status.name }}
                            </SelectItem>
                        </SelectContent>
                    </Select>
                </div>

                <DialogFooter>
                    <Button type="button" variant="outline" @click="closeDialog"> Cancelar </Button>
                    <Button type="submit" :disabled="form.processing">
                        {{ form.processing ? 'Guardando...' : 'Guardar' }}
                    </Button>
                </DialogFooter>
            </form>
        </DialogContent>
    </Dialog>
</template>
