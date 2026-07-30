<template>
  <AppLayout>
    <Head :title="meta.title">
      <meta name="description" :content="meta.description" />
    </Head>

    <!-- Hero -->
    <div class="bg-[#111] border-b border-white/5 pt-6 pb-5 px-4">
      <div class="max-w-7xl mx-auto">
        <h1 class="text-2xl sm:text-3xl md:text-4xl font-black text-[#e35d8f]">
          {{ activeCanton ? t('clubs.title_canton', { canton: activeCanton.name }) : t('clubs.title') }}
        </h1>
        <p class="text-sm text-gray-400 mt-1.5 max-w-2xl">{{ t('clubs.subtitle') }}</p>
      </div>
    </div>

    <!-- Filter -->
    <div class="bg-[#141414] border-b border-white/5 px-4 py-4">
      <div class="max-w-7xl mx-auto grid grid-cols-2 lg:grid-cols-5 gap-2.5">
        <div class="col-span-2 lg:col-span-1 relative">
          <input v-model="f.q" @keyup.enter="apply" type="text" :placeholder="t('clubs.search_ph')"
            class="w-full bg-[#1a1a1a] border border-white/10 text-gray-200 text-sm rounded-lg pl-9 pr-3 py-2.5 focus:outline-none focus:border-[#e35d8f] transition" />
          <svg class="absolute left-3 top-3 w-4 h-4 text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
        </div>
        <select v-model="f.canton" @change="apply" class="filter-select">
          <option value="">{{ t('clubs.all_cantons') }}</option>
          <option v-for="c in cantons" :key="c.code" :value="c.code">{{ c.name }}</option>
        </select>
        <select v-model="f.category" @change="apply" class="filter-select">
          <option value="">{{ t('clubs.all_categories') }}</option>
          <option v-for="c in categories" :key="c" :value="c">{{ c }}</option>
        </select>
        <select v-model="f.opening" @change="apply" class="filter-select">
          <option value="">{{ t('clubs.opening_all') }}</option>
          <option value="open_now">{{ t('clubs.opening_now') }}</option>
          <option value="today">{{ t('clubs.opening_today') }}</option>
          <option value="24h">{{ t('clubs.opening_24h') }}</option>
          <option value="weekend">{{ t('clubs.opening_weekend') }}</option>
        </select>
        <select v-model="f.sort" @change="apply" class="filter-select">
          <option value="premium">{{ t('clubs.sort_premium') }}</option>
          <option v-if="userLoc" value="distance">{{ t('clubs.sort_distance') }}</option>
          <option value="rating">{{ t('clubs.sort_rating') }}</option>
          <option value="newest">{{ t('clubs.sort_newest') }}</option>
          <option value="alpha">{{ t('clubs.sort_alpha') }}</option>
        </select>
      </div>

      <!-- Standort / Entfernung -->
      <div class="max-w-7xl mx-auto mt-2.5 flex flex-wrap items-center gap-2.5">
        <button type="button" @click="useLocation" :disabled="locating"
          class="inline-flex items-center gap-1.5 text-sm font-semibold px-3 py-2 rounded-lg border transition disabled:opacity-50"
          :class="userLoc ? 'border-green-500/40 text-green-400 bg-green-500/10' : 'border-white/10 text-gray-300 hover:border-[#e35d8f] hover:text-[#e35d8f]'">
          <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M17.657 16.657L13.414 20.9a2 2 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
          {{ locating ? '…' : (userLoc ? t('clubs.location_active') : t('clubs.use_location')) }}
        </button>
        <select v-if="userLoc" v-model="radius" class="filter-select">
          <option value="">{{ t('clubs.radius_any') }}</option>
          <option :value="5">5 km</option>
          <option :value="10">10 km</option>
          <option :value="25">25 km</option>
          <option :value="50">50 km</option>
          <option :value="100">100 km</option>
        </select>
        <span v-if="locError" class="text-xs text-red-400">{{ locError }}</span>
      </div>
    </div>

    <!-- Liste -->
    <div class="max-w-7xl mx-auto px-4 py-6">
      <p class="text-xs text-gray-500 mb-4">{{ t('clubs.results', { count: visibleClubs.length }) }}</p>

      <div v-if="visibleClubs.length === 0" class="text-center py-16 text-gray-500">
        <div class="text-5xl mb-3">🏙️</div>
        <p class="text-sm">{{ t('clubs.none') }}</p>
      </div>

      <!-- Längliche Zeilen: je Club eine horizontale Reihe -->
      <div v-else class="space-y-2.5">
        <div v-for="club in visibleClubs" :key="club.id"
          class="bg-[#1a1a1a] border border-white/8 rounded-xl px-4 py-3 flex flex-col sm:flex-row sm:items-center gap-x-4 gap-y-2 hover:border-[#e35d8f]/40 transition">

          <!-- Name + Badges -->
          <div class="sm:w-60 md:w-72 shrink-0 min-w-0">
            <div class="flex items-center gap-2">
              <h2 class="font-bold text-white truncate">{{ club.name }}</h2>
              <span v-if="club.is_open_now" class="shrink-0 text-[10px] font-bold text-green-400 bg-green-500/10 border border-green-500/30 px-1.5 py-0.5 rounded-full">{{ t('clubs.open_now') }}</span>
            </div>
            <div class="flex flex-wrap items-center gap-1.5 mt-1">
              <span class="text-[10px] font-semibold text-[#e35d8f] bg-[#e35d8f]/10 border border-[#e35d8f]/30 px-1.5 py-0.5 rounded-full">{{ club.category }}</span>
              <span v-if="club.is_premium" class="text-[10px] font-bold text-amber-300 bg-amber-400/10 border border-amber-400/40 px-1.5 py-0.5 rounded-full">★ {{ t('clubs.premium') }}</span>
              <span v-if="club.is_verified" class="text-[10px] font-bold text-sky-300 bg-sky-400/10 border border-sky-400/40 px-1.5 py-0.5 rounded-full">✓ {{ t('clubs.verified') }}</span>
            </div>
          </div>

          <!-- Adresse -->
          <div class="flex-1 min-w-0 text-sm">
            <p v-if="club.address" class="text-gray-300 truncate">{{ club.address }}</p>
            <p class="text-xs text-gray-500 truncate">
              {{ club.city }} · {{ club.canton_name }}
              <span v-if="club.distance != null" class="text-[#e35d8f] font-semibold">· {{ club.distance.toFixed(1) }} km</span>
            </p>
          </div>

          <!-- Öffnungszeiten heute -->
          <div class="sm:w-32 shrink-0 text-xs text-gray-400 truncate">
            <span v-if="club.today_label">{{ t('clubs.today') }}: {{ club.today_label }}</span>
          </div>

          <!-- Bewertung -->
          <div class="sm:w-20 shrink-0 text-sm">
            <span v-if="club.rating_average" class="text-yellow-400 whitespace-nowrap">
              ★ <span class="text-white font-semibold">{{ Number(club.rating_average).toFixed(1) }}</span>
            </span>
          </div>

          <!-- Aktionen -->
          <div class="flex gap-2 shrink-0">
            <Link :href="route('clubs.show', club.slug)"
              class="text-center bg-white/5 hover:bg-white/10 border border-white/10 text-white text-sm font-semibold px-4 py-2 rounded-lg transition whitespace-nowrap">
              {{ t('clubs.details') }}
            </Link>
            <a v-if="club.website_url" :href="route('clubs.visit', club.slug)"
              target="_blank" rel="nofollow noopener noreferrer"
              class="text-center bg-[#e35d8f] hover:bg-[#c44a7a] text-white text-sm font-bold px-4 py-2 rounded-lg transition whitespace-nowrap">
              {{ t('clubs.open_website') }}
            </a>
          </div>
        </div>
      </div>
    </div>
  </AppLayout>
</template>

<script setup>
import { reactive, ref, computed } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import { useI18n } from '@/composables/useI18n';

const { t } = useI18n();

const props = defineProps({
  clubs:        { type: Array,  default: () => [] },
  cantons:      { type: Array,  default: () => [] },
  categories:   { type: Array,  default: () => [] },
  filters:      { type: Object, default: () => ({}) },
  activeCanton: { type: Object, default: null },
  meta:         { type: Object, default: () => ({ title: '', description: '' }) },
});

const f = reactive({
  q:        props.filters.q ?? '',
  canton:   props.filters.canton ?? '',
  category: props.filters.category ?? '',
  opening:  props.filters.opening ?? '',
  sort:     props.filters.sort ?? 'premium',
});

function apply() {
  const params = {};
  if (f.q)        params.q = f.q;
  if (f.canton)   params.canton = f.canton;
  if (f.category) params.category = f.category;
  if (f.opening)  params.opening = f.opening;
  // 'distance' wird clientseitig sortiert → nicht an den Server geben
  if (f.sort && !['premium', 'distance'].includes(f.sort)) params.sort = f.sort;
  router.get(route('clubs.index'), params, { preserveScroll: true, preserveState: true, replace: true });
}

// ── Standort / Entfernung (nur nach Zustimmung, nicht gespeichert) ──────────
const userLoc  = ref(null);      // { lat, lng }
const locating = ref(false);
const locError = ref(null);
const radius   = ref('');        // '', 5, 10, 25, 50, 100 (km)

function useLocation() {
  locError.value = null;
  if (! navigator.geolocation) { locError.value = t('clubs.geo_unsupported'); return; }
  locating.value = true;
  navigator.geolocation.getCurrentPosition(
    (pos) => {
      userLoc.value = { lat: pos.coords.latitude, lng: pos.coords.longitude };
      locating.value = false;
      f.sort = 'distance';
    },
    () => { locError.value = t('clubs.geo_denied'); locating.value = false; },
    { enableHighAccuracy: false, timeout: 10000, maximumAge: 60000 },
  );
}

function distanceTo(c) {
  if (! userLoc.value || c.latitude == null || c.longitude == null) return null;
  const R = 6371, toRad = (x) => (x * Math.PI) / 180;
  const dLat = toRad(c.latitude - userLoc.value.lat);
  const dLng = toRad(c.longitude - userLoc.value.lng);
  const a = Math.sin(dLat / 2) ** 2
    + Math.cos(toRad(userLoc.value.lat)) * Math.cos(toRad(c.latitude)) * Math.sin(dLng / 2) ** 2;
  return R * 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1 - a));
}

// Angezeigte Liste inkl. Distanz, optionalem Umkreis-Filter und Distanz-Sortierung
const visibleClubs = computed(() => {
  let list = props.clubs.map((c) => ({ ...c, distance: distanceTo(c) }));
  if (userLoc.value && radius.value) {
    list = list.filter((c) => c.distance != null && c.distance <= Number(radius.value));
  }
  if (userLoc.value && f.sort === 'distance') {
    list = [...list].sort((a, b) => (a.distance ?? Infinity) - (b.distance ?? Infinity));
  }
  return list;
});
</script>

<style scoped>
.filter-select {
  background: #1a1a1a;
  border: 1px solid rgba(255, 255, 255, 0.1);
  color: #e5e7eb;
  font-size: 0.875rem;
  border-radius: 0.5rem;
  padding: 0.625rem 0.75rem;
  cursor: pointer;
  transition: border-color 0.15s ease;
}
.filter-select:focus { outline: none; border-color: #e35d8f; }
</style>
