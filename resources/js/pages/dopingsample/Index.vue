<script setup lang="ts">
import { Alert, AlertDescription, AlertTitle } from '@/components/ui/alert';
import { buttonVariants } from '@/components/ui/button';
import DataTable from '@/components/ui/table/DataTable.vue';
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, Link, usePage } from '@inertiajs/vue3';
import { Check } from 'lucide-vue-next';
import { computed, ref, watch } from 'vue';
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

// Sistema de alertas para mensajes flash
const showAlert = ref(false);
const alertMessage = ref('');

// Detectar mensaje flash de success
watch(
    () => (page.props as any).flash?.success,
    (message) => {
        if (message) {
            alertMessage.value = message as string;
            showAlert.value = true;
            setTimeout(() => (showAlert.value = false), 4000);
        }
    },
    { immediate: true },
);
</script>

<template>
    <Head title="Recepcion de Muestras" />
    <AppLayout>
        <!-- Alert flotante para mensajes de éxito -->
        <div class="fixed top-4 right-4 z-50 w-96">
            <transition name="fade">
                <Alert v-if="showAlert" variant="default" class="border-emerald-500 bg-emerald-50 shadow-md dark:bg-emerald-950">
                    <div class="flex items-center gap-2">
                        <Check class="h-5 w-5 text-emerald-600 dark:text-emerald-400" />
                        <AlertTitle class="text-emerald-800 dark:text-emerald-200">Éxito</AlertTitle>
                    </div>
                    <AlertDescription class="text-emerald-700 dark:text-emerald-300">{{ alertMessage }}</AlertDescription>
                </Alert>
            </transition>
        </div>

        <div class="p-4">
            <h1 class="mb-4 text-2xl font-semibold">Recepcion de Muestras</h1>

            <div class="flex justify-end">
                <Link
                    :href="route ? route('dopingsample.create') : '/dopingsample/create'"
                    :class="buttonVariants({ variant: 'default', size: 'default' })"
                >
                    Agregar Muestra
                </Link>
            </div>

            <DataTable
                :columns="sampleColumns"
                :data="data"
                class="mt-4"
                search-placeholder="Buscar por ID externo, interno, empresa, estado o fecha de recepción..."
                :searchable-columns="['external_id', 'internal_id', 'company_name', 'status_name', 'received_at']"
                :server-mode="true"
                :server-pagination="pagination"
                :server-filters="filters"
            />
        </div>
    </AppLayout>
</template>

<style scoped>
.fade-enter-active,
.fade-leave-active {
    transition: opacity 0.2s;
}

.fade-enter-from,
.fade-leave-to {
    opacity: 0;
}
</style>
