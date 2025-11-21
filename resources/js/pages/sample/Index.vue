<script setup lang="ts">
import DataTable from '@/components/ui/table/DataTable.vue';
import AppLayout from '@/layouts/AppLayout.vue';
import type { PageProps } from '@/types';
import { Head, usePage } from '@inertiajs/vue3';
import { ref } from 'vue';
import { Sample, sampleColumns } from './columns';

const page = usePage<PageProps>();
const data = ref<Sample[]>([...((page.props.sample as unknown as Sample[]) ?? [])]);
</script>

<template>
    <Head title="Reportes de Muestras" />
    <AppLayout>
        <div class="p-4">
            <h1 class="mb-4 text-2xl font-semibold">Reportes de Muestras</h1>

            <DataTable :columns="sampleColumns" :data="data" class="mt-4" />
        </div>
    </AppLayout>
</template>
