<script setup lang="ts">
import { buttonVariants } from '@/components/ui/button';
import DataTable from '@/components/ui/table/DataTable.vue';
import AppLayout from '@/layouts/AppLayout.vue';
import type { PageProps } from '@/types';
import { Head, Link, usePage } from '@inertiajs/vue3';
import { ref } from 'vue';
import { Role, roleColumns } from './columns';

const page = usePage<PageProps>();
const data = ref<Role[]>([...((page.props.roles as unknown as Role[]) ?? [])]);
</script>

<template>
    <Head title="Roles" />
    <AppLayout>
        <div class="p-4">
            <div class="mb-6">
                <h1 class="text-3xl font-bold tracking-tight">Roles</h1>
                <p class="mt-1 text-muted-foreground">Administra los roles y permisos del sistema</p>
            </div>

            <div class="mb-4 flex justify-end">
                <Link :href="route ? route('roles.create') : '/roles/create'" :class="buttonVariants({ variant: 'default', size: 'default' })">
                    Crear Rol
                </Link>
            </div>

            <DataTable :columns="roleColumns" :data="data" search-placeholder="Buscar por nombre de rol..." :searchable-columns="['name']" />
        </div>
    </AppLayout>
</template>
