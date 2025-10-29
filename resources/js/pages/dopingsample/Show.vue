<script setup lang="ts">
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, useForm } from '@inertiajs/vue3';

// Props que vienen del controlador
const props = defineProps<{
    sample: {
        external_id: string;
        internal_id: string;
        type: string;
        description: string;
        shipping_type: string;
        category: string;
        status_name: string;
        sent_at: string;
        received_at: string;
        analyzed_at: string;
        company_name: number;
        user_id: number;
        document: string;
        reception_id: number;
        sample_taken_at: string;
        results_at: string;
        deleted_at: string;
    };
}>();

// Inicializamos el form con los datos de la muestra
const form = useForm({
    external_id: props.sample.external_id,
    internal_id: props.sample.internal_id,
    type: props.sample.type,
    description: props.sample.description,
    shipping_type: props.sample.shipping_type,
    category: props.sample.category,
    status_name: props.sample.status_name,
    sent_at: props.sample.sent_at,
    received_at: props.sample.received_at,
    analyzed_at: props.sample.analyzed_at,
    company_name: props.sample.company_name,
    user_id: props.sample.user_id,
    document: props.sample.document,
    reception_id: props.sample.reception_id,
    sample_taken_at: props.sample.sample_taken_at,
    results_at: props.sample.results_at,
    deleted_at: props.sample.deleted_at,
});
</script>
<template>
    <Head title="Ver Muestra" />
    <AppLayout>
        <div class="w-full max-w-5xl space-y-4 p-6">
            <h1 class="text-2xl font-semibold">Detalles de la Muestra</h1>

            <div class="space-y-4 rounded-md border bg-card p-6">
                <div class="space-y-2">
                    <Label for="external_id">ID Externo</Label>
                    <Input id="external_id" type="text" v-model="form.external_id" readonly />
                </div>
                <div class="space-y-2">
                    <Label for="internal_id">ID Interno</Label>
                    <Input id="internal_id" type="text" v-model="form.internal_id" readonly />
                </div>
                <div class="space-y-2">
                    <Label for="type">Tipo de Muestra</Label>
                    <Input id="type" type="text" v-model="form.type" readonly />
                </div>
                <div class="space-y-2">
                    <Label for="description">Descripción</Label>
                    <Input id="description" type="text" v-model="form.description" readonly />
                </div>

                <div class="space-y-2">
                    <Label for="status">CET/Empresa</Label>
                    <Input id="status" type="text" v-model="form.company_name" readonly />
                </div>

                <div class="space-y-2">
                    <Label for="status">Estado</Label>
                    <Input id="status" type="text" v-model="form.status_name" readonly />
                </div>

                <div class="space-y-2">
                    <Label for="sent_at">Fecha Envio</Label>
                    <Input id="sent_at" type="text" v-model="form.sent_at" readonly />
                </div>
                <div class="space-y-2">
                    <Label for="received_at">Fecha Recepcion</Label>
                    <Input id="received_at" type="text" v-model="form.received_at" readonly />
                </div>
                <div class="space-y-2">
                    <Label for="analyzed_at">Fecha Analisis</Label>
                    <Input id="analyzed_at" type="text" v-model="form.analyzed_at" readonly />
                </div>

                <div class="space-y-2">
                    <Label for="sample_taken_at">Fecha Toma de Muestra</Label>
                    <Input id="sample_taken_at" type="text" v-model="form.sample_taken_at" readonly />
                </div>
                <div class="space-y-2">
                    <Label for="results_at">Fecha Resultado</Label>
                    <Input id="results_at" type="text" v-model="form.results_at" readonly />
                </div>
            </div>
        </div>
    </AppLayout>
</template>
