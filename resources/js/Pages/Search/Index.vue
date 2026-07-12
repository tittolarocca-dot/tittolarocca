<template>
  <AppLayout>
    <Head title="Suchen" />

    <div class="max-w-7xl mx-auto px-3 sm:px-4 py-6">

      <!-- Seiten-Titel -->
      <div class="flex items-center gap-3 mb-5">
        <div class="w-11 h-11 rounded-full bg-gradient-to-br from-[#7c3aed] via-[#c44a7a] to-[#e35d8f] flex items-center justify-center shadow-lg shadow-[#e35d8f]/30">
          <svg class="w-5 h-5 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
        </div>
        <div>
          <h1 class="text-2xl font-black text-white">Suchen</h1>
          <p class="text-xs text-gray-500">
            {{ searched ? resultCount + ' Inserate gefunden' : 'Filter wählen und auf „Suchen“ klicken' }}
          </p>
        </div>
      </div>

      <!-- ══ FILTER (Hauptbereich) ══════════════════════════════════════ -->
      <div class="bg-[#1a1a1a] border border-white/8 rounded-2xl p-4 sm:p-5 space-y-5">
        <h2 class="text-sm font-black text-white uppercase tracking-wide">Filter</h2>

        <!-- Freitext -->
        <div class="relative">
          <input v-model="state.q" type="text" @keyup.enter="applyFilters"
            placeholder="Z.B Zürich, Anna, 5400, Küssen, Escort"
            class="w-full bg-[#111] border border-white/10 text-gray-200 text-sm rounded-xl px-4 py-3 pr-10 focus:outline-none focus:border-[#e35d8f] placeholder-gray-600" />
          <svg class="absolute right-3 top-3.5 w-4 h-4 text-gray-600" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
        </div>

        <!-- Region -->
        <div>
          <label class="block text-xs text-gray-500 mb-1.5">Region</label>
          <div class="relative">
            <select v-model="state.region"
              class="w-full bg-[#111] border border-white/10 text-gray-200 text-sm rounded-xl px-4 py-3 pr-9 appearance-none cursor-pointer focus:outline-none focus:border-[#e35d8f]">
              <option value="nearby">In meiner Umgebung (~25km)…</option>
              <option value="">Ganze Schweiz</option>
              <option v-for="c in cities" :key="c.id" :value="c.slug">{{ c.name }} ({{ c.canton }})</option>
            </select>
            <svg class="pointer-events-none absolute right-3 top-3.5 w-4 h-4 text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
          </div>
        </div>

        <!-- Wer -->
        <div>
          <label class="block text-xs text-gray-500 mb-1.5">Wer</label>
          <div class="flex flex-wrap gap-2">
            <button v-for="[val,lbl] in whoOptions" :key="val" type="button" @click="state.who = val" :class="pill(state.who === val)">{{ lbl }}</button>
          </div>
        </div>

        <!-- Kontaktart -->
        <div>
          <label class="block text-xs text-gray-500 mb-1.5">Kontaktart</label>
          <div class="flex flex-wrap gap-2">
            <button v-for="[val,lbl] in contactOptions" :key="val" type="button" @click="state.contact = val" :class="pill(state.contact === val)">{{ lbl }}</button>
          </div>
        </div>

        <!-- Alter -->
        <RangeSlider v-model="state.age" :min="18" :max="80" label="Alter" unit="Jahre" max-suffix="+" />
      </div>

      <!-- ══ AUSSEHEN (Accordion) ═══════════════════════════════════════ -->
      <div class="bg-[#1a1a1a] border border-white/8 rounded-2xl mt-4 overflow-hidden">
        <button type="button" @click="open.aussehen = !open.aussehen"
          class="w-full flex items-center justify-between px-4 sm:px-5 py-4">
          <span class="text-sm font-black text-white uppercase tracking-wide">Aussehen</span>
          <svg class="w-5 h-5 text-gray-400 transition-transform" :class="open.aussehen ? 'rotate-180' : ''" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
        </button>
        <div v-show="open.aussehen" class="px-4 sm:px-5 pb-5 space-y-5">
          <!-- Herkunft -->
          <div>
            <label class="block text-xs text-gray-500 mb-1.5">Herkunft</label>
            <div class="flex flex-wrap gap-2">
              <button v-for="[val,lbl] in originOptions" :key="val" type="button" @click="state.origin = val" :class="pill(state.origin === val)">{{ lbl }}</button>
            </div>
          </div>

          <RangeSlider v-model="state.height" :min="140" :max="205" label="Größe" unit="cm" max-suffix="+" />
          <RangeSlider v-model="state.weight" :min="40" :max="140" label="Gewicht" unit="kg" max-suffix="+" />
          <RangeSlider v-model="state.cup" :min="0" :max="6" label="Oberweite" max-suffix="" :format="cupFormat" />

          <!-- Brusttyp -->
          <div>
            <label class="block text-xs text-gray-500 mb-1.5">Brusttyp</label>
            <div class="grid grid-cols-3 gap-1 bg-white/5 border border-white/10 rounded-full p-1">
              <button v-for="[val,lbl] in breastOptions" :key="val" type="button" @click="state.breast = val" :class="seg(state.breast === val)">{{ lbl }}</button>
            </div>
          </div>

          <!-- Intimbereich -->
          <div>
            <label class="block text-xs text-gray-500 mb-1.5">Intimbereich</label>
            <div class="flex flex-wrap gap-2">
              <button v-for="[val,lbl] in intimOptions" :key="val" type="button" @click="state.intim = val" :class="pill(state.intim === val)">{{ lbl }}</button>
            </div>
          </div>

          <!-- Tattoos -->
          <div>
            <label class="block text-xs text-gray-500 mb-1.5">Tattoos</label>
            <div class="flex flex-wrap gap-2">
              <button v-for="[val,lbl] in tattooOptions" :key="val" type="button" @click="state.tattooLevel = val" :class="pill(state.tattooLevel === val)">{{ lbl }}</button>
            </div>
          </div>

          <!-- Raucher -->
          <div>
            <label class="block text-xs text-gray-500 mb-1.5">Raucher</label>
            <div class="grid grid-cols-3 gap-1 bg-white/5 border border-white/10 rounded-full p-1">
              <button v-for="[val,lbl] in smokingOptions" :key="val" type="button" @click="state.smoking = val" :class="seg(state.smoking === val)">{{ lbl }}</button>
            </div>
          </div>
        </div>
      </div>

      <!-- ══ PROFIL AUTHENTIFIZIERUNG (Accordion) ═══════════════════════ -->
      <div class="bg-[#1a1a1a] border border-white/8 rounded-2xl mt-4 overflow-hidden">
        <button type="button" @click="open.auth = !open.auth"
          class="w-full flex items-center justify-between px-4 sm:px-5 py-4">
          <span class="text-sm font-black text-white uppercase tracking-wide">Profil Authentifizierung</span>
          <svg class="w-5 h-5 text-gray-400 transition-transform" :class="open.auth ? 'rotate-180' : ''" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
        </button>
        <div v-show="open.auth" class="px-4 sm:px-5 pb-5 space-y-1">
          <button type="button" @click="state.authPhone = !state.authPhone" class="w-full flex items-center justify-between py-2.5">
            <span class="text-sm text-gray-300">Telefonnummer bestätigt</span>
            <span :class="track(state.authPhone)"><span :class="knob(state.authPhone)"></span></span>
          </button>
          <button type="button" @click="state.verified = !state.verified" class="w-full flex items-center justify-between py-2.5">
            <span class="text-sm text-gray-300">Fotos bestätigt</span>
            <span :class="track(state.verified)"><span :class="knob(state.verified)"></span></span>
          </button>
          <button type="button" @click="state.authAge = !state.authAge" class="w-full flex items-center justify-between py-2.5">
            <span class="text-sm text-gray-300">Alter bestätigt</span>
            <span :class="track(state.authAge)"><span :class="knob(state.authAge)"></span></span>
          </button>
          <button type="button" @click="state.authVideo = !state.authVideo" class="w-full flex items-center justify-between py-2.5">
            <span class="text-sm text-gray-300">hat ein Video</span>
            <span :class="track(state.authVideo)"><span :class="knob(state.authVideo)"></span></span>
          </button>
        </div>
      </div>

      <!-- ══ SERVICES (Accordion) ═══════════════════════════════════════ -->
      <div class="bg-[#1a1a1a] border border-white/8 rounded-2xl mt-4 overflow-hidden">
        <button type="button" @click="open.services = !open.services"
          class="w-full flex items-center justify-between px-4 sm:px-5 py-4">
          <span class="text-sm font-black text-white uppercase tracking-wide">Services</span>
          <span class="flex items-center gap-3">
            <span v-if="state.services.length" class="text-xs font-bold text-[#e35d8f]">{{ state.services.length }} gewählt</span>
            <svg class="w-5 h-5 text-gray-400 transition-transform" :class="open.services ? 'rotate-180' : ''" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
          </span>
        </button>
        <div v-show="open.services" class="px-4 sm:px-5 pb-5 space-y-5">
          <div v-for="group in serviceGroups" :key="group.label">
            <div class="flex items-center justify-between mb-2">
              <h3 class="text-[11px] font-bold text-[#e35d8f] uppercase tracking-wide">{{ group.label }}</h3>
              <span class="text-[11px] text-gray-600">{{ serviceCount(group.items) }} / {{ group.items.length }}</span>
            </div>
            <div class="flex flex-wrap gap-2">
              <button v-for="s in group.items" :key="s.id" type="button" @click="toggleService(s.id)" :class="pill(state.services.includes(s.id))">{{ s.name }}</button>
            </div>
          </div>
        </div>
      </div>

      <!-- ══ AKTIONEN ═══════════════════════════════════════════════════ -->
      <div class="flex gap-3 mt-5 sticky bottom-3 z-10">
        <button type="button" @click="resetFilters"
          class="flex-1 sm:flex-none bg-[#1a1a1a] border border-white/15 text-gray-300 text-sm font-bold px-5 py-3 rounded-xl hover:border-[#e35d8f]/60 hover:text-white transition">
          Filter zurücksetzen
        </button>
        <button type="button" @click="applyFilters"
          class="flex-1 bg-[#e35d8f] hover:bg-[#c44a7a] text-white text-sm font-black px-6 py-3 rounded-xl transition shadow-lg shadow-[#e35d8f]/25">
          Suchen
        </button>
      </div>

      <!-- ══ ERGEBNISSE ═════════════════════════════════════════════════ -->
      <div class="mt-8">
        <!-- Noch keine Suche ausgeführt -->
        <div v-if="!searched" class="text-center py-20 text-gray-600">
          <div class="text-5xl mb-4">🔍</div>
          <p class="text-lg">Wähle deine Filter und klicke auf „Suchen“.</p>
          <p class="text-sm mt-1 text-gray-700">Deine Ergebnisse erscheinen dann hier.</p>
        </div>

        <!-- Gesucht, aber nichts gefunden -->
        <div v-else-if="profiles.data.length === 0" class="text-center py-20 text-gray-600">
          <div class="text-5xl mb-4">🔍</div>
          <p class="text-lg">Keine Inserate gefunden.</p>
          <p class="text-sm mt-1 text-gray-700">Versuche es mit weniger Filtern.</p>
        </div>

        <!-- Treffer -->
        <div v-else class="grid grid-cols-1 sm:grid-cols-2 gap-4">
          <ProfileCard v-for="profile in profiles.data" :key="profile.id" :profile="profile" />
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
    </div>
  </AppLayout>
</template>

<script setup>
import { reactive, computed } from 'vue';
import { Head, Link, router, usePage } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import ProfileCard from '@/Components/ProfileCard.vue';
import RangeSlider from '@/Components/RangeSlider.vue';

const props = defineProps({
  profiles:    Object,
  services:    Array,
  filters:     Object,
  resultCount: Number,
  searched:    Boolean,
});

const page = usePage();
const cities = computed(() => page.props.cities ?? []);

// Option-Listen [wert, label]
const whoOptions     = [['', 'Alle'], ['frauen', 'Frauen'], ['trans', 'Trans'], ['gigolos', 'Gigolos']];
const contactOptions = [['', 'Alle'], ['call-out', 'Call-Out'], ['call-in', 'Call-In'], ['escort', 'Escort']];
const originOptions  = [['', 'Alle'], ['europaeisch', 'Europäisch (Weiß)'], ['asiatisch', 'Asiatisch'], ['schwarz', 'Schwarz'], ['indisch', 'Indisch'], ['latina', 'Latina (Hispanisch)'], ['gemischt', 'Gemischt']];
const intimOptions   = [['', 'Alle'], ['Glatt', 'Glatt'], ['Teilrasiert', 'Teilrasiert'], ['Natürlich', 'Natürlich']];
const tattooOptions  = [['', 'Alle'], ['Viele', 'Viele'], ['Einige', 'Einige'], ['Wenige', 'Wenige'], ['Keine', 'Keine']];
const breastOptions  = [['', 'Alle'], ['natur', 'Natur'], ['implantate', 'Implantate']];
const smokingOptions = [['', 'Alle'], ['0', 'Nichtraucher'], ['1', 'Raucher']];
const cupLetters     = ['A', 'B', 'C', 'D', 'E', 'F', 'G'];
const cupFormat      = (v) => cupLetters[v] ?? v;

// UI-only Filter aus der URL wiederherstellen (noch keine DB-Anbindung)
function urlParam(key, fallback = '') {
  if (typeof window === 'undefined') return fallback;
  const v = new URLSearchParams(window.location.search).get(key);
  return v ?? fallback;
}

const f = props.filters ?? {};

const state = reactive({
  // ── Echte Filter (mit DB verbunden) ──
  q:        f.q ?? '',
  region:   f.region ?? urlParam('region'),
  age:      [f.age_min ?? 18, f.age_max ?? 80],
  height:   [f.height_min ?? 140, f.height_max ?? 205],
  intim:    f.intim ?? '',
  smoking:  f.smoking === true ? '1' : f.smoking === false ? '0' : '',
  verified: !!f.verified,
  services: Array.isArray(f.services) ? [...f.services] : [],
  contact:  f.contact ?? urlParam('contact'),
  tattooLevel: urlParam('tattoo_level') || (f.tattoo === false ? 'Keine' : f.tattoo === true ? 'Viele' : ''),

  // ── UI vorbereitet, noch KEINE DB-Felder (siehe README/Hinweis) ──
  who:       urlParam('who'),
  origin:    urlParam('origin'),
  weight:    [Number(urlParam('weight_min', 40)), Number(urlParam('weight_max', 140))],
  cup:       [Number(urlParam('cup_min', 0)), Number(urlParam('cup_max', 6))],
  breast:    urlParam('breast'),
  authPhone: urlParam('auth_phone') === '1',
  authAge:   urlParam('auth_age') === '1',
  authVideo: urlParam('auth_video') === '1',
});

const open = reactive({ aussehen: true, auth: false, services: false });

// Tattoo-Pill → Backend-Parameter (DB kennt nur ja/nein)
const tattooParam = computed(() => {
  if (!state.tattooLevel) return '';
  return state.tattooLevel === 'Keine' ? '0' : '1';
});

// Services nach Gruppe
const serviceGroups = computed(() => {
  const groups = new Map();
  for (const s of props.services ?? []) {
    const key = s.group || 'Allgemein';
    if (!groups.has(key)) groups.set(key, []);
    groups.get(key).push(s);
  }
  const order = ['Allgemein', 'Softcore Service', 'Hardcore Service'];
  return [...groups.entries()]
    .sort((a, b) => {
      const ia = order.indexOf(a[0]); const ib = order.indexOf(b[0]);
      return (ia === -1 ? 99 : ia) - (ib === -1 ? 99 : ib);
    })
    .map(([label, items]) => ({ label, items }));
});
function serviceCount(items) { return items.filter((s) => state.services.includes(s.id)).length; }
function toggleService(id) {
  const i = state.services.indexOf(id);
  if (i === -1) state.services.push(id); else state.services.splice(i, 1);
}

// Style-Helfer
function pill(active) {
  return [
    'px-3 py-1.5 rounded-full text-sm font-medium border transition-all',
    active ? 'bg-[#e35d8f] text-white border-[#e35d8f]' : 'bg-white/5 text-gray-300 border-white/10 hover:border-[#e35d8f]/50',
  ];
}
function seg(active) {
  return [
    'py-2 rounded-full text-sm font-medium transition text-center',
    active ? 'bg-[#e35d8f] text-white shadow' : 'text-gray-400 hover:text-white',
  ];
}
function track(on) {
  return ['relative w-10 h-6 rounded-full transition-colors flex-shrink-0', on ? 'bg-[#e35d8f]' : 'bg-white/15'];
}
function knob(on) {
  return ['absolute top-0.5 w-5 h-5 rounded-full bg-white transition-all', on ? 'left-[18px]' : 'left-0.5'];
}

function buildQuery() {
  const q = { searched: 1 };
  // Echte Filter
  if (state.q.trim())            q.q = state.q.trim();
  if (state.region && state.region !== 'nearby') q.region = state.region;
  if (state.contact)             q.contact = state.contact;
  if (state.age[0] > 18)         q.age_min = state.age[0];
  if (state.age[1] < 80)         q.age_max = state.age[1];
  if (state.height[0] > 140)     q.height_min = state.height[0];
  if (state.height[1] < 205)     q.height_max = state.height[1];
  if (state.intim)               q.intim = state.intim;
  if (state.smoking)             q.smoking = state.smoking;
  if (tattooParam.value !== '')  q.tattoo = tattooParam.value;
  if (state.verified)            q.verified = 1;
  if (state.services.length)     q.services = state.services.join(',');

  // UI-only (für persistente/teilbare URL – Backend ignoriert diese noch)
  if (state.region === 'nearby') q.region = 'nearby';
  if (state.who)                 q.who = state.who;
  if (state.origin)              q.origin = state.origin;
  if (state.weight[0] > 40)      q.weight_min = state.weight[0];
  if (state.weight[1] < 140)     q.weight_max = state.weight[1];
  if (state.cup[0] > 0)          q.cup_min = state.cup[0];
  if (state.cup[1] < 6)          q.cup_max = state.cup[1];
  if (state.breast)              q.breast = state.breast;
  if (state.tattooLevel)         q.tattoo_level = state.tattooLevel;
  if (state.authPhone)           q.auth_phone = 1;
  if (state.authAge)             q.auth_age = 1;
  if (state.authVideo)           q.auth_video = 1;
  return q;
}

function applyFilters() {
  router.get(route('search'), buildQuery(), { preserveScroll: true, preserveState: false });
}
function resetFilters() {
  router.get(route('search'), {}, { preserveScroll: true, preserveState: false });
}
</script>
