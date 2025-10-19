<script setup lang="ts">
import { buttonVariants } from '@/components/ui/button';
import DataTable from '@/components/ui/table/DataTable.vue';
import AppLayout from '@/layouts/AppLayout.vue';
import type { PageProps } from '@/types';
import { Head, Link, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';
import { Permission, permissionColumns } from './columns';

const page = usePage<PageProps>();
const data = computed<Permission[]>(() => (page.props.permissions as unknown as Permission[]) ?? []);
</script>

<template>
    <Head title="Permisos" />
    <AppLayout>
        <div class="p-4">
            <h1 class="mb-4 text-2xl font-semibold">Permisos</h1>

            <div class="flex justify-end">
                <Link
                    :href="route ? route('permissions.create') : '/permissions/create'"
                    :class="buttonVariants({ variant: 'default', size: 'default' })"
                >
                    Crear Permiso
                </Link>
            </div>

            <DataTable :columns="permissionColumns" :data="data" class="mt-4" />
        </div>
    </AppLayout>
</template>
