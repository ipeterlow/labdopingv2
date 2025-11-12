<script setup lang="ts" generic="TData, TValue">
import type { ColumnDef, SortingState, ColumnFiltersState, PaginationState } from '@tanstack/vue-table'
import {
  useVueTable,
  getCoreRowModel,
  getSortedRowModel,
  getFilteredRowModel,
  getPaginationRowModel,
  FlexRender,
} from '@tanstack/vue-table'

import { Table, TableHeader, TableHead, TableBody, TableRow, TableCell } from '@/components/ui/table'
import { ref, watch } from 'vue'

// Función debounce para optimizar el filtrado
function debounce<T extends (...args: any[]) => any>(func: T, wait: number) {
  let timeout: ReturnType<typeof setTimeout> | null = null
  return function(this: any, ...args: Parameters<T>) {
    if (timeout) clearTimeout(timeout)
    timeout = setTimeout(() => func.apply(this, args), wait)
  }
}

const props = withDefaults(defineProps<{
  columns: ColumnDef<TData, TValue>[]
  data: TData[]
  page?: number // página externa (0-based)
  searchPlaceholder?: string // placeholder personalizable
  enableSearch?: boolean // habilitar/deshabilitar búsqueda (default: true)
  searchableColumns?: string[] // columnas donde buscar (si no se define, busca en todas)
}>(), {
  enableSearch: true,
  searchPlaceholder: 'Buscar...'
})

const emit = defineEmits<{
  (e: 'update:page', page: number): void
}>()

const sorting = ref<SortingState>([])
const columnFilters = ref<ColumnFiltersState>([])
const globalFilter = ref('')
const searchInput = ref('') // Input visible para el usuario
const pagination = ref<PaginationState>({
  pageIndex: props.page ?? 0,
  pageSize: 10, // ajusta si quieres otro valor
})

// sincroniza si el padre cambia la prop `page`
watch(() => props.page, (newPage) => {
  if (typeof newPage === 'number' && newPage !== pagination.value.pageIndex) {
    pagination.value.pageIndex = newPage
  }
})

// Debounced filter - actualiza el filtro después de 300ms de inactividad
const debouncedSetFilter = debounce((value: string) => {
  globalFilter.value = value
}, 300)

// Handler para el input
const handleSearchInput = (event: Event) => {
  const value = (event.target as HTMLInputElement).value
  searchInput.value = value
  debouncedSetFilter(value)
}

// Función de filtro personalizada para buscar en columnas específicas
const globalFilterFn = (row: any, columnId: string, filterValue: string) => {
  const searchableColumns = props.searchableColumns
  
  // Si hay columnas específicas definidas, solo buscar en esas
  if (searchableColumns && searchableColumns.length > 0) {
    return searchableColumns.some(colId => {
      const value = row.getValue(colId)
      return String(value).toLowerCase().includes(String(filterValue).toLowerCase())
    })
  }
  
  // Si no hay columnas específicas, buscar en todas
  return String(row.getValue(columnId)).toLowerCase().includes(String(filterValue).toLowerCase())
}

const table = useVueTable({
  get data() {
    return props.data
  },
  get columns() {
    return props.columns
  },
  state: {
    get sorting() {
      return sorting.value
    },
    get columnFilters() {
      return columnFilters.value
    },
    get pagination() {
      return pagination.value
    },
    get globalFilter() {
      return globalFilter.value
    },
  },
  onSortingChange: updater => {
    sorting.value = typeof updater === 'function' ? updater(sorting.value) : updater
  },
  onColumnFiltersChange: updater => {
    columnFilters.value = typeof updater === 'function' ? updater(columnFilters.value) : updater
  },
  onPaginationChange: updater => {
    pagination.value = typeof updater === 'function' ? updater(pagination.value) : updater
    emit('update:page', pagination.value.pageIndex)
  },
  onGlobalFilterChange: updater => {
    globalFilter.value = typeof updater === 'function' ? updater(globalFilter.value) : updater
  },
  globalFilterFn: globalFilterFn,
  getCoreRowModel: getCoreRowModel(),
  getFilteredRowModel: getFilteredRowModel(),
  getSortedRowModel: getSortedRowModel(),
  getPaginationRowModel: getPaginationRowModel(),
})
</script>

<template>
  <div class="rounded-md border">
    <!-- Búsqueda Global Universal con Debounce -->
    <div v-if="props.enableSearch" class="flex gap-2 border-b bg-background p-4">
      <input
        type="text"
        :placeholder="props.searchPlaceholder"
        :value="searchInput"
        @input="handleSearchInput"
        class="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background file:border-0 file:bg-transparent file:text-sm file:font-medium placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50"
      />
    </div>

    <Table>
      <TableHeader>
        <TableRow v-for="headerGroup in table.getHeaderGroups()" :key="headerGroup.id">
          <TableHead v-for="header in headerGroup.headers" :key="header.id">
            <FlexRender
              v-if="!header.isPlaceholder"
              :render="header.column.columnDef.header"
              :props="header.getContext()"
            />
          </TableHead>
        </TableRow>
      </TableHeader>

      <TableBody>
        <TableRow v-for="row in table.getRowModel().rows" :key="row.id">
          <TableCell v-for="cell in row.getVisibleCells()" :key="cell.id" class="text-left">
            <FlexRender :render="cell.column.columnDef.cell" :props="cell.getContext()" />
          </TableCell>
        </TableRow>

        <TableRow v-if="table.getRowModel().rows.length === 0">
          <TableCell :colspan="props.columns.length" class="py-4 text-center text-muted-foreground">No hay resultados.</TableCell>
        </TableRow>
      </TableBody>
    </Table>

    <div class="flex items-center justify-between border-t bg-background p-4">
      <button 
        @click="table.previousPage()" 
        :disabled="!table.getCanPreviousPage()" 
        class="inline-flex items-center justify-center rounded-md border border-input bg-background px-4 py-2 text-sm font-medium ring-offset-background transition-colors hover:bg-accent hover:text-accent-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:pointer-events-none disabled:opacity-50"
      >
        Anterior
      </button>

      <div class="text-sm text-muted-foreground">
        Página {{ table.getState().pagination.pageIndex + 1 }} de {{ table.getPageCount() }}
      </div>

      <button 
        @click="table.nextPage()" 
        :disabled="!table.getCanNextPage()" 
        class="inline-flex items-center justify-center rounded-md border border-input bg-background px-4 py-2 text-sm font-medium ring-offset-background transition-colors hover:bg-accent hover:text-accent-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:pointer-events-none disabled:opacity-50"
      >
        Siguiente
      </button>
    </div>
  </div>
</template>
