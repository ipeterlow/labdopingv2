<script setup lang="ts">
import { Button } from '@/components/ui/button';
import { Calendar } from '@/components/ui/calendar';
import { Dialog, DialogContent, DialogDescription, DialogFooter, DialogHeader, DialogTitle } from '@/components/ui/dialog';
import { Label } from '@/components/ui/label';
import { Popover, PopoverContent, PopoverTrigger } from '@/components/ui/popover';
import type { DateValue } from '@internationalized/date';
import { getLocalTimeZone } from '@internationalized/date';
import { CalendarIcon, Download } from 'lucide-vue-next';
import { ref } from 'vue';

const props = defineProps<{
    open: boolean;
}>();

const emit = defineEmits<{
    (e: 'update:open', value: boolean): void;
}>();

// Fechas
const fechaInicio = ref<DateValue | undefined>(undefined);
const fechaFin = ref<DateValue | undefined>(undefined);
const fechaInicioOpen = ref(false);
const fechaFinOpen = ref(false);
const isExporting = ref(false);

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
};

const handleExport = async () => {
    if (!fechaInicio.value || !fechaFin.value) {
        alert('Por favor selecciona ambas fechas');
        return;
    }

    isExporting.value = true;

    try {
        const inicio = fechaInicio.value.toDate(getLocalTimeZone()).toISOString().split('T')[0];
        const fin = fechaFin.value.toDate(getLocalTimeZone()).toISOString().split('T')[0];

        // Crear URL con parámetros
        const url = `/booksalivasample/export?fecha_inicio=${inicio}&fecha_fin=${fin}`;

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
                <DialogTitle>Exportar Muestras de Saliva</DialogTitle>
                <DialogDescription>Selecciona el rango de fechas de análisis para exportar las muestras a Excel</DialogDescription>
            </DialogHeader>

            <div class="space-y-4 py-4">
                <div class="grid gap-4 sm:grid-cols-2">
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
            </div>

            <DialogFooter>
                <Button type="button" variant="outline" @click="closeDialog"> Cancelar </Button>
                <Button type="button" @click="handleExport" :disabled="!fechaInicio || !fechaFin || isExporting">
                    <Download class="mr-2 h-4 w-4" />
                    {{ isExporting ? 'Exportando...' : 'Exportar' }}
                </Button>
            </DialogFooter>
        </DialogContent>
    </Dialog>
</template>
