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
          <option value="rating">{{ t('clubs.sort_rating') }}</option>
          <option value="newest">{{ t('clubs.sort_newest') }}</option>
          <option value="alpha">{{ t('clubs.sort_alpha') }}</option>
        </select>
      </div>
    </div>

    <!-- Liste -->
    <div class="max-w-7xl mx-auto px-4 py-6">
      <p class="text-xs text-gray-500 mb-4">{{ t('clubs.results', { count: clubs.length }) }}</p>

      <div v-if="clubs.length === 0" class="text-center py-16 text-gray-500">
        <div class="text-5xl mb-3">🏙️</div>
        <p class="text-sm">{{ t('clubs.none') }}</p>
      </div>

      <div v-else class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
        <div v-for="club in clubs" :key="club.id"
          class="bg-[#1a1a1a] border border-white/8 rounded-2xl overflow-hidden flex flex-col hover:border-[#e35d8f]/40 transition">
          <div class="p-5 flex-1">
            <div class="flex items-start justify-between gap-2 mb-2">
              <h2 class="text-lg font-bold text-white leading-tight">{{ club.name }}</h2>
              <span v-if="club.is_open_now" class="shrink-0 text-[10px] font-bold text-green-400 bg-green-500/10 border border-green-500/30 px-2 py-0.5 rounded-full">{{ t('clubs.open_now') }}</span>
            </div>

            <div class="flex flex-wrap gap-1.5 mb-3">
              <span class="text-[11px] font-semibold text-[#e35d8f] bg-[#e35d8f]/10 border border-[#e35d8f]/30 px-2 py-0.5 rounded-full">{{ club.category }}</span>
              <span v-if="club.is_premium" class="text-[11px] font-bold text-amber-300 bg-amber-400/10 border border-amber-400/40 px-2 py-0.5 rounded-full">★ {{ t('clubs.premium') }}</span>
              <span v-if="club.is_verified" class="text-[11px] font-bold text-sky-300 bg-sky-400/10 border border-sky-400/40 px-2 py-0.5 rounded-full">✓ {{ t('clubs.verified') }}</span>
            </div>

            <div class="space-y-1.5 text-sm text-gray-400">
              <p class="flex items-center gap-1.5">
                <svg class="w-4 h-4 text-[#e35d8f] shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M5.05 4.05a7 7 0 119.9 9.9L10 18.9l-4.95-4.95a7 7 0 010-9.9zM10 11a2 2 0 100-4 2 2 0 000 4z" clip-rule="evenodd"/></svg>
                {{ club.city }} · {{ club.canton_name }}
              </p>
              <p v-if="club.address" class="pl-6 text-gray-500 text-xs">{{ club.address }}</p>
              <p v-if="club.today_label" class="flex items-center gap-1.5">
                <svg class="w-4 h-4 text-[#e35d8f] shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                {{ t('clubs.today') }}: {{ club.today_label }}
              </p>
              <p v-if="club.rating_average" class="flex items-center gap-1.5 text-yellow-400">
                ★ <span class="text-white font-semibold">{{ Number(club.rating_average).toFixed(1) }}</span>
                <span class="text-gray-500 text-xs">({{ club.rating_count }})</span>
              </p>
            </div>
          </div>

          <div class="px-5 pb-5 flex gap-2">
            <Link :href="route('clubs.show', club.slug)"
              class="flex-1 text-center bg-white/5 hover:bg-white/10 border border-white/10 text-white text-sm font-semibold px-3 py-2.5 rounded-xl transition">
              {{ t('clubs.details') }}
            </Link>
            <a v-if="club.website_url" :href="route('clubs.visit', club.slug)"
              target="_blank" rel="nofollow noopener noreferrer"
              class="flex-1 text-center bg-[#e35d8f] hover:bg-[#c44a7a] text-white text-sm font-bold px-3 py-2.5 rounded-xl transition">
              {{ t('clubs.open_website') }}
            </a>
          </div>
        </div>
      </div>
    </div>
  </AppLayout>
</template>

<script setup>
import { reactive } from 'vue';
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
  if (f.sort && f.sort !== 'premium') params.sort = f.sort;
  router.get(route('clubs.index'), params, { preserveScroll: true, preserveState: true, replace: true });
}
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
