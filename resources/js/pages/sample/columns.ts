import ActionCell from '@/components/ActionCell.vue';
import type { ColumnDef } from '@tanstack/vue-table';
import { h } from 'vue';

function formatDate(value: unknown): string {
    if (!value) return '';
    const d = new Date(String(value));
    if (isNaN(d.getTime())) return String(value);
    const yyyy = d.getFullYear();
    const mm = String(d.getMonth() + 1).padStart(2, '0');
    const dd = String(d.getDate()).padStart(2, '0');
    return `${yyyy}-${mm}-${dd}`;
}

function formatDateTime(value: unknown): string {
    if (!value) return '';
    const d = new Date(String(value));
    if (isNaN(d.getTime())) return String(value);
    const yyyy = d.getFullYear();
    const mo = String(d.getMonth() + 1).padStart(2, '0');
    const dd = String(d.getDate()).padStart(2, '0');
    const hh = String(d.getHours()).padStart(2, '0');
    const mi = String(d.getMinutes()).padStart(2, '0');
    const ss = String(d.getSeconds()).padStart(2, '0');
    return `${yyyy}-${mo}-${dd} ${hh}:${mi}:${ss}`;
}

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
    tiempo_recepcion: number | null;
    tiempo_respuesta: number | null;
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
        header: 'Categoría Muestra',
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
        header: 'Tipo Muestra',
        cell: (info) => info.getValue(),
    },

    {
        accessorKey: 'sent_at',
        header: 'Fecha Envío',
        cell: (info) => formatDate(info.getValue()),
    },
    {
        accessorKey: 'received_at',
        header: 'Fecha Recepción',
        cell: (info) => formatDateTime(info.getValue()),
    },

    {
        accessorKey: 'sample_taken_at',
        header: 'Fecha Toma Muestra',
        cell: (info) => formatDate(info.getValue()),
    },
    {
        accessorKey: 'analyzed_at',
        header: 'Fecha Análisis',
        cell: (info) => formatDate(info.getValue()),
    },
    {
        accessorKey: 'tiempo_recepcion',
        header: 'Tiempo Recepción',
        cell: (info) => {
            const value = info.getValue() as number | null;
            return value !== null ? `${value} días` : '—';
        },
    },
    {
        accessorKey: 'tiempo_respuesta',
        header: 'Tiempo Respuesta',
        cell: (info) => {
            const value = info.getValue() as number | null;
            return value !== null ? `${value} días` : '—';
        },
    },
    {
        id: 'actions',
        header: 'Acciones',
        cell: ({ row }) =>
            h(ActionCell, {
                resource: 'sample',
                id: row.original.id,
                show: true,
            }),
    },
];
