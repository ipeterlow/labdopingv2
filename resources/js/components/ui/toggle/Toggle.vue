<script setup lang="ts">
import type { ToggleProps, ToggleEmits } from 'reka-ui'
import { Toggle as ToggleRoot, useForwardPropsEmits } from 'reka-ui'
import { computed, type HTMLAttributes } from 'vue'
import { cn } from '@/lib/utils'

const props = defineProps<ToggleProps & { class?: HTMLAttributes['class'] }>()
const emits = defineEmits<ToggleEmits>()

const delegatedProps = computed(() => {
  const { class: _, ...delegated } = props
  return delegated
})

const forwarded = useForwardPropsEmits(delegatedProps, emits)

const toggleVariants = computed(() => {
  return cn(
    'inline-flex items-center justify-center rounded-md text-sm font-medium transition-colors',
    'hover:bg-muted hover:text-muted-foreground',
    'focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-ring',
    'disabled:pointer-events-none disabled:opacity-50',
    'data-[state=on]:bg-primary data-[state=on]:text-primary-foreground',
    'h-9 px-3 border border-input',
    props.class
  )
})
</script>

<template>
  <ToggleRoot
    v-bind="forwarded"
    :class="toggleVariants"
  >
    <slot />
  </ToggleRoot>
</template>
