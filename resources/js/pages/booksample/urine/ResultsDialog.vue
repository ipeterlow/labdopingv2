<script setup lang="ts">
import { Button } from '@/components/ui/button';
import { Dialog, DialogContent, DialogDescription, DialogFooter, DialogHeader, DialogTitle } from '@/components/ui/dialog';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { useForm } from '@inertiajs/vue3';
import { computed, watch } from 'vue';
import type { UrineSample } from './columns';

const props = defineProps<{
    open: boolean;
    sample: UrineSample | null;
}>();

const emit = defineEmits<{
    (e: 'update:open', value: boolean): void;
    (e: 'success'): void;
}>();

// Obtener las drogas de screening
const screeningDrugs = computed(() => {
    if (!props.sample?.screening) return [];
    return props.sample.screening
        .split(',')
        .map((s: string) => s.trim())
        .filter((s: string) => s.length > 0);
});

// Verificar si hay drogas seleccionadas
const hasDrugs = computed(() => screeningDrugs.value.length > 0);

// Formulario dinámico basado en las drogas de screening
const form = useForm<{
    results_gcms: Record<string, string>;
    results_cobas: Record<string, string>;
}>({
    results_gcms: {},
    results_cobas: {},
});

// Sincronizar datos cuando se abre el dialog
watch(
    () => [props.open, props.sample],
    ([isOpen, sample]) => {
        if (isOpen && sample && typeof sample === 'object') {
            // Inicializar formulario con drogas de screening
            const gcmsResults: Record<string, string> = {};
            const cobasResults: Record<string, string> = {};

            // Parsear resultados existentes si los hay
            let existingGcms: Record<string, string> = {};
            let existingCobas: Record<string, string> = {};

            try {
                if (sample.result_gcms) {
                    existingGcms = typeof sample.result_gcms === 'string' ? JSON.parse(sample.result_gcms) : sample.result_gcms;
                }
            } catch (e) {
                console.error('Error parsing result_gcms:', e);
            }

            try {
                if (sample.result_cobas) {
                    existingCobas = typeof sample.result_cobas === 'string' ? JSON.parse(sample.result_cobas) : sample.result_cobas;
                }
            } catch (e) {
                console.error('Error parsing result_cobas:', e);
            }

            // Inicializar campos para cada droga de screening
            screeningDrugs.value.forEach((drug) => {
                gcmsResults[drug] = existingGcms[drug] || '';
                cobasResults[drug] = existingCobas[drug] || '';
            });

            form.results_gcms = gcmsResults;
            form.results_cobas = cobasResults;
        } else if (isOpen && !sample) {
            form.reset();
        }
    },
    { immediate: true },
);

const closeDialog = () => {
    emit('update:open', false);
    form.reset();
};

const handleSubmit = () => {
    if (!props.sample || !hasDrugs.value) return;

    const endpoint = route('bookurinesample.updateResults', props.sample.id_characteristic_samples);

    form.transform(() => ({
        result_gcms: JSON.stringify(form.results_gcms),
        result_cobas: JSON.stringify(form.results_cobas),
    })).put(endpoint, {
        preserveScroll: true,
        onSuccess: () => {
            emit('success');
            closeDialog();
        },
        onError: (errors) => {
            console.error('Error al actualizar resultados:', errors);
        },
    });
};
</script>

<template>
    <Dialog :open="open" @update:open="(value: boolean) => emit('update:open', value)">
        <DialogContent class="max-h-[90vh] overflow-y-auto sm:max-w-[700px]">
            <DialogHeader>
                <DialogTitle>Resultados de Muestras</DialogTitle>
                <DialogDescription v-if="sample"> Muestra: {{ sample.external_id }} - {{ sample.company_name }} </DialogDescription>
            </DialogHeader>

            <!-- Mensaje cuando no hay drogas seleccionadas -->
            <div v-if="!hasDrugs" class="rounded-lg border border-yellow-200 bg-yellow-50 p-4 text-center dark:border-yellow-800 dark:bg-yellow-950">
                <p class="text-sm text-yellow-800 dark:text-yellow-200">No hay drogas seleccionadas para ingresar resultados</p>
                <p class="mt-1 text-xs text-yellow-600 dark:text-yellow-400">Primero debe seleccionar drogas en la sección de Screening</p>
            </div>

            <!-- Formulario de resultados -->
            <form v-else @submit.prevent="handleSubmit" class="space-y-6">
                <!-- Información de la Muestra (Solo lectura) -->
                <div class="rounded-lg border bg-muted/30 p-4">
                    <h3 class="mb-3 font-semibold">Drogas a Analizar</h3>
                    <div class="flex flex-wrap gap-2">
                        <div v-for="drug in screeningDrugs" :key="drug" class="rounded-md bg-primary/10 px-3 py-1 text-sm font-medium text-primary">
                            {{ drug }}
                        </div>
                    </div>
                </div>

                <!-- Resultados GC/MS -->
                <div class="space-y-4">
                    <div class="flex items-center gap-2">
                        <div class="h-px flex-1 bg-border"></div>
                        <h3 class="text-lg font-semibold">Resultados GC/MS</h3>
                        <div class="h-px flex-1 bg-border"></div>
                    </div>
                    <div class="grid gap-4 sm:grid-cols-2">
                        <div v-for="drug in screeningDrugs" :key="'gcms-' + drug" class="space-y-2">
                            <Label :for="'gcms-' + drug">{{ drug }}</Label>
                            <Input :id="'gcms-' + drug" v-model="form.results_gcms[drug]" placeholder="Ej: Positivo, Negativo, 150 ng/ml" />
                        </div>
                    </div>
                </div>

                <!-- Resultados COBAS -->
                <div class="space-y-4">
                    <div class="flex items-center gap-2">
                        <div class="h-px flex-1 bg-border"></div>
                        <h3 class="text-lg font-semibold">Resultados COBAS</h3>
                        <div class="h-px flex-1 bg-border"></div>
                    </div>
                    <div class="grid gap-4 sm:grid-cols-2">
                        <div v-for="drug in screeningDrugs" :key="'cobas-' + drug" class="space-y-2">
                            <Label :for="'cobas-' + drug">{{ drug }}</Label>
                            <Input :id="'cobas-' + drug" v-model="form.results_cobas[drug]" placeholder="Ej: Positivo, Negativo, 150 ng/ml" />
                        </div>
                    </div>
                </div>

                <DialogFooter>
                    <Button type="button" variant="outline" @click="closeDialog"> Cancelar </Button>
                    <Button type="submit" :disabled="form.processing">
                        {{ form.processing ? 'Guardando...' : 'Guardar Resultados' }}
                    </Button>
                </DialogFooter>
            </form>
        </DialogContent>
    </Dialog>
</template>
