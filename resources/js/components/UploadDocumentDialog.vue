<script setup lang="ts">
import { Button } from '@/components/ui/button';
import { Dialog, DialogContent, DialogDescription, DialogFooter, DialogHeader, DialogTitle } from '@/components/ui/dialog';
import { Progress } from '@/components/ui/progress';
import axios from 'axios';
import { CheckCircle, XCircle } from 'lucide-vue-next';
import { ref } from 'vue';

const props = defineProps<{
    modelValue: boolean;
    sampleId: number | string;
    title?: string;
    description?: string;
    action?: string; // endpoint backend (por ejemplo /documents/upload-informe)
}>();

const emit = defineEmits(['update:modelValue', 'uploaded']);

const selectedFile = ref<File | null>(null);
const isUploading = ref(false);
const progress = ref(0);
const message = ref<string | null>(null);

const handleFileChange = (event: Event) => {
    const target = event.target as HTMLInputElement;
    if (target.files && target.files.length > 0) {
        selectedFile.value = target.files[0];
        message.value = null;
    }
};

const uploadFile = async () => {
    if (!selectedFile.value) {
        message.value = '❌ Por favor selecciona un archivo.';
        return;
    }

    isUploading.value = true;
    progress.value = 0;
    message.value = null;

    try {
        const formData = new FormData();
        formData.append('file', selectedFile.value);
        formData.append('external_id', props.sampleId.toString());

        // Si no se pasa action, usa el endpoint por defecto
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

        // Espera 1.5 segundos antes de cerrar el modal
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
                    {{ description || 'Selecciona un archivo PDF para subirlo.' }}
                </DialogDescription>
            </DialogHeader>

            <div class="space-y-4 py-2">
                <input
                    type="file"
                    accept=".pdf"
                    class="block w-full cursor-pointer rounded-lg border border-gray-300 text-sm text-gray-700 file:mr-3 file:rounded-md file:border-0 file:bg-primary file:px-4 file:py-2 file:text-white hover:file:bg-primary/90"
                    @change="handleFileChange"
                />

                <div v-if="selectedFile" class="text-sm text-gray-600">
                    Archivo seleccionado: <strong>{{ selectedFile.name }}</strong>
                </div>

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

                <Button :disabled="isUploading || !selectedFile" @click="uploadFile">
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
