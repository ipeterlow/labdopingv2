<script setup lang="ts">
import { Alert, AlertDescription, AlertTitle } from '@/components/ui/alert';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, useForm } from '@inertiajs/vue3';
import { CheckCircle, XCircle } from 'lucide-vue-next';
import { ref } from 'vue';

// Props de la empresa
const props = defineProps<{
    company: { id: number; name: string; email: string; number: string };
}>();

// Formulario
const form = useForm({
    name: props.company.name,
    email: props.company.email,
    number: props.company.number,
});

// Alert
const showAlert = ref(false);
const alertType = ref<'success' | 'error'>('success');
const alertMessage = ref('');

// Submit
const submit = () => {
    form.put(route('company.update', props.company.id), {
        preserveScroll: true,
        onSuccess: () => {
            alertType.value = 'success';
            alertMessage.value = 'Los datos fueron actualizados correctamente';
            showAlert.value = true;
            setTimeout(() => (showAlert.value = false), 3500);
        },
        onError: () => {
            alertType.value = 'error';
            alertMessage.value = 'Ocurrió un error al actualizar';
            showAlert.value = true;
            setTimeout(() => (showAlert.value = false), 3500);
        },
    });
};
</script>
<template>
    <Head title="Editar Empresa" />
    <AppLayout>
        <div class="w-full max-w-5xl space-y-4 p-6">
            <h1 class="text-2xl font-semibold">Editar Empresa</h1>

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

                    <div class="mt-7">
                        <Button type="submit" :disabled="form.processing" class="w-full sm:w-auto">
                            <span v-if="form.processing">Actualizando…</span>
                            <span v-else>Guardar cambios</span>
                        </Button>
                    </div>
                </div>
            </form>
        </div>
    </AppLayout>
</template>

<style>
.fade-enter-active,
.fade-leave-active {
    transition: all 0.5s ease;
}
.fade-enter-from,
.fade-leave-to {
    opacity: 0;
    transform: translateY(-1rem);
}
.fade-enter-to,
.fade-leave-from {
    opacity: 1;
    transform: translateY(0);
}
</style>
