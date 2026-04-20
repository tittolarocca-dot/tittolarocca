<template>
  <AppLayout>
    <Head title="Mein Inserat" />

    <div class="max-w-5xl mx-auto px-4 py-8">
      <h1 class="text-2xl font-bold text-white mb-6">Mein Inserat</h1>

      <!-- Kein Profil -->
      <div v-if="!profile" class="bg-[#1a1a1a] rounded-xl border border-dashed border-[#e91e8c]/40 p-10 text-center">
        <div class="text-5xl mb-4">📋</div>
        <h2 class="text-lg font-semibold text-white mb-2">Noch kein Inserat erstellt</h2>
        <p class="text-gray-400 text-sm mb-6">Erstelle dein Profil und wähle ein Paket, um sichtbar zu werden.</p>
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
            ? 'bg-green-900/20 border-green-700/40'
            : 'bg-yellow-900/20 border-yellow-700/40',
        ]">
          <div class="flex items-center gap-3">
            <span class="text-2xl">{{ stats.isActive ? '✅' : '⚠️' }}</span>
            <div>
              <p class="font-semibold text-sm" :class="stats.isActive ? 'text-green-400' : 'text-yellow-400'">
                {{ stats.isActive ? 'Inserat aktiv' : 'Inserat inaktiv' }}
              </p>
              <p class="text-xs mt-0.5" :class="stats.isActive ? 'text-green-600' : 'text-yellow-600'">
                {{ stats.isActive ? `Läuft bis ${stats.expiresAt}` : 'Kaufe ein Paket, um sichtbar zu werden.' }}
              </p>
            </div>
          </div>
          <Link v-if="!stats.isActive" :href="route('inserat.package.select')"
            class="shrink-0 bg-[#e91e8c] text-white text-xs font-bold px-4 py-2 rounded-lg hover:bg-[#c91478] transition">
            Paket kaufen
          </Link>
          <Link v-else :href="route('inserat.package.select')"
            class="shrink-0 border border-green-600 text-green-400 text-xs font-semibold px-4 py-2 rounded-lg hover:bg-green-900/30 transition">
            Verlängern
          </Link>
        </div>

        <!-- Statistiken -->
        <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
          <div v-for="stat in statCards" :key="stat.label"
            class="bg-[#1a1a1a] rounded-xl border border-[#2a2a2a] p-4 text-center">
            <div class="text-2xl font-bold text-[#e91e8c]">{{ stat.value }}</div>
            <div class="text-xs text-gray-500 mt-1">{{ stat.label }}</div>
          </div>
        </div>

        <!-- Schnellzugriff -->
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
          <Link v-for="action in quickActions" :key="action.label"
            :href="action.href"
            class="bg-[#1a1a1a] rounded-xl border border-[#2a2a2a] p-4 flex items-center gap-3 hover:border-[#e91e8c]/40 transition group">
            <span class="text-2xl">{{ action.icon }}</span>
            <div>
              <p class="text-sm font-semibold text-gray-200 group-hover:text-[#e91e8c] transition">{{ action.label }}</p>
              <p class="text-xs text-gray-500">{{ action.desc }}</p>
            </div>
          </Link>
        </div>
      </div>
    </div>
  </AppLayout>
</template>

<script setup>
import { Head, Link, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';
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

const quickActions = [
  { icon: '✏️', label: 'Profil bearbeiten', desc: 'Texte und Angaben ändern', href: route('inserat.profile.edit') },
  { icon: '🖼️', label: 'Medien verwalten',  desc: 'Fotos hochladen',          href: route('inserat.media.index') },
  { icon: '💬', label: 'Nachrichten',        desc: 'Postfach öffnen',          href: route('inserat.messages') },
];
</script>
