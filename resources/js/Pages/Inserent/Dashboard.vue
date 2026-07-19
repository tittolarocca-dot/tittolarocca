<template>
  <AppLayout>
    <Head :title="t('dashboard.my_listing')" />

    <div class="max-w-5xl mx-auto px-4 py-8">
      <h1 class="text-2xl font-bold text-white mb-6">{{ t('dashboard.my_listing') }}</h1>

      <!-- Kein Profil -->
      <div v-if="!profile" class="bg-[#1a1a1a] rounded-xl border border-dashed border-[#e35d8f]/40 p-10 text-center shadow-sm">
        <div class="text-5xl mb-4">📋</div>
        <h2 class="text-lg font-semibold text-white mb-2">{{ t('dashboard.no_listing_title') }}</h2>
        <p class="text-gray-400 text-sm mb-6">{{ t('dashboard.no_listing_desc') }}</p>
        <Link :href="route('inserat.profile.edit')"
          class="inline-flex items-center gap-2 bg-[#e35d8f] text-white px-5 py-2.5 rounded-lg text-sm font-bold hover:bg-[#c44a7a] transition">
          {{ t('dashboard.create_btn') }}
        </Link>
      </div>

      <!-- Profil vorhanden -->
      <div v-else class="space-y-6">

        <!-- Schnellzugriff -->
        <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
          <Link v-for="action in quickActions" :key="action.label"
            :href="action.href"
            class="bg-[#1a1a1a] rounded-xl border border-white/8 p-4 flex items-center gap-3 hover:border-[#e35d8f]/40 transition group shadow-sm">
            <span v-if="action.footprint" class="w-8 h-8 shrink-0 flex items-center justify-center">
              <svg class="w-7 h-7 text-[#e35d8f]" viewBox="0 0 64 64" fill="currentColor">
                <ellipse cx="22" cy="14" rx="7" ry="9" transform="rotate(-15 22 14)"/>
                <ellipse cx="36" cy="10" rx="4.5" ry="6" transform="rotate(10 36 10)"/>
                <ellipse cx="47" cy="16" rx="4" ry="5.5" transform="rotate(20 47 16)"/>
                <ellipse cx="54" cy="25" rx="3.5" ry="5" transform="rotate(30 54 25)"/>
                <path d="M10 35 Q14 22 28 26 Q38 30 34 44 Q30 56 18 52 Q8 48 10 35z"/>
                <ellipse cx="42" cy="44" rx="7" ry="9" transform="rotate(15 42 44)"/>
                <ellipse cx="28" cy="48" rx="4.5" ry="6" transform="rotate(-10 28 48)"/>
                <ellipse cx="17" cy="42" rx="4" ry="5.5" transform="rotate(-20 17 42)"/>
                <ellipse cx="10" cy="33" rx="3.5" ry="5" transform="rotate(-30 10 33)"/>
                <path d="M54 29 Q50 42 36 38 Q26 34 30 20 Q34 8 46 12 Q56 16 54 29z"/>
              </svg>
            </span>
            <span v-else class="text-2xl">{{ action.icon }}</span>
            <div class="min-w-0">
              <p class="text-sm font-semibold text-white group-hover:text-[#e35d8f] transition leading-tight">{{ action.label }}</p>
              <p class="text-xs text-gray-500 mt-0.5">{{ action.desc }}</p>
            </div>
          </Link>
        </div>

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
                {{ stats.isActive ? t('dashboard.listing_active') : t('dashboard.listing_expired') }}
              </p>
              <p class="text-xs mt-0.5" :class="stats.isActive ? 'text-green-500' : 'text-yellow-500'">
                {{ stats.isActive
                  ? t('dashboard.expires_at', { date: stats.expiresAt })
                  : stats.isFreeProfile
                    ? t('dashboard.free_expired_msg')
                    : t('dashboard.buy_expired_msg') }}
              </p>
              <p class="text-xs text-gray-500 mt-0.5">{{ t('dashboard.created_at_label', { date: stats.createdAt }) }}</p>
            </div>
          </div>
          <!-- Aktiv: Verlängern -->
          <Link v-if="stats.isActive" :href="route('inserat.package.select')"
            class="shrink-0 border border-green-600 text-green-400 text-xs font-semibold px-4 py-2 rounded-lg hover:bg-green-900/30 transition">
            {{ t('dashboard.extend_btn') }}
          </Link>
          <!-- Abgelaufen + kostenlos: gratis reaktivieren -->
          <form v-else-if="stats.isFreeProfile" @submit.prevent="reactivate">
            <button type="submit" :disabled="reactivating"
              class="shrink-0 bg-[#e35d8f] hover:bg-[#c44a7a] disabled:opacity-50 text-white text-xs font-bold px-4 py-2 rounded-lg transition whitespace-nowrap">
              {{ reactivating ? t('dashboard.reactivating') : t('dashboard.reactivate_btn') }}
            </button>
          </form>
          <!-- Abgelaufen + bezahlt: Paket kaufen -->
          <Link v-else :href="route('inserat.package.select')"
            class="shrink-0 bg-[#e35d8f] text-white text-xs font-bold px-4 py-2 rounded-lg hover:bg-[#c44a7a] transition">
            {{ t('dashboard.buy_package_btn') }}
          </Link>
        </div>

        <!-- Launch-Angebot: Credits-Übersicht -->
        <div v-if="launchMode" class="bg-gradient-to-br from-[#e35d8f]/15 to-[#7c3aed]/10 rounded-xl border border-[#e35d8f]/30 p-5 shadow-sm">
          <div class="flex items-center gap-3">
            <span class="text-3xl">🎁</span>
            <div class="min-w-0">
              <p class="font-bold text-white text-sm">{{ t('dashboard.launch_offer_title') }}</p>
              <p class="text-xs text-gray-300 mt-0.5">{{ t('dashboard.launch_offer_text') }}</p>
            </div>
          </div>
          <div class="mt-3 flex items-center gap-2">
            <span class="text-xs text-gray-400">{{ t('dashboard.your_launch_credits') }}</span>
            <span class="text-lg font-black text-[#e35d8f]">{{ credits }}</span>
          </div>
        </div>

        <!-- Push-Karte -->
        <div v-if="stats.isActive" class="bg-[#1a1a1a] rounded-xl border border-white/8 p-5 shadow-sm flex items-center justify-between gap-4">
          <div class="flex items-center gap-3">
            <span class="text-3xl">🚀</span>
            <div>
              <p class="font-semibold text-white text-sm">{{ t('dashboard.push_title') }}</p>
              <p class="text-xs text-gray-500 mt-0.5">{{ t('dashboard.push_desc') }}</p>
              <p v-if="stats.pushedAt" class="text-xs text-gray-600 mt-0.5">{{ t('dashboard.push_last', { date: stats.pushedAt }) }}</p>
              <!-- Launch-Modus: Credit-Kosten + Rest anzeigen -->
              <template v-if="launchMode">
                <p class="text-xs text-gray-400 mt-1">{{ t('dashboard.push_cost', { cost: pushCost }) }}</p>
                <p class="text-xs" :class="credits >= pushCost ? 'text-gray-500' : 'text-red-400'">
                  {{ credits >= pushCost ? t('dashboard.push_remaining', { count: credits }) : t('dashboard.push_no_credits') }}
                </p>
              </template>
            </div>
          </div>
          <form @submit.prevent="push">
            <button type="submit" :disabled="pushing || (launchMode && credits < pushCost)"
              class="shrink-0 bg-[#e35d8f] hover:bg-[#c44a7a] disabled:opacity-50 disabled:cursor-not-allowed text-white text-sm font-bold px-5 py-2.5 rounded-lg transition whitespace-nowrap">
              {{ pushing ? t('dashboard.redirecting') : (launchMode ? t('dashboard.push_free_btn') : t('dashboard.push_btn')) }}
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

        <!-- Foto-Verifizierung -->
        <div class="bg-[#1a1a1a] rounded-xl border border-white/8 overflow-hidden shadow-sm">
          <div class="bg-[#111] border-b border-white/8 px-5 py-3 flex items-center justify-between">
            <h2 class="text-sm font-semibold text-white flex items-center gap-2">
              📷 {{ t('dashboard.photo_verif_title') }}
            </h2>
            <span :class="verificationBadgeClass">{{ verificationBadgeLabel }}</span>
          </div>

          <p class="px-5 pt-4 text-xs text-gray-400">{{ t('dashboard.photo_verif_desc') }}</p>

          <!-- Genehmigt -->
          <div v-if="profile.verification_status === 'approved'" class="p-5 flex items-center gap-3">
            <div class="w-10 h-10 rounded-full bg-green-900/40 border border-green-600/40 flex items-center justify-center shrink-0 text-xl">✅</div>
            <div>
              <p class="font-semibold text-white text-sm">{{ t('dashboard.v_approved_title') }}</p>
              <p class="text-xs text-gray-400 mt-0.5">{{ t('dashboard.v_approved_desc') }}</p>
            </div>
          </div>

          <!-- Ausstehend -->
          <div v-else-if="profile.verification_status === 'pending'" class="p-5 space-y-4">
            <div class="flex items-center gap-3">
              <div class="w-10 h-10 rounded-full bg-blue-900/40 border border-blue-600/40 flex items-center justify-center shrink-0 text-xl">⏳</div>
              <div>
                <p class="font-semibold text-white text-sm">{{ t('dashboard.v_pending_title') }}</p>
                <p class="text-xs text-gray-400 mt-0.5">{{ t('dashboard.v_pending_desc') }}</p>
              </div>
            </div>
            <div>
              <button @click="showResubmit = !showResubmit"
                class="text-xs text-gray-500 hover:text-gray-300 underline transition">
                {{ showResubmit ? t('dashboard.v_cancel') : t('dashboard.v_resubmit') }}
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
              <strong class="text-red-300">{{ t('dashboard.v_rejected_prefix') }}</strong> {{ profile.verification_rejected_reason }}
            </div>

            <!-- Anleitung -->
            <div class="bg-amber-950/30 border border-amber-700/30 rounded-lg p-4">
              <p class="text-sm font-semibold text-amber-300 mb-2">{{ t('dashboard.v_guide_title') }}</p>
              <ol class="text-sm text-amber-400/80 space-y-1.5 list-decimal list-inside">
                <li>{{ t('dashboard.v_step1') }}</li>
                <li>{{ t('dashboard.v_step2') }}
                  <ul class="ml-5 mt-1 list-disc text-xs space-y-0.5 text-amber-400/70">
                    <li><strong class="text-amber-300">{{ t('dashboard.v_step2a') }}</strong></li>
                    <li>{{ t('dashboard.v_step2b', { name: profile.display_name }) }}</li>
                    <li>{{ t('dashboard.v_step2c') }}</li>
                  </ul>
                </li>
                <li>{{ t('dashboard.v_step3') }}</li>
              </ol>
            </div>

            <VerificationUploadForm :form="verifyForm" @submit="submitVerification" />
          </div>
        </div>

        <!-- Identität & Alter verifizieren (Veriff) -->
        <div class="bg-[#1a1a1a] rounded-xl border border-white/8 overflow-hidden shadow-sm">
          <div class="bg-[#111] border-b border-white/8 px-5 py-3 flex items-center justify-between">
            <h2 class="text-sm font-semibold text-white flex items-center gap-2">
              🪪 {{ t('dashboard.id_verif_title') }}
            </h2>
            <span :class="identityBadgeClass">{{ identityBadgeLabel }}</span>
          </div>

          <div class="p-5 space-y-4">
            <p class="text-sm text-gray-400">{{ t('dashboard.id_verif_desc') }}</p>

            <!-- Verifiziert -->
            <div v-if="profile.identity_verification_status === 'approved'" class="flex items-center gap-3">
              <div class="w-10 h-10 rounded-full bg-green-900/40 border border-green-600/40 flex items-center justify-center shrink-0 text-xl">✅</div>
              <div>
                <p class="font-semibold text-white text-sm">{{ t('dashboard.id_approved_title') }}</p>
                <p class="text-xs text-gray-400 mt-0.5">{{ t('dashboard.id_approved_desc') }}</p>
              </div>
            </div>

            <!-- In Prüfung -->
            <div v-else-if="profile.identity_verification_status === 'pending'" class="flex items-center gap-3">
              <div class="w-10 h-10 rounded-full bg-blue-900/40 border border-blue-600/40 flex items-center justify-center shrink-0 text-xl">⏳</div>
              <div>
                <p class="font-semibold text-white text-sm">{{ t('dashboard.id_pending_title') }}</p>
                <p class="text-xs text-gray-400 mt-0.5">{{ t('dashboard.id_pending_desc') }}</p>
              </div>
            </div>

            <!-- Nicht beantragt / abgelehnt -->
            <template v-else>
              <div v-if="profile.identity_verification_status === 'rejected' && profile.identity_rejected_reason"
                class="bg-red-950/40 border border-red-700/40 rounded-lg p-3 text-sm text-red-400">
                <strong class="text-red-300">{{ t('dashboard.id_rejected_prefix') }}</strong> {{ profile.identity_rejected_reason }}
              </div>
              <button @click="startVeriff" :disabled="startingVeriff"
                class="inline-flex items-center gap-2 bg-[#e35d8f] hover:bg-[#c44a7a] disabled:opacity-50 text-white text-sm font-bold px-5 py-3 rounded-xl transition shadow-lg shadow-[#e35d8f]/20">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                {{ startingVeriff ? '…' : t('dashboard.id_verif_button') }}
              </button>
            </template>
          </div>
        </div>

        <!-- Gefahrenzone -->
        <div class="border border-red-900/40 rounded-xl overflow-hidden">
          <div class="bg-red-950/30 px-5 py-3 border-b border-red-900/40">
            <h2 class="text-sm font-semibold text-red-400">{{ t('dashboard.danger_zone') }}</h2>
          </div>

          <!-- Profil deaktivieren -->
          <div class="bg-[#1a1a1a] px-5 py-4 flex items-center justify-between gap-4 border-b border-red-900/20">
            <div>
              <p class="text-sm font-semibold text-white">{{ t('dashboard.deactivate_title') }}</p>
              <p class="text-xs text-gray-500 mt-0.5">{{ t('dashboard.deactivate_desc') }}</p>
            </div>
            <button @click="deactivateProfile"
              :disabled="deactivating || !stats.isActive"
              class="shrink-0 border border-orange-700/50 text-orange-400 text-sm font-semibold px-4 py-2 rounded-lg hover:bg-orange-950/40 disabled:opacity-40 disabled:cursor-not-allowed transition whitespace-nowrap">
              {{ deactivating ? t('dashboard.deactivating') : stats.isActive ? t('dashboard.deactivate_btn') : t('dashboard.already_inactive') }}
            </button>
          </div>

          <!-- Profil löschen -->
          <div class="bg-[#1a1a1a] px-5 py-4 flex items-center justify-between gap-4">
            <div>
              <p class="text-sm font-semibold text-white">{{ t('dashboard.delete_title') }}</p>
              <p class="text-xs text-gray-500 mt-0.5">{{ t('dashboard.delete_desc') }}</p>
            </div>
            <button @click="showDeleteConfirm = true"
              class="shrink-0 border border-red-700/50 text-red-400 text-sm font-semibold px-4 py-2 rounded-lg hover:bg-red-950/40 transition whitespace-nowrap">
              {{ t('dashboard.delete_btn') }}
            </button>
          </div>
        </div>

      </div>
    </div>

    <!-- Delete confirm modal -->
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
            <h3 class="text-lg font-bold text-white">{{ t('dashboard.delete_modal_title') }}</h3>
          </div>

          <p class="text-sm text-gray-400 mb-3">{{ t('dashboard.delete_irr') }}</p>
          <ul class="text-sm text-gray-400 space-y-1 mb-5 ml-4 list-disc">
            <li>{{ t('dashboard.delete_i1') }}</li>
            <li>{{ t('dashboard.delete_i2') }}</li>
            <li>{{ t('dashboard.delete_i3') }}</li>
            <li>{{ t('dashboard.delete_i4') }}</li>
          </ul>

          <p class="text-sm text-gray-300 mb-2 font-medium">{{ t('dashboard.delete_confirm_hint') }} <span class="font-bold text-red-400">{{ t('dashboard.delete_confirm_word') }}</span></p>
          <input v-model="deleteConfirmText" type="text" :placeholder="t('dashboard.delete_confirm_word')"
            class="w-full border border-white/10 bg-[#111] text-white rounded-lg px-3 py-2 text-sm mb-4 focus:outline-none focus:border-red-500 placeholder-gray-600" />

          <div class="flex gap-3">
            <button @click="showDeleteConfirm = false; deleteConfirmText = ''"
              class="flex-1 border border-white/10 text-gray-400 text-sm font-semibold py-2.5 rounded-lg hover:bg-white/5 transition">
              {{ t('dashboard.delete_cancel') }}
            </button>
            <button @click="deleteProfile"
              :disabled="deleteConfirmText !== t('dashboard.delete_confirm_word') || deleting"
              class="flex-1 bg-red-700 hover:bg-red-800 disabled:opacity-40 disabled:cursor-not-allowed text-white text-sm font-bold py-2.5 rounded-lg transition">
              {{ deleting ? t('dashboard.deleting') : t('dashboard.delete_final') }}
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
import { useI18n } from '@/composables/useI18n';

const { t } = useI18n();

const props = defineProps({
  profile:    Object,
  stats:      Object,
  launchMode: { type: Boolean, default: false },
  credits:    { type: Number,  default: 0 },
  pushCost:   { type: Number,  default: 1 },
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
  unverified: t('dashboard.v_unverified'),
  pending:    t('dashboard.v_pending_badge'),
  approved:   t('dashboard.v_approved_badge'),
  rejected:   t('dashboard.v_rejected_badge'),
}[props.profile?.verification_status] ?? t('dashboard.v_unverified')));

// ── Veriff (Identität & Alter) ────────────────────────────────────────────────
const startingVeriff = ref(false);

function startVeriff() {
  startingVeriff.value = true;
  router.post(route('inserat.veriff.start'), {}, {
    onFinish: () => { startingVeriff.value = false; },
  });
}

const identityBadgeClass = computed(() => {
  const colors = {
    pending:  'bg-blue-900/50 text-blue-400',
    approved: 'bg-green-900/50 text-green-400',
    rejected: 'bg-red-900/50 text-red-400',
  };
  return `text-xs font-semibold px-2.5 py-0.5 rounded-full ${colors[props.profile?.identity_verification_status] ?? 'bg-gray-800 text-gray-400'}`;
});

const identityBadgeLabel = computed(() => ({
  pending:  t('dashboard.id_status_pending'),
  approved: t('dashboard.id_status_approved'),
  rejected: t('dashboard.id_status_rejected'),
}[props.profile?.identity_verification_status] ?? t('dashboard.id_status_none')));

// Inline upload form component
const VerificationUploadForm = defineComponent({
  props: { form: Object },
  emits: ['submit'],
  setup(props, { emit }) {
    return () => h('div', { class: 'space-y-3' }, [
      h('div', [
        h('label', { class: 'block text-xs font-medium text-gray-400 mb-1.5' }, t('dashboard.v_upload_label')),
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
      }, props.form.processing ? t('dashboard.v_uploading') : t('dashboard.v_submit')),
    ]);
  },
});

// ── Stats ─────────────────────────────────────────────────────────────────────
const statCards = computed(() => props.stats ? [
  { label: t('dashboard.stat_views'),       value: props.stats.views },
  { label: t('dashboard.stat_subscribers'), value: props.stats.subscribers },
  { label: t('dashboard.stat_media'),       value: props.stats.mediaCount },
  { label: t('dashboard.stat_visitors'),    value: props.stats.visitorsCount },
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

// ── Deactivate ────────────────────────────────────────────────────────────────
const deactivating = ref(false);
function deactivateProfile() {
  deactivating.value = true;
  router.post(route('inserat.profile.deactivate'), {}, { onFinish: () => { deactivating.value = false; } });
}

// ── Delete ────────────────────────────────────────────────────────────────────
const showDeleteConfirm = ref(false);
const deleteConfirmText = ref('');
const deleting          = ref(false);

function deleteProfile() {
  if (deleteConfirmText.value !== t('dashboard.delete_confirm_word')) return;
  deleting.value = true;
  router.delete(route('inserat.profile.destroy'), {
    onFinish: () => { deleting.value = false; },
  });
}

// ── Quick actions ─────────────────────────────────────────────────────────────
const quickActions = computed(() => [
  { icon: '✏️', label: t('dashboard.action_edit'),     desc: t('dashboard.action_edit_desc'),     href: route('inserat.profile.edit'),  footprint: false },
  { icon: '🖼️', label: t('dashboard.action_media'),    desc: t('dashboard.action_media_desc'),    href: route('inserat.media.index'),   footprint: false },
  { icon: '💬', label: t('dashboard.action_messages'), desc: t('dashboard.action_msg_desc'),      href: route('inserat.messages'),      footprint: false },
  { icon: null, label: t('dashboard.action_visitors'), desc: t('dashboard.action_visitors_desc'), href: route('inserat.visitors'),      footprint: true  },
]);
</script>
