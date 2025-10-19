<script setup lang="ts">
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import AppLayout from '@/layouts/AppLayout.vue';
import { Head } from '@inertiajs/vue3';
import { useForm } from 'laravel-precognition-vue-inertia';

// 1) Inicializa el form: método, url y datos iniciales
const form = useForm('post', '/users', {
    name: '',
    email: '',
    password: '',
    password_confirmation: '',
});

// (Opcional) Ajusta el debounce de la validación (ms)
form.setValidationTimeout(400); // UX más ágil

// 2) Submit: con Inertia sólo pasas opciones del visit
const submit = () => {
    form.submit({
        preserveScroll: true,
        onSuccess: () => form.reset(),
    });
};
</script>

<template>
    <Head title="Create User" />
    <AppLayout>
        <div class="p-4">
            <h1 class="mb-4 text-2xl font-semibold">Create User</h1>

            <form class="w-8/12 space-y-4" @submit.prevent="submit">
                <!-- NAME -->
                <div class="space-y-2">
                    <Label for="name">Nombre</Label>
                    <Input
                        id="name"
                        type="text"
                        autocomplete="name"
                        v-model="form.name"
                        @change="form.validate('name')"
                        :aria-invalid="form.invalid('name') || undefined"
                    />
                    <p v-if="form.invalid('name')" class="text-sm text-red-600">
                        {{ form.errors.name }}
                    </p>
                </div>

                <!-- EMAIL -->
                <div class="space-y-2">
                    <Label for="email">Email</Label>
                    <Input
                        id="email"
                        type="email"
                        autocomplete="email"
                        v-model="form.email"
                        @change="form.validate('email')"
                        :aria-invalid="form.invalid('email') || undefined"
                    />
                    <p v-if="form.invalid('email')" class="text-sm text-red-600">
                        {{ form.errors.email }}
                    </p>
                </div>

                <!-- PASSWORD -->
                <div class="space-y-2">
                    <Label for="password">Contraseña</Label>
                    <Input
                        id="password"
                        type="password"
                        autocomplete="new-password"
                        v-model="form.password"
                        @change="form.validate('password')"
                        :aria-invalid="form.invalid('password') || undefined"
                    />
                    <p v-if="form.invalid('password')" class="text-sm text-red-600">
                        {{ form.errors.password }}
                    </p>
                </div>

                <!-- PASSWORD CONFIRMATION -->
                <div class="space-y-2">
                    <Label for="password_confirmation">Confirmar Contraseña</Label>
                    <Input
                        id="password_confirmation"
                        type="password"
                        autocomplete="new-password"
                        v-model="form.password_confirmation"
                        @change="form.validate('password_confirmation')"
                        :aria-invalid="form.invalid('password_confirmation') || undefined"
                    />
                    <p v-if="form.invalid('password_confirmation')" class="text-sm text-red-600">
                        {{ form.errors.password_confirmation }}
                    </p>
                </div>

                <Button type="submit" class="mt-2" :disabled="form.processing || form.validating">
                    <span v-if="form.validating">Validando…</span>
                    <span v-else-if="form.processing">Creando…</span>
                    <span v-else>Crear cuenta</span>
                </Button>
            </form>
        </div>
    </AppLayout>
</template>
