import ActionCell from '@/components/ActionCell.vue';
import { Button } from '@/components/ui/button';
import type { ColumnDef } from '@tanstack/vue-table';
import { ArrowUpDown } from 'lucide-vue-next';
import { h } from 'vue';

export interface Permission {
    id: number;
    name: string;
    created_at: Date;
}

export const permissionColumns: ColumnDef<Permission>[] = [
    {
        accessorKey: 'name',
        header: ({ column }) =>
            h(
                Button,
                {
                    variant: 'ghost',
                    onClick: () => column.toggleSorting(column.getIsSorted() === 'asc'),
                },
                () => ['Nombre', h(ArrowUpDown, { class: 'ml-2 h-4 w-4' })],
            ),
        cell: (info) => info.getValue(),
    },
    {
        accessorKey: 'created_at',
        header: ({ column }) =>
            h(
                Button,
                {
                    variant: 'ghost',
                    onClick: () => column.toggleSorting(column.getIsSorted() === 'asc'),
                },
                () => ['Creado', h(ArrowUpDown, { class: 'ml-2 h-4 w-4' })],
            ),
        cell: ({ row }) => {
            // Formatear la fecha para que sea más legible
            return new Date(row.getValue('created_at')).toLocaleDateString();
        },
    },
    {
        id: 'actions',
        header: 'Acciones',
        cell: ({ row }) =>
            h(ActionCell, {
                // Se cambia el recurso a 'permissions'
                resource: 'permissions',
                id: row.original.id,
                show: true,
                edit: true,
                destroy: true,
            }),
    },
];