<template>
  <AppLayout>
    <Head title="Startseite" />

    <!-- Hero + Filters -->
    <div class="bg-[#242323] border-b border-white/5 pt-5 pb-4">
      <div class="max-w-5xl mx-auto px-6">
        <h1 class="text-2xl sm:text-3xl md:text-4xl font-black text-[#e35d8f] mb-4">
          Sex und Erotik Inserate in der Schweiz
        </h1>

        <!-- Featured Profiles Carousel -->
        <div v-if="profiles.data.length" class="relative mb-4">
          <div class="flex gap-3 overflow-x-auto pt-1 pb-2 scrollbar-hide">
            <a v-for="profile in profiles.data.slice(0,12)" :key="profile.id"
              :href="route('profile.show', profile.slug)"
              class="flex flex-col items-center shrink-0 w-16 sm:w-20 cursor-pointer group">
              <div class="w-14 h-14 sm:w-[72px] sm:h-[72px] rounded-full overflow-hidden ring-2 ring-[#e35d8f] ring-offset-2 ring-offset-[#111] group-hover:ring-[#f08ab0] transition">
                <img v-if="profile.public_media?.[0]"
                  class="lazyload w-full h-full object-cover"
                  :data-src="profile.public_media[0].url"
                  src="data:image/gif;base64,R0lGODlhAQABAAAAACH5BAEKAAEALAAAAAABAAEAAAICTAEAOw=="
                  :alt="profile.display_name"
                  width="72" height="72" />
                <div v-else class="w-full h-full bg-white/5 flex items-center justify-center text-xl">👤</div>
              </div>
              <span class="text-xs text-gray-400 mt-1 text-center truncate w-full group-hover:text-[#e35d8f] transition">{{ profile.display_name }}</span>
            </a>
          </div>
        </div>

        <!-- Filter Bar -->
        <div class="grid grid-cols-2 sm:flex sm:flex-wrap gap-2 sm:gap-3 mb-3">
          <div class="relative">
            <select v-model="filters.city"
              class="w-full bg-[#1a1a1a] border border-white/10 text-gray-200 text-sm rounded px-3 py-2.5 pr-8 appearance-none cursor-pointer hover:border-[#e35d8f] transition sm:min-w-[130px] focus:outline-none focus:border-[#e35d8f]">
              <option value="">Region</option>
              <option v-for="c in cities" :key="c.id" :value="c.slug">{{ c.name }}</option>
            </select>
            <svg class="pointer-events-none absolute right-2 top-3 w-4 h-4 text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
          </div>
          <div class="relative">
            <select v-model="filters.category"
              class="w-full bg-[#1a1a1a] border border-white/10 text-gray-200 text-sm rounded px-3 py-2.5 pr-8 appearance-none cursor-pointer hover:border-[#e35d8f] transition sm:min-w-[130px] focus:outline-none focus:border-[#e35d8f]">
              <option value="">Rubrik</option>
              <option v-for="c in categories" :key="c.id" :value="c.slug">{{ c.name }}</option>
            </select>
            <svg class="pointer-events-none absolute right-2 top-3 w-4 h-4 text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
          </div>
          <div class="relative">
            <select v-model="filters.service"
              class="w-full bg-[#1a1a1a] border border-white/10 text-gray-200 text-sm rounded px-3 py-2.5 pr-8 appearance-none cursor-pointer hover:border-[#e35d8f] transition sm:min-w-[130px] focus:outline-none focus:border-[#e35d8f]">
              <option value="">Service</option>
              <option v-for="s in services" :key="s.id" :value="s.slug">{{ s.name }}</option>
            </select>
            <svg class="pointer-events-none absolute right-2 top-3 w-4 h-4 text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
          </div>
          <button @click="applyFilters" class="bg-[#e35d8f] hover:bg-[#c44a7a] text-white text-sm font-bold px-4 py-2.5 rounded transition sm:hidden">
            Suchen
          </button>
          <div class="relative col-span-2 sm:col-span-1 sm:flex-1">
            <input v-model="filters.search" type="text" placeholder="Suchen..."
              @keyup.enter="applyFilters"
              class="w-full bg-[#1a1a1a] border border-white/10 text-gray-200 text-sm rounded px-4 py-2.5 pr-10 focus:outline-none focus:border-[#e35d8f] placeholder-gray-600" />
            <svg class="absolute right-3 top-3 w-4 h-4 text-gray-600" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
          </div>
          <button @click="applyFilters" class="hidden sm:block bg-[#e35d8f] hover:bg-[#c44a7a] text-white text-sm font-bold px-5 py-2.5 rounded transition">
            Suchen
          </button>
        </div>

        <div class="flex items-center justify-between text-xs text-gray-500 mb-2">
          <div class="flex flex-wrap gap-2">
            <span v-if="activeCity" class="text-[#e35d8f]">📍 {{ activeCity.name }}</span>
            <span v-if="activeCategory" class="text-[#e35d8f]">🏷 {{ activeCategory.name }}</span>
            <span v-if="activeService" class="text-[#e35d8f]">✨ {{ activeService.name }}</span>
            <Link v-if="activeCity || activeCategory || activeService" :href="route('home')" class="text-gray-600 hover:text-gray-300 transition">✕ Zurücksetzen</Link>
          </div>
          <span class="shrink-0 ml-2 text-gray-500">{{ profiles.total }} Inserate</span>
        </div>
      </div>
    </div>

    <!-- Listings -->
    <div class="max-w-5xl mx-auto px-6 pb-12 pt-4 sm:pt-6">
      <div v-if="profiles.data.length === 0" class="text-center py-20 text-gray-600">
        <div class="text-5xl mb-4">🔍</div>
        <p class="text-lg">Keine Inserate gefunden.</p>
      </div>

      <div v-else class="space-y-3">
        <!-- TOP AD -->
        <a v-if="profiles.data[0]" :href="route('profile.show', profiles.data[0].slug)"
          class="block bg-[#1a1a1a] border border-white/8 rounded-xl overflow-hidden hover:border-[#e35d8f]/60 transition group shadow-xl">
          <div class="flex">
            <div class="relative w-[101px] sm:w-[158px] md:w-[230px] shrink-0 h-[130px] sm:h-[158px] md:h-[187px]">
              <img v-if="profiles.data[0].public_media?.[0]"
                :src="profiles.data[0].public_media[0].url"
                :alt="profiles.data[0].display_name"
                class="w-full h-full object-cover object-top"
                loading="eager"
                fetchpriority="high"
                width="256" height="208" />
              <div v-else class="w-full h-full bg-white/5 flex items-center justify-center text-5xl">👤</div>
              <span class="absolute bottom-2 left-2 bg-[#e35d8f] text-white text-xs font-bold px-2 py-0.5 rounded">TOP AD</span>
            </div>
            <div class="p-3 sm:p-5 flex-1 min-w-0">
              <h2 class="text-base sm:text-xl font-bold text-white group-hover:text-[#e35d8f] transition mb-1 sm:mb-2 truncate">
                {{ profiles.data[0].display_name }}
              </h2>
              <p class="text-gray-400 text-xs sm:text-sm leading-relaxed line-clamp-2 sm:line-clamp-3 mb-2 sm:mb-4">{{ profiles.data[0].description }}</p>
              <div class="flex flex-wrap gap-2 text-xs">
                <span v-if="profiles.data[0].category" class="text-[#e35d8f]">🏷 {{ profiles.data[0].category?.name }}</span>
                <span v-if="profiles.data[0].city" class="text-gray-500">📍 {{ profiles.data[0].city?.name }}</span>
                <span class="hidden sm:inline text-gray-600">📅 {{ formatDate(profiles.data[0].created_at) }}</span>
                <span class="sm:ml-auto text-[#e35d8f] font-bold text-xs sm:text-sm">CHF {{ profiles.data[0].subscription_price_chf }}/Mo</span>
              </div>
            </div>
          </div>
        </a>

        <!-- Grid -->
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
          <a v-for="profile in profiles.data.slice(1)" :key="profile.id"
            :href="route('profile.show', profile.slug)"
            class="flex bg-[#1a1a1a] border border-white/8 rounded-xl overflow-hidden hover:border-[#e35d8f]/60 transition group shadow-lg">
            <div class="w-[226px] h-[302px] shrink-0">
              <img v-if="profile.public_media?.[0]"
                class="lazyload w-full h-full object-cover object-top"
                :data-src="profile.public_media[0].url"
                src="data:image/gif;base64,R0lGODlhAQABAAAAACH5BAEKAAEALAAAAAABAAEAAAICTAEAOw=="
                :alt="profile.display_name"
                width="226" height="302" />
              <div v-else class="w-full h-full bg-white/5 flex items-center justify-center text-3xl">👤</div>
            </div>
            <div class="p-3 flex-1 min-w-0">
              <div v-if="profile.listing_orders?.[0]?.amount_chf > 0" class="flex items-center gap-2 mb-1">
                <span class="text-[#e35d8f] text-xs font-bold uppercase tracking-wide">Premium</span>
              </div>
              <h3 class="text-white text-sm font-bold group-hover:text-[#e35d8f] transition truncate mb-1">
                {{ profile.display_name }}
              </h3>
              <p class="text-gray-500 text-xs line-clamp-2 leading-relaxed">{{ profile.description }}</p>
              <div class="flex items-center gap-2 mt-1.5 text-xs text-gray-600 flex-wrap">
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
          :class="link.active
            ? 'bg-[#e35d8f] text-white border-[#e35d8f]'
            : 'bg-[#1a1a1a] text-gray-400 border-white/10 hover:border-[#e35d8f]/50 hover:text-[#e35d8f]'" />
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
  activeSearch:   String,
});

const filters = ref({
  city:     props.activeCity?.slug ?? '',
  category: props.activeCategory?.slug ?? '',
  service:  props.activeService?.slug ?? '',
  search:   props.activeSearch ?? '',
});

function formatDate(iso) {
  if (!iso) return '';
  const d = new Date(iso);
  return d.toLocaleDateString('de-CH', { day: '2-digit', month: '2-digit', year: 'numeric' });
}

function applyFilters() {
  const query = filters.value.search ? { search: filters.value.search } : {};

  if (filters.value.service) {
    router.get(route('service', filters.value.service), query);
  } else if (filters.value.city) {
    router.get(route('city', filters.value.city), query);
  } else if (filters.value.category) {
    router.get(route('category', filters.value.category), query);
  } else {
    router.get(route('home'), query);
  }
}
</script>

<style scoped>
.scrollbar-hide::-webkit-scrollbar { display: none; }
.scrollbar-hide { -ms-overflow-style: none; scrollbar-width: none; }
</style>
