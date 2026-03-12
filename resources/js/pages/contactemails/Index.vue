<script setup lang="ts">
import { Button, buttonVariants } from '@/components/ui/button';
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, router, usePage } from '@inertiajs/vue3';
import { Pencil, Trash2 } from 'lucide-vue-next';
import { computed, ref } from 'vue';
import ContactEmailDialog from './ContactEmailDialog.vue';

interface Pagination {
    current_page: number;
    last_page: number;
    per_page: number;
    total: number;
    from: number | null;
    to: number | null;
}

interface Filters {
    search: string | null;
    company_id: number | null;
    is_active: string | null;
    per_page: number;
}

interface CompanyOption {
    id: number;
    name: string;
}

interface Contact {
    id: number;
    company_id: number;
    name: string;
    email: string;
    is_active: boolean;
    company?: CompanyOption;
}

const page = usePage();

const contacts = computed(() => (page.props.contacts as Contact[]) ?? []);
const pagination = computed(() => page.props.pagination as Pagination | undefined);
const filters = computed(() => page.props.filters as Filters | undefined);
const companies = computed(() => (page.props.companies as CompanyOption[]) ?? []);

const search = ref(filters.value?.search ?? '');
const selectedCompanyId = ref<string | null>(
    filters.value?.company_id ? String(filters.value.company_id) : null,
);
const selectedIsActive = ref<string | null>(filters.value?.is_active ?? '');

const dialogOpen = ref(false);
const dialogMode = ref<'create' | 'edit'>('create');
const selectedContact = ref<Contact | null>(null);

const applyFilters = () => {
    router.get(
        '/companyemailcontacts',
        {
            search: search.value || null,
            company_id: selectedCompanyId.value || null,
            is_active: selectedIsActive.value || null,
            per_page: pagination.value?.per_page ?? 25,
        },
        {
            preserveState: true,
            replace: true,
        },
    );
};

const resetFilters = () => {
    search.value = '';
    selectedCompanyId.value = null;
    selectedIsActive.value = '';
    applyFilters();
};

const openCreateDialog = () => {
    dialogMode.value = 'create';
    selectedContact.value = null;
    dialogOpen.value = true;
};

const openEditDialog = (contact: Contact) => {
    dialogMode.value = 'edit';
    selectedContact.value = contact;
    dialogOpen.value = true;
};

const deleteContact = (contact: Contact) => {
    if (!confirm('¿Seguro que deseas eliminar este contacto?')) {
        return;
    }

    router.delete(`/companyemailcontacts/${contact.id}`, {
        preserveScroll: true,
        onSuccess: () => {
            // Se recarga la lista automáticamente por Inertia
        },
    });
};

const handleSuccess = () => {
    router.reload({ only: ['contacts', 'pagination', 'filters'] });
};
</script>

<template>
    <Head title="Gestor Contacto Correos" />
    <AppLayout>
        <div class="p-4 space-y-6">
            <div>
                <h1 class="text-2xl font-semibold">Gestor Contacto Correos</h1>
                <p class="text-sm text-muted-foreground">
                    Administra los contactos de correo por empresa que recibirán los informes diarios.
                </p>
            </div>

            <div class="flex flex-col gap-4 md:flex-row md:items-end md:justify-between">
                <div class="flex flex-col gap-2 md:flex-row md:items-end">
                    <div class="flex flex-col gap-1">
                        <label class="text-sm font-medium text-muted-foreground">Buscar</label>
                        <input
                            v-model="search"
                            type="text"
                            class="h-9 w-full rounded-md border border-input bg-background px-2 text-sm shadow-sm focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-ring md:w-64"
                            placeholder="Nombre o email"
                            @keyup.enter="applyFilters"
                        />
                    </div>

                    <div class="flex flex-col gap-1">
                        <label class="text-sm font-medium text-muted-foreground">Empresa</label>
                        <select
                            v-model="selectedCompanyId"
                            class="h-9 w-full rounded-md border border-input bg-background px-2 text-sm shadow-sm focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-ring md:w-64"
                        >
                            <option value="">Todas</option>
                            <option v-for="company in companies" :key="company.id" :value="String(company.id)">
                                {{ company.name }}
                            </option>
                        </select>
                    </div>

                    <div class="flex flex-col gap-1">
                        <label class="text-sm font-medium text-muted-foreground">Estado</label>
                        <select
                            v-model="selectedIsActive"
                            class="h-9 w-full rounded-md border border-input bg-background px-2 text-sm shadow-sm focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-ring md:w-40"
                        >
                            <option value="">Todos</option>
                            <option value="1">Activos</option>
                            <option value="0">Inactivos</option>
                        </select>
                    </div>

                    <div class="flex gap-2 pt-2 md:pt-0">
                        <button
                            type="button"
                            :class="buttonVariants({ variant: 'default', size: 'sm' })"
                            @click="applyFilters"
                        >
                            Aplicar
                        </button>
                        <button
                            type="button"
                            :class="buttonVariants({ variant: 'outline', size: 'sm' })"
                            @click="resetFilters"
                        >
                            Limpiar
                        </button>
                    </div>
                </div>

                <div class="flex justify-end">
                    <button
                        type="button"
                        :class="buttonVariants({ variant: 'default', size: 'sm' })"
                        @click="openCreateDialog"
                    >
                        Agregar contacto
                    </button>
                </div>
            </div>

            <div class="overflow-x-auto rounded-md border">
                <table class="min-w-full text-sm">
                    <thead class="bg-muted/50 text-xs uppercase text-muted-foreground">
                        <tr>
                            <th class="px-3 py-2 text-left">Empresa</th>
                            <th class="px-3 py-2 text-left">Nombre</th>
                            <th class="px-3 py-2 text-left">Email</th>
                            <th class="px-3 py-2 text-left">Activo</th>
                            <th class="px-3 py-2 text-right">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-if="contacts.length === 0">
                            <td colspan="5" class="px-3 py-4 text-center text-sm text-muted-foreground">
                                No hay contactos registrados.
                            </td>
                        </tr>
                        <tr
                            v-for="contact in contacts"
                            :key="contact.id"
                            class="border-t text-sm hover:bg-muted/40"
                        >
                            <td class="px-3 py-2">
                                {{ contact.company?.name ?? '-' }}
                            </td>
                            <td class="px-3 py-2">
                                {{ contact.name }}
                            </td>
                            <td class="px-3 py-2">
                                {{ contact.email }}
                            </td>
                            <td class="px-3 py-2">
                                <span
                                    class="inline-flex rounded-full px-2 py-0.5 text-xs font-medium"
                                    :class="
                                        contact.is_active
                                            ? 'bg-emerald-50 text-emerald-700'
                                            : 'bg-slate-100 text-slate-600'
                                    "
                                >
                                    {{ contact.is_active ? 'Activo' : 'Inactivo' }}
                                </span>
                            </td>
                            <td class="px-3 py-2 text-right">
                                <div class="flex justify-end gap-2">
                                    <Button size="sm" variant="outline" @click="openEditDialog(contact)" title="Editar">
                                        <Pencil class="h-4 w-4" />
                                    </Button>
                                    <Button
                                        size="sm"
                                        variant="outline"
                                        @click="deleteContact(contact)"
                                        title="Eliminar"
                                    >
                                        <Trash2 class="h-4 w-4" />
                                    </Button>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <div v-if="pagination" class="flex items-center justify-between text-xs text-muted-foreground">
                <div>
                    Mostrando
                    <span class="font-medium">
                        {{ pagination.from ?? 0 }}–{{ pagination.to ?? 0 }}
                    </span>
                    de
                    <span class="font-medium">{{ pagination.total }}</span>
                    contactos
                </div>
                <!-- Paginación simple: se podría mejorar con un componente reutilizable -->
            </div>

            <ContactEmailDialog
                v-model:open="dialogOpen"
                :mode="dialogMode"
                :contact="selectedContact"
                :companies="companies"
                @success="handleSuccess"
            />
        </div>
    </AppLayout>
</template>

