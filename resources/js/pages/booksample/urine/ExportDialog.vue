<script setup lang="ts">
import { Button } from '@/components/ui/button';
import { Calendar } from '@/components/ui/calendar';
import { Dialog, DialogContent, DialogDescription, DialogFooter, DialogHeader, DialogTitle } from '@/components/ui/dialog';
import { Label } from '@/components/ui/label';
import { Input } from '@/components/ui/input';
import { Popover, PopoverContent, PopoverTrigger } from '@/components/ui/popover';
import type { DateValue } from '@internationalized/date';
import { getLocalTimeZone } from '@internationalized/date';
import { CalendarIcon, Download } from 'lucide-vue-next';
import { ref, watch } from 'vue';

const props = defineProps<{
    open: boolean;
}>();

const emit = defineEmits<{
    (e: 'update:open', value: boolean): void;
}>();

// Tipo de filtro
const tipoFiltro = ref<'fecha' | 'internal_id'>('fecha');

// Fechas
const fechaInicio = ref<DateValue | undefined>(undefined);
const fechaFin = ref<DateValue | undefined>(undefined);
const fechaInicioOpen = ref(false);
const fechaFinOpen = ref(false);
const isExporting = ref(false);

// Números internos
const internalIdInicio = ref('');
const internalIdFin = ref('');

// Mensaje de error
const errorMessage = ref('');

// Validación reactiva del rango de número interno
watch([internalIdInicio, internalIdFin, tipoFiltro], ([inicio, fin, tipo]) => {
    if (tipo !== 'internal_id') {
        errorMessage.value = '';
        return;
    }

    if (!inicio || !fin) {
        // No marcamos error hasta que ambos campos tengan valor
        errorMessage.value = '';
        return;
    }

    const inicioNumero = Number(inicio);
    const finNumero = Number(fin);

    if (Number.isNaN(inicioNumero) || Number.isNaN(finNumero)) {
        errorMessage.value = 'Los números internos deben ser valores numéricos.';
        return;
    }

    if (inicioNumero > finNumero) {
        errorMessage.value = 'El Nº interno inicio no puede ser mayor que el Nº interno fin.';
        return;
    }

    errorMessage.value = '';
});

const updateFechaInicio = (value: DateValue | undefined) => {
    fechaInicio.value = value;
    fechaInicioOpen.value = false;
};

const updateFechaFin = (value: DateValue | undefined) => {
    fechaFin.value = value;
    fechaFinOpen.value = false;
};

const closeDialog = () => {
    emit('update:open', false);
    fechaInicio.value = undefined;
    fechaFin.value = undefined;
    internalIdInicio.value = '';
    internalIdFin.value = '';
    tipoFiltro.value = 'fecha';
    errorMessage.value = '';
};

const handleExport = async () => {
    errorMessage.value = '';

    isExporting.value = true;

    try {
        let url = '/bookurinesample/export';

        if (tipoFiltro.value === 'fecha') {
            if (!fechaInicio.value || !fechaFin.value) {
                errorMessage.value = 'Debes seleccionar ambas fechas de análisis.';
                return;
            }

            const inicio = fechaInicio.value.toDate(getLocalTimeZone()).toISOString().split('T')[0];
            const fin = fechaFin.value.toDate(getLocalTimeZone()).toISOString().split('T')[0];

            url += `?tipo_filtro=fecha&fecha_inicio=${inicio}&fecha_fin=${fin}`;
        } else {
            if (!internalIdInicio.value || !internalIdFin.value) {
                errorMessage.value = 'Debes ingresar ambos números internos (inicio y fin).';
                return;
            }

            const inicioNumero = Number(internalIdInicio.value);
            const finNumero = Number(internalIdFin.value);

            if (Number.isNaN(inicioNumero) || Number.isNaN(finNumero)) {
                errorMessage.value = 'Los números internos deben ser valores numéricos.';
                return;
            }

            if (inicioNumero > finNumero) {
                errorMessage.value = 'El Nº interno inicio no puede ser mayor que el Nº interno fin.';
                return;
            }

            const inicio = encodeURIComponent(internalIdInicio.value.trim());
            const fin = encodeURIComponent(internalIdFin.value.trim());

            url += `?tipo_filtro=internal_id&internal_id_inicio=${inicio}&internal_id_fin=${fin}`;
        }

        // Abrir en nueva ventana para descargar
        window.location.href = url;

        closeDialog();
    } catch (error) {
        console.error('Error al exportar:', error);
        alert('Error al exportar los datos');
    } finally {
        isExporting.value = false;
    }
};
</script>

<template>
    <Dialog :open="open" @update:open="(value: boolean) => emit('update:open', value)">
        <DialogContent class="sm:max-w-[500px]">
            <DialogHeader>
                <DialogTitle>Exportar Muestras de Orina</DialogTitle>
                <DialogDescription>Selecciona el rango de fechas de análisis para exportar las muestras a Excel</DialogDescription>
            </DialogHeader>

            <div class="space-y-4 py-4">
                <!-- Selector de tipo de filtro -->
                <div class="space-y-2">
                    <Label>Tipo de filtro</Label>
                    <div class="grid grid-cols-2 gap-2">
                        <Button
                            type="button"
                            variant="outline"
                            :class="tipoFiltro === 'fecha' ? 'border-primary text-primary' : ''"
                            @click="tipoFiltro = 'fecha'"
                        >
                            Por fecha
                        </Button>
                        <Button
                            type="button"
                            variant="outline"
                            :class="tipoFiltro === 'internal_id' ? 'border-primary text-primary' : ''"
                            @click="tipoFiltro = 'internal_id'"
                        >
                            Por Nº interno
                        </Button>
                    </div>
                </div>

                <!-- Filtro por fechas -->
                <div v-if="tipoFiltro === 'fecha'" class="grid gap-4 sm:grid-cols-2">
                    <!-- Fecha Inicio -->
                    <div class="space-y-2">
                        <Label for="fecha_inicio">Fecha Inicio</Label>
                        <Popover v-model:open="fechaInicioOpen">
                            <PopoverTrigger as-child>
                                <Button
                                    id="fecha_inicio"
                                    variant="outline"
                                    :class="['w-full justify-start text-left font-normal', !fechaInicio && 'text-muted-foreground']"
                                >
                                    <CalendarIcon class="mr-2 h-4 w-4" />
                                    {{ fechaInicio ? fechaInicio.toDate(getLocalTimeZone()).toLocaleDateString('es-CL') : 'Selecciona fecha…' }}
                                </Button>
                            </PopoverTrigger>
                            <PopoverContent class="w-auto p-0" align="start">
                                <Calendar :model-value="fechaInicio" @update:model-value="updateFechaInicio" />
                            </PopoverContent>
                        </Popover>
                    </div>

                    <!-- Fecha Fin -->
                    <div class="space-y-2">
                        <Label for="fecha_fin">Fecha Fin</Label>
                        <Popover v-model:open="fechaFinOpen">
                            <PopoverTrigger as-child>
                                <Button
                                    id="fecha_fin"
                                    variant="outline"
                                    :class="['w-full justify-start text-left font-normal', !fechaFin && 'text-muted-foreground']"
                                >
                                    <CalendarIcon class="mr-2 h-4 w-4" />
                                    {{ fechaFin ? fechaFin.toDate(getLocalTimeZone()).toLocaleDateString('es-CL') : 'Selecciona fecha…' }}
                                </Button>
                            </PopoverTrigger>
                            <PopoverContent class="w-auto p-0" align="start">
                                <Calendar :model-value="fechaFin" @update:model-value="updateFechaFin" />
                            </PopoverContent>
                        </Popover>
                    </div>
                </div>

                <!-- Filtro por número interno -->
                <div v-else class="grid gap-4 sm:grid-cols-2">
                    <div class="space-y-2">
                        <Label for="internal_id_inicio">Nº interno inicio</Label>
                        <Input
                            id="internal_id_inicio"
                            v-model="internalIdInicio"
                            type="text"
                            placeholder="Ej: 100"
                        />
                    </div>
                    <div class="space-y-2">
                        <Label for="internal_id_fin">Nº interno fin</Label>
                        <Input
                            id="internal_id_fin"
                            v-model="internalIdFin"
                            type="text"
                            placeholder="Ej: 200"
                        />
                    </div>
                </div>

                <p v-if="errorMessage" class="text-sm text-destructive">
                    {{ errorMessage }}
                </p>
            </div>

            <DialogFooter>
                <Button type="button" variant="outline" @click="closeDialog"> Cancelar </Button>
                <Button
                    type="button"
                    @click="handleExport"
                    :disabled="
                        isExporting ||
                        !!errorMessage ||
                        (tipoFiltro === 'fecha' ? !fechaInicio || !fechaFin : !internalIdInicio || !internalIdFin)
                    "
                >
                    <Download class="mr-2 h-4 w-4" />
                    {{ isExporting ? 'Exportando...' : 'Exportar' }}
                </Button>
            </DialogFooter>
        </DialogContent>
    </Dialog>
</template>
