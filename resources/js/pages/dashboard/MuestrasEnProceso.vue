<script setup lang="ts">
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { FlaskConical, Scissors, Droplets } from 'lucide-vue-next';

defineProps<{
    orina: number;
    pelo: number;
    saliva: number;
}>();
</script>

<template>
    <div class="grid min-w-0 gap-4 lg:grid-cols-3">
        <!-- Orina -->
        <Card class="border-t-4 border-t-amber-500 shadow-sm transition-shadow hover:shadow-md">
            <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
                <CardTitle class="text-sm font-medium">Orina en Proceso</CardTitle>
                <div class="rounded-md bg-amber-500/10 p-2">
                    <FlaskConical class="h-5 w-5 text-amber-500" />
                </div>
            </CardHeader>
            <CardContent>
                <div class="text-3xl font-bold">{{ orina.toLocaleString('es-CL') }}</div>
                <p class="text-xs text-muted-foreground">Muestras en análisis — {{ new Date().getFullYear() }}</p>
            </CardContent>
        </Card>

        <!-- Pelo -->
        <Card class="border-t-4 border-t-emerald-500 shadow-sm transition-shadow hover:shadow-md">
            <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
                <CardTitle class="text-sm font-medium">Pelo en Proceso</CardTitle>
                <div class="rounded-md bg-emerald-500/10 p-2">
                    <Scissors class="h-5 w-5 text-emerald-500" />
                </div>
            </CardHeader>
            <CardContent>
                <div class="text-3xl font-bold">{{ pelo.toLocaleString('es-CL') }}</div>
                <p class="text-xs text-muted-foreground">Muestras en análisis — {{ new Date().getFullYear() }}</p>
            </CardContent>
        </Card>

        <!-- Saliva -->
        <Card class="border-t-4 border-t-blue-500 shadow-sm transition-shadow hover:shadow-md">
            <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
                <CardTitle class="text-sm font-medium">Saliva en Proceso</CardTitle>
                <div class="rounded-md bg-blue-500/10 p-2">
                    <Droplets class="h-5 w-5 text-blue-500" />
                </div>
            </CardHeader>
            <CardContent>
                <div class="text-3xl font-bold">{{ saliva.toLocaleString('es-CL') }}</div>
                <p class="text-xs text-muted-foreground">Muestras en análisis — {{ new Date().getFullYear() }}</p>
            </CardContent>
        </Card>
    </div>
</template>
