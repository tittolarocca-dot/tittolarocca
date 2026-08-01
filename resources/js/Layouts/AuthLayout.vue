<template>
  <div class="min-h-screen bg-[#0f0f0f] flex flex-col justify-center py-12 px-4">
    <FlashMessage />

    <!-- Logo -->
    <div class="text-center mb-8">
      <Link href="/" class="inline-block">
        <span class="text-white font-black text-3xl tracking-tight">
          booklola<span class="text-[#e35d8f]">.ch</span>
        </span>
        <p class="text-gray-500 text-sm mt-1">Schweizer Erotik-Inserate · 18+</p>
      </Link>
    </div>

    <!-- Card -->
    <div class="mx-auto w-full max-w-md bg-[#1a1a1a] border border-white/10 rounded-2xl shadow-2xl overflow-hidden">
      <!-- Header stripe -->
      <div class="bg-[#111] border-b border-white/10 px-6 py-4">
        <h1 class="text-white font-semibold text-lg">{{ title }}</h1>
        <p v-if="subtitle" class="text-gray-400 text-sm mt-0.5">{{ subtitle }}</p>
      </div>
      <div class="px-6 py-6">
        <slot />
      </div>
    </div>

    <!-- Back link -->
    <div class="text-center mt-6">
      <Link href="/" class="text-gray-500 text-sm hover:text-gray-300 transition">{{ backLabel }}</Link>
    </div>
  </div>
</template>

<script setup>
import { computed } from 'vue';
import { Link } from '@inertiajs/vue3';
import FlashMessage from '@/Components/FlashMessage.vue';
import { useI18n } from '@/composables/useI18n';

const { t } = useI18n();

defineProps({
  title:    { type: String, required: true },
  subtitle: String,
});

const backLabel = computed(() => t('auth.back_to_home'));
</script>
