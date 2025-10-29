<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, useForm } from '@inertiajs/vue3';
import { computed, ref } from 'vue';

// shadcn-vue
import { Alert, AlertDescription, AlertTitle } from '@/components/ui/alert';
import { Button } from '@/components/ui/button';
import { Command, CommandEmpty, CommandGroup, CommandInput, CommandItem, CommandList } from '@/components/ui/command';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Popover, PopoverContent, PopoverTrigger } from '@/components/ui/popover';
import { ScrollArea } from '@/components/ui/scroll-area';
import { Select, SelectContent, SelectGroup, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { Textarea } from '@/components/ui/textarea';
import { Check, ChevronsUpDown, SendHorizonal, XCircle } from 'lucide-vue-next';

// --- Props de Edición ---
type Company = { id: number; name: string };

type Sample = {
    id: number;
    reception_id: number;
    company_id: number;
    sent_at: string | null;
    received_at: string | null;
    received_at_hour: string | null;
    description: string | null;
    shipping_type: string;
    custom_shipping_type: string | null;
};

type SampleItem = {
    id: number;
    external: string; // <-- El controlador ahora envía 'external'
    type: 'orina' | 'pelo' | 'saliva' | 'suero' | '';
    category: 'A' | 'B' | '';
};

const props = defineProps<{
    companies: Company[];
    sample: Sample;
    sampleGroup: SampleItem[];
}>();

// --- Formulario ---
const form = useForm({
    company_id: props.sample.company_id || '',
    sent_at: props.sample.sent_at || '',
    received_at: props.sample.received_at || '',
    received_at_hour: props.sample.received_at_hour || '',
    description: props.sample.description || '',
    shipping_type: props.sample.shipping_type || '',
    custom_shipping_type: props.sample.custom_shipping_type || '',
    samples: props.sampleGroup,
});

// Alert (sin cambios)
const showAlert = ref(false);
const alertType = ref<'success' | 'error'>('success');
const alertMessage = ref('');
function flash(kind: 'success' | 'error', msg: string) {
    alertType.value = kind;
    alertMessage.value = msg;
    showAlert.value = true;
    setTimeout(() => (showAlert.value = false), 3000);
}

// Combobox Empresas (sin cambios)
const companiesOpen = ref(false);
function onOpenChange(v: boolean) { companiesOpen.value = v; }
const selectedCompany = computed(() => props.companies.find((c) => c.id === form.company_id) || null);
const companyQuery = ref(selectedCompany.value?.name ?? '');
const filteredCompanies = computed(() => {
    const q = companyQuery.value.trim().toLowerCase();
    if (!q) return props.companies;
    return props.companies.filter((c) => c.name.toLowerCase().includes(q));
});
function chooseCompany(c: Company) {
    form.company_id = c.id;
    companyQuery.value = c.name;
    companiesOpen.value = false;
}
const companyError = computed(() => form.errors.company_id);


// Muestras (sin cambios)
function removeSample(idx: number) {
    form.samples.splice(idx, 1);
}
const isOtherShipping = computed(() => form.shipping_type === 'otros');

// Submit (sin cambios)
function submit() {
    form.put(route('dopingsample.update', props.sample.reception_id), {
        preserveScroll: true,
        onSuccess: () => {
            flash('success', 'Las muestras fueron actualizadas correctamente.');
        },
        onError: () => {
            flash('error', 'Revisa los campos con errores.');
        },
    });
}
</script>

<template>

    <Head title="Editar Muestras" />
    <AppLayout>
        <div class="fixed top-4 right-4 z-50 w-96">
            <transition name="fade">
                <Alert v-if="showAlert" :variant="alertType === 'success' ? 'default' : 'destructive'"
                    class="shadow-md">
                    <div class="flex items-center gap-2">
                        <component :is="alertType === 'success' ? Check : XCircle" class="h-5 w-5" />
                        <AlertTitle>{{ alertType === 'success' ? 'Éxito' : 'Error' }}</AlertTitle>
                    </div>
                    <AlertDescription>{{ alertMessage }}</AlertDescription>
                </Alert>
            </transition>
        </div>

        <div class="w-full max-w-6xl space-y-6 p-6">
            <h1 class="text-2xl font-semibold">Editar Muestras (Recepción: {{ props.sample.reception_id }})</h1>

            <form @submit.prevent="submit" class="space-y-6">
                <div class="space-y-5 rounded-md border bg-card p-6">
                    <h2 class="text-lg font-medium">Información general</h2>
                    <div class="space-y-2">
                        <Label for="company">Empresas</Label>
                        <Popover :open="companiesOpen" @update:open="onOpenChange">
                            <PopoverTrigger as-child>
                                <Button id="company" variant="outline" role="combobox" :aria-expanded="companiesOpen"
                                    class="w-full justify-between" :aria-invalid="!!companyError || undefined">
                                    <span class="truncate">
                                        {{ selectedCompany ? selectedCompany.name : 'Selecciona una empresa…' }}
                                    </span>
                                    <ChevronsUpDown class="ml-2 h-4 w-4 opacity-50" />
                                </Button>
                            </PopoverTrigger>
                            <PopoverContent class="w-[--radix-popover-trigger-width] p-0">
                                <Command>
                                    <CommandInput v-model="companyQuery" placeholder="Buscar empresa…" class="h-9"
                                        autocomplete="off" />
                                    <CommandList>
                                        <CommandEmpty>No se encontraron empresas</CommandEmpty>
                                        <CommandGroup>
                                            <ScrollArea class="h-64">
                                                <CommandItem v-for="c in filteredCompanies" :key="c.id" :value="c.name"
                                                    class="cursor-pointer" @mousedown.prevent
                                                    @select="() => chooseCompany(c)">
                                                    <Check class="mr-2 h-4 w-4"
                                                        :class="selectedCompany?.id === c.id ? 'opacity-100' : 'opacity-0'" />
                                                    {{ c.name }}
                                                </CommandItem>
                                            </ScrollArea>
                                        </CommandGroup>
                                    </CommandList>
                                </Command>
                            </PopoverContent>
                        </Popover>
                        <p v-if="companyError" class="text-sm text-red-600">{{ companyError }}</p>
                    </div>
                    <div class="grid gap-4 sm:grid-cols-3">
                        <div class="space-y-2">
                            <Label for="sent_at">Fecha envío</Label>
                            <Input id="sent_at" type="date" v-model="form.sent_at"
                                :aria-invalid="!!form.errors.sent_at || undefined" />
                            <p v-if="form.errors.sent_at" class="text-sm text-red-600">{{ form.errors.sent_at }}</p>
                        </div>
                        <div class="space-y-2">
                            <Label for="received_at">Fecha recepción</Label>
                            <Input id="received_at" type="date" v-model="form.received_at"
                                :aria-invalid="!!form.errors.received_at || undefined" />
                            <p v-if="form.errors.received_at" class="text-sm text-red-600">{{ form.errors.received_at }}
                            </p>
                        </div>
                        <div class="space-y-2">
                            <Label for="received_at_hour">Hora recepción</Label>
                            <Input id="received_at_hour" type="time" v-model="form.received_at_hour"
                                :aria-invalid="!!form.errors.received_at_hour || undefined" />
                            <p v-if="form.errors.received_at_hour" class="text-sm text-red-600">{{
                                form.errors.received_at_hour }}</p>
                        </div>
                    </div>
                    <div class="space-y-2">
                        <Label for="description">Observación</Label>
                        <Textarea id="description" v-model="form.description" rows="4"
                            :aria-invalid="!!form.errors.description || undefined" />
                        <p v-if="form.errors.description" class="text-sm text-red-600">{{ form.errors.description }}</p>
                    </div>
                </div>

                <div class="space-y-5 rounded-md border bg-card p-6">
                    <h2 class="text-lg font-medium">Información de envío</h2>
                    <div class="grid gap-4 sm:grid-cols-2">
                        <div class="space-y-2">
                            <Label>Tipo de envío</Label>

                            <Select v-model="form.shipping_type">

                                <SelectTrigger :aria-invalid="!!form.errors.shipping_type || undefined">
                                    <SelectValue placeholder="Seleccionar…" />
                                </SelectTrigger>
                                <SelectContent>
                                    <SelectGroup>
                                        <SelectItem value="Chilexpress">Chilexpress</SelectItem>
                                        <SelectItem value="Correos de Chile">Correos de Chile</SelectItem>
                                        <SelectItem value="Soserval">Soserval</SelectItem>
                                        <SelectItem value="Pullman">Pullman</SelectItem>
                                        <SelectItem value="Speed Cargo">Speed Cargo</SelectItem>
                                        <SelectItem value="Starken">Starken</SelectItem>
                                        <SelectItem value="Chibra">Chibra</SelectItem>
                                        <SelectItem value="otros">Otros</SelectItem>
                                    </SelectGroup>
                                </SelectContent>
                            </Select>
                            <p v-if="form.errors.shipping_type" class="text-sm text-red-600">{{
                                form.errors.shipping_type }}</p>
                        </div>
                        <div class="space-y-2">
                            <Label for="custom_shipping_type">Otros</Label>
                            <Input id="custom_shipping_type" type="text" v-model="form.custom_shipping_type"
                                :disabled="!isOtherShipping"
                                :placeholder="isOtherShipping ? 'Especifica el tipo de envío…' : '—'" />
                        </div>
                    </div>
                </div>

                <div class="space-y-5 rounded-md border bg-card p-6">
                    <h2 class="text-lg font-medium">Listado de muestras</h2>

                    <div class="space-y-4">
                        <div v-for="(s, idx) in form.samples" :key="s.id" class="rounded-md border p-4">
                            <div class="mb-3 text-sm font-medium text-muted-foreground">Muestra #{{ idx + 1 }}</div>
                            <div class="grid gap-4 sm:grid-cols-3">
                                <div class="space-y-2">
                                    <Label :for="`ext-${idx}`">Código de Muestra Externo "FCC"</Label>
                                    <Input :id="`ext-${idx}`" type="text" v-model="s.external" />
                                </div>
                                <div class="space-y-2">
                                    <Label :for="`type-${idx}`">Tipo de Muestra</Label>
                                    <Select v-model="s.type">
                                        <SelectTrigger :id="`type-${idx}`">
                                            <SelectValue placeholder="Seleccione…" />
                                        </SelectTrigger>
                                        <SelectContent>
                                            <SelectGroup>
                                                <SelectItem value="orina">Orina</SelectItem>
                                                <SelectItem value="pelo">Pelo</SelectItem>
                                                <SelectItem value="saliva">Saliva</SelectItem>
                                                <SelectItem value="suero">Suero</SelectItem>
                                            </SelectGroup>
                                        </SelectContent>
                                    </Select>
                                </div>
                                <div class="space-y-2">
                                    <Label :for="`cat-${idx}`">Categoría de Muestra</Label>
                                    <Select v-model="s.category">
                                        <SelectTrigger :id="`cat-${idx}`">
                                            <SelectValue placeholder="Seleccione…" />
                                        </SelectTrigger>
                                        <SelectContent>
                                            <SelectGroup>
                                                <SelectItem value="A">A</SelectItem>
                                                <SelectItem value="B">B</SelectItem>
                                            </SelectGroup>
                                        </SelectContent>
                                    </Select>
                                </div>
                            </div>
                            <div class="mt-3 flex justify-end">
                                <Button type="button" variant="destructive" @click="removeSample(idx)">Eliminar</Button>
                            </div>
                        </div>
                    </div>

                    <p v-if="form.errors.samples" class="text-sm text-red-600">
                        {{ form.errors.samples }}
                    </p>
                </div>

                <div class="pt-2">
                    <Button type="submit" :disabled="form.processing" class="w-full sm:w-auto">
                        <SendHorizonal class="mr-2 h-4 w-4" />
                        <span v-if="form.processing">Actualizando…</span>
                        <span v-else>Actualizar</span>
                    </Button>
                </div>
            </form>
        </div>
    </AppLayout>
</template>

<style scoped>
/* Estilos (sin cambios) */
.fade-enter-active,
.fade-leave-active {
    transition: opacity 0.2s;
}

.fade-enter-from,
.fade-leave-to {
    opacity: 0;
}
</style>