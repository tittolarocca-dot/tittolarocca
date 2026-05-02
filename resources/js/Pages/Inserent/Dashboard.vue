<template>
  <AppLayout>
    <Head title="Mein Inserat" />

    <div class="max-w-5xl mx-auto px-4 py-8">
      <h1 class="text-2xl font-bold text-gray-900 mb-6">Mein Inserat</h1>

      <!-- Kein Profil -->
      <div v-if="!profile" class="bg-white rounded-xl border border-dashed border-[#e35d8f]/40 p-10 text-center shadow-sm">
        <div class="text-5xl mb-4">📋</div>
        <h2 class="text-lg font-semibold text-gray-900 mb-2">Noch kein Inserat erstellt</h2>
        <p class="text-gray-500 text-sm mb-6">Erstelle dein Profil und wähle ein Paket, um sichtbar zu werden.</p>
        <Link :href="route('inserat.profile.edit')"
          class="inline-flex items-center gap-2 bg-[#e35d8f] text-white px-5 py-2.5 rounded-lg text-sm font-bold hover:bg-[#c44a7a] transition">
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
            class="shrink-0 bg-[#e35d8f] text-white text-xs font-bold px-4 py-2 rounded-lg hover:bg-[#c44a7a] transition">
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
              class="shrink-0 bg-[#e35d8f] hover:bg-[#c44a7a] disabled:opacity-50 text-white text-sm font-bold px-5 py-2.5 rounded-lg transition whitespace-nowrap">
              {{ pushing ? 'Weiterleitung…' : 'CHF 5.00 pushen' }}
            </button>
          </form>
        </div>

        <!-- Statistiken -->
        <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
          <div v-for="stat in statCards" :key="stat.label"
            class="bg-white rounded-xl border border-gray-200 p-4 text-center shadow-sm">
            <div class="text-2xl font-bold text-[#e35d8f]">{{ stat.value }}</div>
            <div class="text-xs text-gray-500 mt-1">{{ stat.label }}</div>
          </div>
        </div>

        <!-- Schnellzugriff -->
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
          <Link v-for="action in quickActions" :key="action.label"
            :href="action.href"
            class="bg-white rounded-xl border border-gray-200 p-4 flex items-center gap-3 hover:border-[#e35d8f]/40 transition group shadow-sm">
            <span class="text-2xl">{{ action.icon }}</span>
            <div>
              <p class="text-sm font-semibold text-gray-800 group-hover:text-[#e35d8f] transition">{{ action.label }}</p>
              <p class="text-xs text-gray-500">{{ action.desc }}</p>
            </div>
          </Link>
        </div>

        <!-- Gefahrenzone -->
        <div class="border border-red-200 rounded-xl overflow-hidden">
          <div class="bg-red-50 px-5 py-3 border-b border-red-200">
            <h2 class="text-sm font-semibold text-red-700">Gefahrenzone</h2>
          </div>
          <div class="bg-white px-5 py-4 flex items-center justify-between gap-4">
            <div>
              <p class="text-sm font-semibold text-gray-800">Profil löschen</p>
              <p class="text-xs text-gray-500 mt-0.5">Löscht dein Profil, alle Medien und beendet aktive Abonnements. Nicht rückgängig zu machen.</p>
            </div>
            <button @click="showDeleteConfirm = true"
              class="shrink-0 border border-red-300 text-red-600 text-sm font-semibold px-4 py-2 rounded-lg hover:bg-red-50 transition whitespace-nowrap">
              Profil löschen
            </button>
          </div>
        </div>

      </div>
    </div>

    <!-- Lösch-Bestätigungsmodal -->
    <Teleport to="body">
      <div v-if="showDeleteConfirm"
        class="fixed inset-0 bg-black/60 flex items-center justify-center z-50 px-4"
        @click.self="showDeleteConfirm = false">
        <div class="bg-white rounded-2xl shadow-2xl w-full max-w-md p-6">
          <div class="flex items-center gap-3 mb-4">
            <div class="w-10 h-10 rounded-full bg-red-100 flex items-center justify-center shrink-0">
              <svg class="w-5 h-5 text-red-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/>
              </svg>
            </div>
            <h3 class="text-lg font-bold text-gray-900">Profil wirklich löschen?</h3>
          </div>

          <p class="text-sm text-gray-600 mb-3">Folgendes wird <strong>unwiderruflich</strong> gelöscht:</p>
          <ul class="text-sm text-gray-600 space-y-1 mb-5 ml-4 list-disc">
            <li>Dein Profil und alle Profilinformationen</li>
            <li>Alle hochgeladenen Fotos und Medien</li>
            <li>Alle aktiven Abonnements deiner Kunden</li>
            <li>Alle Bewertungen und Nachrichten</li>
          </ul>

          <p class="text-sm text-gray-700 mb-2 font-medium">Gib zur Bestätigung <span class="font-bold text-red-600">LÖSCHEN</span> ein:</p>
          <input v-model="deleteConfirmText" type="text" placeholder="LÖSCHEN"
            class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm mb-4 focus:outline-none focus:border-red-400" />

          <div class="flex gap-3">
            <button @click="showDeleteConfirm = false; deleteConfirmText = ''"
              class="flex-1 border border-gray-200 text-gray-600 text-sm font-semibold py-2.5 rounded-lg hover:bg-gray-50 transition">
              Abbrechen
            </button>
            <button @click="deleteProfile"
              :disabled="deleteConfirmText !== 'LÖSCHEN' || deleting"
              class="flex-1 bg-red-600 hover:bg-red-700 disabled:opacity-40 disabled:cursor-not-allowed text-white text-sm font-bold py-2.5 rounded-lg transition">
              {{ deleting ? 'Wird gelöscht…' : 'Endgültig löschen' }}
            </button>
          </div>
        </div>
      </div>
    </Teleport>
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

const showDeleteConfirm = ref(false);
const deleteConfirmText = ref('');
const deleting          = ref(false);

function deleteProfile() {
  if (deleteConfirmText.value !== 'LÖSCHEN') return;
  deleting.value = true;
  router.delete(route('inserat.profile.destroy'), {
    onFinish: () => { deleting.value = false; },
  });
}

const quickActions = [
  { icon: '✏️', label: 'Profil bearbeiten', desc: 'Texte und Angaben ändern', href: route('inserat.profile.edit') },
  { icon: '🖼️', label: 'Medien verwalten',  desc: 'Fotos hochladen',          href: route('inserat.media.index') },
  { icon: '💬', label: 'Nachrichten',        desc: 'Postfach öffnen',          href: route('inserat.messages') },
];
</script>
