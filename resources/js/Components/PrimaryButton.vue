<template>
  <button
    v-bind="$attrs"
    :disabled="disabled || loading"
    :class="[
      'inline-flex items-center justify-center gap-2 px-5 py-2.5 rounded-md text-sm font-bold transition focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-offset-white',
      disabled || loading
        ? 'bg-[#e35d8f]/40 cursor-not-allowed text-white/60'
        : 'bg-[#e35d8f] hover:bg-[#c44a7a] text-white focus:ring-[#e35d8f]',
      fullWidth ? 'w-full' : '',
    ]"
  >
    <svg v-if="loading" class="animate-spin h-4 w-4" fill="none" viewBox="0 0 24 24">
      <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
      <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"/>
    </svg>
    <slot />
  </button>
</template>

<script setup>
defineProps({
  disabled:  Boolean,
  loading:   Boolean,
  fullWidth: Boolean,
});
defineOptions({ inheritAttrs: false });
</script>
