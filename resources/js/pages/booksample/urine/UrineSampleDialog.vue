<script setup lang="ts">
import { Button } from '@/components/ui/button';
import { Checkbox } from '@/components/ui/checkbox';
import { Dialog, DialogContent, DialogDescription, DialogFooter, DialogHeader, DialogTitle } from '@/components/ui/dialog';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { ScrollArea } from '@/components/ui/scroll-area';
import { Textarea } from '@/components/ui/textarea';
import { useForm } from '@inertiajs/vue3';
import { computed, watch } from 'vue';
import type { UrineSample } from './columns';

const props = defineProps<{
    open: boolean;
    sample: UrineSample | null;
    mode: 'view' | 'edit';
}>();

const emit = defineEmits<{
    (e: 'update:open', value: boolean): void;
    (e: 'success'): void;
}>();

const isReadOnly = computed(() => props.mode === 'view');

// Computed properties para los campos de solo lectura
const externalId = computed(() => props.sample?.external_id || '');
const companyName = computed(() => props.sample?.company_name || '');

// Opciones de drogas
const drugOptions = [
    { value: 'THC', label: 'THC' },
    { value: 'COC', label: 'COC' },
    { value: 'BZD', label: 'BZD' },
    { value: 'OPI', label: 'OPI' },
    { value: 'ANF', label: 'ANF' },
];

const form = useForm({
    internal_id: '',
    ph: '',
    densidad: '',
    volumen: '',
    largo: '',
    screening: [] as string[],
    confirmacion: [] as string[],
    color: '',
    observaciones: '',
    cantidad_droga: null as number | null,
    encargado_ingreso: '',
    fecha_ingreso: '',
});

// Funciones para manejar checkboxes
const toggleScreening = (drug: string) => {
    const index = form.screening.indexOf(drug);
    if (index > -1) {
        form.screening.splice(index, 1);
    } else {
        form.screening.push(drug);
    }
};

const toggleConfirmacion = (drug: string) => {
    const index = form.confirmacion.indexOf(drug);
    if (index > -1) {
        form.confirmacion.splice(index, 1);
    } else {
        form.confirmacion.push(drug);
    }
};

const isScreeningChecked = (drug: string) => form.screening.includes(drug);
const isConfirmacionChecked = (drug: string) => form.confirmacion.includes(drug);

// Sincronizar datos cuando se abre el dialog
watch(
    () => [props.open, props.sample],
    ([isOpen, sample]) => {
        if (isOpen && sample && typeof sample === 'object') {
            console.log('=== FULL SAMPLE DATA ===', sample); // Debug completo
            console.log('Screening raw:', sample.screening, 'Type:', typeof sample.screening); // Debug
            console.log('Confirmacion raw:', sample.confirmacion, 'Type:', typeof sample.confirmacion); // Debug

            form.internal_id = sample.internal_id || '';
            form.ph = sample.ph || '';
            form.densidad = sample.densidad || '';
            form.volumen = sample.volumen || '';
            form.largo = sample.largo || '';

            // Parsear screening y confirmacion de string a array
            // Filtrar strings vacíos después del split
            if (sample.screening && typeof sample.screening === 'string' && sample.screening.trim().length > 0) {
                form.screening = sample.screening
                    .split(',')
                    .map((s: string) => s.trim())
                    .filter((s: string) => s.length > 0);
            } else {
                form.screening = [];
            }

            if (sample.confirmacion && typeof sample.confirmacion === 'string' && sample.confirmacion.trim().length > 0) {
                form.confirmacion = sample.confirmacion
                    .split(',')
                    .map((s: string) => s.trim())
                    .filter((s: string) => s.length > 0);
            } else {
                form.confirmacion = [];
            }

            console.log('Screening parsed:', form.screening); // Debug
            console.log('Confirmacion parsed:', form.confirmacion); // Debug

            form.color = sample.color || '';
            form.observaciones = sample.observaciones || '';
            form.cantidad_droga = sample.cantidad_droga || null;
            form.encargado_ingreso = sample.encargado_ingreso || '';
            form.fecha_ingreso = sample.fecha_ingreso || '';
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
    if (props.mode === 'view') {
        closeDialog();
        return;
    }

    if (!props.sample) return;

    // Convertir arrays a strings separados por coma antes de enviar
    const dataToSend = {
        ...form.data(),
        screening: form.screening.join(', '),
        confirmacion: form.confirmacion.join(', '),
    };

    // Si ya tiene características (tiene pH u otro campo), actualizar. Si no, crear.
    const hasExistingData = props.sample.ph || props.sample.densidad || props.sample.screening;

    if (hasExistingData) {
        // Actualizar registro existente
        const endpoint = route('bookurinesample.update', props.sample.id_characteristic_samples);
        form.transform(() => dataToSend).put(endpoint, {
            preserveScroll: true,
            onSuccess: () => {
                emit('success');
                closeDialog();
            },
        });
    } else {
        // Crear nuevo registro
        const endpoint = route('bookurinesample.store');
        const createData = {
            ...dataToSend,
            sample_id: props.sample.sample_id,
        };

        form.transform(() => createData).post(endpoint, {
            preserveScroll: true,
            onSuccess: () => {
                emit('success');
                closeDialog();
            },
        });
    }
};

const dialogTitle = computed(() => {
    if (props.mode === 'view') return 'Ver Características de Orina';
    return props.sample?.ph ? 'Editar Características de Orina' : 'Agregar Características de Orina';
});
</script>

<template>
    <Dialog :open="open" @update:open="(value) => emit('update:open', value)">
        <DialogContent class="max-h-[90vh] w-[95vw] max-w-7xl">
            <DialogHeader>
                <DialogTitle>{{ dialogTitle }}</DialogTitle>
                <DialogDescription v-if="sample"> Muestra: {{ sample.external_id }} - {{ sample.company_name }} </DialogDescription>
            </DialogHeader>

            <ScrollArea class="max-h-[70vh] pr-4">
                <form @submit.prevent="handleSubmit" class="space-y-6 px-1">
                    <!-- Información de la Muestra (Solo lectura) -->
                    <div class="rounded-lg border bg-muted/30 p-4">
                        <h3 class="mb-3 font-semibold">Información de la Muestra</h3>
                        <div class="grid gap-4 md:grid-cols-3">
                            <div class="space-y-2">
                                <Label>Nº Externo</Label>
                                <Input :model-value="externalId" disabled class="bg-muted" />
                            </div>
                            <div class="space-y-2">
                                <Label for="internal_id">Nº Interno</Label>
                                <Input
                                    id="internal_id"
                                    v-model="form.internal_id"
                                    :disabled="isReadOnly"
                                    :placeholder="isReadOnly ? '' : 'Número interno'"
                                />
                            </div>
                            <div class="space-y-2">
                                <Label>Empresa</Label>
                                <Input :model-value="companyName" disabled class="bg-muted" />
                            </div>
                        </div>
                    </div>

                    <!-- Características Físicas -->
                    <div class="space-y-4">
                        <h3 class="font-semibold">Características Físicas</h3>
                        <div class="grid gap-4 md:grid-cols-2 lg:grid-cols-4">
                            <div class="space-y-2">
                                <Label for="ph">pH</Label>
                                <Input id="ph" v-model="form.ph" :disabled="isReadOnly" :placeholder="isReadOnly ? '' : 'Ej: 6.5'" />
                            </div>
                            <div class="space-y-2">
                                <Label for="densidad">Densidad</Label>
                                <Input id="densidad" v-model="form.densidad" :disabled="isReadOnly" :placeholder="isReadOnly ? '' : 'Ej: 1.020'" />
                            </div>
                            <div class="space-y-2">
                                <Label for="volumen">Volumen (ml)</Label>
                                <Input id="volumen" v-model="form.volumen" :disabled="isReadOnly" :placeholder="isReadOnly ? '' : 'Ej: 50'" />
                            </div>
                            <div class="space-y-2">
                                <Label for="largo">Largo (cm)</Label>
                                <Input id="largo" v-model="form.largo" :disabled="isReadOnly" :placeholder="isReadOnly ? '' : 'Ej: 3.5'" />
                            </div>
                        </div>

                        <div class="space-y-2">
                            <Label for="color">Color</Label>
                            <Input id="color" v-model="form.color" :disabled="isReadOnly" :placeholder="isReadOnly ? '' : 'Ej: Amarillo claro'" />
                        </div>
                    </div>

                    <!-- Análisis con Checkboxes -->
                    <div class="space-y-6">
                        <h3 class="font-semibold">Análisis</h3>

                        <!-- Screening -->
                        <div class="space-y-3">
                            <Label class="text-base">Screening</Label>
                            <div class="grid grid-cols-1 gap-3 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-5">
                                <div
                                    v-for="drug in drugOptions"
                                    :key="'screening-' + drug.value"
                                    class="flex items-start space-x-3 rounded-md border p-3 transition-colors hover:bg-muted/50"
                                >
                                    <Checkbox
                                        :id="'screening-' + drug.value"
                                        :checked="isScreeningChecked(drug.value)"
                                        @update:checked="() => toggleScreening(drug.value)"
                                        :disabled="isReadOnly"
                                        class="mt-0.5"
                                    />
                                    <Label :for="'screening-' + drug.value" class="flex-1 cursor-pointer text-sm leading-tight font-normal">
                                        {{ drug.label }}
                                    </Label>
                                </div>
                            </div>
                        </div>

                        <!-- Confirmación -->
                        <div class="space-y-3">
                            <Label class="text-base">Confirmación</Label>
                            <div class="grid grid-cols-1 gap-3 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-5">
                                <div
                                    v-for="drug in drugOptions"
                                    :key="'confirmacion-' + drug.value"
                                    class="flex items-start space-x-3 rounded-md border p-3 transition-colors hover:bg-muted/50"
                                >
                                    <Checkbox
                                        :id="'confirmacion-' + drug.value"
                                        :checked="isConfirmacionChecked(drug.value)"
                                        @update:checked="() => toggleConfirmacion(drug.value)"
                                        :disabled="isReadOnly"
                                        class="mt-0.5"
                                    />
                                    <Label :for="'confirmacion-' + drug.value" class="flex-1 cursor-pointer text-sm leading-tight font-normal">
                                        {{ drug.label }}
                                    </Label>
                                </div>
                            </div>
                        </div>

                        <div class="grid gap-4 md:grid-cols-2">
                            <div class="space-y-2">
                                <Label for="cantidad_droga">Cantidad de Droga</Label>
                                <Input
                                    id="cantidad_droga"
                                    type="number"
                                    :model-value="form.cantidad_droga ?? ''"
                                    @update:model-value="(val) => (form.cantidad_droga = val ? Number(val) : null)"
                                    :disabled="isReadOnly"
                                    :placeholder="isReadOnly ? '' : 'Cantidad detectada'"
                                />
                            </div>
                        </div>
                    </div>

                    <!-- Registro -->
                    <div class="space-y-4">
                        <h3 class="font-semibold">Información de Registro</h3>
                        <div class="grid gap-4 md:grid-cols-2">
                            <div class="space-y-2">
                                <Label for="encargado_ingreso">Encargado de Ingreso</Label>
                                <Input
                                    id="encargado_ingreso"
                                    v-model="form.encargado_ingreso"
                                    :disabled="isReadOnly"
                                    :placeholder="isReadOnly ? '' : 'Nombre del encargado'"
                                />
                            </div>
                            <div class="space-y-2">
                                <Label for="fecha_ingreso">Fecha de Ingreso</Label>
                                <Input id="fecha_ingreso" type="date" v-model="form.fecha_ingreso" :disabled="isReadOnly" />
                            </div>
                        </div>

                        <div class="space-y-2">
                            <Label for="observaciones">Observaciones</Label>
                            <Textarea
                                id="observaciones"
                                v-model="form.observaciones"
                                :disabled="isReadOnly"
                                rows="4"
                                :placeholder="isReadOnly ? '' : 'Observaciones adicionales...'"
                            />
                        </div>
                    </div>
                </form>
            </ScrollArea>

            <DialogFooter class="gap-2">
                <Button type="button" variant="outline" @click="closeDialog">
                    {{ isReadOnly ? 'Cerrar' : 'Cancelar' }}
                </Button>
                <Button v-if="!isReadOnly" type="button" @click="handleSubmit" :disabled="form.processing">
                    {{ form.processing ? 'Guardando...' : 'Guardar' }}
                </Button>
            </DialogFooter>
        </DialogContent>
    </Dialog>
</template>
