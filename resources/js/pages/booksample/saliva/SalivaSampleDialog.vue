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
import { CalendarIcon, PlusCircle, X } from 'lucide-vue-next';
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
const horaIngreso = ref('');

const fechaTomaMuestraDate = ref<DateValue | undefined>(undefined);
const fechaTomaMuestraOpen = ref(false);

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

// Drogas personalizadas (otros)
const screeningOtros = ref<string[]>([]);
const confirmacionOtros = ref<string[]>([]);

// Dialogs para agregar otros
const screeningOtrosDialog = ref(false);
const confirmacionOtrosDialog = ref(false);
const screeningOtroInput = ref('');
const confirmacionOtroInput = ref('');

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
    sample_taken_at: '',
});

// Funciones para manejar toggle manualmente
const handleScreeningToggle = (drug: string, pressed: boolean) => {
    screeningChecks[drug] = pressed;
    updateScreeningForm();
};

const handleConfirmacionToggle = (drug: string, pressed: boolean) => {
    confirmacionChecks[drug] = pressed;
    updateConfirmacionForm();
};

// Seleccionar todas las drogas en screening
const handleSelectAllScreening = () => {
    const allChecked = Object.values(screeningChecks).every((val) => val === true);
    Object.keys(screeningChecks).forEach((key) => {
        screeningChecks[key] = !allChecked;
    });
    updateScreeningForm();
};

const allScreeningSelected = computed(() => {
    return Object.values(screeningChecks).every((val) => val === true);
});

// Actualizar form con drogas del listado + otros
const updateScreeningForm = () => {
    const checkedDrugs = Object.keys(screeningChecks).filter((key) => screeningChecks[key]);
    form.screening = [...checkedDrugs, ...screeningOtros.value];
};

const updateConfirmacionForm = () => {
    const checkedDrugs = Object.keys(confirmacionChecks).filter((key) => confirmacionChecks[key]);
    form.confirmacion = [...checkedDrugs, ...confirmacionOtros.value];
};

// Agregar droga personalizada
const addScreeningOtro = () => {
    const drug = screeningOtroInput.value.trim().toUpperCase();
    if (drug && !screeningOtros.value.includes(drug) && !(drug in screeningChecks)) {
        screeningOtros.value.push(drug);
        updateScreeningForm();
        screeningOtroInput.value = '';
        screeningOtrosDialog.value = false;
    }
};

const removeScreeningOtro = (drug: string) => {
    screeningOtros.value = screeningOtros.value.filter((d) => d !== drug);
    updateScreeningForm();
};

const addConfirmacionOtro = () => {
    const drug = confirmacionOtroInput.value.trim().toUpperCase();
    if (drug && !confirmacionOtros.value.includes(drug) && !(drug in confirmacionChecks)) {
        confirmacionOtros.value.push(drug);
        updateConfirmacionForm();
        confirmacionOtroInput.value = '';
        confirmacionOtrosDialog.value = false;
    }
};

const removeConfirmacionOtro = (drug: string) => {
    confirmacionOtros.value = confirmacionOtros.value.filter((d) => d !== drug);
    updateConfirmacionForm();
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
            form.sample_taken_at = sample.sample_taken_at || '';

            // Parsear fecha y hora si existe
            if (sample.fecha_ingreso && sample.fecha_ingreso.trim().length > 0) {
                try {
                    // Extraer fecha y hora (formato: YYYY-MM-DD HH:MM:SS)
                    const [fechaSolo, horaSolo] = sample.fecha_ingreso.split(' ');
                    fechaIngresoDate.value = parseDate(fechaSolo);
                    // Extraer solo HH:MM de HH:MM:SS
                    if (horaSolo) {
                        horaIngreso.value = horaSolo.substring(0, 5);
                    }
                } catch (e) {
                    fechaIngresoDate.value = undefined;
                    horaIngreso.value = '';
                }
            } else {
                fechaIngresoDate.value = undefined;
                horaIngreso.value = '';
            }

            // Parsear fecha de toma de muestra si existe
            if (sample.sample_taken_at && sample.sample_taken_at.trim().length > 0) {
                try {
                    const fechaSolo = sample.sample_taken_at.split(' ')[0];
                    fechaTomaMuestraDate.value = parseDate(fechaSolo);
                } catch (e) {
                    fechaTomaMuestraDate.value = undefined;
                }
            } else {
                fechaTomaMuestraDate.value = undefined;
            }

            // Parsear screening: convertir string separado por comas a array
            // Resetear checkboxes y otros
            Object.keys(screeningChecks).forEach((key) => (screeningChecks[key] = false));
            Object.keys(confirmacionChecks).forEach((key) => (confirmacionChecks[key] = false));
            screeningOtros.value = [];
            confirmacionOtros.value = [];

            if (sample.screening && typeof sample.screening === 'string' && sample.screening.trim().length > 0) {
                const screeningArray = sample.screening
                    .split(',')
                    .map((s: string) => s.trim())
                    .filter((s: string) => s.length > 0);
                form.screening = screeningArray;
                // Marcar los checkboxes correspondientes y agregar otros
                screeningArray.forEach((drug) => {
                    if (drug in screeningChecks) {
                        screeningChecks[drug] = true;
                    } else {
                        screeningOtros.value.push(drug);
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
                // Marcar los checkboxes correspondientes y agregar otros
                confirmacionArray.forEach((drug) => {
                    if (drug in confirmacionChecks) {
                        confirmacionChecks[drug] = true;
                    } else {
                        confirmacionOtros.value.push(drug);
                    }
                });
            } else {
                form.confirmacion = [];
            }
        } else if (isOpen && !sample) {
            form.reset();
            fechaIngresoDate.value = undefined;
            fechaTomaMuestraDate.value = undefined;
            horaIngreso.value = '';
            // Resetear checkboxes y otros
            Object.keys(screeningChecks).forEach((key) => (screeningChecks[key] = false));
            Object.keys(confirmacionChecks).forEach((key) => (confirmacionChecks[key] = false));
            screeningOtros.value = [];
            confirmacionOtros.value = [];
        }
    },
    { immediate: true },
);

const updateFechaIngreso = (value: DateValue | undefined) => {
    if (value) {
        fechaIngresoDate.value = value;
        const date = value.toDate(getLocalTimeZone());
        const fechaStr = date.toISOString().split('T')[0];
        // Combinar fecha con hora si existe
        if (horaIngreso.value) {
            form.fecha_ingreso = `${fechaStr} ${horaIngreso.value}:00`;
        } else {
            form.fecha_ingreso = `${fechaStr} 00:00:00`;
        }
        fechaIngresoOpen.value = false;
    }
};

const updateFechaTomaMuestra = (value: DateValue | undefined) => {
    if (value) {
        fechaTomaMuestraDate.value = value;
        const date = value.toDate(getLocalTimeZone());
        const fechaStr = date.toISOString().split('T')[0];
        form.sample_taken_at = `${fechaStr} 00:00:00`;
    } else {
        form.sample_taken_at = '';
    }
    fechaTomaMuestraOpen.value = false;
};

// Actualizar datetime cuando cambia la hora
const updateHoraIngreso = () => {
    if (fechaIngresoDate.value && horaIngreso.value) {
        const date = fechaIngresoDate.value.toDate(getLocalTimeZone());
        const fechaStr = date.toISOString().split('T')[0];
        form.fecha_ingreso = `${fechaStr} ${horaIngreso.value}:00`;
    }
};

const closeDialog = () => {
    emit('update:open', false);
    form.reset();
    fechaIngresoDate.value = undefined;
    fechaTomaMuestraDate.value = undefined;
    horaIngreso.value = '';
    // Resetear todos los toggles y otros
    Object.keys(screeningChecks).forEach((key) => (screeningChecks[key] = false));
    Object.keys(confirmacionChecks).forEach((key) => (confirmacionChecks[key] = false));
    screeningOtros.value = [];
    confirmacionOtros.value = [];
    screeningOtroInput.value = '';
    confirmacionOtroInput.value = '';
};

const handleSubmit = () => {
    if (props.mode === 'view') {
        closeDialog();
        return;
    }

    if (!props.sample) return;

    // Sincronizar arrays desde los estados de los toggles + otros
    updateScreeningForm();
    updateConfirmacionForm();

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
        sample_taken_at: form.sample_taken_at,
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
                        <div class="flex items-center justify-between">
                            <Label class="text-base">Screening</Label>
                            <Button v-if="!isReadOnly" type="button" @click="handleSelectAllScreening" variant="ghost" size="sm" class="h-8 text-xs">
                                {{ allScreeningSelected ? 'Deseleccionar Todos' : 'Seleccionar Todos' }}
                            </Button>
                        </div>
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
                            <!-- Drogas personalizadas -->
                            <div
                                v-for="otro in screeningOtros"
                                :key="'screening-otro-' + otro"
                                class="inline-flex items-center gap-1 rounded-md border border-input bg-secondary px-3 py-1.5 text-sm font-medium"
                            >
                                <span>{{ otro }}</span>
                                <button
                                    v-if="!isReadOnly"
                                    type="button"
                                    @click="removeScreeningOtro(otro)"
                                    class="ml-1 rounded-sm hover:bg-secondary-foreground/10"
                                >
                                    <X class="h-3 w-3" />
                                </button>
                            </div>
                            <!-- Botón Agregar Otros -->
                            <Button
                                v-if="!isReadOnly"
                                type="button"
                                @click="screeningOtrosDialog = true"
                                variant="outline"
                                size="sm"
                                class="gap-1 border-dashed"
                            >
                                <PlusCircle class="h-4 w-4" />
                                Otros
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
                            <!-- Drogas personalizadas -->
                            <div
                                v-for="otro in confirmacionOtros"
                                :key="'confirmacion-otro-' + otro"
                                class="inline-flex items-center gap-1 rounded-md border border-input bg-secondary px-3 py-1.5 text-sm font-medium"
                            >
                                <span>{{ otro }}</span>
                                <button
                                    v-if="!isReadOnly"
                                    type="button"
                                    @click="removeConfirmacionOtro(otro)"
                                    class="ml-1 rounded-sm hover:bg-secondary-foreground/10"
                                >
                                    <X class="h-3 w-3" />
                                </button>
                            </div>
                            <!-- Botón Agregar Otros -->
                            <Button
                                v-if="!isReadOnly"
                                type="button"
                                @click="confirmacionOtrosDialog = true"
                                variant="outline"
                                size="sm"
                                class="gap-1 border-dashed"
                            >
                                <PlusCircle class="h-4 w-4" />
                                Otros
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
                            <Label for="fecha_toma_muestra" class="px-1">Fecha de Toma de Muestra</Label>
                            <Popover v-model:open="fechaTomaMuestraOpen">
                                <PopoverTrigger as-child>
                                    <Button
                                        id="fecha_toma_muestra"
                                        variant="outline"
                                        :class="['w-full justify-start text-left font-normal', !fechaTomaMuestraDate && 'text-muted-foreground']"
                                        :disabled="isReadOnly"
                                    >
                                        <CalendarIcon class="mr-2 h-4 w-4" />
                                        {{
                                            fechaTomaMuestraDate
                                                ? fechaTomaMuestraDate.toDate(getLocalTimeZone()).toLocaleDateString('es-CL')
                                                : 'Selecciona fecha…'
                                        }}
                                    </Button>
                                </PopoverTrigger>
                                <PopoverContent class="w-auto overflow-hidden p-0" align="start">
                                    <Calendar :model-value="fechaTomaMuestraDate" @update:model-value="updateFechaTomaMuestra" />
                                </PopoverContent>
                            </Popover>
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
                        <div class="space-y-2">
                            <Label for="hora_ingreso">Hora de Ingreso</Label>
                            <Input id="hora_ingreso" v-model="horaIngreso" type="time" :disabled="isReadOnly" @change="updateHoraIngreso" />
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

    <!-- Dialog para agregar drogas personalizadas en Screening -->
    <Dialog :open="screeningOtrosDialog" @update:open="(val: boolean) => (screeningOtrosDialog = val)">
        <DialogContent class="sm:max-w-[425px]">
            <DialogHeader>
                <DialogTitle>Agregar Droga Personalizada - Screening</DialogTitle>
                <DialogDescription>Ingresa el nombre de la droga en mayúsculas (ej: MDMA, LSD)</DialogDescription>
            </DialogHeader>
            <div class="space-y-4 py-4">
                <div class="space-y-2">
                    <Label for="screening-otro-input">Nombre de la Droga</Label>
                    <Input
                        id="screening-otro-input"
                        v-model="screeningOtroInput"
                        placeholder="Ej: MDMA"
                        @keyup.enter="addScreeningOtro"
                        class="uppercase"
                    />
                </div>
            </div>
            <DialogFooter>
                <Button type="button" variant="outline" @click="screeningOtrosDialog = false">Cancelar</Button>
                <Button type="button" @click="addScreeningOtro" :disabled="!screeningOtroInput.trim()">Agregar</Button>
            </DialogFooter>
        </DialogContent>
    </Dialog>

    <!-- Dialog para agregar drogas personalizadas en Confirmación -->
    <Dialog :open="confirmacionOtrosDialog" @update:open="(val: boolean) => (confirmacionOtrosDialog = val)">
        <DialogContent class="sm:max-w-[425px]">
            <DialogHeader>
                <DialogTitle>Agregar Droga Personalizada - Confirmación</DialogTitle>
                <DialogDescription>Ingresa el nombre de la droga en mayúsculas (ej: MDMA, LSD)</DialogDescription>
            </DialogHeader>
            <div class="space-y-4 py-4">
                <div class="space-y-2">
                    <Label for="confirmacion-otro-input">Nombre de la Droga</Label>
                    <Input
                        id="confirmacion-otro-input"
                        v-model="confirmacionOtroInput"
                        placeholder="Ej: MDMA"
                        @keyup.enter="addConfirmacionOtro"
                        class="uppercase"
                    />
                </div>
            </div>
            <DialogFooter>
                <Button type="button" variant="outline" @click="confirmacionOtrosDialog = false">Cancelar</Button>
                <Button type="button" @click="addConfirmacionOtro" :disabled="!confirmacionOtroInput.trim()">Agregar</Button>
            </DialogFooter>
        </DialogContent>
    </Dialog>
</template>
