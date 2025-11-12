<script setup lang="ts">
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import AppLayout from '@/layouts/AppLayout.vue';
import type { PageProps } from '@/types';
import { Head, router, useForm, usePage } from '@inertiajs/vue3';
import { Check, X } from 'lucide-vue-next';
import { computed } from 'vue';

interface Permission {
    id: number;
    name: string;
}

const page = usePage<PageProps>();
const permissions = computed(() => (page.props.permissions as unknown as Permission[]) || []);

const form = useForm({
    name: '',
    permissions: [] as number[],
});

const isSelected = (permissionId: number) => {
    return form.permissions.includes(permissionId);
};

const togglePermission = (permissionId: number) => {
    const index = form.permissions.indexOf(permissionId);
    if (index > -1) {
        form.permissions.splice(index, 1);
    } else {
        form.permissions.push(permissionId);
    }
};

const removePermission = (permissionId: number) => {
    const index = form.permissions.indexOf(permissionId);
    if (index > -1) {
        form.permissions.splice(index, 1);
    }
};

const selectedPermissions = computed(() => {
    return permissions.value.filter((p) => form.permissions.includes(p.id));
});

const submit = () => {
    form.post(route('roles.store'), {
        onSuccess: () => {
            router.visit(route('roles.index'));
        },
    });
};
</script>

<template>
    <Head title="Crear Rol" />
    <AppLayout>
        <div class="mx-auto w-full max-w-4xl space-y-6 p-6">
            <div>
                <h1 class="text-3xl font-bold tracking-tight">Crear Nuevo Rol</h1>
                <p class="mt-1 text-muted-foreground">Define un nuevo rol y asigna los permisos correspondientes</p>
            </div>

            <form @submit.prevent="submit" class="space-y-6">
                <!-- Nombre del Rol -->
                <div class="space-y-4 rounded-lg border bg-card p-6 shadow-sm">
                    <div class="space-y-2">
                        <Label for="name" class="text-base font-semibold">Nombre del Rol</Label>
                        <Input
                            id="name"
                            v-model="form.name"
                            type="text"
                            placeholder="Ej: Administrador, Editor, Moderador..."
                            :class="{ 'border-red-500': form.errors.name }"
                            required
                        />
                        <p v-if="form.errors.name" class="text-sm text-red-500">{{ form.errors.name }}</p>
                    </div>
                </div>

                <!-- Permisos Seleccionados (Tags) -->
                <div v-if="selectedPermissions.length > 0" class="rounded-lg border bg-card p-6 shadow-sm">
                    <Label class="mb-3 block text-base font-semibold">Permisos Asignados ({{ selectedPermissions.length }})</Label>
                    <div class="flex flex-wrap gap-2">
                        <Badge
                            v-for="permission in selectedPermissions"
                            :key="permission.id"
                            variant="default"
                            class="group cursor-pointer px-3 py-1.5 transition-colors hover:bg-primary/80"
                        >
                            {{ permission.name }}
                            <button @click="removePermission(permission.id)" type="button" class="ml-2 transition-colors hover:text-destructive">
                                <X :size="14" />
                            </button>
                        </Badge>
                    </div>
                </div>

                <!-- Lista de Permisos Disponibles -->
                <div class="space-y-4 rounded-lg border bg-card p-6 shadow-sm">
                    <div>
                        <Label class="text-base font-semibold">Permisos Disponibles</Label>
                        <p class="mt-1 text-sm text-muted-foreground">Haz click en los permisos que deseas asignar a este rol</p>
                    </div>

                    <div class="mt-4 grid grid-cols-1 gap-3 md:grid-cols-2">
                        <button
                            v-for="permission in permissions"
                            :key="permission.id"
                            type="button"
                            @click="togglePermission(permission.id)"
                            :class="[
                                'flex items-center justify-between rounded-lg border-2 p-4 text-left transition-all hover:bg-accent',
                                isSelected(permission.id) ? 'border-primary bg-primary/5' : 'border-border hover:border-primary/50',
                            ]"
                        >
                            <span class="text-sm font-medium">{{ permission.name }}</span>
                            <div
                                :class="[
                                    'flex h-5 w-5 items-center justify-center rounded-full transition-colors',
                                    isSelected(permission.id) ? 'bg-primary text-primary-foreground' : 'bg-muted',
                                ]"
                            >
                                <Check v-if="isSelected(permission.id)" :size="14" />
                            </div>
                        </button>
                    </div>

                    <p v-if="form.errors.permissions" class="mt-2 text-sm text-red-500">
                        {{ form.errors.permissions }}
                    </p>
                </div>

                <!-- Botones de Acción -->
                <div class="flex justify-end gap-3 pt-4">
                    <Button type="button" variant="outline" @click="router.visit(route('roles.index'))" :disabled="form.processing">
                        Cancelar
                    </Button>
                    <Button type="submit" :disabled="form.processing">
                        {{ form.processing ? 'Creando...' : 'Crear Rol' }}
                    </Button>
                </div>
            </form>
        </div>
    </AppLayout>
</template>
