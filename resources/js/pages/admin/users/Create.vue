<script setup lang="ts">
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Checkbox } from '@/components/ui/checkbox';
import { Command, CommandEmpty, CommandGroup, CommandInput, CommandItem, CommandList } from '@/components/ui/command';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Popover, PopoverContent, PopoverTrigger } from '@/components/ui/popover';
import AppLayout from '@/layouts/AppLayout.vue';
import { Head } from '@inertiajs/vue3';
import { useForm } from 'laravel-precognition-vue-inertia';
import { Check, ChevronsUpDown, SendHorizonal, X } from 'lucide-vue-next';
import { computed, ref } from 'vue';

// Props
const props = defineProps<{
    roles: Array<{ id: number; name: string }>;
    companies: Array<{ id: number; name: string }>;
}>();

// Refs para el combobox y checkbox
const companyComboboxOpen = ref(false);
const isLaboratory = ref(false);

// 1) Inicializa el form: método, url y datos iniciales
const form = useForm('post', '/users', {
    name: '',
    email: '',
    password: '',
    password_confirmation: '',
    roles: [] as number[],
    current_team_id: null as number | null,
});

// (Opcional) Ajusta el debounce de la validación (ms)
form.setValidationTimeout(400); // UX más ágil

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

// 2) Submit: con Inertia sólo pasas opciones del visit
const submit = () => {
    form.submit({
        preserveScroll: true,
        onSuccess: () => form.reset(),
    });
};
</script>

<template>
    <Head title="Create User" />
    <AppLayout>
        <div class="p-4">
            <h1 class="mb-4 text-2xl font-semibold">Create User</h1>

            <form class="w-8/12 space-y-4" @submit.prevent="submit">
                <!-- NAME -->

                <div class="space-y-4 rounded-md border bg-card p-6">
                    <div class="space-y-2">
                        <Label for="name">Nombre</Label>
                        <Input
                            id="name"
                            type="text"
                            autocomplete="name"
                            v-model="form.name"
                            @change="form.validate('name')"
                            :aria-invalid="form.invalid('name') || undefined"
                        />
                        <p v-if="form.invalid('name')" class="text-sm text-red-600">
                            {{ form.errors.name }}
                        </p>
                    </div>

                    <!-- EMAIL -->
                    <div class="space-y-2">
                        <Label for="email">Email</Label>
                        <Input
                            id="email"
                            type="email"
                            autocomplete="email"
                            v-model="form.email"
                            @change="form.validate('email')"
                            :aria-invalid="form.invalid('email') || undefined"
                        />
                        <p v-if="form.invalid('email')" class="text-sm text-red-600">
                            {{ form.errors.email }}
                        </p>
                    </div>

                    <!-- PASSWORD -->
                    <div class="space-y-2">
                        <Label for="password">Contraseña</Label>
                        <Input
                            id="password"
                            type="password"
                            autocomplete="new-password"
                            v-model="form.password"
                            @change="form.validate('password')"
                            :aria-invalid="form.invalid('password') || undefined"
                        />
                        <p v-if="form.invalid('password')" class="text-sm text-red-600">
                            {{ form.errors.password }}
                        </p>
                    </div>

                    <!-- PASSWORD CONFIRMATION -->
                    <div class="space-y-2">
                        <Label for="password_confirmation">Confirmar Contraseña</Label>
                        <Input
                            id="password_confirmation"
                            type="password"
                            autocomplete="new-password"
                            v-model="form.password_confirmation"
                            @change="form.validate('password_confirmation')"
                            :aria-invalid="form.invalid('password_confirmation') || undefined"
                        />
                        <p v-if="form.invalid('password_confirmation')" class="text-sm text-red-600">
                            {{ form.errors.password_confirmation }}
                        </p>
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

                        <p v-if="form.invalid('current_team_id')" class="text-sm text-red-600">
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

                        <p v-if="form.invalid('roles')" class="text-sm text-red-600">
                            {{ form.errors.roles }}
                        </p>
                    </div>
                </div>

                <Button type="submit" class="mt-2" :disabled="form.processing || form.validating">
                    <SendHorizonal class="mr-2 h-4 w-4" />
                    <span v-if="form.validating">Validando…</span>
                    <span v-else-if="form.processing">Creando…</span>
                    <span v-else>Crear cuenta</span>
                </Button>
            </form>
        </div>
    </AppLayout>
</template>
