<script setup lang="ts">
import { Badge } from '@/components/ui/badge';
import { buttonVariants } from '@/components/ui/button';
import { Label } from '@/components/ui/label';
import AppLayout from '@/layouts/AppLayout.vue';
import type { PageProps } from '@/types';
import { Head, Link, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';

interface Permission {
    id: number;
    name: string;
}

interface Role {
    id: number;
    name: string;
    guard_name: string;
    permissions: Permission[];
    created_at: string;
    updated_at: string;
}

const page = usePage<PageProps>();
const role = computed(() => page.props.role as unknown as Role);
</script>

<template>
    <Head title="Detalles del Rol" />
    <AppLayout>
        <div class="mx-auto w-full max-w-4xl space-y-6 p-6">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-3xl font-bold tracking-tight">Detalles del Rol</h1>
                    <p class="mt-1 text-muted-foreground">Información completa del rol y sus permisos</p>
                </div>
                <Link :href="route('roles.edit', role.id)" :class="buttonVariants({ variant: 'default' })"> Editar Rol </Link>
            </div>

            <!-- Información General -->
            <div class="space-y-4 rounded-lg border bg-card p-6 shadow-sm">
                <h2 class="text-xl font-semibold">Información General</h2>

                <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                    <div class="space-y-2">
                        <Label class="text-sm font-medium text-muted-foreground">Nombre del Rol</Label>
                        <p class="text-lg font-semibold">{{ role.name }}</p>
                    </div>

                    <div class="space-y-2">
                        <Label class="text-sm font-medium text-muted-foreground">Guard Name</Label>
                        <Badge variant="secondary">{{ role.guard_name }}</Badge>
                    </div>

                    <div class="space-y-2">
                        <Label class="text-sm font-medium text-muted-foreground">Creado</Label>
                        <p class="text-sm">
                            {{
                                new Date(role.created_at).toLocaleDateString('es-ES', {
                                    year: 'numeric',
                                    month: 'long',
                                    day: 'numeric',
                                    hour: '2-digit',
                                    minute: '2-digit',
                                })
                            }}
                        </p>
                    </div>

                    <div class="space-y-2">
                        <Label class="text-sm font-medium text-muted-foreground">Última Actualización</Label>
                        <p class="text-sm">
                            {{
                                new Date(role.updated_at).toLocaleDateString('es-ES', {
                                    year: 'numeric',
                                    month: 'long',
                                    day: 'numeric',
                                    hour: '2-digit',
                                    minute: '2-digit',
                                })
                            }}
                        </p>
                    </div>
                </div>
            </div>

            <!-- Permisos Asignados -->
            <div class="space-y-4 rounded-lg border bg-card p-6 shadow-sm">
                <div class="flex items-center justify-between">
                    <h2 class="text-xl font-semibold">Permisos Asignados</h2>
                    <Badge variant="outline" class="text-sm">
                        {{ role.permissions.length }} {{ role.permissions.length === 1 ? 'permiso' : 'permisos' }}
                    </Badge>
                </div>

                <div v-if="role.permissions.length > 0" class="mt-4 flex flex-wrap gap-2">
                    <Badge v-for="permission in role.permissions" :key="permission.id" variant="default" class="px-3 py-1.5 text-sm">
                        {{ permission.name }}
                    </Badge>
                </div>

                <div v-else class="py-8 text-center">
                    <p class="text-sm text-muted-foreground">Este rol no tiene permisos asignados</p>
                    <Link :href="route('roles.edit', role.id)" :class="buttonVariants({ variant: 'outline', size: 'sm' })" class="mt-4">
                        Asignar Permisos
                    </Link>
                </div>
            </div>

            <!-- Botón Volver -->
            <div class="flex justify-start">
                <Link :href="route('roles.index')" :class="buttonVariants({ variant: 'outline' })"> Volver a la lista </Link>
            </div>
        </div>
    </AppLayout>
</template>
