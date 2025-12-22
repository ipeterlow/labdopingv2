<script setup lang="ts">
import { Button } from '@/components/ui/button';
import { Calendar } from '@/components/ui/calendar';
import { Dialog, DialogContent, DialogDescription, DialogFooter, DialogHeader, DialogTitle } from '@/components/ui/dialog';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Popover, PopoverContent, PopoverTrigger } from '@/components/ui/popover';
import { Textarea } from '@/components/ui/textarea';
import { useForm } from '@inertiajs/vue3';
import type { DateValue } from '@internationalized/date';
import { getLocalTimeZone, parseDate } from '@internationalized/date';
import { CalendarIcon } from 'lucide-vue-next';
import { computed, reactive, ref, watch } from 'vue';
import type { SalivaSample } from './columns';

const props = defineProps<{
    open: boolean;
    sample: SalivaSample | null;
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

// Date picker
const fechaIngresoDate = ref<DateValue | undefined>(undefined);
const fechaIngresoOpen = ref(false);

// Checkboxes states (usar reactive en lugar de ref para objetos)
const screeningChecks = reactive<Record<string, boolean>>({
    THC: false,
    COC: false,
    BZD: false,
    OPI: false,
    ANF: false,
});

const confirmacionChecks = reactive<Record<string, boolean>>({
    THC: false,
    COC: false,
    BZD: false,
    OPI: false,
    ANF: false,
});

const form = useForm({
    internal_id: '',
    ph: '',
    densidad: '',
    volumen: '',
    screening: [] as string[],
    confirmacion: [] as string[],
    observaciones: '',
    cantidad_droga: null as number | null,
    encargado_ingreso: '',
    fecha_ingreso: '',
});

// Funciones para manejar toggle manualmente
const handleScreeningToggle = (drug: string, pressed: boolean) => {
    screeningChecks[drug] = pressed;
    form.screening = Object.keys(screeningChecks).filter((key) => screeningChecks[key]);
};

const handleConfirmacionToggle = (drug: string, pressed: boolean) => {
    confirmacionChecks[drug] = pressed;
    form.confirmacion = Object.keys(confirmacionChecks).filter((key) => confirmacionChecks[key]);
};

// Sincronizar datos cuando se abre el dialog
watch(
    () => [props.open, props.sample],
    ([isOpen, sample]) => {
        if (isOpen && sample && typeof sample === 'object') {
            // Llenar datos básicos
            form.internal_id = sample.internal_id || '';
            form.ph = sample.ph || '';
            form.densidad = sample.densidad || '';
            form.volumen = sample.volumen || '';
            form.observaciones = sample.observaciones || '';
            form.cantidad_droga = sample.cantidad_droga || null;
            form.encargado_ingreso = sample.encargado_ingreso || '';
            form.fecha_ingreso = sample.fecha_ingreso || '';

            // Parsear fecha si existe
            if (sample.fecha_ingreso && sample.fecha_ingreso.trim().length > 0) {
                try {
                    // Extraer solo la parte de la fecha (YYYY-MM-DD) si viene con hora
                    const fechaSolo = sample.fecha_ingreso.split(' ')[0];
                    fechaIngresoDate.value = parseDate(fechaSolo);
                } catch (e) {
                    fechaIngresoDate.value = undefined;
                }
            } else {
                fechaIngresoDate.value = undefined;
            }

            // Parsear screening: convertir string separado por comas a array
            // Resetear checkboxes
            Object.keys(screeningChecks).forEach((key) => (screeningChecks[key] = false));
            Object.keys(confirmacionChecks).forEach((key) => (confirmacionChecks[key] = false));

            if (sample.screening && typeof sample.screening === 'string' && sample.screening.trim().length > 0) {
                const screeningArray = sample.screening
                    .split(',')
                    .map((s: string) => s.trim())
                    .filter((s: string) => s.length > 0);
                form.screening = screeningArray;
                // Marcar los checkboxes correspondientes
                screeningArray.forEach((drug) => {
                    if (drug in screeningChecks) {
                        screeningChecks[drug] = true;
                    }
                });
            } else {
                form.screening = [];
            }

            // Parsear confirmacion: convertir string separado por comas a array
            if (sample.confirmacion && typeof sample.confirmacion === 'string' && sample.confirmacion.trim().length > 0) {
                const confirmacionArray = sample.confirmacion
                    .split(',')
                    .map((s: string) => s.trim())
                    .filter((s: string) => s.length > 0);
                form.confirmacion = confirmacionArray;
                // Marcar los checkboxes correspondientes
                confirmacionArray.forEach((drug) => {
                    if (drug in confirmacionChecks) {
                        confirmacionChecks[drug] = true;
                    }
                });
            } else {
                form.confirmacion = [];
            }
        } else if (isOpen && !sample) {
            form.reset();
            fechaIngresoDate.value = undefined;
            // Resetear checkboxes
            Object.keys(screeningChecks).forEach((key) => (screeningChecks[key] = false));
            Object.keys(confirmacionChecks).forEach((key) => (confirmacionChecks[key] = false));
        }
    },
    { immediate: true },
);

const updateFechaIngreso = (value: DateValue | undefined) => {
    if (value) {
        fechaIngresoDate.value = value;
        const date = value.toDate(getLocalTimeZone());
        form.fecha_ingreso = date.toISOString().split('T')[0];
        fechaIngresoOpen.value = false;
    }
};

const closeDialog = () => {
    emit('update:open', false);
    form.reset();
    fechaIngresoDate.value = undefined;
    // Resetear todos los toggles
    Object.keys(screeningChecks).forEach((key) => (screeningChecks[key] = false));
    Object.keys(confirmacionChecks).forEach((key) => (confirmacionChecks[key] = false));
};

const handleSubmit = () => {
    if (props.mode === 'view') {
        closeDialog();
        return;
    }

    if (!props.sample) return;

    // Sincronizar arrays desde los estados de los toggles
    form.screening = Object.keys(screeningChecks).filter((key) => screeningChecks[key]);
    form.confirmacion = Object.keys(confirmacionChecks).filter((key) => confirmacionChecks[key]);

    // Convertir arrays a strings separados por coma
    const screeningString = form.screening.join(',');
    const confirmacionString = form.confirmacion.join(',');

    const dataToSend = {
        internal_id: form.internal_id,
        ph: form.ph,
        densidad: form.densidad,
        volumen: form.volumen,
        screening: screeningString,
        confirmacion: confirmacionString,
        observaciones: form.observaciones,
        cantidad_droga: form.cantidad_droga,
        encargado_ingreso: form.encargado_ingreso,
        fecha_ingreso: form.fecha_ingreso,
    };

    // El registro en characteristic_samples ya existe, solo actualizar
    const endpoint = route('booksalivasample.update', props.sample.id_characteristic_samples);
    form.transform(() => dataToSend).put(endpoint, {
        preserveScroll: true,
        onSuccess: () => {
            emit('success');
            closeDialog();
        },
        onError: (errors) => {
            console.error('Error al actualizar:', errors);
        },
    });
};

const dialogTitle = computed(() => {
    if (props.mode === 'view') return 'Ver Características de Saliva';
    return 'Editar Características de Saliva';
});
</script>

<template>
    <Dialog :open="open" @update:open="(value: boolean) => emit('update:open', value)">
        <DialogContent class="max-h-[90vh] overflow-y-auto sm:max-w-[700px]">
            <DialogHeader>
                <DialogTitle>{{ dialogTitle }}</DialogTitle>
                <DialogDescription v-if="sample"> Muestra: {{ sample.external_id }} - {{ sample.company_name }} </DialogDescription>
            </DialogHeader>

            <form @submit.prevent="handleSubmit" class="space-y-6">
                <!-- Información de la Muestra (Solo lectura) -->
                <div class="rounded-lg border bg-muted/30 p-4">
                    <h3 class="mb-3 font-semibold">Información de la Muestra</h3>
                    <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
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
                    <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
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
                    </div>
                </div>

                <!-- Análisis con Checkboxes -->
                <div class="space-y-6">
                    <h3 class="font-semibold">Análisis</h3>

                    <!-- Screening -->
                    <div class="space-y-3">
                        <Label class="text-base">Screening</Label>
                        <div class="flex flex-wrap gap-2">
                            <Button
                                v-for="drug in drugOptions"
                                :key="'screening-' + drug.value"
                                type="button"
                                @click="handleScreeningToggle(drug.value, !screeningChecks[drug.value])"
                                :disabled="isReadOnly"
                                :variant="screeningChecks[drug.value] ? 'default' : 'outline'"
                                size="sm"
                            >
                                {{ drug.label }}
                            </Button>
                        </div>
                    </div>

                    <!-- Confirmación -->
                    <div class="space-y-3">
                        <Label class="text-base">Confirmación</Label>
                        <div class="flex flex-wrap gap-2">
                            <Button
                                v-for="drug in drugOptions"
                                :key="'confirmacion-' + drug.value"
                                type="button"
                                @click="handleConfirmacionToggle(drug.value, !confirmacionChecks[drug.value])"
                                :disabled="isReadOnly"
                                :variant="confirmacionChecks[drug.value] ? 'default' : 'outline'"
                                size="sm"
                            >
                                {{ drug.label }}
                            </Button>
                        </div>
                    </div>

                    <div class="space-y-2">
                        <Label for="cantidad_droga">Cantidad de Droga</Label>
                        <Input
                            id="cantidad_droga"
                            type="number"
                            :model-value="form.cantidad_droga ?? ''"
                            @update:model-value="(val: string) => (form.cantidad_droga = val ? Number(val) : null)"
                            :disabled="isReadOnly"
                            :placeholder="isReadOnly ? '' : 'Cantidad detectada'"
                            class="max-w-xs"
                        />
                    </div>
                </div>

                <!-- Registro -->
                <div class="space-y-4">
                    <h3 class="font-semibold">Información de Registro</h3>
                    <div class="grid gap-4 sm:grid-cols-2">
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
                            <Label for="fecha_ingreso" class="px-1">Fecha de Ingreso</Label>
                            <Popover v-model:open="fechaIngresoOpen">
                                <PopoverTrigger as-child>
                                    <Button
                                        id="fecha_ingreso"
                                        variant="outline"
                                        :class="['w-full justify-start text-left font-normal', !fechaIngresoDate && 'text-muted-foreground']"
                                        :disabled="isReadOnly"
                                    >
                                        <CalendarIcon class="mr-2 h-4 w-4" />
                                        {{
                                            fechaIngresoDate
                                                ? fechaIngresoDate.toDate(getLocalTimeZone()).toLocaleDateString('es-CL')
                                                : 'Selecciona fecha…'
                                        }}
                                    </Button>
                                </PopoverTrigger>
                                <PopoverContent class="w-auto overflow-hidden p-0" align="start">
                                    <Calendar :model-value="fechaIngresoDate" @update:model-value="updateFechaIngreso" />
                                </PopoverContent>
                            </Popover>
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
                            class="resize-none"
                        />
                    </div>
                </div>

                <DialogFooter>
                    <Button type="button" variant="outline" @click="closeDialog">
                        {{ isReadOnly ? 'Cerrar' : 'Cancelar' }}
                    </Button>
                    <Button v-if="!isReadOnly" type="submit" :disabled="form.processing">
                        {{ form.processing ? 'Guardando...' : 'Guardar' }}
                    </Button>
                </DialogFooter>
            </form>
        </DialogContent>
    </Dialog>
</template>
