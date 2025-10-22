<script setup lang="ts">
import { buttonVariants } from '@/components/ui/button';
import DataTable from '@/components/ui/table/DataTable.vue';
import AppLayout from '@/layouts/AppLayout.vue';
import type { PageProps } from '@/types';
import { Head, Link, usePage } from '@inertiajs/vue3';
import { ref } from 'vue';
import { Company, companyColumns } from './columns';

const page = usePage<PageProps>();
const data = ref<Company[]>([...((page.props.company as unknown as Company[]) ?? [])]);
</script>

<template>
    <Head title="Empresa" />
    <AppLayout>
        <div class="p-4">
            <h1 class="mb-4 text-2xl font-semibold">Empresas</h1>

            <div class="flex justify-end">
                <Link :href="route ? route('company.create') : '/company/create'" :class="buttonVariants({ variant: 'default', size: 'default' })">
                    Crear Empresa
                </Link>
            </div>

            <DataTable :columns="companyColumns" :data="data" class="mt-4" />
        </div>
    </AppLayout>
</template>
