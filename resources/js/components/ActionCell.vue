<script setup lang="ts">
import {
    AlertDialog,
    AlertDialogAction,
    AlertDialogCancel,
    AlertDialogContent,
    AlertDialogDescription,
    AlertDialogFooter,
    AlertDialogHeader,
    AlertDialogTitle,
    AlertDialogTrigger,
} from '@/components/ui/alert-dialog'
import { Button } from '@/components/ui/button'
import {
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuItem,
    DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu'
import UploadDocumentDialog from '@/components/UploadDocumentDialog.vue'
import { router } from '@inertiajs/vue3'
import {
    ClipboardList,
    Eye,
    FileDown,
    FileUp,
    MoreHorizontal,
    Pencil,
    Trash2,
} from 'lucide-vue-next'
import { ref } from 'vue'

const props = defineProps<{
    resource: string
    id: string | number
    show?: boolean
    edit?: boolean
    destroy?: boolean
    pdf?: boolean
    uploadInforme?: boolean
    uploadCadenaCustodia?: boolean
}>()

const showUploadInforme = ref(false)
const showUploadCadena = ref(false)
const uploadMessage = ref<string | null>(null)

const goToEdit = () => router.visit(route(`${props.resource}.edit`, props.id))
const goToView = () => router.visit(route(`${props.resource}.show`, props.id))

const destroyItem = () => {
    router.delete(route(`${props.resource}.destroy`, props.id), {
        preserveState: false,
        preserveScroll: true,
    })
}

const downloadPdf = () => {
    const url = route('samples.pdf', props.id)
    window.open(url, '_blank')
}

// 🔥 Nuevo manejo sin alert()
const handleUploaded = (tipo: string) => {
    uploadMessage.value = `✅ ${tipo} subido correctamente.`

    // Limpia el mensaje automáticamente después de 3s
    setTimeout(() => {
        uploadMessage.value = null
    }, 3000)
}
</script>

<template>
    <AlertDialog>
        <DropdownMenu>
            <DropdownMenuTrigger as-child>
                <Button variant="ghost" class="h-8 w-8 p-0">
                    <MoreHorizontal class="h-4 w-4" />
                </Button>
            </DropdownMenuTrigger>

            <DropdownMenuContent align="end" class="w-52">
                <!-- Ver -->
                <DropdownMenuItem v-if="show !== false" @click="goToView">
                    <Eye class="mr-2 h-4 w-4 text-muted-foreground" />
                    Ver Detalle
                </DropdownMenuItem>

                <!-- Editar -->
                <DropdownMenuItem v-if="edit !== false" @click="goToEdit">
                    <Pencil class="mr-2 h-4 w-4 text-muted-foreground" />
                    Editar
                </DropdownMenuItem>

                <!-- Subir Informe Muestra -->
                <DropdownMenuItem v-if="uploadInforme !== false" @click="showUploadInforme = true">
                    <FileUp class="mr-2 h-4 w-4 text-blue-600" />
                    Subir Informe Muestra
                </DropdownMenuItem>

                <!-- Subir Cadena de Custodia -->
                <DropdownMenuItem v-if="uploadCadenaCustodia !== false" @click="showUploadCadena = true">
                    <ClipboardList class="mr-2 h-4 w-4 text-amber-600" />
                    Subir Cadena Custodia
                </DropdownMenuItem>

                <!-- Eliminar -->
                <AlertDialogTrigger v-if="destroy !== false" as-child>
                    <DropdownMenuItem class="text-red-600" @select.prevent>
                        <Trash2 class="mr-2 h-4 w-4 text-red-600" />
                        Eliminar
                    </DropdownMenuItem>
                </AlertDialogTrigger>

                <!-- Descargar PDF -->
                <DropdownMenuItem v-if="pdf !== false || $attrs.id === 'actions'" @click="downloadPdf">
                    <FileDown class="mr-2 h-4 w-4 text-green-600" />
                    Comp. Recepción
                </DropdownMenuItem>
            </DropdownMenuContent>
        </DropdownMenu>

        <!-- Confirmación Eliminar -->
        <AlertDialogContent>
            <AlertDialogHeader>
                <AlertDialogTitle>¿Estás absolutamente seguro?</AlertDialogTitle>
                <AlertDialogDescription>
                    Esta acción no se puede deshacer. Esto eliminará permanentemente el
                    registro de nuestros servidores.
                </AlertDialogDescription>
            </AlertDialogHeader>
            <AlertDialogFooter>
                <AlertDialogCancel>Cancelar</AlertDialogCancel>
                <AlertDialogAction @click="destroyItem"> Continuar </AlertDialogAction>
            </AlertDialogFooter>
        </AlertDialogContent>
    </AlertDialog>

    <!-- Diálogo de Subida: Informe Muestra -->
    <UploadDocumentDialog v-if="uploadInforme !== false" v-model="showUploadInforme" :sample-id="id"
        title="Subir Informe de Muestra"
        description="Selecciona el archivo PDF correspondiente al informe de la muestra."
        action="/documents/upload-informe" @uploaded="handleUploaded('Informe de Muestra')" />

    <!-- Diálogo de Subida: Cadena de Custodia -->
    <UploadDocumentDialog v-if="uploadCadenaCustodia !== false" v-model="showUploadCadena" :sample-id="id"
        title="Subir Cadena de Custodia"
        description="Selecciona el archivo PDF correspondiente a la cadena de custodia."
        action="/documents/upload-cadena" @uploaded="handleUploaded('Cadena de Custodia')" />

    <!-- Mensaje de confirmación dentro de la vista -->
    <transition name="fade">
        <div v-if="uploadMessage"
            class="fixed bottom-6 right-6 z-50 rounded-lg bg-green-600 text-white px-4 py-3 shadow-lg text-sm font-medium flex items-center gap-2">
            <FileUp class="h-4 w-4 text-white" />
            {{ uploadMessage }}
        </div>
    </transition>
</template>

<style scoped>
.fade-enter-active,
.fade-leave-active {
    transition: opacity 0.3s ease;
}

.fade-enter-from,
.fade-leave-to {
    opacity: 0;
}
</style>
