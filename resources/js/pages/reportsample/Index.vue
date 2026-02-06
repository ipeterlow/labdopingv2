<script setup lang="ts">
import DataTable from '@/components/ui/table/DataTable.vue';
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';
import { Sample, sampleColumns } from './columns';

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
const data = computed(() => (page.props.sample as Sample[]) ?? []);
const pagination = computed(() => page.props.pagination as ServerPagination | undefined);
const filters = computed(() => page.props.filters as ServerFilters | undefined);
</script>

<template>
    <Head title="Informes Muestras" />
    <AppLayout>
        <div class="p-4">
            <h1 class="mb-4 text-2xl font-semibold">Informes Muestras</h1>

            <DataTable
                :columns="sampleColumns"
                :data="data"
                class="mt-4"
                search-placeholder="Buscar por ID externo, interno, empresa o estado..."
                :searchable-columns="['external_id', 'internal_id', 'company_name', 'status_name']"
                :server-mode="true"
                :server-pagination="pagination"
                :server-filters="filters"
            />
        </div>
    </AppLayout>
</template>
