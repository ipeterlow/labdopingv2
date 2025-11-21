<script setup lang="ts">
import DataTable from '@/components/ui/table/DataTable.vue';
import AppLayout from '@/layouts/AppLayout.vue';
import type { PageProps } from '@/types';
import { Head, router, usePage } from '@inertiajs/vue3';
import { computed, ref, watch } from 'vue';
import { UrineSample, urineSampleColumns } from './columns';
import UrineSampleDialog from './UrineSampleDialog.vue';

const page = usePage<PageProps>();
const rawData = computed(() => (page.props.urineSamples as unknown as UrineSample[]) ?? []);
const data = ref<UrineSample[]>([...rawData.value]);

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
const selectedSample = ref<UrineSample | null>(null);
const dialogMode = ref<'view' | 'edit'>('edit');

// Manejar eventos de las acciones
const handleEdit = (sample: UrineSample) => {
    selectedSample.value = sample;
    dialogMode.value = 'edit';
    dialogOpen.value = true;
};

const handleView = (sample: UrineSample) => {
    selectedSample.value = sample;
    dialogMode.value = 'view';
    dialogOpen.value = true;
};

const handleSuccess = () => {
    // Recargar la página para obtener datos actualizados
    router.reload({ only: ['urineSamples'] });
};

// Proporcionar handlers globalmente a través de provide/inject
import { provide } from 'vue';
provide('handleEdit', handleEdit);
provide('handleView', handleView);
</script>

<template>
    <Head title="Libro de Muestras - Orina" />
    <AppLayout>
        <div class="p-4">
            <div class="mb-6">
                <h1 class="text-2xl font-semibold">Libro de Muestras - Orina</h1>
                <p class="text-sm text-muted-foreground">Gestión de características de muestras de orina</p>
            </div>

            <DataTable
                :columns="urineSampleColumns"
                :data="data"
                search-placeholder="Buscar por ID externo, interno, empresa..."
                :searchable-columns="['external_id', 'internal_id', 'company_name', 'ph', 'densidad']"
            />

            <!-- Dialog para editar/ver -->
            <UrineSampleDialog v-model:open="dialogOpen" :sample="selectedSample" :mode="dialogMode" @success="handleSuccess" />
        </div>
    </AppLayout>
</template>
