<script setup lang="ts">
import { buttonVariants } from '@/components/ui/button';
import DataTable from '@/components/ui/table/DataTable.vue';
import AppLayout from '@/layouts/AppLayout.vue';
import type { PageProps } from '@/types';
import { Head, Link, usePage } from '@inertiajs/vue3';
import { nextTick, onBeforeUnmount, onMounted, ref, watch } from 'vue';
import { User, userColumns } from './columns';

const page = usePage<PageProps>();
const data = ref<User[]>([...((page.props.users as unknown as User[]) ?? [])]);

</script>

<template>
    <Head title="Users" />
    <AppLayout>
        <div class="p-4">
            <h1 class="mb-4 text-2xl font-semibold">Users</h1>

            <div class="flex justify-end">
                <Link :href="route ? route('users.create') : '/users/create'" :class="buttonVariants({ variant: 'default', size: 'default' })">
                    Crear Usuario
                </Link>
            </div>

            <DataTable :columns="userColumns" :data="data" class="mt-4" />
        </div>
    </AppLayout>
</template>
