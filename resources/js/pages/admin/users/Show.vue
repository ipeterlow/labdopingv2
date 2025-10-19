<script setup lang="ts">
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, useForm } from '@inertiajs/vue3';

// Props que vienen del controlador
const props = defineProps<{
    user: {
        name: string;
        email: string;
    };
}>();

// Inicializamos el form con los datos del usuario
const form = useForm({
    name: props.user.name,
    email: props.user.email,
});
</script>

<template>
    <Head title="Show User" />
    <AppLayout>
        <div class="p-4">
            <h1 class="mb-4 text-2xl font-semibold">User Details</h1>

            <div class="w-8/12 space-y-4">
                <!-- NAME -->
                <div class="space-y-2">
                    <Label for="name">Nombre</Label>
                    <Input id="name" type="text" v-model="form.name" readonly />
                </div>

                <!-- EMAIL -->
                <div class="space-y-2">
                    <Label for="email">Email</Label>
                    <Input id="email" type="email" v-model="form.email" readonly />
                </div>
            </div>
        </div>
    </AppLayout>
</template>
