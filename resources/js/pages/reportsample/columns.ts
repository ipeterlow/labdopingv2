import ActionCell from '@/components/ActionCell.vue';
import type { ColumnDef } from '@tanstack/vue-table';
import { h } from 'vue';

export interface Sample {
    id: number;
    external_id: string;
    internal_id: string;
    category: string;
    status_name: string;
    type: string;
    sent_at: string;
}

function getStatusClass(status: string): string {
    const normalized = status.trim().toLowerCase();

    if (normalized === 'informada') {
        return 'inline-flex items-center rounded-full border px-2.5 py-0.5 text-xs font-medium border-emerald-500/30 bg-emerald-500/10 text-emerald-700 dark:border-emerald-500/40 dark:bg-emerald-500/15 dark:text-emerald-300';
    }

    if (normalized === 'anulada') {
        return 'inline-flex items-center rounded-full border px-2.5 py-0.5 text-xs font-medium border-red-500/30 bg-red-500/10 text-red-700 dark:border-red-500/40 dark:bg-red-500/15 dark:text-red-300';
    }

    return 'inline-flex items-center rounded-full border px-2.5 py-0.5 text-xs font-medium border-amber-500/30 bg-amber-500/10 text-amber-700 dark:border-amber-500/40 dark:bg-amber-500/15 dark:text-amber-300';
}

export const sampleColumns: ColumnDef<Sample>[] = [
    {
        accessorKey: 'external_id',
        header: 'Nº Externo',
        cell: (info) => info.getValue(),
    },
    {
        accessorKey: 'internal_id',
        header: 'Nº Interno',
        cell: (info) => info.getValue(),
    },
    {
        accessorKey: 'category',
        header: 'Categoría de Muestra',
        cell: (info) => info.getValue(),
    },
    {
        accessorKey: 'company_name',
        header: 'CET / Empresa',
        cell: (info) => info.getValue(),
    },
    {
        accessorKey: 'status_name',
        header: 'Estado',
        cell: (info) => {
            const value = String(info.getValue() ?? '');
            return h(
                'span',
                {
                    class: getStatusClass(value),
                },
                value,
            );
        },
    },
    {
        accessorKey: 'type',
        header: 'Tipo de Muestra',
        cell: (info) => info.getValue(),
    },

    {
        id: 'actions',
        header: 'Acciones',
        cell: ({ row }) =>
            h(ActionCell, {
                resource: 'reportsample',
                id: row.original.id,
                externalId: row.original.external_id,
                show: true,
                uploadInforme: true,
                uploadCadenaCustodia: true,
                edit: false,
                destroy: false,
                pdf: false,
            }),
    },
];
