<script setup lang="ts">
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import {
    CategoryScale,
    Chart as ChartJS,
    Filler,
    Legend,
    LinearScale,
    LineElement,
    PointElement,
    Title,
    Tooltip,
} from 'chart.js';
import { computed } from 'vue';
import { Line } from 'vue-chartjs';

// Registrar componentes de Chart.js
ChartJS.register(CategoryScale, LinearScale, PointElement, LineElement, Title, Tooltip, Legend, Filler);

interface YearData {
    year: number;
    data: (number | null)[];
}

const props = withDefaults(defineProps<{
    data: YearData[];
    title?: string;
    description?: string;
    compact?: boolean;
}>(), {
    title: 'Total de Muestras por Año',
    description: 'Evolución mensual de muestras recibidas',
    compact: false,
});

const months = ['Ene', 'Feb', 'Mar', 'Abr', 'May', 'Jun', 'Jul', 'Ago', 'Sep', 'Oct', 'Nov', 'Dic'];

// Paleta de colores para las líneas (hasta 10 años)
const colorPalette = [
    { border: 'rgb(59, 130, 246)', background: 'rgba(59, 130, 246, 0.1)' },   // Azul
    { border: 'rgb(16, 185, 129)', background: 'rgba(16, 185, 129, 0.1)' },   // Verde
    { border: 'rgb(245, 158, 11)', background: 'rgba(245, 158, 11, 0.1)' },   // Ámbar
    { border: 'rgb(239, 68, 68)', background: 'rgba(239, 68, 68, 0.1)' },     // Rojo
    { border: 'rgb(139, 92, 246)', background: 'rgba(139, 92, 246, 0.1)' },   // Violeta
    { border: 'rgb(236, 72, 153)', background: 'rgba(236, 72, 153, 0.1)' },   // Rosa
    { border: 'rgb(6, 182, 212)', background: 'rgba(6, 182, 212, 0.1)' },     // Cyan
    { border: 'rgb(251, 146, 60)', background: 'rgba(251, 146, 60, 0.1)' },   // Naranja
    { border: 'rgb(34, 197, 94)', background: 'rgba(34, 197, 94, 0.1)' },     // Esmeralda
    { border: 'rgb(168, 85, 247)', background: 'rgba(168, 85, 247, 0.1)' },   // Púrpura
];

const chartData = computed(() => ({
    labels: months,
    datasets: props.data.map((yearData, index) => ({
        label: String(yearData.year),
        data: yearData.data,
        borderColor: colorPalette[index % colorPalette.length].border,
        backgroundColor: colorPalette[index % colorPalette.length].background,
        borderWidth: 2.5,
        pointBackgroundColor: colorPalette[index % colorPalette.length].border,
        pointBorderColor: '#fff',
        pointBorderWidth: props.compact ? 1 : 2,
        pointRadius: props.compact ? 2 : 4,
        pointHoverRadius: props.compact ? 5 : 7,
        tension: 0.3,
        fill: false,
        spanGaps: false,
    })),
}));

const chartOptions = computed(() => ({
    responsive: true,
    maintainAspectRatio: false,
    resizeDelay: 0,
    interaction: {
        mode: 'index' as const,
        intersect: false,
    },
    plugins: {
        legend: {
            position: 'top' as const,
            align: 'end' as const,
            labels: {
                usePointStyle: true,
                pointStyle: 'circle',
                padding: props.compact ? 8 : 20,
                boxWidth: props.compact ? 6 : 40,
                font: {
                    size: props.compact ? 10 : 12,
                    weight: 500 as const,
                },
            },
        },
        tooltip: {
            backgroundColor: 'rgba(0, 0, 0, 0.8)',
            titleFont: { size: 13, weight: 'bold' as const },
            bodyFont: { size: 12 },
            padding: 12,
            cornerRadius: 8,
            displayColors: true,
            usePointStyle: true,
            callbacks: {
                title: (items: any[]) => {
                    if (items.length > 0) {
                        const monthIndex = items[0].dataIndex;
                        const fullMonths = [
                            'Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio',
                            'Julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre',
                        ];
                        return fullMonths[monthIndex];
                    }
                    return '';
                },
                label: (context: any) => {
                    return ` ${context.dataset.label}: ${context.parsed.y} muestras`;
                },
            },
        },
    },
    scales: {
        x: {
            grid: {
                display: false,
            },
            ticks: {
                font: { size: props.compact ? 9 : 12 },
                maxRotation: props.compact ? 45 : 0,
            },
        },
        y: {
            beginAtZero: true,
            grid: {
                color: 'rgba(0, 0, 0, 0.06)',
            },
            ticks: {
                font: { size: props.compact ? 9 : 12 },
                precision: 0,
            },
        },
    },
}));

// Total general de muestras (ignorar nulls)
const totalMuestras = computed(() => {
    return props.data.reduce((sum, yearData) => {
        return sum + yearData.data.reduce((s: number, v) => s + (v ?? 0), 0);
    }, 0);
});
</script>

<template>
    <Card class="min-w-0 shadow-sm transition-shadow hover:shadow-md">
        <CardHeader>
            <CardTitle :class="compact ? 'text-base' : ''">{{ title }}</CardTitle>
            <CardDescription>{{ description }}</CardDescription>
        </CardHeader>
        <CardContent>
            <div :class="compact ? 'h-[220px]' : 'h-[280px]'">
                <Line v-if="data.length > 0" :data="chartData" :options="chartOptions" />
                <div v-else class="flex h-full items-center justify-center text-muted-foreground">
                    No hay datos disponibles
                </div>
            </div>
        </CardContent>
    </Card>
</template>
