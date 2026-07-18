<template>
  <AppLayout>
    <Head :title="t('dashboard.favorites_title')" />
    <div class="max-w-5xl mx-auto px-4 py-8">

      <!-- Header -->
      <div class="flex items-center gap-3 mb-6">
        <div class="w-11 h-11 rounded-full bg-[#e35d8f]/15 flex items-center justify-center">
          <svg class="w-5 h-5 text-[#e35d8f]" fill="currentColor" viewBox="0 0 24 24">
            <path d="M4.318 6.318a4.5 4.5 0 016.364 0L12 7.636l1.318-1.318a4.5 4.5 0 116.364 6.364L12 20.364l-7.682-7.682a4.5 4.5 0 010-6.364z"/>
          </svg>
        </div>
        <div>
          <h1 class="text-xl font-bold text-white">{{ t('dashboard.favorites_title') }}</h1>
          <p class="text-xs text-gray-500">{{ t('dashboard.favorites_saved', { count: favorites.length }) }}</p>
        </div>
      </div>

      <!-- Empty state -->
      <div v-if="favorites.length === 0" class="text-center py-20">
        <svg class="w-14 h-14 text-gray-600 mx-auto mb-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
          <path stroke-linecap="round" stroke-linejoin="round" d="M4.318 6.318a4.5 4.5 0 016.364 0L12 7.636l1.318-1.318a4.5 4.5 0 116.364 6.364L12 20.364l-7.682-7.682a4.5 4.5 0 010-6.364z"/>
        </svg>
        <p class="text-gray-500 text-sm mb-3">{{ t('dashboard.favorites_empty') }}</p>
        <Link :href="route('home')" class="text-[#e35d8f] text-sm hover:underline">
          {{ t('dashboard.favorites_empty_hint') }}
        </Link>
      </div>

      <!-- Grid -->
      <div v-else class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-3">
        <Link
          v-for="item in favorites"
          :key="item.slug"
          :href="route('profile.show', item.slug)"
          class="group block bg-[#1a1a1a] rounded-2xl border border-white/8 overflow-hidden hover:border-[#e35d8f]/40 transition"
        >
          <!-- Cover image -->
          <div class="aspect-[3/4] relative overflow-hidden bg-[#111]">
            <img
              v-if="item.cover_url"
              :src="item.cover_url"
              :alt="item.display_name"
              class="w-full h-full object-cover group-hover:scale-105 transition duration-300"
              loading="lazy"
            />
            <div v-else class="w-full h-full flex items-center justify-center">
              <svg class="w-10 h-10 text-gray-600" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z" clip-rule="evenodd"/>
              </svg>
            </div>
          </div>

          <!-- Name -->
          <div class="px-3 py-2.5 flex items-center gap-1.5">
            <span class="text-sm font-semibold text-white truncate group-hover:text-[#e35d8f] transition">
              {{ item.display_name }}
            </span>
            <span v-if="item.identity_verification_status === 'approved' || item.verification_status === 'approved'"
              class="inline-flex items-center justify-center w-4 h-4 rounded-full shrink-0"
              :class="item.identity_verification_status === 'approved' ? 'bg-green-500' : 'bg-sky-500'">
              <svg class="w-2.5 h-2.5 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3">
                <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
              </svg>
            </span>
          </div>
        </Link>
      </div>

    </div>
  </AppLayout>
</template>

<script setup>
import { Head, Link } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import { useI18n } from '@/composables/useI18n';

const { t } = useI18n();

defineProps({
  favorites: { type: Array, default: () => [] },
});
</script>
