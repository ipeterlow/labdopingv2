<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head } from '@inertiajs/vue3';
import MuestrasEnProceso from './dashboard/MuestrasEnProceso.vue';
import TotalMuestrasPorAno from './dashboard/TotalMuestrasPorAno.vue';

interface YearData {
    year: number;
    data: (number | null)[];
}

defineProps<{
    muestrasPorMesAno: YearData[];
    muestrasOrina: YearData[];
    muestrasPelo: YearData[];
    muestrasSaliva: YearData[];
    muestrasEnProceso: { orina: number; pelo: number; saliva: number };
}>();

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Dashboard',
        href: '/dashboard',
    },
];
</script>

<template>
    <Head title="Dashboard" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-4 overflow-x-auto rounded-xl p-4">
            <!-- Tarjetas de muestras en proceso -->
            <MuestrasEnProceso :orina="muestrasEnProceso.orina" :pelo="muestrasEnProceso.pelo" :saliva="muestrasEnProceso.saliva" />

            <!-- Gráfico principal: Total de muestras por año -->
            <TotalMuestrasPorAno :data="muestrasPorMesAno" />

            <!-- Gráficos por tipo de muestra -->
            <div class="grid min-w-0 gap-4 lg:grid-cols-3">
                <TotalMuestrasPorAno :data="muestrasOrina" title="Muestras de Orina" description="Evolución mensual — Orina" compact />
                <TotalMuestrasPorAno :data="muestrasPelo" title="Muestras de Pelo" description="Evolución mensual — Pelo" compact />
                <TotalMuestrasPorAno :data="muestrasSaliva" title="Muestras de Saliva" description="Evolución mensual — Saliva" compact />
            </div>
        </div>
    </AppLayout>
</template>
