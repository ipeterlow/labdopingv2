<script setup lang="ts">
import { computed, nextTick, onMounted, onUnmounted, ref, watch } from 'vue';

import { Separator } from '@/components/ui/separator';
import { useAppearance } from '@/composables/useAppearance';

const emit = defineEmits<{
    (e: 'verify', token: string): void;
    (e: 'error'): void;
    (e: 'expire'): void;
}>();

const siteKey = import.meta.env.VITE_TURNSTILE_SITE_KEY as string;
const isEnabled = !!siteKey;

const { appearance } = useAppearance();
const widgetId = ref<string | null>(null);
let observer: ResizeObserver | null = null;
let scriptElement: HTMLScriptElement | null = null;
let mediaQueryCleanup: (() => void) | null = null;

const loadTurnstileScript = (): Promise<void> => {
    return new Promise((resolve) => {
        if ((window as any).turnstile) {
            resolve();
            return;
        }

        const existingScript = document.querySelector(
            'script[src*="challenges.cloudflare.com/turnstile"]',
        );
        if (existingScript) {
            if ((window as any).turnstile) {
                resolve();
            } else {
                existingScript.addEventListener('load', () => resolve());
            }
            return;
        }

        scriptElement = document.createElement('script');
        scriptElement.src =
            'https://challenges.cloudflare.com/turnstile/v0/api.js?onload=onloadTurnstileCallback&render=explicit';
        scriptElement.async = true;
        scriptElement.defer = true;
        document.head.appendChild(scriptElement);
        resolve();
    });
};

const getCurrentTheme = (): 'light' | 'dark' => {
    if (typeof window === 'undefined') {
        return 'dark';
    }

    if (appearance.value === 'system') {
        const mediaQuery = window.matchMedia('(prefers-color-scheme: dark)');
        return mediaQuery.matches ? 'dark' : 'light';
    }

    return appearance.value === 'dark' ? 'dark' : 'light';
};

const currentTheme = computed(() => getCurrentTheme());

const renderTurnstile = () => {
    if (!isEnabled || !siteKey) {
        emit('verify', 'disabled');
        return;
    }

    if ((window as any).turnstile) {
        const theme = getCurrentTheme();

        if (widgetId.value) {
            (window as any).turnstile.remove(widgetId.value);
            widgetId.value = null;
        }

        widgetId.value = (window as any).turnstile.render('#turnstile-widget', {
            sitekey: siteKey,
            theme: theme,
            callback: (token: string) => emit('verify', token),
            'error-callback': () => emit('error'),
            'expired-callback': () => emit('expire'),
        });

        const el = document.getElementById('turnstile-widget');
        if (el && !observer) {
            observer = new ResizeObserver(() => {});
            observer.observe(el);
        }
    }
};

watch(currentTheme, () => {
    if (widgetId.value) {
        renderTurnstile();
    }
});

onMounted(async () => {
    if (!isEnabled) {
        await nextTick();
        emit('verify', 'disabled');
        return;
    }

    (window as any).onloadTurnstileCallback = renderTurnstile;

    await loadTurnstileScript();

    if ((window as any).turnstile) {
        renderTurnstile();
    }

    if (appearance.value === 'system') {
        const mediaQuery = window.matchMedia('(prefers-color-scheme: dark)');
        const handler = () => {
            if (widgetId.value) {
                renderTurnstile();
            }
        };
        mediaQuery.addEventListener('change', handler);
        mediaQueryCleanup = () =>
            mediaQuery.removeEventListener('change', handler);
    }
});

onUnmounted(() => {
    if (mediaQueryCleanup) {
        mediaQueryCleanup();
        mediaQueryCleanup = null;
    }
    if (widgetId.value && (window as any).turnstile) {
        (window as any).turnstile.remove(widgetId.value);
    }
    if (observer) {
        observer.disconnect();
    }
    delete (window as any).onloadTurnstileCallback;
});
</script>

<template>
    <template v-if="isEnabled && siteKey">
        <Separator />
        <div id="turnstile-widget" class="flex justify-center"></div>
    </template>
</template>
