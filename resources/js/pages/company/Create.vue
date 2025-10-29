<script setup lang="ts">
import { Alert, AlertDescription, AlertTitle } from '@/components/ui/alert';
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, useForm } from '@inertiajs/vue3';
import { CheckCircle, SendHorizonal, XCircle } from 'lucide-vue-next';
import { ref } from 'vue';

// shadcn-vue components
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';

const form = useForm({
    name: '',
    email: '',
    number: '',
});

// Alert
const showAlert = ref(false);
const alertType = ref<'success' | 'error'>('success');
const alertMessage = ref('');

const submit = () => {
    form.post(route('company.store'), {
        preserveScroll: true,
        onSuccess: () => {
            form.reset();
            alertType.value = 'success';
            alertMessage.value = 'La empresa fue creada correctamente';
            showAlert.value = true;
            setTimeout(() => (showAlert.value = false), 3500);
        },
        onError: () => {
            alertType.value = 'error';
            alertMessage.value = 'Ocurrió un error al crear la empresa';
            showAlert.value = true;
            setTimeout(() => (showAlert.value = false), 3500);
        },
    });
};
</script>

<template>
    <Head title="Crear Empresa" />
    <AppLayout>
        <div class="w-full max-w-5xl space-y-4 p-6">
            <h1 class="text-2xl font-semibold">Crear Empresa</h1>

            <div class="fixed top-4 right-4 z-50 w-96">
                <transition name="fade">
                    <Alert v-if="showAlert" :variant="alertType === 'success' ? 'default' : 'destructive'" class="shadow-md">
                        <div class="flex items-center gap-2">
                            <component :is="alertType === 'success' ? CheckCircle : XCircle" class="h-5 w-5" />
                            <AlertTitle>{{ alertType === 'success' ? 'Éxito' : 'Error' }}</AlertTitle>
                        </div>
                        <AlertDescription>{{ alertMessage }}</AlertDescription>
                    </Alert>
                </transition>
            </div>
            <form @submit.prevent="submit" class="space-y-4">
                <div class="space-y-4 rounded-md border bg-card p-6">
                    <div class="space-y-2">
                        <Label for="name">Nombre</Label>
                        <Input id="name" type="text" v-model="form.name" :aria-invalid="!!form.errors.name || undefined" />
                        <p v-if="form.errors.name" class="text-sm text-red-600">
                            {{ form.errors.name }}
                        </p>
                    </div>

                    <div class="space-y-2">
                        <Label for="email">Email</Label>
                        <Input id="email" type="email" v-model="form.email" :aria-invalid="!!form.errors.email || undefined" />
                        <p v-if="form.errors.email" class="text-sm text-red-600">
                            {{ form.errors.email }}
                        </p>
                    </div>

                    <div class="space-y-2">
                        <Label for="number">Número</Label>
                        <Input id="number" type="text" v-model="form.number" :aria-invalid="!!form.errors.number || undefined" />
                        <p v-if="form.errors.number" class="text-sm text-red-600">
                            {{ form.errors.number }}
                        </p>
                    </div>
                </div>
                <div class="mt-7">
                    <Button type="submit" :disabled="form.processing" class="w-full sm:w-auto">
                        <SendHorizonal class="mr-2 h-4 w-4" />
                        <span v-if="form.processing">Guardando…</span>
                        <span v-else>Crear empresa</span>
                    </Button>
                </div>
            </form>
        </div>
    </AppLayout>
</template>
