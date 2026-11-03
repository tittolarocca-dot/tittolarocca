<template>
  <DashboardLayout role="inserent">
    <Head :title="t('stats.title')" />

    <div class="space-y-5">
      <!-- Kopf -->
      <div class="flex items-center gap-2">
        <svg class="w-6 h-6 text-[#e35d8f]" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4 20V10M10 20V4M16 20v-7M22 20H2"/></svg>
        <h1 class="text-2xl font-black text-white">{{ t('stats.title') }}</h1>
      </div>

      <!-- Filter -->
      <div class="flex flex-wrap items-end gap-3">
        <div>
          <label class="block text-[11px] font-medium text-gray-500 mb-1">{{ t('stats.date_range') }}</label>
          <select v-model="form.range" @change="onRangeChange"
            class="bg-[#1a1a1a] border border-white/10 rounded-lg px-3 py-2.5 text-sm text-white focus:border-[#e35d8f] focus:outline-none min-w-[170px]">
            <option v-for="o in rangeOptions" :key="o" :value="o">{{ t('stats.range_' + o) }}</option>
          </select>
        </div>

        <div>
          <label class="block text-[11px] font-medium text-gray-500 mb-1">{{ t('stats.compare_with') }}</label>
          <select v-model="form.compare" @change="reload"
            class="bg-[#1a1a1a] border border-white/10 rounded-lg px-3 py-2.5 text-sm text-white focus:border-[#e35d8f] focus:outline-none min-w-[170px]">
            <option v-for="o in compareOptions" :key="o" :value="o">{{ t('stats.compare_' + o) }}</option>
          </select>
        </div>

        <button v-if="isFiltered" type="button" @click="reset"
          class="inline-flex items-center gap-1.5 text-sm font-bold text-[#e35d8f] hover:text-[#c44a7a] px-2 py-2.5 transition">
          <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4 4v5h5M20 20v-5h-5M5 19A9 9 0 0119 5"/></svg>
          {{ t('stats.reset') }}
        </button>
      </div>

      <!-- Custom Datumsfelder -->
      <div v-if="form.range === 'custom'" class="flex flex-wrap items-end gap-3 bg-[#1a1a1a] border border-white/8 rounded-xl p-3">
        <div>
          <label class="block text-[11px] text-gray-500 mb-1">{{ t('stats.from') }}</label>
          <input v-model="form.from" type="date" class="bg-[#111] border border-white/10 rounded-lg px-3 py-2 text-sm text-white focus:border-[#e35d8f] focus:outline-none" />
        </div>
        <div>
          <label class="block text-[11px] text-gray-500 mb-1">{{ t('stats.to') }}</label>
          <input v-model="form.to" type="date" class="bg-[#111] border border-white/10 rounded-lg px-3 py-2 text-sm text-white focus:border-[#e35d8f] focus:outline-none" />
        </div>
        <button type="button" @click="reload" class="bg-[#e35d8f] hover:bg-[#c44a7a] text-white text-sm font-bold px-4 py-2 rounded-lg transition">{{ t('stats.apply') }}</button>
      </div>
      <div v-if="form.compare === 'custom'" class="flex flex-wrap items-end gap-3 bg-[#1a1a1a] border border-white/8 rounded-xl p-3">
        <div>
          <label class="block text-[11px] text-gray-500 mb-1">{{ t('stats.compare_from') }}</label>
          <input v-model="form.compare_from" type="date" class="bg-[#111] border border-white/10 rounded-lg px-3 py-2 text-sm text-white focus:border-[#e35d8f] focus:outline-none" />
        </div>
        <div>
          <label class="block text-[11px] text-gray-500 mb-1">{{ t('stats.compare_to') }}</label>
          <input v-model="form.compare_to" type="date" class="bg-[#111] border border-white/10 rounded-lg px-3 py-2 text-sm text-white focus:border-[#e35d8f] focus:outline-none" />
        </div>
        <button type="button" @click="reload" class="bg-[#e35d8f] hover:bg-[#c44a7a] text-white text-sm font-bold px-4 py-2 rounded-lg transition">{{ t('stats.apply') }}</button>
      </div>

      <!-- Zeitraum-Anzeige -->
      <p class="text-xs text-gray-500">
        {{ t('stats.period') }}: <span class="text-gray-300">{{ fmt(filters.from) }} – {{ fmt(filters.to) }}</span>
        <template v-if="filters.compare_from">
          · {{ t('stats.vs') }} <span class="text-gray-300">{{ fmt(filters.compare_from) }} – {{ fmt(filters.compare_to) }}</span>
        </template>
      </p>

      <!-- KPI-Karten -->
      <div class="grid gap-3 sm:grid-cols-2 lg:grid-cols-3">
        <div v-for="m in metrics" :key="m.key"
          class="relative bg-[#1a1a1a] border border-white/8 rounded-2xl p-5 overflow-hidden">
          <span class="absolute left-0 top-0 bottom-0 w-1 bg-[#e35d8f]"></span>
          <div class="flex items-start justify-between">
            <p class="text-sm font-semibold text-gray-300">{{ t('stats.m_' + m.key) }}</p>
            <svg class="w-5 h-5 text-[#e35d8f] shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" v-html="icons[m.key]"></svg>
          </div>
          <div class="mt-2 flex items-end gap-2">
            <span class="text-3xl font-black text-white tabular-nums">{{ nf(m.value) }}</span>
            <span v-if="m.delta !== null" class="mb-1 inline-flex items-center gap-0.5 text-xs font-bold" :class="deltaClass(m.delta)">
              <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24" v-html="deltaIcon(m.delta)"></svg>
              {{ m.delta > 0 ? '+' + nf(m.delta) : nf(m.delta) }}
            </span>
          </div>
          <p v-if="m.compare !== null" class="text-[11px] text-gray-600 mt-1">{{ t('stats.previous') }}: {{ nf(m.compare) }}</p>
          <p v-if="eventBased.includes(m.key)" class="text-[11px] text-gray-700 mt-1.5">{{ t('stats.since_tracking') }}</p>
        </div>
      </div>
    </div>
  </DashboardLayout>
</template>

<script setup>
import { Head, router } from '@inertiajs/vue3';
import { reactive, computed } from 'vue';
import DashboardLayout from '@/Layouts/DashboardLayout.vue';
import { useI18n } from '@/composables/useI18n';

const { t } = useI18n();

const props = defineProps({
  metrics: { type: Array,  default: () => [] },
  filters: { type: Object, default: () => ({ range: 'month', compare: 'none' }) },
});

const metrics = computed(() => props.metrics);
const filters = computed(() => props.filters);

const rangeOptions   = ['today', 'week', 'month', 'year', 'custom'];
const compareOptions = ['none', 'yesterday', 'prev_week', 'prev_month', 'prev_year', 'custom'];

// Aufrufe/Klicks stammen aus dem Event-Tracking (erst ab Einführung).
const eventBased = ['views', 'whatsapp', 'calls'];

const form = reactive({
  range:        props.filters.range   ?? 'month',
  compare:      props.filters.compare ?? 'none',
  from:         props.filters.from         ?? '',
  to:           props.filters.to           ?? '',
  compare_from: props.filters.compare_from ?? '',
  compare_to:   props.filters.compare_to   ?? '',
});

const isFiltered = computed(() => form.range !== 'month' || form.compare !== 'none');

function onRangeChange() {
  if (form.range !== 'custom') reload();
}

function reload() {
  const q = { range: form.range, compare: form.compare };
  if (form.range === 'custom')   { q.from = form.from; q.to = form.to; }
  if (form.compare === 'custom') { q.compare_from = form.compare_from; q.compare_to = form.compare_to; }
  router.get(route('inserat.statistics'), q, { preserveState: true, preserveScroll: true, replace: true });
}

function reset() {
  form.range = 'month'; form.compare = 'none';
  router.get(route('inserat.statistics'), {}, { preserveState: true, preserveScroll: true, replace: true });
}

const nf = (n) => new Intl.NumberFormat('de-CH').format(n ?? 0);
function fmt(d) {
  if (!d) return '';
  const [y, m, day] = d.split('-');
  return `${day}.${m}.${y}`;
}

function deltaClass(d) {
  if (d > 0) return 'text-green-400';
  if (d < 0) return 'text-red-400';
  return 'text-gray-500';
}
function deltaIcon(d) {
  if (d > 0) return '<path stroke-linecap="round" stroke-linejoin="round" d="M7 17L17 7M17 7H9M17 7v8"/>';
  if (d < 0) return '<path stroke-linecap="round" stroke-linejoin="round" d="M7 7l10 10M17 17H9M17 17V9"/>';
  return '<path stroke-linecap="round" stroke-linejoin="round" d="M5 12h14"/>';
}

const icons = {
  views:     '<path stroke-linecap="round" stroke-linejoin="round" d="M2 12s3.5-7 10-7 10 7 10 7-3.5 7-10 7-10-7-10-7z"/><circle cx="12" cy="12" r="3"/>',
  whatsapp:  '<path stroke-linecap="round" stroke-linejoin="round" d="M21 11.5a8.5 8.5 0 01-12.5 7.5L3 21l2-5.5A8.5 8.5 0 1121 11.5z"/>',
  calls:     '<path stroke-linecap="round" stroke-linejoin="round" d="M22 16.9v3a2 2 0 01-2.2 2 19.8 19.8 0 01-8.6-3.1 19.5 19.5 0 01-6-6A19.8 19.8 0 012 4.2 2 2 0 014 2h3a2 2 0 012 1.7c.1.9.3 1.8.6 2.6a2 2 0 01-.5 2.1L8 9.6a16 16 0 006 6l1.2-1.1a2 2 0 012.1-.5c.8.3 1.7.5 2.6.6A2 2 0 0122 16.9z"/>',
  messages:  '<path stroke-linecap="round" stroke-linejoin="round" d="M21 15a2 2 0 01-2 2H8l-4 4V5a2 2 0 012-2h13a2 2 0 012 2z"/>',
  favorites: '<path stroke-linecap="round" stroke-linejoin="round" d="M12 20s-6.5-4-9-7.5C1.4 10 2.3 6.5 6 6.5c1.9 0 3.1 1.1 4 2.3.9-1.2 2.1-2.3 4-2.3 3.7 0 4.6 3.5 3 6-2.5 3.5-9 7.5-9 7.5z"/>',
  follows:   '<circle cx="9" cy="7" r="3"/><path stroke-linecap="round" stroke-linejoin="round" d="M3 20a6 6 0 0112 0M16 3.1a3 3 0 010 5.8M21 20a5 5 0 00-4-4.9"/>',
};
</script>
