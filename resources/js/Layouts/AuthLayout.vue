<template>
  <div class="min-h-screen bg-gray-50 flex flex-col justify-center py-12 px-4">
    <FlashMessage />

    <!-- Logo -->
    <div class="text-center mb-8">
      <Link href="/" class="inline-block">
        <span class="text-gray-900 font-black text-3xl tracking-tight">
          inserate<span class="text-[#e91e8c]">®</span>
        </span>
        <p class="text-gray-500 text-sm mt-1">Schweizer Erotik-Inserate · 18+</p>
      </Link>
    </div>

    <!-- Card -->
    <div class="mx-auto w-full max-w-md bg-white border border-gray-200 rounded-2xl shadow-lg overflow-hidden">
      <!-- Header stripe -->
      <div class="bg-gray-50 border-b border-gray-200 px-6 py-4">
        <h1 class="text-gray-900 font-semibold text-lg">{{ title }}</h1>
        <p v-if="subtitle" class="text-gray-500 text-sm mt-0.5">{{ subtitle }}</p>
      </div>
      <div class="px-6 py-6">
        <slot />
      </div>
    </div>

    <!-- Back link -->
    <div class="text-center mt-6">
      <Link href="/" class="text-gray-500 text-sm hover:text-gray-900 transition">← Zurück zur Startseite</Link>
    </div>
  </div>
</template>

<script setup>
import { Link } from '@inertiajs/vue3';
import FlashMessage from '@/Components/FlashMessage.vue';

defineProps({
  title:    { type: String, required: true },
  subtitle: String,
});
</script>
