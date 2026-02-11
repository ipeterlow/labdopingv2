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
import { router } from '@inertiajs/vue3'
import { ref, watch, computed } from 'vue'

// Función debounce para optimizar el filtrado
function debounce<T extends (...args: any[]) => any>(func: T, wait: number) {
  let timeout: ReturnType<typeof setTimeout> | null = null
  return function(this: any, ...args: Parameters<T>) {
    if (timeout) clearTimeout(timeout)
    timeout = setTimeout(() => func.apply(this, args), wait)
  }
}

// Tipos para paginación del servidor
interface ServerPagination {
  current_page: number
  last_page: number
  per_page: number
  total: number
  from: number | null
  to: number | null
}

interface ServerFilters {
  search?: string
  per_page?: number
}

const props = withDefaults(defineProps<{
  columns: ColumnDef<TData, TValue>[]
  data: TData[]
  page?: number // página externa (0-based) - para modo cliente
  searchPlaceholder?: string // placeholder personalizable
  enableSearch?: boolean // habilitar/deshabilitar búsqueda (default: true)
  searchableColumns?: string[] // columnas donde buscar (si no se define, busca en todas)
  // Props para paginación del servidor
  serverPagination?: ServerPagination | null
  serverFilters?: ServerFilters | null
  serverMode?: boolean // true = paginación del servidor, false = cliente
}>(), {
  enableSearch: true,
  searchPlaceholder: 'Buscar...',
  serverMode: false,
  serverPagination: null,
  serverFilters: null,
})

const emit = defineEmits<{
  (e: 'update:page', page: number): void
}>()

const sorting = ref<SortingState>([])
const columnFilters = ref<ColumnFiltersState>([])
const globalFilter = ref(props.serverFilters?.search ?? '')
const searchInput = ref(props.serverFilters?.search ?? '')

// Estado de carga para paginación del servidor
const isLoading = ref(false)

// Paginación local (para modo cliente)
const pagination = ref<PaginationState>({
  pageIndex: props.page ?? 0,
  pageSize: props.serverMode ? (props.serverFilters?.per_page ?? 50) : 10, // 10 para cliente, 50 para servidor
})

// Computed para determinar si estamos en modo servidor
const isServerMode = computed(() => props.serverMode && props.serverPagination !== null)

// sincroniza si el padre cambia la prop `page` (modo cliente)
watch(() => props.page, (newPage) => {
  if (!isServerMode.value && typeof newPage === 'number' && newPage !== pagination.value.pageIndex) {
    pagination.value.pageIndex = newPage
  }
})

// Sincronizar filtros del servidor
watch(() => props.serverFilters?.search, (newSearch) => {
  if (newSearch !== undefined) {
    searchInput.value = newSearch
    globalFilter.value = newSearch
  }
})

// Función para navegar con Inertia (modo servidor)
const navigateServer = (params: Record<string, any>) => {
  isLoading.value = true
  router.get(
    window.location.pathname,
    { ...params },
    {
      preserveState: true,
      preserveScroll: true,
      only: ['sample', 'urineSamples', 'hairSamples', 'salivaSamples', 'pagination', 'filters'],
      onFinish: () => {
        isLoading.value = false
      },
    }
  )
}

// Debounced search para modo servidor (500ms para reducir requests)
const debouncedServerSearch = debounce((value: string) => {
  if (isServerMode.value) {
    navigateServer({
      search: value,
      per_page: props.serverFilters?.per_page ?? 50,
      page: 1, // Reset a primera página al buscar
    })
  }
}, 500)

// Debounced filter para modo cliente (300ms)
const debouncedClientFilter = debounce((value: string) => {
  globalFilter.value = value
}, 300)

// Handler para el input de búsqueda
const handleSearchInput = (event: Event) => {
  const value = (event.target as HTMLInputElement).value
  searchInput.value = value
  
  if (isServerMode.value) {
    debouncedServerSearch(value)
  } else {
    debouncedClientFilter(value)
  }
}

// Funciones de navegación para modo servidor
const goToPage = (page: number) => {
  if (isServerMode.value) {
    navigateServer({
      page,
      search: searchInput.value,
      per_page: props.serverFilters?.per_page ?? 50,
    })
  }
}

const previousPage = () => {
  if (isServerMode.value && props.serverPagination) {
    if (props.serverPagination.current_page > 1) {
      goToPage(props.serverPagination.current_page - 1)
    }
  } else {
    table.previousPage()
  }
}

const nextPage = () => {
  if (isServerMode.value && props.serverPagination) {
    if (props.serverPagination.current_page < props.serverPagination.last_page) {
      goToPage(props.serverPagination.current_page + 1)
    }
  } else {
    table.nextPage()
  }
}

const canPreviousPage = computed(() => {
  if (isServerMode.value && props.serverPagination) {
    return props.serverPagination.current_page > 1
  }
  return table.getCanPreviousPage()
})

const canNextPage = computed(() => {
  if (isServerMode.value && props.serverPagination) {
    return props.serverPagination.current_page < props.serverPagination.last_page
  }
  return table.getCanNextPage()
})

const currentPageDisplay = computed(() => {
  if (isServerMode.value && props.serverPagination) {
    return props.serverPagination.current_page
  }
  return table.getState().pagination.pageIndex + 1
})

const totalPages = computed(() => {
  if (isServerMode.value && props.serverPagination) {
    return props.serverPagination.last_page
  }
  return table.getPageCount()
})

const totalRecords = computed(() => {
  if (isServerMode.value && props.serverPagination) {
    return props.serverPagination.total
  }
  return props.data.length
})

// Función de filtro personalizada para buscar en columnas específicas (modo cliente)
const globalFilterFn = (row: any, columnId: string, filterValue: string) => {
  const searchableColumns = props.searchableColumns
  
  if (searchableColumns && searchableColumns.length > 0) {
    return searchableColumns.some(colId => {
      const value = row.getValue(colId)
      return String(value).toLowerCase().includes(String(filterValue).toLowerCase())
    })
  }
  
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
      return isServerMode.value ? '' : globalFilter.value // Deshabilitar filtro local en modo servidor
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
    if (!isServerMode.value) {
      globalFilter.value = typeof updater === 'function' ? updater(globalFilter.value) : updater
    }
  },
  globalFilterFn: globalFilterFn,
  getCoreRowModel: getCoreRowModel(),
  getFilteredRowModel: isServerMode.value ? undefined : getFilteredRowModel(),
  getSortedRowModel: getSortedRowModel(),
  getPaginationRowModel: isServerMode.value ? undefined : getPaginationRowModel(),
  manualPagination: isServerMode.value,
  manualFiltering: isServerMode.value,
})
</script>

<template>
  <div class="rounded-md border" :class="{ 'opacity-60 pointer-events-none': isLoading }">
    <!-- Indicador de carga -->
    <div v-if="isLoading" class="absolute inset-0 flex items-center justify-center bg-background/50 z-10">
      <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-primary"></div>
    </div>

    <!-- Búsqueda Global con Debounce -->
    <div v-if="props.enableSearch" class="flex gap-2 border-b bg-background p-4">
      <input
        type="text"
        :placeholder="props.searchPlaceholder"
        :value="searchInput"
        @input="handleSearchInput"
        class="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background file:border-0 file:bg-transparent file:text-sm file:font-medium placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50"
      />
      <!-- Mostrar total de registros en modo servidor -->
      <div v-if="isServerMode" class="flex items-center text-sm text-muted-foreground whitespace-nowrap">
        {{ totalRecords.toLocaleString() }} registros
      </div>
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
          <TableCell :colspan="props.columns.length" class="py-4 text-center text-muted-foreground">
            {{ isLoading ? 'Cargando...' : 'No hay resultados.' }}
          </TableCell>
        </TableRow>
      </TableBody>
    </Table>

    <div class="flex items-center justify-between border-t bg-background p-4">
      <button 
        @click="previousPage" 
        :disabled="!canPreviousPage || isLoading" 
        class="inline-flex items-center justify-center rounded-md border border-input bg-background px-4 py-2 text-sm font-medium ring-offset-background transition-colors hover:bg-accent hover:text-accent-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:pointer-events-none disabled:opacity-50"
      >
        Anterior
      </button>

      <div class="flex items-center gap-4">
        <div class="text-sm text-muted-foreground">
          Página {{ currentPageDisplay }} de {{ totalPages }}
        </div>
        
        <!-- Información adicional en modo servidor -->
        <div v-if="isServerMode && serverPagination" class="text-sm text-muted-foreground">
          ({{ serverPagination.from ?? 0 }}-{{ serverPagination.to ?? 0 }} de {{ serverPagination.total }})
        </div>
      </div>

      <button 
        @click="nextPage" 
        :disabled="!canNextPage || isLoading" 
        class="inline-flex items-center justify-center rounded-md border border-input bg-background px-4 py-2 text-sm font-medium ring-offset-background transition-colors hover:bg-accent hover:text-accent-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:pointer-events-none disabled:opacity-50"
      >
        Siguiente
      </button>
    </div>
  </div>
</template>
