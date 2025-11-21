<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, useForm } from '@inertiajs/vue3';
import { computed, ref } from 'vue';
// shadcn-vue
import { Button } from '@/components/ui/button';
import { Command, CommandEmpty, CommandGroup, CommandInput, CommandItem, CommandList } from '@/components/ui/command';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Popover, PopoverContent, PopoverTrigger } from '@/components/ui/popover';
import { ScrollArea } from '@/components/ui/scroll-area';
import { Select, SelectContent, SelectGroup, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import { Textarea } from '@/components/ui/textarea';
import { ChevronsUpDown, Plus, SendHorizonal, Trash2 } from 'lucide-vue-next';

// Props desde Inertia (precargadas)
type Company = { id: number; name: string };
const props = defineProps<{ companies: Company[] }>();

// Form
type SampleItem = {
    external: string;
    type: 'orina' | 'pelo' | 'saliva' | 'suero' | '';
    category: 'A' | 'B' | 'A-B' | '';
};

const form = useForm({
    company_id: null as number | null,
    sent_at: '',
    received_at: '',
    received_at_hour: '',
    description: '',

    shipping_type: '',
    custom_shipping_type: '',

    samples: [] as SampleItem[],
});

// --- Empresas: Combobox 100% shadcn ---
const companiesOpen = ref(false);
function onOpenChange(v: boolean) {
    companiesOpen.value = v;
}

const selectedCompany = computed(() => props.companies.find((c) => c.id === form.company_id) || null);

// Filtrado local con v-model en CommandInput
const companyQuery = ref('');
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

// Muestras dinámicas
const amountToAdd = ref<number | undefined>(undefined);
function addSamples() {
    const n = Number(amountToAdd.value ?? 0);
    if (!Number.isFinite(n) || n <= 0) return;
    for (let i = 0; i < n; i++) {
        form.samples.push({ external: '', type: '', category: '' });
    }
    amountToAdd.value = undefined;
}
function removeSample(idx: number) {
    form.samples.splice(idx, 1);
}

const isOtherShipping = computed(() => form.shipping_type === 'otros');

// Submit
function submit() {
    form.post(route('dopingsample.store'), {
        preserveScroll: false,
    });
}
</script>

<template>
    <Head title="Agregar Muestras" />
    <AppLayout>
        <div class="w-full max-w-6xl space-y-6 p-6">
            <h1 class="text-2xl font-semibold">Agregar Muestras</h1>

            <form @submit.prevent="submit" class="space-y-6">
                <!-- Información general -->
                <div class="space-y-5 rounded-md border bg-card p-6">
                    <h2 class="text-lg font-medium">Información general</h2>

                    <!-- Empresas (Combobox shadcn) -->
                    <div class="space-y-2">
                        <Label for="company">Empresas</Label>

                        <Popover :open="companiesOpen" @update:open="onOpenChange">
                            <PopoverTrigger as-child>
                                <Button
                                    id="company"
                                    variant="outline"
                                    role="combobox"
                                    :aria-expanded="companiesOpen"
                                    class="w-full justify-between"
                                    :aria-invalid="!!companyError || undefined"
                                >
                                    <span class="truncate">
                                        {{ selectedCompany ? selectedCompany.name : 'Selecciona una empresa…' }}
                                    </span>
                                    <ChevronsUpDown class="ml-2 h-4 w-4 opacity-50" />
                                </Button>
                            </PopoverTrigger>

                            <PopoverContent class="w-[--radix-popover-trigger-width] p-0">
                                <Command>
                                    <!-- ✅ v-model correcto en CommandInput -->
                                    <CommandInput v-model="companyQuery" placeholder="Buscar empresa…" class="h-9" autocomplete="off" />
                                    <CommandList>
                                        <CommandEmpty>No se encontraron empresas</CommandEmpty>
                                        <CommandGroup>
                                            <ScrollArea class="h-64">
                                                <CommandItem
                                                    v-for="c in filteredCompanies"
                                                    :key="c.id"
                                                    :value="c.name"
                                                    class="cursor-pointer"
                                                    @mousedown.prevent
                                                    @select="() => chooseCompany(c)"
                                                >
                                                    <Check class="mr-2 h-4 w-4" :class="selectedCompany?.id === c.id ? 'opacity-100' : 'opacity-0'" />
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
                            <Input id="sent_at" type="date" v-model="form.sent_at" :aria-invalid="!!form.errors.sent_at || undefined" />
                            <p v-if="form.errors.sent_at" class="text-sm text-red-600">{{ form.errors.sent_at }}</p>
                        </div>
                        <div class="space-y-2">
                            <Label for="received_at">Fecha recepción</Label>
                            <Input id="received_at" type="date" v-model="form.received_at" :aria-invalid="!!form.errors.received_at || undefined" />
                            <p v-if="form.errors.received_at" class="text-sm text-red-600">{{ form.errors.received_at }}</p>
                        </div>
                        <div class="space-y-2">
                            <Label for="received_at_hour">Hora recepción</Label>
                            <Input
                                id="received_at_hour"
                                type="time"
                                v-model="form.received_at_hour"
                                :aria-invalid="!!form.errors.received_at_hour || undefined"
                            />
                            <p v-if="form.errors.received_at_hour" class="text-sm text-red-600">{{ form.errors.received_at_hour }}</p>
                        </div>
                    </div>

                    <div class="space-y-2">
                        <Label for="description">Observación</Label>
                        <Textarea id="description" v-model="form.description" rows="4" :aria-invalid="!!form.errors.description || undefined" />
                        <p v-if="form.errors.description" class="text-sm text-red-600">{{ form.errors.description }}</p>
                    </div>
                </div>

                <!-- Información de envío -->
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
                            <p v-if="form.errors.shipping_type" class="text-sm text-red-600">{{ form.errors.shipping_type }}</p>
                        </div>

                        <div class="space-y-2">
                            <Label for="custom_shipping_type">Otros</Label>
                            <Input
                                id="custom_shipping_type"
                                type="text"
                                v-model="form.custom_shipping_type"
                                :disabled="!isOtherShipping"
                                :placeholder="isOtherShipping ? 'Especifica el tipo de envío…' : '—'"
                            />
                        </div>
                    </div>
                </div>

                <!-- Listado de muestras -->
                <div class="space-y-5 rounded-md border bg-card p-6">
                    <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                        <h2 class="text-lg font-medium">Listado de muestras</h2>
                        <div class="flex items-center gap-2">
                            <Input type="number" min="1" v-model.number="amountToAdd" placeholder="Cantidad…" class="w-40" />
                            <Button type="button" class="bg-emerald-600 text-white hover:bg-emerald-700" @click="addSamples">
                                <Plus class="mr-2 h-4 w-4" /> Agregar
                            </Button>
                        </div>
                    </div>

                    <div v-if="form.samples.length > 0" class="rounded-md border">
                        <Table>
                            <TableHeader>
                                <TableRow>
                                    <TableHead class="w-12">#</TableHead>
                                    <TableHead>Código Externo "FCC"</TableHead>
                                    <TableHead>Tipo de Muestra</TableHead>
                                    <TableHead>Categoría</TableHead>
                                    <TableHead class="w-20 text-center">Acción</TableHead>
                                </TableRow>
                            </TableHeader>
                            <TableBody>
                                <TableRow v-for="(s, idx) in form.samples" :key="idx">
                                    <TableCell class="font-medium text-muted-foreground">{{ idx + 1 }}</TableCell>
                                    <TableCell>
                                        <Input type="text" v-model="s.external" placeholder="Ingrese código…" class="w-full" />
                                    </TableCell>
                                    <TableCell>
                                        <Select v-model="s.type">
                                            <SelectTrigger class="w-full">
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
                                    </TableCell>
                                    <TableCell>
                                        <Select v-model="s.category">
                                            <SelectTrigger class="w-full">
                                                <SelectValue placeholder="Seleccione…" />
                                            </SelectTrigger>
                                            <SelectContent>
                                                <SelectGroup>
                                                    <SelectItem value="A">A</SelectItem>
                                                    <SelectItem value="B">B</SelectItem>
                                                    <SelectItem value="A-B">A y B</SelectItem>
                                                </SelectGroup>
                                            </SelectContent>
                                        </Select>
                                    </TableCell>
                                    <TableCell class="text-center">
                                        <Button type="button" variant="ghost" size="icon" @click="removeSample(idx)">
                                            <Trash2 class="h-4 w-4 text-destructive" />
                                        </Button>
                                    </TableCell>
                                </TableRow>
                            </TableBody>
                        </Table>
                    </div>

                    <p v-else class="py-8 text-center text-sm text-muted-foreground">
                        No hay muestras agregadas. Usa el botón "Agregar" para añadir muestras.
                    </p>

                    <p v-if="form.errors.samples" class="text-sm text-red-600">
                        {{ form.errors.samples }}
                    </p>
                </div>

                <div class="pt-2">
                    <Button type="submit" :disabled="form.processing" class="w-full sm:w-auto">
                        <SendHorizonal class="mr-2 h-4 w-4" />
                        <span v-if="form.processing">Enviando…</span>
                        <span v-else>Enviar</span>
                    </Button>
                </div>
            </form>
        </div>
    </AppLayout>
</template>
