<script setup lang="ts">
import DataTable from '@/components/ui/table/DataTable.vue';
import AppLayout from '@/layouts/AppLayout.vue';
import type { PageProps } from '@/types';
import { Head, router, usePage } from '@inertiajs/vue3';
import { computed, provide, ref, watch } from 'vue';
import { SalivaSample, salivaSampleColumns } from './columns';
import ResultsDialog from './ResultsDialog.vue';
import SalivaSampleDialog from './SalivaSampleDialog.vue';

const page = usePage<PageProps>();
const rawData = computed(() => (page.props.salivaSamples as unknown as SalivaSample[]) ?? []);
const data = ref<SalivaSample[]>([...rawData.value]);

// Sincronizar data cuando rawData cambie
watch(
    rawData,
    (newData) => {
        data.value = [...newData];
    },
    { deep: true },
);

// Estado del dialog
const dialogOpen = ref(false);
const selectedSample = ref<SalivaSample | null>(null);
const dialogMode = ref<'view' | 'edit'>('edit');

// Estado del dialog de resultados
const resultsDialogOpen = ref(false);
const selectedSampleForResults = ref<SalivaSample | null>(null);

// Manejar eventos de las acciones
const handleEdit = (sample: SalivaSample) => {
    selectedSample.value = sample;
    dialogMode.value = 'edit';
    dialogOpen.value = true;
};

const handleView = (sample: SalivaSample) => {
    selectedSample.value = sample;
    dialogMode.value = 'view';
    dialogOpen.value = true;
};

const handleResults = (sample: SalivaSample) => {
    selectedSampleForResults.value = sample;
    resultsDialogOpen.value = true;
};

const handleSuccess = () => {
    // Recargar la página para obtener datos actualizados
    router.reload({ only: ['salivaSamples'] });
};

// Proporcionar handlers globalmente a través de provide/inject
provide('handleEdit', handleEdit);
provide('handleView', handleView);
provide('handleResults', handleResults);
</script>

<template>
    <Head title="Libro de Muestras - Saliva" />
    <AppLayout>
        <div class="p-4">
            <div class="mb-6">
                <h1 class="text-2xl font-semibold">Libro de Muestras - Saliva</h1>
                <p class="text-sm text-muted-foreground">Gestión de características de muestras de saliva</p>
            </div>

            <DataTable
                :columns="salivaSampleColumns"
                :data="data"
                search-placeholder="Buscar por ID externo, interno, empresa..."
                :searchable-columns="['external_id', 'internal_id', 'company_name', 'ph', 'densidad', 'volumen']"
            />

            <!-- Dialog para editar/ver -->
            <SalivaSampleDialog v-model:open="dialogOpen" :sample="selectedSample" :mode="dialogMode" @success="handleSuccess" />

            <!-- Dialog para resultados -->
            <ResultsDialog v-model:open="resultsDialogOpen" :sample="selectedSampleForResults" @success="handleSuccess" />
        </div>
    </AppLayout>
</template>
