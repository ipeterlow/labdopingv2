<script setup lang="ts">
import ToastAlert from '@/components/ui/ToastAlert.vue'; // <-- Importa tu componente global
import AppLayout from '@/layouts/app/AppSidebarLayout.vue';
import type { BreadcrumbItemType } from '@/types';

interface Props {
    breadcrumbs?: BreadcrumbItemType[];
}

withDefaults(defineProps<Props>(), {
    breadcrumbs: () => [],
});
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbs">
        <slot />
        <!-- ToastAlert global para flash messages -->
        <ToastAlert />
    </AppLayout>
</template>
