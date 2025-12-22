import type { ColumnDef } from '@tanstack/vue-table';
import { h } from 'vue';
import HairSampleActions from './HairSampleActions.vue';

export interface HairSample {
    id_characteristic_samples: number;
    external_id: string;
    internal_id: string;
    type: string;
    status_id: number;
    received_at: string;
    analyzed_at: string;
    company_name: string;
    largo?: string;
    color?: string;
    screening?: string;
    confirmacion?: string;
    observaciones?: string;
    cantidad_droga?: number;
    encargado_ingreso?: string;
    fecha_ingreso?: string;
    sample_id: number;
}

export const hairSampleColumns: ColumnDef<HairSample>[] = [
    {
        accessorKey: 'external_id',
        header: 'Nº Externo',
        cell: (info) => info.getValue(),
    },
    {
        accessorKey: 'internal_id',
        header: 'Nº Interno',
        cell: (info) => info.getValue() || '—',
    },
    {
        accessorKey: 'company_name',
        header: 'Empresa',
        cell: (info) => info.getValue(),
    },
    {
        accessorKey: 'received_at',
        header: 'Fecha Recepción',
        cell: (info) => info.getValue() || '—',
    },
    {
        accessorKey: 'analyzed_at',
        header: 'Fecha Ingreso y Análisis',
        cell: (info) => info.getValue() || '—',
    },
    {
        accessorKey: 'largo',
        header: 'Largo',
        cell: (info) => info.getValue() || '—',
    },
    {
        accessorKey: 'color',
        header: 'Color',
        cell: (info) => info.getValue() || '—',
    },
    {
        id: 'actions',
        header: 'Acciones',
        cell: ({ row }) =>
            h(HairSampleActions, {
                sample: row.original,
            }),
    },
];
