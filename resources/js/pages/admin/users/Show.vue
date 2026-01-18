<script setup lang="ts">
import { Badge } from '@/components/ui/badge';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import AppLayout from '@/layouts/AppLayout.vue';
import { Head } from '@inertiajs/vue3';

// Props que vienen del controlador
const props = defineProps<{
    user: {
        id: number;
        name: string;
        email: string;
        roles: Array<{ id: number; name: string }>;
        company: { id: number; name: string } | null;
        created_at: string;
        updated_at: string;
    };
}>();
</script>

<template>
    <Head title="Ver Usuario" />
    <AppLayout>
        <div class="p-4">
            <h1 class="mb-4 text-2xl font-semibold">Detalles del Usuario</h1>

            <div class="w-8/12 space-y-4">
                <div class="space-y-4 rounded-md border bg-card p-6">
                    <!-- NAME -->
                    <div class="space-y-2">
                        <Label for="name">Nombre</Label>
                        <Input id="name" type="text" :model-value="user.name" readonly />
                    </div>

                    <!-- EMAIL -->
                    <div class="space-y-2">
                        <Label for="email">Email</Label>
                        <Input id="email" type="email" :model-value="user.email" readonly />
                    </div>

                    <!-- EMPRESA -->
                    <div class="space-y-2">
                        <Label>Empresa</Label>
                        <div v-if="user.company" class="rounded-md bg-primary/10 px-3 py-2 text-sm font-medium text-primary">
                            {{ user.company.name }}
                        </div>
                        <p v-else class="text-sm text-muted-foreground">Usuario de Laboratorio (sin empresa asignada)</p>
                    </div>

                    <!-- ROLES -->
                    <div class="space-y-2">
                        <Label>Roles Asignados</Label>
                        <div v-if="user.roles && user.roles.length > 0" class="flex flex-wrap gap-2">
                            <Badge v-for="role in user.roles" :key="role.id" variant="default">
                                {{ role.name }}
                            </Badge>
                        </div>
                        <p v-else class="text-sm text-muted-foreground">Sin roles asignados</p>
                    </div>

                    <!-- FECHAS -->
                    <div class="grid grid-cols-2 gap-4">
                        <div class="space-y-2">
                            <Label>Fecha de Creación</Label>
                            <p class="text-sm text-muted-foreground">
                                {{ new Date(user.created_at).toLocaleString('es-ES') }}
                            </p>
                        </div>
                        <div class="space-y-2">
                            <Label>Última Actualización</Label>
                            <p class="text-sm text-muted-foreground">
                                {{ new Date(user.updated_at).toLocaleString('es-ES') }}
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
