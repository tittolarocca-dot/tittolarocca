<template>
  <AppLayout>
    <Head title="Mein Inserat" />

    <div class="max-w-5xl mx-auto px-4 py-8">
      <h1 class="text-2xl font-bold text-gray-900 mb-6">Mein Inserat</h1>

      <!-- Kein Profil -->
      <div v-if="!profile" class="bg-white rounded-xl border border-dashed border-[#e91e8c]/40 p-10 text-center shadow-sm">
        <div class="text-5xl mb-4">📋</div>
        <h2 class="text-lg font-semibold text-gray-900 mb-2">Noch kein Inserat erstellt</h2>
        <p class="text-gray-500 text-sm mb-6">Erstelle dein Profil und wähle ein Paket, um sichtbar zu werden.</p>
        <Link :href="route('inserat.profile.edit')"
          class="inline-flex items-center gap-2 bg-[#e91e8c] text-white px-5 py-2.5 rounded-lg text-sm font-bold hover:bg-[#c91478] transition">
          Profil erstellen →
        </Link>
      </div>

      <!-- Profil vorhanden -->
      <div v-else class="space-y-6">

        <!-- Status-Banner -->
        <div :class="[
          'rounded-xl px-5 py-4 flex items-center justify-between gap-4 border',
          stats.isActive
            ? 'bg-green-50 border-green-200'
            : 'bg-yellow-50 border-yellow-200',
        ]">
          <div class="flex items-center gap-3">
            <span class="text-2xl">{{ stats.isActive ? '✅' : '⚠️' }}</span>
            <div>
              <p class="font-semibold text-sm" :class="stats.isActive ? 'text-green-700' : 'text-yellow-700'">
                {{ stats.isActive ? 'Inserat aktiv' : 'Inserat inaktiv' }}
              </p>
              <p class="text-xs mt-0.5" :class="stats.isActive ? 'text-green-600' : 'text-yellow-600'">
                {{ stats.isActive ? `Läuft bis ${stats.expiresAt}` : 'Kaufe ein Paket, um sichtbar zu werden.' }}
              </p>
              <p class="text-xs text-gray-400 mt-0.5">Inseriert am {{ stats.createdAt }}</p>
            </div>
          </div>
          <Link v-if="!stats.isActive" :href="route('inserat.package.select')"
            class="shrink-0 bg-[#e91e8c] text-white text-xs font-bold px-4 py-2 rounded-lg hover:bg-[#c91478] transition">
            Paket kaufen
          </Link>
          <Link v-else :href="route('inserat.package.select')"
            class="shrink-0 border border-green-600 text-green-700 text-xs font-semibold px-4 py-2 rounded-lg hover:bg-green-50 transition">
            Verlängern
          </Link>
        </div>

        <!-- Push-Karte -->
        <div v-if="stats.isActive" class="bg-white rounded-xl border border-gray-200 p-5 shadow-sm flex items-center justify-between gap-4">
          <div class="flex items-center gap-3">
            <span class="text-3xl">🚀</span>
            <div>
              <p class="font-semibold text-gray-900 text-sm">Inserat pushen</p>
              <p class="text-xs text-gray-500 mt-0.5">Erscheine für 24h ganz oben auf der ersten Seite</p>
              <p v-if="stats.pushedAt" class="text-xs text-gray-400 mt-0.5">Zuletzt gepusht: {{ stats.pushedAt }}</p>
            </div>
          </div>
          <form @submit.prevent="push">
            <button type="submit" :disabled="pushing"
              class="shrink-0 bg-[#e91e8c] hover:bg-[#c91478] disabled:opacity-50 text-white text-sm font-bold px-5 py-2.5 rounded-lg transition whitespace-nowrap">
              {{ pushing ? 'Weiterleitung…' : 'CHF 5.00 pushen' }}
            </button>
          </form>
        </div>

        <!-- Statistiken -->
        <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
          <div v-for="stat in statCards" :key="stat.label"
            class="bg-white rounded-xl border border-gray-200 p-4 text-center shadow-sm">
            <div class="text-2xl font-bold text-[#e91e8c]">{{ stat.value }}</div>
            <div class="text-xs text-gray-500 mt-1">{{ stat.label }}</div>
          </div>
        </div>

        <!-- Schnellzugriff -->
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
          <Link v-for="action in quickActions" :key="action.label"
            :href="action.href"
            class="bg-white rounded-xl border border-gray-200 p-4 flex items-center gap-3 hover:border-[#e91e8c]/40 transition group shadow-sm">
            <span class="text-2xl">{{ action.icon }}</span>
            <div>
              <p class="text-sm font-semibold text-gray-800 group-hover:text-[#e91e8c] transition">{{ action.label }}</p>
              <p class="text-xs text-gray-500">{{ action.desc }}</p>
            </div>
          </Link>
        </div>
      </div>
    </div>
  </AppLayout>
</template>

<script setup>
import { Head, Link, router } from '@inertiajs/vue3';
import { ref, computed } from 'vue';
import AppLayout from '@/Layouts/AppLayout.vue';

const props = defineProps({
  profile: Object,
  stats:   Object,
});

const statCards = computed(() => props.stats ? [
  { label: 'Profilaufrufe',  value: props.stats.views },
  { label: 'Abonnenten',     value: props.stats.subscribers },
  { label: 'Medien',         value: props.stats.mediaCount },
  { label: 'Status',         value: props.stats.isActive ? 'Aktiv' : 'Inaktiv' },
] : []);

const pushing = ref(false);
function push() {
  pushing.value = true;
  router.post(route('inserat.push'), {}, { onFinish: () => { pushing.value = false; } });
}

const quickActions = [
  { icon: '✏️', label: 'Profil bearbeiten', desc: 'Texte und Angaben ändern', href: route('inserat.profile.edit') },
  { icon: '🖼️', label: 'Medien verwalten',  desc: 'Fotos hochladen',          href: route('inserat.media.index') },
  { icon: '💬', label: 'Nachrichten',        desc: 'Postfach öffnen',          href: route('inserat.messages') },
];
</script>
