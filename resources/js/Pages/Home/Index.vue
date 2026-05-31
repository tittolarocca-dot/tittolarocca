<template>
  <AppLayout>
    <Head title="Startseite" />

    <!-- Hero + Filters -->
    <div class="bg-[#111] border-b border-white/5 pt-5 pb-4 px-4">
      <div class="max-w-7xl mx-auto">
        <h1 class="text-2xl sm:text-3xl md:text-4xl font-black text-[#e35d8f] mb-4">
          Anschauen oder treffen – du entscheidest - Schweizweit
        </h1>

        <!-- Featured Profiles Carousel -->
        <div v-if="profiles.data.length" class="relative mb-4">
          <div class="flex gap-3 overflow-x-auto pt-1 pb-2 scrollbar-hide">
            <!-- Neue Bilder -->
            <a :href="route('neue-bilder')"
              class="flex flex-col items-center shrink-0 w-16 sm:w-20 cursor-pointer group">
              <div class="w-14 h-14 sm:w-[72px] sm:h-[72px] rounded-full ring-2 ring-[#e35d8f] ring-offset-2 ring-offset-[#111] group-hover:ring-[#f08ab0] transition bg-gradient-to-br from-[#e35d8f] via-[#c44a7a] to-[#7c3aed] flex items-center justify-center shadow-lg shadow-[#e35d8f]/40">
                <svg class="w-6 h-6 sm:w-7 sm:h-7 text-white drop-shadow" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
              </div>
              <span class="text-xs text-[#e35d8f] font-bold mt-1 text-center w-full">Neue Bilder</span>
            </a>

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
            <select v-model="filters.age"
              class="w-full bg-[#1a1a1a] border border-white/10 text-gray-200 text-sm rounded px-3 py-2.5 pr-8 appearance-none cursor-pointer hover:border-[#e35d8f] transition sm:min-w-[130px] focus:outline-none focus:border-[#e35d8f]">
              <option value="">Alter</option>
              <option value="18-22">Alter 18-22</option>
              <option value="23-29">Alter 23-29</option>
              <option value="30-39">Alter 30-39</option>
              <option value="40-49">Alter 40-49</option>
              <option value="50-59">Alter 50-59</option>
              <option value="60+">Alter 60+</option>
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
            <span v-if="activeAge" class="text-[#e35d8f]">🎂 Alter {{ activeAge }}</span>
            <Link v-if="activeCity || activeCategory || activeService || activeAge" :href="route('home')" class="text-gray-600 hover:text-gray-300 transition">✕ Zurücksetzen</Link>
          </div>
          <span class="shrink-0 ml-2 text-gray-500">{{ profiles.total }} Inserate</span>
        </div>
      </div>
    </div>

    <!-- Listings -->
    <div class="max-w-7xl mx-auto px-3 sm:px-4 pb-12 pt-4 sm:pt-6">
      <div v-if="profiles.data.length === 0" class="text-center py-20 text-gray-600">
        <div class="text-5xl mb-4">🔍</div>
        <p class="text-lg">Keine Inserate gefunden.</p>
      </div>

      <div v-else class="space-y-4">

        <!-- ── TOP AD ──────────────────────────────────────────────────────── -->
        <a v-if="profiles.data[0]" :href="route('profile.show', profiles.data[0].slug)"
          class="block rounded-2xl overflow-hidden bg-[#1a1a1a] border border-white/8 hover:border-[#e35d8f]/50 hover:shadow-xl hover:shadow-[#e35d8f]/10 transition-all duration-300 group">
          <div class="flex h-[200px] sm:h-[260px] md:h-[300px]">

            <!-- Photo -->
            <div class="relative w-[160px] sm:w-[220px] md:w-[300px] shrink-0 overflow-hidden">
              <img v-if="profiles.data[0].public_media?.[0]"
                :src="profiles.data[0].public_media[0].url"
                :alt="profiles.data[0].display_name"
                class="w-full h-full object-cover object-center group-hover:scale-105 transition duration-500"
                loading="eager" fetchpriority="high" />
              <div v-else class="w-full h-full bg-white/5 flex items-center justify-center text-5xl">👤</div>
              <span class="absolute top-2 left-2 bg-[#e35d8f] text-white text-[10px] font-black px-2 py-0.5 rounded-full tracking-wider">TOP AD</span>
            </div>

            <!-- Info -->
            <div class="p-4 sm:p-6 flex flex-col justify-between flex-1 min-w-0">
              <div>
                <!-- Name + Verified -->
                <div class="flex items-center gap-2 flex-wrap mb-1.5">
                  <h2 class="text-white font-black text-lg sm:text-2xl group-hover:text-[#e35d8f] transition truncate">
                    {{ profiles.data[0].display_name }}
                  </h2>
                  <span v-if="profiles.data[0].verification_status === 'approved'"
                    class="inline-flex items-center justify-center w-5 h-5 rounded-full bg-green-500 shrink-0"
                    title="Verifiziert">
                    <svg class="w-3 h-3 text-white" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>
                  </span>
                </div>

                <!-- Meta -->
                <div class="flex flex-wrap items-center gap-x-3 gap-y-1 text-xs text-gray-400 mb-3">
                  <span v-if="profiles.data[0].age" class="text-white/70">{{ profiles.data[0].age }} J.</span>
                  <span v-if="profiles.data[0].city">📍 {{ profiles.data[0].city?.name }}</span>
                  <span v-if="profiles.data[0].category" class="text-[#e35d8f] font-medium">{{ profiles.data[0].category?.name }}</span>
                </div>

                <!-- Teaser -->
                <p v-if="profiles.data[0].description" class="text-gray-300 text-sm line-clamp-2 leading-relaxed">
                  {{ profiles.data[0].description }}
                </p>
              </div>

              <!-- Badges -->
              <div class="flex flex-wrap gap-1.5 mt-3">
                <span v-if="isNew(profiles.data[0].created_at)"
                  class="bg-blue-500/15 text-blue-300 text-[10px] font-semibold px-2.5 py-1 rounded-full">✦ Neu</span>
                <span v-if="profiles.data[0].private_media_count > 0"
                  class="bg-white/5 text-gray-300 text-[10px] font-semibold px-2.5 py-1 rounded-full">🔒 Private Galerie</span>
              </div>
            </div>
          </div>
        </a>

        <!-- ── GRID: 2 Spalten Desktop, 1 Spalte Mobile ───────────────────── -->
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
          <a v-for="profile in profiles.data.slice(1)" :key="profile.id"
            :href="route('profile.show', profile.slug)"
            class="flex rounded-2xl overflow-hidden bg-[#1a1a1a] border border-white/8 hover:border-[#e35d8f]/50 hover:shadow-xl hover:shadow-[#e35d8f]/10 transition-all duration-300 group">

            <!-- Photo -->
            <div class="relative w-[50%] shrink-0 overflow-hidden">
              <img v-if="profile.public_media?.[0]"
                class="lazyload w-full h-full object-cover object-center group-hover:scale-105 transition duration-500"
                :data-src="profile.public_media[0].url"
                src="data:image/gif;base64,R0lGODlhAQABAAAAACH5BAEKAAEALAAAAAABAAEAAAICTAEAOw=="
                :alt="profile.display_name" />
              <div v-else class="w-full h-full bg-white/5 flex items-center justify-center text-3xl">👤</div>

              <!-- Badges top-left -->
              <div class="absolute top-2 left-2 flex flex-col gap-1">
                <span v-if="profile.listing_orders?.[0]?.amount_chf > 0"
                  class="bg-[#e35d8f] text-white text-[10px] font-black px-2 py-0.5 rounded-full tracking-wider">TOP AD</span>
                <span v-if="profile.verification_status === 'approved'"
                  class="flex items-center gap-1 bg-green-500 text-white text-[10px] font-bold px-2 py-0.5 rounded-full">
                  <svg class="w-2.5 h-2.5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>
                  Verifiziert
                </span>
                <span v-if="isNew(profile.created_at)"
                  class="bg-blue-500 text-white text-[10px] font-bold px-2 py-0.5 rounded-full">✦ Neu</span>
              </div>
            </div>

            <!-- Text rechts -->
            <div class="p-3 flex-1 min-w-0 flex flex-col justify-between">
              <div>
                <!-- Name + Alter -->
                <div class="flex items-center gap-2 mb-1 flex-wrap">
                  <h3 class="text-white font-bold text-sm sm:text-base group-hover:text-[#e35d8f] transition truncate">
                    {{ profile.display_name }}
                  </h3>
                  <span v-if="profile.verification_status === 'approved'"
                    class="inline-flex items-center justify-center w-4 h-4 rounded-full bg-green-500 shrink-0"
                    title="Verifiziert">
                    <svg class="w-2.5 h-2.5 text-white" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>
                  </span>
                  <span v-if="profile.age" class="shrink-0 text-gray-400 text-xs">{{ profile.age }} J.</span>
                </div>

                <!-- Stadt + Kategorie -->
                <div class="flex items-center gap-2 text-xs mb-2 flex-wrap">
                  <span v-if="profile.city" class="text-gray-400">📍 {{ profile.city?.name }}</span>
                  <span v-if="profile.category" class="text-[#e35d8f] font-medium">{{ profile.category?.name }}</span>
                </div>

                <!-- Teaser -->
                <p v-if="profile.description" class="text-gray-300 text-xs line-clamp-2 leading-relaxed">
                  {{ profile.description }}
                </p>
              </div>

              <!-- Badges unten -->
              <div class="flex flex-wrap gap-1 mt-2">
                <span v-if="profile.private_media_count > 0"
                  class="bg-white/5 text-gray-400 text-[10px] px-2 py-0.5 rounded-full">🔒 Privat</span>
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
  activeAge:      String,
});

const filters = ref({
  city:     props.activeCity?.slug ?? '',
  category: props.activeCategory?.slug ?? '',
  service:  props.activeService?.slug ?? '',
  age:      props.activeAge ?? '',
  search:   props.activeSearch ?? '',
});

function isNew(iso) {
  if (!iso) return false;
  return (Date.now() - new Date(iso).getTime()) < 14 * 24 * 60 * 60 * 1000;
}

function applyFilters() {
  const query = {};
  if (filters.value.search) query.search = filters.value.search;
  if (filters.value.age)    query.age    = filters.value.age;

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
