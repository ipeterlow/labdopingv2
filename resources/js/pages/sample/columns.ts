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
