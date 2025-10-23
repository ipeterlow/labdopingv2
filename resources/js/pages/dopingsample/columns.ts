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
    received_at: string;
    analyzed_at: string;
    sample_taken_at: string;
    results_at: string;
    company_name: string;
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
        cell: (info) => info.getValue(),
    },
    {
        accessorKey: 'type',
        header: 'Tipo de Muestra',
        cell: (info) => info.getValue(),
    },

    {
        accessorKey: 'sent_at',
        header: 'Fecha de Envío',
        cell: (info) => info.getValue(),
    },
    {
        accessorKey: 'received_at',
        header: 'Fecha de Recepción',
        cell: (info) => info.getValue(),
    },

    {
        accessorKey: 'sample_taken_at',
        header: 'Fecha de Toma de Muestra',
        cell: (info) => info.getValue(),
    },
    {
        accessorKey: 'analyzed_at',
        header: 'Fecha de Análisis',
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
