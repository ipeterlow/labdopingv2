import ActionCell from '@/components/ActionCell.vue';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import type { ColumnDef } from '@tanstack/vue-table';
import { ArrowUpDown } from 'lucide-vue-next';
import { h } from 'vue';

export interface Role {
    id: number;
    name: string;
    guard_name: string;
    permissions_count: number;
    permissions: string[];
    created_at: Date;
}

export const roleColumns: ColumnDef<Role>[] = [
    {
        accessorKey: 'name',
        header: ({ column }) =>
            h(
                Button,
                {
                    variant: 'ghost',
                    onClick: () => column.toggleSorting(column.getIsSorted() === 'asc'),
                },
                () => ['Nombre del Rol', h(ArrowUpDown, { class: 'ml-2 h-4 w-4' })],
            ),
        cell: (info) => h('span', { class: 'font-semibold' }, info.getValue() as string),
    },
    {
        accessorKey: 'permissions_count',
        header: ({ column }) =>
            h(
                Button,
                {
                    variant: 'ghost',
                    onClick: () => column.toggleSorting(column.getIsSorted() === 'asc'),
                },
                () => ['Permisos', h(ArrowUpDown, { class: 'ml-2 h-4 w-4' })],
            ),
        cell: ({ row }) => {
            const count = row.getValue('permissions_count') as number;
            return h(Badge, { variant: 'secondary' }, () => `${count} permisos`);
        },
    },
    {
        accessorKey: 'permissions',
        header: 'Permisos Asignados',
        cell: ({ row }) => {
            const permissions = row.getValue('permissions') as string[];
            if (permissions.length === 0) {
                return h('span', { class: 'text-gray-400 text-sm' }, 'Sin permisos');
            }
            return h(
                'div',
                { class: 'flex flex-wrap gap-1' },
                permissions
                    .slice(0, 3)
                    .map((permission) => h(Badge, { variant: 'outline', class: 'text-xs' }, () => permission))
                    .concat(permissions.length > 3 ? [h(Badge, { variant: 'secondary', class: 'text-xs' }, () => `+${permissions.length - 3}`)] : []),
            );
        },
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
            return new Date(row.getValue('created_at')).toLocaleDateString('es-ES');
        },
    },
    {
        id: 'actions',
        header: 'Acciones',
        cell: ({ row }) =>
            h(ActionCell, {
                resource: 'roles',
                id: row.original.id,
                show: true,
                edit: true,
                destroy: true,
            }),
    },
];
