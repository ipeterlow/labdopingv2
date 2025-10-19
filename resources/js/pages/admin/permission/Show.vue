<script setup lang="ts">
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, useForm } from '@inertiajs/vue3';

// Props que vienen del controlador
const props = defineProps<{
    permission: {
        name: string;
        created_at: Date;
    };
}>();

// Inicializamos el form con los datos del permiso
const form = useForm({
    name: props.permission.name,
    created_at: new Date(props.permission.created_at).toLocaleDateString(),
});
</script>

<template>
    <Head title="Show Permission" />
    <AppLayout>
        <div class="p-4">
            <h1 class="mb-4 text-2xl font-semibold">Permission Details</h1>

            <div class="w-8/12 space-y-4">
                <!-- NAME -->
                <div class="space-y-2">
                    <Label for="name">Nombre</Label>
                    <Input id="name" type="text" v-model="form.name" readonly />
                </div>

                <!-- EMAIL -->
                <div class="space-y-2">
                    <Label for="created_at">Creado</Label>
                    <Input id="created_at" type="text" v-model="form.created_at" readonly />
                </div>
            </div>
        </div>
    </AppLayout>
</template>
