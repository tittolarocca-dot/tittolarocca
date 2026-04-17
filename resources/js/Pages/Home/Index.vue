<template>
  <AppLayout>
    <Head title="Startseite" />

    <!-- Kategorie-Leiste -->
    <div class="bg-white border-b border-gray-200 sticky top-12 z-40">
      <div class="max-w-7xl mx-auto px-4 overflow-x-auto">
        <div class="flex gap-1 py-2">
          <Link :href="route('home')"
            class="flex flex-col items-center gap-1 px-3 py-1.5 rounded cursor-pointer min-w-[56px] transition-colors"
            :class="!activeCategory ? 'text-pink-600' : 'text-gray-500 hover:text-pink-500'">
            <div class="w-10 h-10 rounded-full border-2 flex items-center justify-center text-lg"
              :class="!activeCategory ? 'border-pink-500' : 'border-gray-300'">🏠</div>
            <span class="text-[10px]">alle</span>
          </Link>
          <Link v-for="cat in categories" :key="cat.id"
            :href="route('category', cat.slug)"
            class="flex flex-col items-center gap-1 px-3 py-1.5 rounded cursor-pointer min-w-[56px] transition-colors"
            :class="activeCategory?.id === cat.id ? 'text-pink-600' : 'text-gray-500 hover:text-pink-500'">
            <div class="w-10 h-10 rounded-full border-2 flex items-center justify-center text-xs font-bold"
              :class="activeCategory?.id === cat.id ? 'border-pink-500 bg-pink-50' : 'border-gray-300'">
              {{ cat.name.charAt(0) }}
            </div>
            <span class="text-[10px] text-center leading-tight">{{ cat.name }}</span>
          </Link>
        </div>
      </div>
    </div>

    <!-- Filter-Info -->
    <div v-if="activeCity || activeCategory" class="bg-pink-50 border-b border-pink-100">
      <div class="max-w-7xl mx-auto px-4 py-2 flex items-center gap-2 text-sm text-pink-700">
        <span v-if="activeCity">📍 {{ activeCity.name }}</span>
        <span v-if="activeCategory">· {{ activeCategory.name }}</span>
        <Link :href="route('home')" class="ml-auto text-xs text-pink-500 underline">Filter zurücksetzen</Link>
      </div>
    </div>

    <!-- Profile Grid -->
    <div class="max-w-7xl mx-auto px-4 py-4">
      <div v-if="profiles.data.length === 0" class="text-center py-16 text-gray-400">
        <div class="text-4xl mb-3">🔍</div>
        <p>Keine Profile gefunden.</p>
      </div>

      <div v-else class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 gap-3">
        <a v-for="profile in profiles.data" :key="profile.id"
          :href="route('profile.show', profile.slug)"
          class="bg-white border border-gray-200 rounded overflow-hidden hover:shadow-md transition-shadow cursor-pointer group">
          <!-- Name + Ort -->
          <div class="px-2 pt-2 pb-1.5">
            <div class="font-bold text-gray-800 text-sm truncate">
              {{ profile.display_name }}
              <span class="text-pink-500 text-xs">✓</span>
            </div>
            <div class="text-xs text-gray-500 truncate">
              {{ profile.city?.name }}{{ profile.city ? ', ' : '' }}{{ profile.category?.name }} ›
            </div>
          </div>
          <!-- Foto -->
          <div class="relative h-48 bg-gray-100 overflow-hidden">
            <img v-if="profile.public_media?.[0]"
              :src="profile.public_media[0].thumbnail_path || profile.public_media[0].storage_path"
              :alt="profile.display_name"
              class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300" />
            <div v-else class="w-full h-full flex items-center justify-center text-gray-300 text-4xl">👤</div>
            <!-- Abo-Preis Badge -->
            <div class="absolute bottom-2 right-2 bg-pink-600 text-white text-xs font-bold px-2 py-0.5 rounded">
              CHF {{ profile.subscription_price_chf }}/Mo
            </div>
          </div>
        </a>
      </div>

      <!-- Pagination -->
      <div v-if="profiles.last_page > 1" class="flex justify-center gap-2 mt-8">
        <Link v-for="link in profiles.links" :key="link.label"
          :href="link.url || '#'"
          v-html="link.label"
          class="px-3 py-1.5 text-sm rounded border transition-colors"
          :class="link.active
            ? 'bg-pink-600 text-white border-pink-600'
            : 'bg-white text-gray-600 border-gray-200 hover:border-pink-400'" />
      </div>
    </div>
  </AppLayout>
</template>

<script setup>
import { Head, Link } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';

defineProps({
  profiles:       Object,
  cities:         Array,
  categories:     Array,
  activeCity:     Object,
  activeCategory: Object,
});
</script>
