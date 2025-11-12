# 🔍 Guía de Implementación del Buscador Universal

## ✅ Ya Implementado

### DopingSample (Muestras Doping)

```vue
<DataTable
    :columns="sampleColumns"
    :data="data"
    search-placeholder="Buscar por ID externo, interno, empresa, estado o fecha de recepción..."
    :searchable-columns="['external_id', 'internal_id', 'company_name', 'status_name', 'received_at']"
/>
```

---

## 📋 Ejemplos para Implementar en Otras Tablas

### 1. ReportSample (Reportes de Muestras)

**Archivo:** `resources/js/pages/reportsample/Index.vue`

Primero revisa las columnas en `reportsample/columns.ts` y luego aplica:

```vue
<DataTable
    :columns="sampleColumns"
    :data="data"
    search-placeholder="Buscar por ID, empresa, estado..."
    :searchable-columns="['external_id', 'internal_id', 'company_name', 'status_name']"
/>
```

---

### 2. Company (Empresas/CET)

**Archivo:** `resources/js/pages/company/Index.vue`

```vue
<DataTable
    :columns="companyColumns"
    :data="data"
    search-placeholder="Buscar por nombre, RUT o dirección..."
    :searchable-columns="['name', 'rut', 'address', 'email']"
/>
```

---

### 3. Users (Usuarios)

**Archivo:** `resources/js/pages/admin/users/Index.vue`

```vue
<DataTable
    :columns="userColumns"
    :data="data"
    search-placeholder="Buscar por nombre, email o rol..."
    :searchable-columns="['name', 'email', 'role']"
/>
```

---

### 4. Permissions (Permisos)

**Archivo:** `resources/js/pages/admin/permission/Index.vue`

```vue
<DataTable
    :columns="permissionColumns"
    :data="data"
    search-placeholder="Buscar por nombre o descripción..."
    :searchable-columns="['name', 'description']"
/>
```

---

## 🎯 Opciones Disponibles

### Props del DataTable

| Prop                | Tipo       | Default       | Descripción                                                                                  |
| ------------------- | ---------- | ------------- | -------------------------------------------------------------------------------------------- |
| `searchPlaceholder` | `string`   | `"Buscar..."` | Texto del placeholder del input de búsqueda                                                  |
| `enableSearch`      | `boolean`  | `true`        | Habilitar/deshabilitar el buscador                                                           |
| `searchableColumns` | `string[]` | `undefined`   | Array con los IDs de las columnas donde buscar. Si no se define, busca en TODAS las columnas |

### Ejemplos de Uso

#### 1. Búsqueda en TODAS las columnas

```vue
<DataTable :columns="columns" :data="data" search-placeholder="Buscar en todos los campos..." />
```

#### 2. Búsqueda en columnas específicas

```vue
<DataTable :columns="columns" :data="data" search-placeholder="Buscar por nombre o email..." :searchable-columns="['name', 'email']" />
```

#### 3. Sin buscador

```vue
<DataTable :columns="columns" :data="data" :enable-search="false" />
```

---

## 📝 Cómo Determinar los IDs de las Columnas

Los IDs de las columnas son los valores de `accessorKey` en tu archivo `columns.ts`:

```typescript
export const sampleColumns: ColumnDef<Sample>[] = [
    {
        accessorKey: 'external_id', // 👈 Este es el ID
        header: 'Nº Externo',
        cell: (info) => info.getValue(),
    },
    {
        accessorKey: 'internal_id', // 👈 Este es el ID
        header: 'Nº Interno',
        cell: (info) => info.getValue(),
    },
    // ...
];
```

---

## 🚀 Pasos para Implementar en Cada Tabla

1. **Abre el archivo Index.vue** de la tabla que quieres modificar
2. **Revisa el archivo columns.ts** correspondiente para ver los `accessorKey`
3. **Decide qué columnas quieres que sean buscables**
4. **Actualiza el componente DataTable** con las nuevas props:

```vue
<DataTable
    :columns="tuColumns"
    :data="data"
    search-placeholder="Tu placeholder personalizado..."
    :searchable-columns="['columna1', 'columna2', 'columna3']"
/>
```

---

## 💡 Consejos

- ✅ **Buscar en 3-5 columnas** es ideal para performance
- ✅ Prioriza columnas que los usuarios buscarían más (IDs, nombres, emails)
- ✅ Usa placeholders descriptivos para guiar al usuario
- ❌ Evita buscar en columnas de acciones o botones
- ❌ Evita buscar en demasiadas columnas (>7) para mejor performance

---

## 🔧 Ejemplo Completo

```vue
<script setup lang="ts">
import DataTable from '@/components/ui/table/DataTable.vue';
import AppLayout from '@/layouts/AppLayout.vue';
import { sampleColumns } from './columns';

const data = ref<Sample[]>([...samples]);
</script>

<template>
    <AppLayout>
        <DataTable
            :columns="sampleColumns"
            :data="data"
            search-placeholder="Buscar por ID externo, interno, empresa..."
            :searchable-columns="['external_id', 'internal_id', 'company_name']"
        />
    </AppLayout>
</template>
```

---

¡Listo! Ahora puedes aplicar el buscador universal en todas tus tablas de forma consistente. 🎉
