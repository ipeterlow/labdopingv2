<script setup lang="ts">
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, useForm } from '@inertiajs/vue3';

// Props que vienen del controlador
const props = defineProps<{
    company: {
        name: string;
        email: string;
        number: string;
    };
}>();

// Inicializamos el form con los datos de la empresa
const form = useForm({
    name: props.company.name,
    email: props.company.email,
    number: props.company.number,
});
</script>
<template>
    <Head title="Ver Empresa" />
    <AppLayout>
        <div class="w-full max-w-5xl space-y-4 p-6">
            <h1 class="text-2xl font-semibold">Detalles de la Empresa</h1>

            <div class="space-y-4 rounded-md border bg-card p-6">
                <div class="space-y-2">
                    <Label for="name">Nombre</Label>
                    <Input id="name" type="text" v-model="form.name" readonly />
                </div>

                <div class="space-y-2">
                    <Label for="email">Email</Label>
                    <Input id="email" type="email" v-model="form.email" readonly />
                </div>

                <div class="space-y-2">
                    <Label for="number">Número</Label>
                    <Input id="number" type="text" v-model="form.number" readonly />
                </div>
            </div>
        </div>
    </AppLayout>
</template>
