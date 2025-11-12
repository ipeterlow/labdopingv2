<script setup lang="ts">
import { buttonVariants } from '@/components/ui/button';
import DataTable from '@/components/ui/table/DataTable.vue';
import AppLayout from '@/layouts/AppLayout.vue';
import type { PageProps } from '@/types';
import { Head, Link, usePage } from '@inertiajs/vue3';
import { ref } from 'vue';
import { Sample, sampleColumns } from './columns';

const page = usePage<PageProps>();
const data = ref<Sample[]>([...((page.props.sample as unknown as Sample[]) ?? [])]);
</script>

<template>
    <Head title="Muestras Doping" />
    <AppLayout>
        <div class="p-4">
            <h1 class="mb-4 text-2xl font-semibold">Muestras Doping</h1>

            <div class="flex justify-end">
                <Link
                    :href="route ? route('dopingsample.create') : '/dopingsample/create'"
                    :class="buttonVariants({ variant: 'default', size: 'default' })"
                >
                    Crear Muestra Doping
                </Link>
            </div>

            <DataTable
                :columns="sampleColumns"
                :data="data"
                class="mt-4"
                search-placeholder="Buscar por ID externo, interno, empresa, estado o fecha de recepción..."
                :searchable-columns="['external_id', 'internal_id', 'company_name', 'status_name', 'received_at']"
            />
        </div>
    </AppLayout>
</template>
