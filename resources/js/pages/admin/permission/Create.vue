<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, useForm } from '@inertiajs/vue3';

// shadcn-vue components
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';

const form = useForm({
    permiso: '',
});

const submit = () => {
    form.post(route('permissions.store'), {
        preserveScroll: true,
        onSuccess: () => form.reset(),
    });
};
</script>

<template>
    <Head title="Crear Permiso" />
    <AppLayout>
        <div class="max-w-md space-y-4 p-6">
            <h1 class="text-2xl font-semibold">Crear Permiso</h1>

            <form @submit.prevent="submit" class="space-y-4">
                <!-- Permiso -->
                <div class="space-y-2">
                    <Label for="permiso">Permiso</Label>
                    <Input id="permiso" type="text" v-model="form.permiso" :aria-invalid="!!form.errors.permiso || undefined" />
                    <p v-if="form.errors.permiso" class="text-sm text-red-600">
                        {{ form.errors.permiso }}
                    </p>
                </div>

                <!-- Botón -->
                <Button type="submit" :disabled="form.processing">
                    <span v-if="form.processing">Guardando…</span>
                    <span v-else>Crear permiso</span>
                </Button>
            </form>
        </div>
    </AppLayout>
</template>
