<script setup lang="ts">
import { Button } from '@/components/ui/button';
import { Checkbox } from '@/components/ui/checkbox';
import { Dialog, DialogContent, DialogFooter, DialogHeader, DialogTitle } from '@/components/ui/dialog';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { router } from '@inertiajs/vue3';
import { computed, ref, watch } from 'vue';

interface CompanyOption {
    id: number;
    name: string;
}

interface Contact {
    id?: number;
    company_id: number | null;
    name: string;
    email: string;
    is_active: boolean;
}

const props = defineProps<{
    open: boolean;
    mode: 'create' | 'edit';
    contact: Contact | null;
    companies: CompanyOption[];
}>();

const emit = defineEmits<{
    (e: 'update:open', value: boolean): void;
    (e: 'success'): void;
}>();

const formCompanyId = ref<string | null>(null);
const formName = ref('');
const formEmail = ref('');
const formIsActive = ref(true);
const processing = ref(false);
const errors = ref<Record<string, string>>({});

const title = computed(() => (props.mode === 'create' ? 'Agregar contacto' : 'Editar contacto'));

watch(
    () => props.open,
    (open) => {
        if (open) {
            formCompanyId.value = props.contact?.company_id ? String(props.contact.company_id) : null;
            formName.value = props.contact?.name ?? '';
            formEmail.value = props.contact?.email ?? '';
            formIsActive.value = props.contact?.is_active ?? true;
            errors.value = {};
        }
    },
);

const close = () => {
    emit('update:open', false);
};

const submit = () => {
    processing.value = true;
    errors.value = {};

    const data = {
        company_id: formCompanyId.value,
        name: formName.value,
        email: formEmail.value,
        is_active: formIsActive.value,
    };

    const onError = (err: Record<string, string[]>) => {
        processing.value = false;
        errors.value = Object.fromEntries(Object.entries(err).map(([k, v]) => [k, v[0]]));
    };

    const onFinish = () => {
        processing.value = false;
    };

    if (props.mode === 'create') {
        router.post('/companyemailcontacts', data, {
            onSuccess: () => {
                emit('success');
                close();
            },
            onError,
            onFinish,
            preserveScroll: true,
        });
    } else if (props.contact?.id) {
        router.put(`/companyemailcontacts/${props.contact.id}`, data, {
            onSuccess: () => {
                emit('success');
                close();
            },
            onError,
            onFinish,
            preserveScroll: true,
        });
    }
};
</script>

<template>
    <Dialog :open="open" @update:open="(value: boolean) => emit('update:open', value)">
        <DialogContent class="sm:max-w-[500px]">
            <DialogHeader>
                <DialogTitle>{{ title }}</DialogTitle>
            </DialogHeader>

            <div class="space-y-4 py-4">
                <div class="space-y-2">
                    <Label for="company_id">Empresa</Label>
                    <Select v-model="formCompanyId">
                        <SelectTrigger id="company_id">
                            <SelectValue placeholder="Selecciona una empresa" />
                        </SelectTrigger>
                        <SelectContent>
                            <SelectItem
                                v-for="company in companies"
                                :key="company.id"
                                :value="String(company.id)"
                            >
                                {{ company.name }}
                            </SelectItem>
                        </SelectContent>
                    </Select>
                    <p v-if="errors.company_id" class="text-sm text-destructive">{{ errors.company_id }}</p>
                </div>

                <div class="space-y-2">
                    <Label for="name">Nombre</Label>
                    <Input id="name" v-model="formName" type="text" />
                    <p v-if="errors.name" class="text-sm text-destructive">{{ errors.name }}</p>
                </div>

                <div class="space-y-2">
                    <Label for="email">Email</Label>
                    <Input id="email" v-model="formEmail" type="email" />
                    <p v-if="errors.email" class="text-sm text-destructive">{{ errors.email }}</p>
                </div>

                <div class="flex items-center space-x-2">
                    <Checkbox id="is_active" v-model:checked="formIsActive" />
                    <Label for="is_active">Activo</Label>
                </div>
            </div>

            <DialogFooter>
                <Button type="button" variant="outline" @click="close"> Cancelar </Button>
                <Button type="button" @click="submit" :disabled="processing">
                    {{ processing ? 'Guardando...' : 'Guardar' }}
                </Button>
            </DialogFooter>
        </DialogContent>
    </Dialog>
</template>

