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

const props = defineProps<{
  columns: ColumnDef<TData, TValue>[]
  data: TData[]
  page?: number // página externa (0-based)
}>()

const emit = defineEmits<{
  (e: 'update:page', page: number): void
}>()

const sorting = ref<SortingState>([])
const columnFilters = ref<ColumnFiltersState>([])
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
  getCoreRowModel: getCoreRowModel(),
  getFilteredRowModel: getFilteredRowModel(),
  getSortedRowModel: getSortedRowModel(),
  getPaginationRowModel: getPaginationRowModel(),
})
</script>

<template>
  <div class="border rounded-md">
    <div class="flex gap-2 p-4">
      <input
        type="text"
        placeholder="Buscar por nombre"
        :value="table.getColumn('name')?.getFilterValue() ?? ''"
        @input="table.getColumn('name')?.setFilterValue(($event.target as HTMLInputElement).value)"
        class="border px-2 py-1 rounded w-full"
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
          <TableCell :colspan="props.columns.length" class="text-center py-4">No hay resultados.</TableCell>
        </TableRow>
      </TableBody>
    </Table>

    <div class="flex justify-between items-center p-4">
      <button @click="table.previousPage()" :disabled="!table.getCanPreviousPage()" class="px-2 py-1 border rounded disabled:opacity-50">
        Anterior
      </button>

      <div>
        Página {{ table.getState().pagination.pageIndex + 1 }} de {{ table.getPageCount() }}
      </div>

      <button @click="table.nextPage()" :disabled="!table.getCanNextPage()" class="px-2 py-1 border rounded disabled:opacity-50">
        Siguiente
      </button>
    </div>
  </div>
</template>
