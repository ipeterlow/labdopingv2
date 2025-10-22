import ActionCell from '@/components/ActionCell.vue';
import { Button } from '@/components/ui/button';
import type { ColumnDef } from '@tanstack/vue-table';
import { ArrowUpDown } from 'lucide-vue-next';
import { h } from 'vue';

export interface Company {
    id: number;
    name: string;
    email: string;
    number: string;
}

export const companyColumns: ColumnDef<Company>[] = [
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
        accessorKey: 'email',
        header: 'Email',
        cell: (info) => info.getValue(),
    },
    {
        accessorKey: 'number',
        header: 'Número',
        cell: (info) => info.getValue(),
    },
    {
        id: 'actions',
        header: 'Acciones',
        cell: ({ row }) =>
            h(ActionCell, {
                resource: 'company',
                id: row.original.id,
                show: true,
                edit: true,
                destroy: true,
            }),
    },
];
