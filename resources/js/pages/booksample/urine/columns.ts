import type { ColumnDef } from '@tanstack/vue-table';
import { h } from 'vue';
import UrineSampleActions from './UrineSampleActions.vue';

export interface UrineSample {
    id_characteristic_samples: number;
    external_id: string;
    internal_id: string;
    type: string;
    category: string;
    status_id: number;
    received_at: string;
    analyzed_at: string;
    company_name: string;
    ph?: string;
    densidad?: string;
    volumen?: string;
    largo?: string;
    screening?: string;
    confirmacion?: string;
    color?: string;
    observaciones?: string;
    cantidad_droga?: number;
    encargado_ingreso?: string;
    fecha_ingreso?: string;
    sample_taken_at?: string;
    sample_id: number;
    result_gcms?: string;
    result_cobas?: string;
}

export const urineSampleColumns: ColumnDef<UrineSample>[] = [
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
        accessorKey: 'category',
        header: 'Tipo',
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
        accessorKey: 'ph',
        header: 'pH',
        cell: (info) => info.getValue() || '—',
    },
    {
        accessorKey: 'densidad',
        header: 'Densidad',
        cell: (info) => info.getValue() || '—',
    },
    {
        accessorKey: 'volumen',
        header: 'Volumen',
        cell: (info) => info.getValue() || '—',
    },
    {
        id: 'actions',
        header: 'Acciones',
        cell: ({ row }) =>
            h(UrineSampleActions, {
                sample: row.original,
            }),
    },
];
