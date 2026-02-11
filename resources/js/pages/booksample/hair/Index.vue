<script setup lang="ts">
import { buttonVariants } from '@/components/ui/button';
import DataTable from '@/components/ui/table/DataTable.vue';
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, router, usePage } from '@inertiajs/vue3';
import { computed, provide, ref } from 'vue';
import { HairSample, hairSampleColumns } from './columns';
import ExportDialog from './ExportDialog.vue';
import HairSampleDialog from './HairSampleDialog.vue';
import ResultsDialog from './ResultsDialog.vue';

// Tipos para paginación del servidor
interface ServerPagination {
    current_page: number;
    last_page: number;
    per_page: number;
    total: number;
    from: number | null;
    to: number | null;
}

interface ServerFilters {
    search: string;
    per_page: number;
}

const page = usePage();

// Datos reactivos desde el servidor
const data = computed(() => (page.props.hairSamples as HairSample[]) ?? []);
const pagination = computed(() => page.props.pagination as ServerPagination | undefined);
const filters = computed(() => page.props.filters as ServerFilters | undefined);

// Estado del dialog
const dialogOpen = ref(false);
const selectedSample = ref<HairSample | null>(null);
const dialogMode = ref<'view' | 'edit'>('edit');

// Estado del dialog de resultados
const resultsDialogOpen = ref(false);
const selectedSampleForResults = ref<HairSample | null>(null);

// Estado del dialog de exportar
const exportDialogOpen = ref(false);

// Manejar eventos de las acciones
const handleEdit = (sample: HairSample) => {
    selectedSample.value = sample;
    dialogMode.value = 'edit';
    dialogOpen.value = true;
};

const handleView = (sample: HairSample) => {
    selectedSample.value = sample;
    dialogMode.value = 'view';
    dialogOpen.value = true;
};

const handleResults = (sample: HairSample) => {
    selectedSampleForResults.value = sample;
    resultsDialogOpen.value = true;
};

const handleSuccess = () => {
    // Recargar solo los datos necesarios
    router.reload({ only: ['hairSamples', 'pagination'] });
};

// Proporcionar handlers globalmente a través de provide/inject
provide('handleEdit', handleEdit);
provide('handleView', handleView);
provide('handleResults', handleResults);
</script>

<template>
    <Head title="Libro de Muestras - Pelo" />
    <AppLayout>
        <div class="p-4">
            <div class="mb-6">
                <h1 class="text-2xl font-semibold">Libro de Muestras - Pelo</h1>
                <p class="text-sm text-muted-foreground">Gestión de características de muestras de pelo</p>
            </div>

            <div class="mb-4 flex justify-end gap-2">
                <button @click="exportDialogOpen = true" :class="buttonVariants({ variant: 'outline', size: 'default' })">Exportar</button>
            </div>

            <DataTable
                :columns="hairSampleColumns"
                :data="data"
                search-placeholder="Buscar por ID externo, interno, empresa..."
                :searchable-columns="['external_id', 'internal_id', 'company_name', 'largo', 'color']"
                :server-mode="true"
                :server-pagination="pagination"
                :server-filters="filters"
            />

            <!-- Dialog para editar/ver -->
            <HairSampleDialog v-model:open="dialogOpen" :sample="selectedSample" :mode="dialogMode" @success="handleSuccess" />

            <!-- Dialog para resultados -->
            <ResultsDialog v-model:open="resultsDialogOpen" :sample="selectedSampleForResults" @success="handleSuccess" />

            <!-- Dialog para exportar -->
            <ExportDialog v-model:open="exportDialogOpen" />
        </div>
    </AppLayout>
</template>
