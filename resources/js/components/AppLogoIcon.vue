<script setup lang="ts">
import type { HTMLAttributes } from 'vue';

defineOptions({
    inheritAttrs: false,
});

interface Props {
    className?: HTMLAttributes['class'];
}

defineProps<Props>();
</script>

<template>
    <img
        src="/images/pdf/escudo_uchile_fondo.png"
        alt="Logo Universidad de Chile"
        class="h-40 w-auto object-contain"
        :class="className"
        v-bind="$attrs"
    />
</template>
