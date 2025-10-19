<script setup lang="ts">
import { Button } from '@/components/ui/button';
import { DropdownMenu, DropdownMenuContent, DropdownMenuItem, DropdownMenuTrigger } from '@/components/ui/dropdown-menu';
import { router } from '@inertiajs/vue3';
import { MoreHorizontal } from 'lucide-vue-next';

const props = defineProps<{
    resource: string;
    id: string | number;
    show?: boolean;
    edit?: boolean;
    destroy?: boolean;
}>();

// 👇 CAMBIO 3: Ya no necesitamos emitir eventos, lo eliminamos.
// const emit = defineEmits<{ ... }>();

const goToEdit = () => {
    // Esta función está bien como está
    router.visit(route(`${props.resource}.edit`, props.id));
};

const goToView = () => {
    // Esta función también está bien
    router.visit(route(`${props.resource}.show`, props.id));
};

const destroyItem = () => {
    if (confirm('¿Estás seguro de eliminar este registro?')) {
        // Usamos la ruta dinámica con el 'resource' prop
        router.delete(route(`${props.resource}.destroy`, props.id), {
            // 👇 CAMBIO 1: El cambio más importante.
            // Le decimos a Inertia que SÍ debe recargar las props de la página.
            preserveState: false,
            preserveScroll: true,

            // 👇 CAMBIO 2: El callback onSuccess ya no es necesario,
            // porque la actualización de la tabla será automática.
        });
    }
};
</script>

<template>
    <DropdownMenu>
        <DropdownMenuTrigger as-child>
            <Button variant="ghost" class="h-8 w-8 p-0">
                <MoreHorizontal class="h-4 w-4" />
            </Button>
        </DropdownMenuTrigger>

        <DropdownMenuContent align="end">
            <DropdownMenuItem v-if="show !== false" @click="goToView">Ver</DropdownMenuItem>
            <DropdownMenuItem v-if="edit !== false" @click="goToEdit">Editar</DropdownMenuItem>
            <DropdownMenuItem v-if="destroy !== false" class="text-red-600" @click="destroyItem">Eliminar</DropdownMenuItem>
        </DropdownMenuContent>
    </DropdownMenu>
</template>
