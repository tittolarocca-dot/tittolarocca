<template>
  <AppLayout>
    <Head title="Startseite" />

    <!-- Hero -->
    <div class="bg-white border-b border-gray-200 pt-5 pb-4 px-4">
      <div class="max-w-7xl mx-auto">
        <h1 class="text-2xl sm:text-3xl md:text-4xl font-black text-gray-900 mb-4">
          Sex und Erotik Inserate in der Schweiz
        </h1>

        <!-- Featured Profiles Carousel -->
        <div v-if="profiles.data.length" class="relative mb-4">
          <div class="flex gap-3 overflow-x-auto pb-2 scrollbar-hide">
            <a v-for="profile in profiles.data.slice(0,12)" :key="profile.id"
              :href="route('profile.show', profile.slug)"
              class="flex flex-col items-center shrink-0 w-16 sm:w-20 cursor-pointer group">
              <div class="w-14 h-14 sm:w-[72px] sm:h-[72px] rounded-full overflow-hidden ring-2 ring-[#e91e8c] ring-offset-2 ring-offset-white group-hover:ring-[#c91478] transition">
                <img v-if="profile.public_media?.[0]"
                  class="lazyload w-full h-full object-cover"
                  :data-src="profile.public_media[0].url"
                  src="data:image/gif;base64,R0lGODlhAQABAAAAACH5BAEKAAEALAAAAAABAAEAAAICTAEAOw=="
                  :alt="profile.display_name"
                  width="72" height="72" />
                <div v-else class="w-full h-full bg-gray-100 flex items-center justify-center text-xl">👤</div>
              </div>
              <span class="text-xs text-gray-600 mt-1 text-center truncate w-full">{{ profile.display_name }}</span>
            </a>
          </div>
        </div>

        <!-- Filter Bar — 2×2 grid on mobile, flex row on desktop -->
        <div class="grid grid-cols-2 sm:flex sm:flex-wrap gap-2 sm:gap-3 mb-3">
          <div class="relative">
            <select v-model="filters.city"
              class="w-full bg-white border border-gray-300 text-gray-700 text-sm rounded px-3 py-2.5 pr-8 appearance-none cursor-pointer hover:border-[#e91e8c] transition sm:min-w-[130px]">
              <option value="">Region</option>
              <option v-for="c in cities" :key="c.id" :value="c.slug">{{ c.name }}</option>
            </select>
            <svg class="pointer-events-none absolute right-2 top-3 w-4 h-4 text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
          </div>
          <div class="relative">
            <select v-model="filters.category"
              class="w-full bg-white border border-gray-300 text-gray-700 text-sm rounded px-3 py-2.5 pr-8 appearance-none cursor-pointer hover:border-[#e91e8c] transition sm:min-w-[130px]">
              <option value="">Rubrik</option>
              <option v-for="c in categories" :key="c.id" :value="c.slug">{{ c.name }}</option>
            </select>
            <svg class="pointer-events-none absolute right-2 top-3 w-4 h-4 text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
          </div>
          <div class="relative">
            <select v-model="filters.service"
              class="w-full bg-white border border-gray-300 text-gray-700 text-sm rounded px-3 py-2.5 pr-8 appearance-none cursor-pointer hover:border-[#e91e8c] transition sm:min-w-[130px]">
              <option value="">Service</option>
              <option v-for="s in services" :key="s.id" :value="s.slug">{{ s.name }}</option>
            </select>
            <svg class="pointer-events-none absolute right-2 top-3 w-4 h-4 text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
          </div>
          <button @click="applyFilters" class="bg-[#e91e8c] hover:bg-[#c91478] text-white text-sm font-bold px-4 py-2.5 rounded transition sm:hidden">
            Suchen
          </button>
          <div class="relative col-span-2 sm:col-span-1 sm:flex-1">
            <input v-model="filters.search" type="text" placeholder="Suchen..."
              @keyup.enter="applyFilters"
              class="w-full bg-white border border-gray-300 text-gray-700 text-sm rounded px-4 py-2.5 pr-10 focus:outline-none focus:border-[#e91e8c] placeholder-gray-400" />
            <svg class="absolute right-3 top-3 w-4 h-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
          </div>
          <button @click="applyFilters" class="hidden sm:block bg-[#e91e8c] hover:bg-[#c91478] text-white text-sm font-bold px-5 py-2.5 rounded transition">
            Suchen
          </button>
        </div>

        <div class="flex items-center justify-between text-xs text-gray-500 mb-2">
          <div class="flex flex-wrap gap-2">
            <span v-if="activeCity" class="text-[#e91e8c]">📍 {{ activeCity.name }}</span>
            <span v-if="activeCategory" class="text-[#e91e8c]">🏷 {{ activeCategory.name }}</span>
            <span v-if="activeService" class="text-[#e91e8c]">✨ {{ activeService.name }}</span>
            <Link v-if="activeCity || activeCategory || activeService" :href="route('home')" class="text-gray-400 hover:text-gray-900 transition">✕ Zurücksetzen</Link>
          </div>
          <span class="shrink-0 ml-2">{{ profiles.total }} Inserate</span>
        </div>
      </div>
    </div>

    <!-- Listings -->
    <div class="max-w-7xl mx-auto px-3 sm:px-4 pb-12 pt-4 sm:pt-6">
      <div v-if="profiles.data.length === 0" class="text-center py-20 text-gray-400">
        <div class="text-5xl mb-4">🔍</div>
        <p class="text-lg">Keine Inserate gefunden.</p>
      </div>

      <div v-else class="space-y-3">
        <!-- TOP AD -->
        <a v-if="profiles.data[0]" :href="route('profile.show', profiles.data[0].slug)"
          class="block bg-white border border-gray-200 rounded-lg overflow-hidden hover:border-[#e91e8c] transition group shadow-sm">
          <div class="flex">
            <div class="relative w-28 sm:w-44 md:w-64 shrink-0 h-36 sm:h-44 md:h-52">
              <!-- Top ad: above fold → eager + high priority -->
              <img v-if="profiles.data[0].public_media?.[0]"
                :src="profiles.data[0].public_media[0].url"
                :alt="profiles.data[0].display_name"
                class="w-full h-full object-cover"
                loading="eager"
                fetchpriority="high"
                width="256" height="208" />
              <div v-else class="w-full h-full bg-gray-100 flex items-center justify-center text-5xl">👤</div>
              <span class="absolute bottom-2 left-2 bg-[#e91e8c] text-white text-xs font-bold px-2 py-0.5 rounded">TOP AD</span>
            </div>
            <div class="p-3 sm:p-5 flex-1 min-w-0">
              <h2 class="text-base sm:text-xl font-bold text-gray-900 group-hover:text-[#e91e8c] transition mb-1 sm:mb-2 truncate">
                {{ profiles.data[0].display_name }}
              </h2>
              <p class="text-gray-500 text-xs sm:text-sm leading-relaxed line-clamp-2 sm:line-clamp-3 mb-2 sm:mb-4">{{ profiles.data[0].description }}</p>
              <div class="flex flex-wrap gap-2 text-xs">
                <span v-if="profiles.data[0].category" class="text-[#e91e8c]">🏷 {{ profiles.data[0].category?.name }}</span>
                <span v-if="profiles.data[0].city" class="text-gray-500">📍 {{ profiles.data[0].city?.name }}</span>
                <span class="hidden sm:inline text-gray-400">📅 {{ formatDate(profiles.data[0].created_at) }}</span>
                <span class="sm:ml-auto text-[#e91e8c] font-bold text-xs sm:text-sm">CHF {{ profiles.data[0].subscription_price_chf }}/Mo</span>
              </div>
            </div>
          </div>
        </a>

        <!-- PREMIUM Grid -->
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
          <a v-for="profile in profiles.data.slice(1)" :key="profile.id"
            :href="route('profile.show', profile.slug)"
            class="flex bg-white border border-gray-200 rounded-lg overflow-hidden hover:border-[#e91e8c] transition group shadow-sm"
            style="min-height: 110px;">
            <div class="w-24 sm:w-32 shrink-0">
              <img v-if="profile.public_media?.[0]"
                class="lazyload w-full h-full object-cover"
                :data-src="profile.public_media[0].url"
                src="data:image/gif;base64,R0lGODlhAQABAAAAACH5BAEKAAEALAAAAAABAAEAAAICTAEAOw=="
                :alt="profile.display_name"
                width="128" height="110" />
              <div v-else class="w-full h-full bg-gray-100 flex items-center justify-center text-3xl">👤</div>
            </div>
            <div class="p-3 flex-1 min-w-0">
              <div class="flex items-center gap-2 mb-1">
                <span class="text-[#e91e8c] text-xs font-bold uppercase tracking-wide">Premium</span>
              </div>
              <h3 class="text-gray-900 text-sm font-bold group-hover:text-[#e91e8c] transition truncate mb-1">
                {{ profile.display_name }}
              </h3>
              <p class="text-gray-500 text-xs line-clamp-2 leading-relaxed">{{ profile.description }}</p>
              <div class="flex items-center gap-2 mt-1.5 text-xs text-gray-400 flex-wrap">
                <span v-if="profile.category">🏷 {{ profile.category?.name }}</span>
                <span v-if="profile.city">📍 {{ profile.city?.name }}</span>
                <span class="hidden sm:inline ml-auto">📅 {{ formatDate(profile.created_at) }}</span>
              </div>
            </div>
          </a>
        </div>
      </div>

      <!-- Pagination -->
      <div v-if="profiles.last_page > 1" class="flex justify-center flex-wrap gap-2 mt-8">
        <Link v-for="link in profiles.links" :key="link.label"
          :href="link.url || '#'"
          v-html="link.label"
          class="px-3 py-1.5 text-sm rounded border transition-colors"
          :class="link.active ? 'bg-[#e91e8c] text-white border-[#e91e8c]' : 'bg-white text-gray-600 border-gray-200 hover:border-[#e91e8c]'" />
      </div>
    </div>
  </AppLayout>
</template>

<script setup>
import { ref } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';

const props = defineProps({
  profiles:       Object,
  cities:         Array,
  categories:     Array,
  services:       Array,
  activeCity:     Object,
  activeCategory: Object,
  activeService:  Object,
});

const filters = ref({
  city:     props.activeCity?.slug ?? '',
  category: props.activeCategory?.slug ?? '',
  service:  props.activeService?.slug ?? '',
  search:   '',
});

function formatDate(iso) {
  if (!iso) return '';
  const d = new Date(iso);
  return d.toLocaleDateString('de-CH', { day: '2-digit', month: '2-digit', year: 'numeric' });
}

function applyFilters() {
  if (filters.value.service) {
    router.get(route('service', filters.value.service));
  } else if (filters.value.city) {
    router.get(route('city', filters.value.city));
  } else if (filters.value.category) {
    router.get(route('category', filters.value.category));
  } else {
    router.get(route('home'));
  }
}
</script>

<style scoped>
.scrollbar-hide::-webkit-scrollbar { display: none; }
.scrollbar-hide { -ms-overflow-style: none; scrollbar-width: none; }
</style>
