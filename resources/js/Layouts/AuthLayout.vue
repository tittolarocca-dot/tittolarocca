<template>
  <div class="min-h-screen bg-gradient-to-br from-pink-600 to-pink-800 flex flex-col justify-center py-12 px-4">
    <FlashMessage />

    <!-- Logo -->
    <div class="text-center mb-8">
      <Link href="/" class="inline-block">
        <span class="text-white font-bold text-3xl tracking-tight">
          Inserate<span class="text-pink-200">Plattform</span>
        </span>
        <p class="text-pink-200 text-sm mt-1">Schweizer Inserate-Plattform · 18+</p>
      </Link>
    </div>

    <!-- Card -->
    <div class="mx-auto w-full max-w-md bg-white rounded-2xl shadow-2xl overflow-hidden">
      <!-- Pink header stripe -->
      <div class="bg-pink-600 px-6 py-4">
        <h1 class="text-white font-semibold text-lg">{{ title }}</h1>
        <p v-if="subtitle" class="text-pink-200 text-sm mt-0.5">{{ subtitle }}</p>
      </div>
      <div class="px-6 py-6">
        <slot />
      </div>
    </div>

    <!-- Back link -->
    <div class="text-center mt-6">
      <Link href="/" class="text-pink-200 text-sm hover:text-white transition">← Zurück zur Startseite</Link>
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
