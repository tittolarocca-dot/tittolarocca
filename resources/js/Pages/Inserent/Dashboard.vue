<template>
  <AppLayout>
    <Head title="Mein Inserat" />

    <div class="max-w-5xl mx-auto px-4 py-8">
      <h1 class="text-2xl font-bold text-white mb-6">Mein Inserat</h1>

      <!-- Kein Profil -->
      <div v-if="!profile" class="bg-[#1a1a1a] rounded-xl border border-dashed border-[#e35d8f]/40 p-10 text-center shadow-sm">
        <div class="text-5xl mb-4">📋</div>
        <h2 class="text-lg font-semibold text-white mb-2">Noch kein Inserat erstellt</h2>
        <p class="text-gray-400 text-sm mb-6">Erstelle dein Profil und wähle ein Paket, um sichtbar zu werden.</p>
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
            ? 'bg-green-950/40 border-green-700/50'
            : 'bg-yellow-950/40 border-yellow-700/50',
        ]">
          <div class="flex items-center gap-3">
            <span class="text-2xl">{{ stats.isActive ? '✅' : '⚠️' }}</span>
            <div>
              <p class="font-semibold text-sm" :class="stats.isActive ? 'text-green-400' : 'text-yellow-400'">
                {{ stats.isActive ? 'Inserat aktiv' : 'Inserat abgelaufen' }}
              </p>
              <p class="text-xs mt-0.5" :class="stats.isActive ? 'text-green-500' : 'text-yellow-500'">
                {{ stats.isActive
                  ? `Läuft bis ${stats.expiresAt}`
                  : stats.isFreeProfile
                    ? 'Dein kostenloses Inserat ist abgelaufen. Reaktiviere es gratis für 7 weitere Tage.'
                    : 'Kaufe ein Paket, um wieder sichtbar zu werden.' }}
              </p>
              <p class="text-xs text-gray-500 mt-0.5">Inseriert am {{ stats.createdAt }}</p>
            </div>
          </div>
          <!-- Aktiv: Verlängern -->
          <Link v-if="stats.isActive" :href="route('inserat.package.select')"
            class="shrink-0 border border-green-600 text-green-400 text-xs font-semibold px-4 py-2 rounded-lg hover:bg-green-900/30 transition">
            Verlängern
          </Link>
          <!-- Abgelaufen + kostenlos: gratis reaktivieren -->
          <form v-else-if="stats.isFreeProfile" @submit.prevent="reactivate">
            <button type="submit" :disabled="reactivating"
              class="shrink-0 bg-[#e35d8f] hover:bg-[#c44a7a] disabled:opacity-50 text-white text-xs font-bold px-4 py-2 rounded-lg transition whitespace-nowrap">
              {{ reactivating ? 'Wird aktiviert…' : 'Gratis reaktivieren (7 Tage)' }}
            </button>
          </form>
          <!-- Abgelaufen + bezahlt: Paket kaufen -->
          <Link v-else :href="route('inserat.package.select')"
            class="shrink-0 bg-[#e35d8f] text-white text-xs font-bold px-4 py-2 rounded-lg hover:bg-[#c44a7a] transition">
            Paket kaufen
          </Link>
        </div>

        <!-- Push-Karte -->
        <div v-if="stats.isActive" class="bg-[#1a1a1a] rounded-xl border border-white/8 p-5 shadow-sm flex items-center justify-between gap-4">
          <div class="flex items-center gap-3">
            <span class="text-3xl">🚀</span>
            <div>
              <p class="font-semibold text-white text-sm">Inserat pushen</p>
              <p class="text-xs text-gray-500 mt-0.5">Erscheine für 24h ganz oben auf der ersten Seite</p>
              <p v-if="stats.pushedAt" class="text-xs text-gray-600 mt-0.5">Zuletzt gepusht: {{ stats.pushedAt }}</p>
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
            class="bg-[#1a1a1a] rounded-xl border border-white/8 p-4 text-center shadow-sm">
            <div class="text-2xl font-bold text-[#e35d8f]">{{ stat.value }}</div>
            <div class="text-xs text-gray-500 mt-1">{{ stat.label }}</div>
          </div>
        </div>

        <!-- Verifikation -->
        <div class="bg-[#1a1a1a] rounded-xl border border-white/8 overflow-hidden shadow-sm">
          <div class="bg-[#111] border-b border-white/8 px-5 py-3 flex items-center justify-between">
            <h2 class="text-sm font-semibold text-white flex items-center gap-2">
              🪪 Identitätsverifikation
            </h2>
            <span :class="verificationBadgeClass">{{ verificationBadgeLabel }}</span>
          </div>

          <!-- Genehmigt -->
          <div v-if="profile.verification_status === 'approved'" class="p-5 flex items-center gap-3">
            <div class="w-10 h-10 rounded-full bg-green-900/40 border border-green-600/40 flex items-center justify-center shrink-0 text-xl">✅</div>
            <div>
              <p class="font-semibold text-white text-sm">Verifiziert</p>
              <p class="text-xs text-gray-400 mt-0.5">Dein Profil trägt das Verifiziert-Badge.</p>
            </div>
          </div>

          <!-- Ausstehend -->
          <div v-else-if="profile.verification_status === 'pending'" class="p-5 space-y-4">
            <div class="flex items-center gap-3">
              <div class="w-10 h-10 rounded-full bg-blue-900/40 border border-blue-600/40 flex items-center justify-center shrink-0 text-xl">⏳</div>
              <div>
                <p class="font-semibold text-white text-sm">Wird geprüft…</p>
                <p class="text-xs text-gray-400 mt-0.5">Wir prüfen dein Foto in der Regel innerhalb von 24 Stunden.</p>
              </div>
            </div>
            <div>
              <button @click="showResubmit = !showResubmit"
                class="text-xs text-gray-500 hover:text-gray-300 underline transition">
                {{ showResubmit ? 'Abbrechen' : 'Neues Foto einreichen' }}
              </button>
              <div v-if="showResubmit" class="mt-3">
                <VerificationUploadForm :form="verifyForm" @submit="submitVerification" />
              </div>
            </div>
          </div>

          <!-- Nicht verifiziert oder abgelehnt -->
          <div v-else class="p-5 space-y-4">
            <!-- Ablehnungsgrund -->
            <div v-if="profile.verification_status === 'rejected' && profile.verification_rejected_reason"
              class="bg-red-950/40 border border-red-700/40 rounded-lg p-3 text-sm text-red-400">
              <strong class="text-red-300">Abgelehnt:</strong> {{ profile.verification_rejected_reason }}
            </div>

            <!-- Anleitung -->
            <div class="bg-amber-950/30 border border-amber-700/30 rounded-lg p-4">
              <p class="text-sm font-semibold text-amber-300 mb-2">📸 So funktioniert die Verifikation:</p>
              <ol class="text-sm text-amber-400/80 space-y-1.5 list-decimal list-inside">
                <li>Nimm ein gut belichtetes <strong class="text-amber-300">Selfie</strong> von dir</li>
                <li>Halte ein handgeschriebenes Schild mit:
                  <ul class="ml-5 mt-1 list-disc text-xs space-y-0.5 text-amber-400/70">
                    <li><strong class="text-amber-300">inserate.ch</strong></li>
                    <li>Dein Profilname: <strong class="text-amber-300">{{ profile.display_name }}</strong></li>
                    <li>Das heutige Datum</li>
                  </ul>
                </li>
                <li>Lade das Foto unten hoch</li>
              </ol>
            </div>

            <VerificationUploadForm :form="verifyForm" @submit="submitVerification" />
          </div>
        </div>

        <!-- Schnellzugriff -->
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
          <Link v-for="action in quickActions" :key="action.label"
            :href="action.href"
            class="bg-[#1a1a1a] rounded-xl border border-white/8 p-4 flex items-center gap-3 hover:border-[#e35d8f]/40 transition group shadow-sm">
            <span class="text-2xl">{{ action.icon }}</span>
            <div>
              <p class="text-sm font-semibold text-white group-hover:text-[#e35d8f] transition">{{ action.label }}</p>
              <p class="text-xs text-gray-500">{{ action.desc }}</p>
            </div>
          </Link>
        </div>

        <!-- Gefahrenzone -->
        <div class="border border-red-900/40 rounded-xl overflow-hidden">
          <div class="bg-red-950/30 px-5 py-3 border-b border-red-900/40">
            <h2 class="text-sm font-semibold text-red-400">Gefahrenzone</h2>
          </div>
          <div class="bg-[#1a1a1a] px-5 py-4 flex items-center justify-between gap-4">
            <div>
              <p class="text-sm font-semibold text-white">Profil löschen</p>
              <p class="text-xs text-gray-500 mt-0.5">Löscht dein Profil, alle Medien und beendet aktive Abonnements. Nicht rückgängig zu machen.</p>
            </div>
            <button @click="showDeleteConfirm = true"
              class="shrink-0 border border-red-700/50 text-red-400 text-sm font-semibold px-4 py-2 rounded-lg hover:bg-red-950/40 transition whitespace-nowrap">
              Profil löschen
            </button>
          </div>
        </div>

      </div>
    </div>

    <!-- Lösch-Bestätigungsmodal -->
    <Teleport to="body">
      <div v-if="showDeleteConfirm"
        class="fixed inset-0 bg-black/70 flex items-center justify-center z-50 px-4"
        @click.self="showDeleteConfirm = false">
        <div class="bg-[#1a1a1a] border border-white/10 rounded-2xl shadow-2xl w-full max-w-md p-6">
          <div class="flex items-center gap-3 mb-4">
            <div class="w-10 h-10 rounded-full bg-red-900/40 flex items-center justify-center shrink-0">
              <svg class="w-5 h-5 text-red-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/>
              </svg>
            </div>
            <h3 class="text-lg font-bold text-white">Profil wirklich löschen?</h3>
          </div>

          <p class="text-sm text-gray-400 mb-3">Folgendes wird <strong class="text-white">unwiderruflich</strong> gelöscht:</p>
          <ul class="text-sm text-gray-400 space-y-1 mb-5 ml-4 list-disc">
            <li>Dein Profil und alle Profilinformationen</li>
            <li>Alle hochgeladenen Fotos und Medien</li>
            <li>Alle aktiven Abonnements deiner Kunden</li>
            <li>Alle Bewertungen und Nachrichten</li>
          </ul>

          <p class="text-sm text-gray-300 mb-2 font-medium">Gib zur Bestätigung <span class="font-bold text-red-400">LÖSCHEN</span> ein:</p>
          <input v-model="deleteConfirmText" type="text" placeholder="LÖSCHEN"
            class="w-full border border-white/10 bg-[#111] text-white rounded-lg px-3 py-2 text-sm mb-4 focus:outline-none focus:border-red-500 placeholder-gray-600" />

          <div class="flex gap-3">
            <button @click="showDeleteConfirm = false; deleteConfirmText = ''"
              class="flex-1 border border-white/10 text-gray-400 text-sm font-semibold py-2.5 rounded-lg hover:bg-white/5 transition">
              Abbrechen
            </button>
            <button @click="deleteProfile"
              :disabled="deleteConfirmText !== 'LÖSCHEN' || deleting"
              class="flex-1 bg-red-700 hover:bg-red-800 disabled:opacity-40 disabled:cursor-not-allowed text-white text-sm font-bold py-2.5 rounded-lg transition">
              {{ deleting ? 'Wird gelöscht…' : 'Endgültig löschen' }}
            </button>
          </div>
        </div>
      </div>
    </Teleport>
  </AppLayout>
</template>

<script setup>
import { Head, Link, router, useForm } from '@inertiajs/vue3';
import { ref, computed, defineComponent, h } from 'vue';
import AppLayout from '@/Layouts/AppLayout.vue';

const props = defineProps({
  profile: Object,
  stats:   Object,
});

// ── Verification ──────────────────────────────────────────────────────────────
const verifyForm    = useForm({ photo: null });
const showResubmit  = ref(false);

function submitVerification() {
  verifyForm.post(route('inserat.verification.store'), {
    forceFormData: true,
    onSuccess: () => {
      verifyForm.reset();
      showResubmit.value = false;
    },
  });
}

const verificationBadgeClass = computed(() => {
  const colors = {
    unverified: 'bg-gray-800 text-gray-400',
    pending:    'bg-blue-900/50 text-blue-400',
    approved:   'bg-green-900/50 text-green-400',
    rejected:   'bg-red-900/50 text-red-400',
  };
  return `text-xs font-semibold px-2.5 py-0.5 rounded-full ${colors[props.profile?.verification_status] ?? colors.unverified}`;
});

const verificationBadgeLabel = computed(() => ({
  unverified: 'Nicht beantragt',
  pending:    '⏳ Ausstehend',
  approved:   '✓ Verifiziert',
  rejected:   '✗ Abgelehnt',
}[props.profile?.verification_status] ?? 'Nicht beantragt'));

// Inline upload form component
const VerificationUploadForm = defineComponent({
  props: { form: Object },
  emits: ['submit'],
  setup(props, { emit }) {
    return () => h('div', { class: 'space-y-3' }, [
      h('div', [
        h('label', { class: 'block text-xs font-medium text-gray-400 mb-1.5' }, 'Selfie mit Schild hochladen (JPG / PNG, max. 15 MB)'),
        h('input', {
          type: 'file',
          accept: 'image/jpeg,image/jpg,image/png',
          class: 'block w-full text-sm text-gray-400 file:mr-3 file:py-2 file:px-4 file:rounded-lg file:border-0 file:bg-[#e35d8f] file:text-white file:text-sm file:font-semibold hover:file:bg-[#c44a7a] cursor-pointer',
          onChange: (e) => { props.form.photo = e.target.files[0]; },
        }),
        props.form.errors?.photo
          ? h('p', { class: 'mt-1 text-xs text-red-400' }, props.form.errors.photo)
          : null,
      ]),
      h('button', {
        type: 'button',
        disabled: !props.form.photo || props.form.processing,
        class: 'bg-[#e35d8f] hover:bg-[#c44a7a] disabled:opacity-50 text-white text-sm font-bold px-5 py-2.5 rounded-lg transition',
        onClick: () => emit('submit'),
      }, props.form.processing ? 'Wird hochgeladen…' : 'Foto einreichen'),
    ]);
  },
});

// ── Stats ─────────────────────────────────────────────────────────────────────
const statCards = computed(() => props.stats ? [
  { label: 'Profilaufrufe',  value: props.stats.views },
  { label: 'Abonnenten',     value: props.stats.subscribers },
  { label: 'Medien',         value: props.stats.mediaCount },
  { label: 'Status',         value: props.stats.isActive ? 'Aktiv' : 'Inaktiv' },
] : []);

// ── Reactivate (free listings) ────────────────────────────────────────────────
const reactivating = ref(false);
function reactivate() {
  reactivating.value = true;
  router.post(route('inserat.reactivate'), {}, { onFinish: () => { reactivating.value = false; } });
}

// ── Push ──────────────────────────────────────────────────────────────────────
const pushing = ref(false);
function push() {
  pushing.value = true;
  router.post(route('inserat.push'), {}, { onFinish: () => { pushing.value = false; } });
}

// ── Delete ────────────────────────────────────────────────────────────────────
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

// ── Quick actions ─────────────────────────────────────────────────────────────
const quickActions = [
  { icon: '✏️', label: 'Profil bearbeiten', desc: 'Texte und Angaben ändern', href: route('inserat.profile.edit') },
  { icon: '🖼️', label: 'Medien verwalten',  desc: 'Fotos hochladen',          href: route('inserat.media.index') },
  { icon: '💬', label: 'Nachrichten',        desc: 'Postfach öffnen',          href: route('inserat.messages') },
];
</script>
