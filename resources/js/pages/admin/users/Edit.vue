<script setup lang="ts">
import { Alert, AlertDescription, AlertTitle } from '@/components/ui/alert';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Checkbox } from '@/components/ui/checkbox';
import { Command, CommandEmpty, CommandGroup, CommandInput, CommandItem, CommandList } from '@/components/ui/command';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Popover, PopoverContent, PopoverTrigger } from '@/components/ui/popover';
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, useForm } from '@inertiajs/vue3';
import { Check, CheckCircle, ChevronsUpDown, X, XCircle } from 'lucide-vue-next';
import { computed, ref } from 'vue';

// Props del usuario
const props = defineProps<{
    user: { id: number; name: string; email: string; roles: number[]; current_team_id: number | null };
    roles: Array<{ id: number; name: string }>;
    companies: Array<{ id: number; name: string }>;
}>();

// Refs para el combobox y checkbox
const companyComboboxOpen = ref(false);
const isLaboratory = ref(props.user.current_team_id === null);

// Formulario
const form = useForm({
    name: props.user.name,
    email: props.user.email,
    password: '',
    password_confirmation: '',
    roles: props.user.roles || [],
    current_team_id: props.user.current_team_id,
});

// Alert
const showAlert = ref(false);
const alertType = ref<'success' | 'error'>('success');
const alertMessage = ref('');

// Computed para obtener el nombre de la company seleccionada
const selectedCompanyName = computed(() => {
    if (!form.current_team_id) return 'Selecciona una empresa...';
    const company = props.companies.find((c) => c.id === form.current_team_id);
    return company ? company.name : 'Selecciona una empresa...';
});

// Función para alternar roles
const toggleRole = (roleId: number) => {
    const index = form.roles.indexOf(roleId);
    if (index === -1) {
        form.roles.push(roleId);
    } else {
        form.roles.splice(index, 1);
    }
};

// Función para verificar si un rol está seleccionado
const isSelected = (roleId: number) => {
    return form.roles.includes(roleId);
};

// Obtener roles seleccionados para mostrar como badges
const selectedRoles = computed(() => {
    return props.roles.filter((role) => form.roles.includes(role.id));
});

// Función para remover un rol
const removeRole = (roleId: number) => {
    const index = form.roles.indexOf(roleId);
    if (index !== -1) {
        form.roles.splice(index, 1);
    }
};

// Función para manejar el checkbox de laboratorio
const handleLaboratoryChange = (checked: boolean) => {
    isLaboratory.value = checked;
    if (checked) {
        form.current_team_id = null;
    }
};

// Submit
const submit = () => {
    form.put(route('users.update', props.user.id), {
        preserveScroll: true,
        onSuccess: () => {
            alertType.value = 'success';
            alertMessage.value = 'Los datos fueron actualizados correctamente';
            showAlert.value = true;
            setTimeout(() => (showAlert.value = false), 3500);
        },
        onError: () => {
            alertType.value = 'error';
            alertMessage.value = 'Ocurrió un error al actualizar';
            showAlert.value = true;
            setTimeout(() => (showAlert.value = false), 3500);
        },
    });
};
</script>

<template>
    <Head title="Edit User" />
    <AppLayout>
        <div class="p-4">
            <h1 class="mb-4 text-2xl font-semibold">Edit User</h1>

            <!-- ALERT -->
            <div class="fixed top-4 right-4 z-50 w-96">
                <transition name="fade">
                    <Alert v-if="showAlert" :variant="alertType === 'success' ? 'default' : 'destructive'" class="shadow-md">
                        <div class="flex items-center gap-2">
                            <component :is="alertType === 'success' ? CheckCircle : XCircle" class="h-5 w-5" />
                            <AlertTitle>{{ alertType === 'success' ? 'Éxito' : 'Error' }}</AlertTitle>
                        </div>
                        <AlertDescription>{{ alertMessage }}</AlertDescription>
                    </Alert>
                </transition>
            </div>

            <!-- FORMULARIO -->
            <form class="w-8/12 space-y-4" @submit.prevent="submit">
                <div class="space-y-4 rounded-md border bg-card p-6">
                    <div class="space-y-2">
                        <Label for="name">Nombre</Label>
                        <Input id="name" type="text" v-model="form.name" />
                        <p v-if="form.errors.name" class="text-sm text-red-600">{{ form.errors.name }}</p>
                    </div>

                    <div class="space-y-2">
                        <Label for="email">Email</Label>
                        <Input id="email" type="email" v-model="form.email" />
                        <p v-if="form.errors.email" class="text-sm text-red-600">{{ form.errors.email }}</p>
                    </div>

                    <div class="space-y-2">
                        <Label for="password">Contraseña (dejar vacío para no cambiar)</Label>
                        <Input id="password" type="password" v-model="form.password" />
                        <p v-if="form.errors.password" class="text-sm text-red-600">{{ form.errors.password }}</p>
                    </div>

                    <div class="space-y-2">
                        <Label for="password_confirmation">Confirmar Contraseña</Label>
                        <Input id="password_confirmation" type="password" v-model="form.password_confirmation" />
                        <p v-if="form.errors.password_confirmation" class="text-sm text-red-600">{{ form.errors.password_confirmation }}</p>
                    </div>

                    <!-- EMPRESA -->
                    <div class="space-y-3">
                        <Label>Empresa</Label>

                        <!-- Checkbox de Laboratorio -->
                        <div class="flex items-center space-x-2">
                            <Checkbox id="laboratory" :checked="isLaboratory" @update:checked="handleLaboratoryChange" />
                            <Label for="laboratory" class="cursor-pointer text-sm font-normal"> Usuario de Laboratorio (sin empresa asignada) </Label>
                        </div>

                        <!-- Combobox de Empresas -->
                        <div class="space-y-2">
                            <Popover v-model:open="companyComboboxOpen">
                                <PopoverTrigger as-child>
                                    <Button
                                        variant="outline"
                                        role="combobox"
                                        :aria-expanded="companyComboboxOpen"
                                        :disabled="isLaboratory"
                                        class="w-full justify-between"
                                    >
                                        {{ selectedCompanyName }}
                                        <ChevronsUpDown class="ml-2 h-4 w-4 shrink-0 opacity-50" />
                                    </Button>
                                </PopoverTrigger>
                                <PopoverContent class="w-full p-0">
                                    <Command>
                                        <CommandInput placeholder="Buscar empresa..." />
                                        <CommandList>
                                            <CommandEmpty>No se encontró la empresa.</CommandEmpty>
                                            <CommandGroup>
                                                <CommandItem
                                                    v-for="company in companies"
                                                    :key="company.id"
                                                    :value="company.name"
                                                    @select="
                                                        () => {
                                                            form.current_team_id = company.id;
                                                            companyComboboxOpen = false;
                                                        }
                                                    "
                                                >
                                                    <Check
                                                        :class="['mr-2 h-4 w-4', form.current_team_id === company.id ? 'opacity-100' : 'opacity-0']"
                                                    />
                                                    {{ company.name }}
                                                </CommandItem>
                                            </CommandGroup>
                                        </CommandList>
                                    </Command>
                                </PopoverContent>
                            </Popover>
                        </div>

                        <p v-if="form.errors.current_team_id" class="text-sm text-red-600">
                            {{ form.errors.current_team_id }}
                        </p>
                    </div>

                    <!-- ROLES -->
                    <div class="space-y-3">
                        <Label>Roles</Label>

                        <!-- Roles seleccionados como badges -->
                        <div v-if="selectedRoles.length > 0" class="flex flex-wrap gap-2">
                            <Badge
                                v-for="role in selectedRoles"
                                :key="role.id"
                                variant="default"
                                class="cursor-pointer gap-1"
                                @click="removeRole(role.id)"
                            >
                                {{ role.name }}
                                <X class="h-3 w-3" />
                            </Badge>
                        </div>
                        <p v-else class="text-sm text-muted-foreground">Ningún rol seleccionado</p>

                        <!-- Selector de roles disponibles -->
                        <div class="space-y-2">
                            <Label class="text-sm text-muted-foreground">Roles disponibles</Label>
                            <div class="grid grid-cols-2 gap-2">
                                <button
                                    v-for="role in roles"
                                    :key="role.id"
                                    type="button"
                                    @click="toggleRole(role.id)"
                                    :class="[
                                        'flex items-center justify-between rounded-md border px-3 py-2 text-sm transition-colors hover:bg-accent',
                                        isSelected(role.id) ? 'border-primary bg-primary/5' : 'border-border',
                                    ]"
                                >
                                    <span>{{ role.name }}</span>
                                    <Check v-if="isSelected(role.id)" class="h-4 w-4 text-primary" />
                                </button>
                            </div>
                        </div>

                        <p v-if="form.errors.roles" class="text-sm text-red-600">
                            {{ form.errors.roles }}
                        </p>
                    </div>
                </div>

                <Button type="submit" class="mt-2" :disabled="form.processing">
                    <span v-if="form.processing">Actualizando…</span>
                    <span v-else>Guardar cambios</span>
                </Button>
            </form>
        </div>
    </AppLayout>
</template>

<style>
.fade-enter-active,
.fade-leave-active {
    transition: all 0.5s ease;
}
.fade-enter-from,
.fade-leave-to {
    opacity: 0;
    transform: translateY(-1rem);
}
.fade-enter-to,
.fade-leave-from {
    opacity: 1;
    transform: translateY(0);
}
</style>
