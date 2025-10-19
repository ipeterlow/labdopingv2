<script setup lang="ts">
import { Alert, AlertDescription, AlertTitle } from '@/components/ui/alert';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, useForm } from '@inertiajs/vue3';
import { CheckCircle, XCircle } from 'lucide-vue-next';
import { ref } from 'vue';


// Props del usuario
const props = defineProps<{
    user: { id: number; name: string; email: string };
}>();

// Formulario
const form = useForm({
    name: props.user.name,
    email: props.user.email,
    password: '',
    password_confirmation: '',
});

// Alert
const showAlert = ref(false);
const alertType = ref<'success' | 'error'>('success');
const alertMessage = ref('');

// Submit
const submit = () => {
    form.put(route('users.update', props.user.id), {
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
    <Head title="Edit User" />
    <AppLayout>
        <div class="p-4">
            <h1 class="mb-4 text-2xl font-semibold">Edit User</h1>

            <!-- ALERT -->
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

            <!-- FORMULARIO -->
            <form class="w-8/12 space-y-4" @submit.prevent="submit">
                <div class="space-y-2">
                    <Label for="name">Nombre</Label>
                    <Input id="name" type="text" v-model="form.name" />
                    <p v-if="form.errors.name" class="text-sm text-red-600">{{ form.errors.name }}</p>
                </div>

                <div class="space-y-2">
                    <Label for="email">Email</Label>
                    <Input id="email" type="email" v-model="form.email" />
                    <p v-if="form.errors.email" class="text-sm text-red-600">{{ form.errors.email }}</p>
                </div>

                <div class="space-y-2">
                    <Label for="password">Contraseña</Label>
                    <Input id="password" type="password" v-model="form.password" />
                    <p v-if="form.errors.password" class="text-sm text-red-600">{{ form.errors.password }}</p>
                </div>

                <div class="space-y-2">
                    <Label for="password_confirmation">Confirmar Contraseña</Label>
                    <Input id="password_confirmation" type="password" v-model="form.password_confirmation" />
                    <p v-if="form.errors.password_confirmation" class="text-sm text-red-600">{{ form.errors.password_confirmation }}</p>
                </div>

                <Button type="submit" class="mt-2" :disabled="form.processing">
                    <span v-if="form.processing">Actualizando…</span>
                    <span v-else>Guardar cambios</span>
                </Button>
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
