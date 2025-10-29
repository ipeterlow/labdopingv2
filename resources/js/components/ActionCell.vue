<script setup lang="ts">
import {
    AlertDialog,
    AlertDialogAction,
    AlertDialogCancel,
    AlertDialogContent,
    AlertDialogDescription,
    AlertDialogFooter,
    AlertDialogHeader,
    AlertDialogTitle,
    AlertDialogTrigger,
} from '@/components/ui/alert-dialog'; // 👈 1. Importar componentes
import { Button } from '@/components/ui/button';
import { DropdownMenu, DropdownMenuContent, DropdownMenuItem, DropdownMenuTrigger } from '@/components/ui/dropdown-menu';
import { router } from '@inertiajs/vue3';
import { FileDown, MoreHorizontal } from 'lucide-vue-next';

const props = defineProps<{
    resource: string;
    id: string | number;
    show?: boolean;
    edit?: boolean;
    destroy?: boolean;
    pdf?: boolean;
}>();

const goToEdit = () => router.visit(route(`${props.resource}.edit`, props.id));
const goToView = () => router.visit(route(`${props.resource}.show`, props.id));

// 2. Modificada: Se quita el confirm()
const destroyItem = () => {
    router.delete(route(`${props.resource}.destroy`, props.id), {
        preserveState: false,
        preserveScroll: true,
    });
};

const downloadPdf = () => {
    const url = route('samples.pdf', props.id);
    window.open(url, '_blank');
};
</script>

<template>
    <AlertDialog>
        <DropdownMenu>
            <DropdownMenuTrigger as-child>
                <Button variant="ghost" class="h-8 w-8 p-0">
                    <MoreHorizontal class="h-4 w-4" />
                </Button>
            </DropdownMenuTrigger>

            <DropdownMenuContent align="end">
                <DropdownMenuItem v-if="show !== false" @click="goToView"> Ver </DropdownMenuItem>

                <DropdownMenuItem v-if="edit !== false" @click="goToEdit"> Editar </DropdownMenuItem>

                <AlertDialogTrigger v-if="destroy !== false" as-child>
                    <DropdownMenuItem class="text-red-600" @select.prevent> Eliminar </DropdownMenuItem>
                </AlertDialogTrigger>

                <DropdownMenuItem v-if="pdf || $attrs.id === 'actions'" @click="downloadPdf">
                    <FileDown class="h-4 w-4" />
                    Comp. Recepcion
                </DropdownMenuItem>
            </DropdownMenuContent>
        </DropdownMenu>

        <AlertDialogContent>
            <AlertDialogHeader>
                <AlertDialogTitle>¿Estás absolutamente seguro?</AlertDialogTitle>
                <AlertDialogDescription>
                    Esta acción no se puede deshacer. Esto eliminará permanentemente el registro de nuestros servidores.
                </AlertDialogDescription>
            </AlertDialogHeader>
            <AlertDialogFooter>
                <AlertDialogCancel>Cancelar</AlertDialogCancel>
                <AlertDialogAction @click="destroyItem"> Continuar </AlertDialogAction>
            </AlertDialogFooter>
        </AlertDialogContent>
    </AlertDialog>
</template>
