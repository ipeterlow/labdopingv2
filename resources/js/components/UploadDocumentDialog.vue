<script setup lang="ts">
import { Button } from '@/components/ui/button';
import { Dialog, DialogContent, DialogDescription, DialogFooter, DialogHeader, DialogTitle } from '@/components/ui/dialog';
import { Progress } from '@/components/ui/progress';
import axios from 'axios';
import { AlertTriangle, CheckCircle, X, XCircle } from 'lucide-vue-next';
import { ref } from 'vue';

const props = defineProps<{
    modelValue: boolean;
    sampleId: number | string;
    externalId?: string;
    title?: string;
    description?: string;
    action?: string;
}>();

const emit = defineEmits(['update:modelValue', 'uploaded']);

const selectedFile = ref<File | null>(null);
const isUploading = ref(false);
const progress = ref(0);
const message = ref<string | null>(null);
const mismatchWarning = ref(false);

const getFileNameWithoutExtension = (filename: string): string => {
    return filename.replace(/\.[^/.]+$/, '');
};

const handleFileChange = (event: Event) => {
    const target = event.target as HTMLInputElement;
    if (target.files && target.files.length > 0) {
        selectedFile.value = target.files[0];
        message.value = null;
        mismatchWarning.value = false;
    }
};

const validateAndUpload = () => {
    if (!selectedFile.value) {
        message.value = '❌ Por favor selecciona un archivo.';
        return;
    }

    if (props.externalId) {
        const fileBaseName = getFileNameWithoutExtension(selectedFile.value.name);
        if (fileBaseName !== props.externalId) {
            mismatchWarning.value = true;
            return;
        }
    }

    uploadFile();
};

const closeMismatchBanner = () => {
    mismatchWarning.value = false;
};

const uploadFile = async () => {
    if (!selectedFile.value) return;

    isUploading.value = true;
    progress.value = 0;
    message.value = null;

    try {
        const formData = new FormData();
        formData.append('file', selectedFile.value);
        formData.append('external_id', props.sampleId.toString());

        const endpoint = props.action ?? '/documents/upload-informe';

        await axios.post(endpoint, formData, {
            headers: { 'Content-Type': 'multipart/form-data' },
            onUploadProgress: (event) => {
                if (event.total) {
                    progress.value = Math.round((event.loaded * 100) / event.total);
                }
            },
        });

        message.value = '✅ Documento subido correctamente.';
        progress.value = 100;
        emit('uploaded');

        setTimeout(() => {
            emit('update:modelValue', false);
            selectedFile.value = null;
            progress.value = 0;
            message.value = null;
        }, 1500);
    } catch (error) {
        console.error(error);
        message.value = '❌ Error al subir el documento.';
    } finally {
        isUploading.value = false;
    }
};
</script>

<template>
    <Dialog :open="modelValue" @update:open="emit('update:modelValue', $event)">
        <DialogContent class="max-w-md">
            <DialogHeader>
                <DialogTitle>{{ title || 'Subir Documento' }}</DialogTitle>
                <DialogDescription>
                    {{ description || 'Selecciona un archivo o toma una foto para subirlo.' }}
                </DialogDescription>
            </DialogHeader>

            <div class="space-y-4 py-2">
                <input
                    type="file"
                    accept="image/*,.pdf"
                    capture="environment"
                    class="block w-full cursor-pointer rounded-lg border border-gray-300 text-sm text-gray-700 file:mr-3 file:rounded-md file:border-0 file:bg-primary file:px-4 file:py-2 file:text-white hover:file:bg-primary/90"
                    @change="handleFileChange"
                />

                <div v-if="selectedFile" class="text-sm text-gray-600">
                    Archivo seleccionado: <strong>{{ selectedFile.name }}</strong>
                </div>

                <!-- Alerta de discrepancia entre nombre de archivo y número externo -->
                <transition name="fade">
                    <div v-if="mismatchWarning" class="relative rounded-lg border border-amber-300 bg-amber-50 p-4 pr-10">
                        <button
                            type="button"
                            class="absolute right-2 top-2 rounded p-1 text-amber-600 hover:bg-amber-100"
                            aria-label="Cerrar"
                            @click="closeMismatchBanner"
                        >
                            <X class="h-4 w-4" />
                        </button>
                        <div class="flex items-start gap-3">
                            <AlertTriangle class="mt-0.5 h-5 w-5 shrink-0 text-amber-600" />
                            <div class="space-y-1">
                                <p class="text-sm font-semibold text-amber-800">
                                    El informe no corresponde al registro
                                </p>
                                <p class="text-sm text-amber-700">
                                    El nombre del archivo
                                    <strong>"{{ selectedFile?.name }}"</strong>
                                    no coincide con el número externo
                                    <strong>"{{ externalId }}"</strong>.
                                    Verifica que estás subiendo el documento correcto.
                                </p>
                            </div>
                        </div>
                    </div>
                </transition>

                <!-- Barra de progreso -->
                <div v-if="isUploading" class="space-y-2">
                    <Progress :value="progress" class="h-2" />
                    <p class="text-center text-xs text-muted-foreground">
                        {{ progress < 100 ? `Subiendo... ${progress}%` : 'Procesando...' }}
                    </p>
                </div>

                <!-- Mensaje de éxito o error -->
                <transition name="fade">
                    <div v-if="message" class="mt-3 flex flex-col items-center">
                        <component
                            :is="message.startsWith('✅') ? CheckCircle : XCircle"
                            class="mb-1 h-6 w-6"
                            :class="message.startsWith('✅') ? 'text-green-600' : 'text-red-600'"
                        />
                        <p
                            class="text-center text-sm font-medium"
                            :class="{
                                'text-green-600': message.startsWith('✅'),
                                'text-red-600': message.startsWith('❌'),
                            }"
                        >
                            {{ message }}
                        </p>
                    </div>
                </transition>
            </div>

            <DialogFooter>
                <Button variant="secondary" @click="emit('update:modelValue', false)" :disabled="isUploading"> Cancelar </Button>

                <Button :disabled="isUploading || !selectedFile" @click="validateAndUpload">
                    <span v-if="!isUploading && !message">Subir</span>
                    <span v-else-if="isUploading">Subiendo...</span>
                    <span v-else-if="message?.startsWith('✅')">Listo ✅</span>
                    <span v-else-if="message?.startsWith('❌')">Reintentar</span>
                </Button>
            </DialogFooter>
        </DialogContent>
    </Dialog>
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
