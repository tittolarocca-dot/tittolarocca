<template>
  <DashboardLayout role="inserent" :badges="{ messages: unread, gallery: galleryRequests }">
    <Head :title="t('dashboard.my_listing')" />

    <!-- Kein Profil -->
    <div v-if="!profile" class="bg-[#1a1a1a] rounded-2xl border border-dashed border-[#e35d8f]/40 p-10 text-center">
      <div class="text-5xl mb-4">📋</div>
      <h2 class="text-lg font-semibold text-white mb-2">{{ t('dashboard.no_listing_title') }}</h2>
      <p class="text-gray-400 text-sm mb-6">{{ t('dashboard.no_listing_desc') }}</p>
      <Link :href="route('inserat.profile.edit')" class="inline-flex items-center gap-2 bg-[#e35d8f] text-white px-5 py-2.5 rounded-lg text-sm font-bold hover:bg-[#c44a7a] transition">
        {{ t('dashboard.create_btn') }}
      </Link>
    </div>

    <div v-else class="space-y-5">
      <!-- ── Hero ───────────────────────────────────────────────────────── -->
      <section class="rounded-2xl p-5 sm:p-6 bg-gradient-to-br from-[#e35d8f]/16 to-[#7c3aed]/10 border border-[#e35d8f]/28 flex flex-col lg:flex-row lg:items-center gap-5">
        <div class="flex-1 min-w-0">
          <h1 class="text-2xl font-black text-white">{{ t('dashboard.welcome', { name: $page.props.auth.user.name }) }}</h1>
          <div class="mt-3">
            <span v-if="stats.isActive" class="inline-flex items-center gap-2 text-xs font-bold text-green-400 bg-green-900/30 border border-green-700/40 px-3 py-1.5 rounded-full">
              <span class="w-1.5 h-1.5 rounded-full bg-green-400"></span>{{ t('dashboard.listing_active') }} · {{ t('dashboard.expires_at', { date: stats.expiresAt }) }}
            </span>
            <span v-else class="inline-flex items-center gap-2 text-xs font-bold text-yellow-400 bg-yellow-900/30 border border-yellow-700/40 px-3 py-1.5 rounded-full">
              ⚠️ {{ t('dashboard.listing_expired') }}
            </span>
          </div>
          <p class="text-gray-400 text-sm mt-2.5">{{ t('dashboard.created_at_label', { date: stats.createdAt }) }}</p>
          <div class="flex flex-wrap gap-2.5 mt-4">
            <Link v-if="stats.isActive" :href="route('inserat.package.select')" class="bg-[#e35d8f] hover:bg-[#c44a7a] text-white text-sm font-bold px-4 py-2.5 rounded-xl transition">{{ t('dashboard.extend_btn') }}</Link>
            <button v-else-if="stats.isFreeProfile" @click="reactivate" :disabled="reactivating" class="bg-[#e35d8f] hover:bg-[#c44a7a] disabled:opacity-50 text-white text-sm font-bold px-4 py-2.5 rounded-xl transition">{{ reactivating ? t('dashboard.reactivating') : t('dashboard.reactivate_btn') }}</button>
            <Link v-else :href="route('inserat.package.select')" class="bg-[#e35d8f] hover:bg-[#c44a7a] text-white text-sm font-bold px-4 py-2.5 rounded-xl transition">{{ t('dashboard.buy_package_btn') }}</Link>
            <Link :href="route('inserat.profile.edit')" class="border border-white/10 text-gray-200 hover:bg-white/5 text-sm font-bold px-4 py-2.5 rounded-xl transition">{{ t('dashboard.action_edit') }}</Link>
          </div>
        </div>
        <ProgressRing :percent="completeness" :label="t('dashboard.completeness_label')" class="shrink-0 mx-auto lg:mx-0" />
      </section>

      <!-- ── KPI-Streifen ───────────────────────────────────────────────── -->
      <div class="bg-[#1a1a1a] border border-white/8 rounded-2xl grid grid-cols-2 sm:grid-cols-4 overflow-hidden">
        <div v-for="(stat, i) in statCards" :key="stat.label"
          class="px-4 py-5 text-center border-white/8"
          :class="[i < statCards.length - 1 ? 'sm:border-r' : '', i % 2 === 0 ? 'border-r sm:border-r' : '', i < 2 ? 'border-b sm:border-b-0' : '']">
          <div class="text-2xl font-extrabold text-[#e35d8f] tabular-nums">{{ stat.value }}</div>
          <div class="text-xs text-gray-500 mt-1">{{ stat.label }}</div>
        </div>
      </div>

      <!-- ── Nächste Schritte + Sichtbarkeit ────────────────────────────── -->
      <div class="grid lg:grid-cols-[1.3fr_1fr] gap-5">
        <!-- Tipps -->
        <div class="bg-[#1a1a1a] border border-white/8 rounded-2xl">
          <div class="flex items-center justify-between px-5 py-4 border-b border-white/8">
            <h2 class="text-[15px] font-bold text-white">{{ t('dashboard.next_steps') }}</h2>
            <span class="text-xs text-gray-500">{{ completeness }}%</span>
          </div>
          <div class="px-5 py-2">
            <template v-for="tip in tips" :key="tip.key">
              <component :is="tip.href.startsWith('#') ? 'a' : Link" :href="tip.href"
                class="flex items-start gap-3 py-3 border-b border-white/8 last:border-0 group">
                <span class="w-[30px] h-[30px] rounded-[9px] shrink-0 flex items-center justify-center"
                  :class="tip.done ? 'bg-green-900/35 text-green-400' : 'bg-[#e35d8f]/14 text-[#e35d8f]'">
                  <svg v-if="tip.done" class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.4" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                  <svg v-else class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 5v14M5 12h14"/></svg>
                </span>
                <div class="min-w-0">
                  <div class="text-[13.5px] font-bold text-white">{{ tip.label }}</div>
                  <div class="text-xs text-gray-500 mt-0.5">{{ tip.desc }}</div>
                </div>
                <span class="ml-auto self-center text-xs font-bold whitespace-nowrap" :class="tip.done ? 'text-green-400' : 'text-[#e35d8f]'">
                  {{ tip.done ? t('dashboard.tip_done') : t('dashboard.tip_do') + ' →' }}
                </span>
              </component>
            </template>
          </div>
        </div>

        <!-- Sichtbarkeit / Push -->
        <div v-if="stats.isActive" class="bg-[#1a1a1a] border border-white/8 rounded-2xl p-5 flex flex-col">
          <h2 class="text-[15px] font-bold text-white">{{ t('dashboard.push_title') }}</h2>
          <div v-if="launchMode" class="flex items-baseline gap-2 mt-1 mb-3">
            <span class="text-3xl font-extrabold text-[#e35d8f] tabular-nums">{{ credits }}</span>
            <span class="text-sm text-gray-400">{{ t('dashboard.your_launch_credits') }}</span>
          </div>
          <p class="text-xs text-gray-400 leading-relaxed">{{ t('dashboard.push_desc') }}</p>
          <p v-if="launchMode" class="text-xs mt-1.5" :class="credits >= pushCost ? 'text-gray-500' : 'text-red-400'">
            {{ credits >= pushCost ? t('dashboard.push_cost', { cost: pushCost }) : t('dashboard.push_no_credits') }}
          </p>
          <form @submit.prevent="push" class="mt-auto pt-4">
            <button type="submit" :disabled="pushing || (launchMode && credits < pushCost)"
              class="w-full bg-[#e35d8f] hover:bg-[#c44a7a] disabled:opacity-50 disabled:cursor-not-allowed text-white text-sm font-bold px-5 py-2.5 rounded-xl transition">
              {{ pushing ? t('dashboard.redirecting') : (launchMode ? t('dashboard.push_free_btn') : t('dashboard.push_btn')) }}
            </button>
            <p v-if="stats.pushedAt" class="text-xs text-gray-600 mt-2 text-center">{{ t('dashboard.push_last', { date: stats.pushedAt }) }}</p>
          </form>
        </div>
      </div>

      <!-- ── Foto-Verifizierung ─────────────────────────────────────────── -->
      <div id="verifizierung" class="bg-[#1a1a1a] rounded-2xl border border-white/8 overflow-hidden scroll-mt-20">
        <div class="bg-[#111] border-b border-white/8 px-5 py-3 flex items-center justify-between">
          <h2 class="text-sm font-semibold text-white flex items-center gap-2">📷 {{ t('dashboard.photo_verif_title') }}</h2>
          <span :class="verificationBadgeClass">{{ verificationBadgeLabel }}</span>
        </div>
        <p class="px-5 pt-4 text-xs text-gray-400">{{ t('dashboard.photo_verif_desc') }}</p>

        <div v-if="profile.verification_status === 'approved'" class="p-5 flex items-center gap-3">
          <div class="w-10 h-10 rounded-full bg-green-900/40 border border-green-600/40 flex items-center justify-center shrink-0 text-xl">✅</div>
          <div>
            <p class="font-semibold text-white text-sm">{{ t('dashboard.v_approved_title') }}</p>
            <p class="text-xs text-gray-400 mt-0.5">{{ t('dashboard.v_approved_desc') }}</p>
          </div>
        </div>

        <div v-else-if="profile.verification_status === 'pending'" class="p-5 space-y-4">
          <div class="flex items-center gap-3">
            <div class="w-10 h-10 rounded-full bg-blue-900/40 border border-blue-600/40 flex items-center justify-center shrink-0 text-xl">⏳</div>
            <div>
              <p class="font-semibold text-white text-sm">{{ t('dashboard.v_pending_title') }}</p>
              <p class="text-xs text-gray-400 mt-0.5">{{ t('dashboard.v_pending_desc') }}</p>
            </div>
          </div>
          <div>
            <button @click="showResubmit = !showResubmit" class="text-xs text-gray-500 hover:text-gray-300 underline transition">
              {{ showResubmit ? t('dashboard.v_cancel') : t('dashboard.v_resubmit') }}
            </button>
            <div v-if="showResubmit" class="mt-3"><VerificationUploadForm :form="verifyForm" @submit="submitVerification" /></div>
          </div>
        </div>

        <div v-else class="p-5 space-y-4">
          <div v-if="profile.verification_status === 'rejected' && profile.verification_rejected_reason" class="bg-red-950/40 border border-red-700/40 rounded-lg p-3 text-sm text-red-400">
            <strong class="text-red-300">{{ t('dashboard.v_rejected_prefix') }}</strong> {{ profile.verification_rejected_reason }}
          </div>
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

      <!-- ── Identität & Alter (Veriff) ─────────────────────────────────── -->
      <div v-if="$page.props.features?.veriff" class="bg-[#1a1a1a] rounded-2xl border border-white/8 overflow-hidden">
        <div class="bg-[#111] border-b border-white/8 px-5 py-3 flex items-center justify-between">
          <h2 class="text-sm font-semibold text-white flex items-center gap-2">🪪 {{ t('dashboard.id_verif_title') }}</h2>
          <span :class="identityBadgeClass">{{ identityBadgeLabel }}</span>
        </div>
        <div class="p-5 space-y-4">
          <p class="text-sm text-gray-400">{{ t('dashboard.id_verif_desc') }}</p>
          <div v-if="profile.identity_verification_status === 'approved'" class="flex items-center gap-3">
            <div class="w-10 h-10 rounded-full bg-green-900/40 border border-green-600/40 flex items-center justify-center shrink-0 text-xl">✅</div>
            <div>
              <p class="font-semibold text-white text-sm">{{ t('dashboard.id_approved_title') }}</p>
              <p class="text-xs text-gray-400 mt-0.5">{{ t('dashboard.id_approved_desc') }}</p>
            </div>
          </div>
          <div v-else-if="profile.identity_verification_status === 'pending'" class="flex items-center gap-3">
            <div class="w-10 h-10 rounded-full bg-blue-900/40 border border-blue-600/40 flex items-center justify-center shrink-0 text-xl">⏳</div>
            <div>
              <p class="font-semibold text-white text-sm">{{ t('dashboard.id_pending_title') }}</p>
              <p class="text-xs text-gray-400 mt-0.5">{{ t('dashboard.id_pending_desc') }}</p>
            </div>
          </div>
          <template v-else>
            <div v-if="profile.identity_verification_status === 'rejected' && profile.identity_rejected_reason" class="bg-red-950/40 border border-red-700/40 rounded-lg p-3 text-sm text-red-400">
              <strong class="text-red-300">{{ t('dashboard.id_rejected_prefix') }}</strong> {{ profile.identity_rejected_reason }}
            </div>
            <button @click="startVeriff" :disabled="startingVeriff" class="inline-flex items-center gap-2 bg-[#e35d8f] hover:bg-[#c44a7a] disabled:opacity-50 text-white text-sm font-bold px-5 py-3 rounded-xl transition shadow-lg shadow-[#e35d8f]/20">
              <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
              {{ startingVeriff ? '…' : t('dashboard.id_verif_button') }}
            </button>
          </template>
        </div>
      </div>

      <!-- ── Geoblocking ─────────────────────────────────────────────────── -->
      <div class="bg-[#1a1a1a] rounded-2xl border border-white/8 overflow-hidden">
        <div class="px-5 py-3 border-b border-white/8">
          <h2 class="text-sm font-semibold text-white flex items-center gap-2">🌍 {{ t('dashboard.geo_title') }}</h2>
        </div>
        <div class="p-5 grid sm:grid-cols-2 gap-4">
          <p class="text-xs text-gray-400 leading-relaxed">{{ t('dashboard.geo_desc') }}<br>{{ t('dashboard.geo_max') }}</p>
          <div>
            <select :disabled="blockedCountries.length >= 5" @change="addCountry($event)"
              class="w-full px-3 py-2.5 border border-white/10 rounded-lg text-sm bg-[#111] text-gray-100 focus:outline-none focus:border-[#e35d8f] transition disabled:opacity-50">
              <option value="">{{ blockedCountries.length >= 5 ? t('dashboard.geo_max_reached') : t('dashboard.geo_select') }}</option>
              <option v-for="(name, code) in availableCountries" :key="code" :value="code">{{ name }}</option>
            </select>
            <div v-if="blockedCountries.length" class="flex flex-wrap gap-2 mt-3">
              <span v-for="code in blockedCountries" :key="code" class="inline-flex items-center gap-1.5 bg-[#e35d8f]/10 border border-[#e35d8f]/40 text-[#e35d8f] text-xs font-semibold px-2.5 py-1 rounded-full">
                {{ countries[code] ?? code }}
                <button type="button" @click="removeCountry(code)" class="hover:text-white transition">✕</button>
              </span>
            </div>
            <button type="button" @click="saveCountries" :disabled="savingCountries" class="mt-3 bg-[#e35d8f] hover:bg-[#c44a7a] disabled:opacity-50 text-white text-sm font-bold px-5 py-2 rounded-lg transition">
              {{ savingCountries ? t('dashboard.redirecting') : t('dashboard.geo_save') }}
            </button>
            <span v-if="countriesSaved" class="ml-2 text-xs text-green-400">✓ {{ t('dashboard.geo_saved') }}</span>
          </div>
        </div>
      </div>

      <!-- ── Gefahrenzone ────────────────────────────────────────────────── -->
      <div class="border border-red-900/40 rounded-2xl overflow-hidden">
        <div class="bg-red-950/30 px-5 py-3 border-b border-red-900/40">
          <h2 class="text-sm font-semibold text-red-400">{{ t('dashboard.danger_zone') }}</h2>
        </div>
        <div class="bg-[#1a1a1a] px-5 py-4 flex items-center justify-between gap-4 border-b border-red-900/20">
          <div>
            <p class="text-sm font-semibold text-white">{{ t('dashboard.deactivate_title') }}</p>
            <p class="text-xs text-gray-500 mt-0.5">{{ t('dashboard.deactivate_desc') }}</p>
          </div>
          <button @click="deactivateProfile" :disabled="deactivating || !stats.isActive"
            class="shrink-0 border border-orange-700/50 text-orange-400 text-sm font-semibold px-4 py-2 rounded-lg hover:bg-orange-950/40 disabled:opacity-40 disabled:cursor-not-allowed transition whitespace-nowrap">
            {{ deactivating ? t('dashboard.deactivating') : stats.isActive ? t('dashboard.deactivate_btn') : t('dashboard.already_inactive') }}
          </button>
        </div>
        <div class="bg-[#1a1a1a] px-5 py-4 flex items-center justify-between gap-4">
          <div>
            <p class="text-sm font-semibold text-white">{{ t('dashboard.delete_title') }}</p>
            <p class="text-xs text-gray-500 mt-0.5">{{ t('dashboard.delete_desc') }}</p>
          </div>
          <button @click="showDeleteConfirm = true" class="shrink-0 border border-red-700/50 text-red-400 text-sm font-semibold px-4 py-2 rounded-lg hover:bg-red-950/40 transition whitespace-nowrap">
            {{ t('dashboard.delete_btn') }}
          </button>
        </div>
      </div>
    </div>

    <!-- Delete confirm modal -->
    <Teleport to="body">
      <div v-if="showDeleteConfirm" class="fixed inset-0 bg-black/70 flex items-center justify-center z-50 px-4" @click.self="showDeleteConfirm = false">
        <div class="bg-[#1a1a1a] border border-white/10 rounded-2xl shadow-2xl w-full max-w-md p-6">
          <div class="flex items-center gap-3 mb-4">
            <div class="w-10 h-10 rounded-full bg-red-900/40 flex items-center justify-center shrink-0">
              <svg class="w-5 h-5 text-red-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/></svg>
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
          <input v-model="deleteConfirmText" type="text" :placeholder="t('dashboard.delete_confirm_word')" class="w-full border border-white/10 bg-[#111] text-white rounded-lg px-3 py-2 text-sm mb-4 focus:outline-none focus:border-red-500 placeholder-gray-600" />
          <div class="flex gap-3">
            <button @click="showDeleteConfirm = false; deleteConfirmText = ''" class="flex-1 border border-white/10 text-gray-400 text-sm font-semibold py-2.5 rounded-lg hover:bg-white/5 transition">{{ t('dashboard.delete_cancel') }}</button>
            <button @click="deleteProfile" :disabled="deleteConfirmText !== t('dashboard.delete_confirm_word') || deleting" class="flex-1 bg-red-700 hover:bg-red-800 disabled:opacity-40 disabled:cursor-not-allowed text-white text-sm font-bold py-2.5 rounded-lg transition">{{ deleting ? t('dashboard.deleting') : t('dashboard.delete_final') }}</button>
          </div>
        </div>
      </div>
    </Teleport>
  </DashboardLayout>
</template>

<script setup>
import { Head, Link, router, useForm } from '@inertiajs/vue3';
import { ref, computed, defineComponent, h, onMounted, onUnmounted } from 'vue';
import DashboardLayout from '@/Layouts/DashboardLayout.vue';
import ProgressRing from '@/Components/ProgressRing.vue';
import { useI18n } from '@/composables/useI18n';

const { t } = useI18n();

const props = defineProps({
  profile:        Object,
  stats:          Object,
  completeness:   { type: Number,  default: 0 },
  tips:           { type: Array,   default: () => [] },
  launchMode:     { type: Boolean, default: false },
  credits:        { type: Number,  default: 0 },
  pushCost:       { type: Number,  default: 1 },
  unreadMessages: { type: Number,  default: 0 },
  galleryRequests:{ type: Number,  default: 0 },
  countries:      { type: Object,  default: () => ({}) },
});

// ── Neue-Nachrichten-Badge (Polling alle 20s) ───────────────────────────────
const unread = ref(props.unreadMessages ?? 0);
let unreadTimer = null;
async function refreshUnread() {
  try {
    const res = await fetch(route('inserat.messages.unread'), { headers: { Accept: 'application/json' } });
    if (res.ok) { const d = await res.json(); unread.value = d.count ?? 0; }
  } catch { /* still, ignore */ }
}
onMounted(() => { unreadTimer = setInterval(refreshUnread, 20000); });
onUnmounted(() => { if (unreadTimer) clearInterval(unreadTimer); });

// ── Blockierte Länder (Geoblocking) ─────────────────────────────────────────
const blockedCountries = ref([...(props.profile?.blocked_countries ?? [])]);
const savingCountries  = ref(false);
const countriesSaved   = ref(false);

const availableCountries = computed(() => {
  const out = {};
  for (const [code, name] of Object.entries(props.countries || {})) {
    if (!blockedCountries.value.includes(code)) out[code] = name;
  }
  return out;
});

function addCountry(e) {
  const code = e.target.value;
  e.target.value = '';
  if (code && !blockedCountries.value.includes(code) && blockedCountries.value.length < 5) {
    blockedCountries.value.push(code);
  }
}
function removeCountry(code) {
  blockedCountries.value = blockedCountries.value.filter(c => c !== code);
}
function saveCountries() {
  savingCountries.value = true;
  countriesSaved.value  = false;
  router.post(route('inserat.countries.update'), { blocked_countries: blockedCountries.value }, {
    preserveScroll: true,
    onSuccess: () => { countriesSaved.value = true; setTimeout(() => { countriesSaved.value = false; }, 2500); },
    onFinish:  () => { savingCountries.value = false; },
  });
}

// ── Verification ──────────────────────────────────────────────────────────────
const verifyForm    = useForm({ photo: null });
const showResubmit  = ref(false);

function submitVerification() {
  verifyForm.post(route('inserat.verification.store'), {
    forceFormData: true,
    onSuccess: () => { verifyForm.reset(); showResubmit.value = false; },
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
  router.post(route('inserat.veriff.start'), {}, { onFinish: () => { startingVeriff.value = false; } });
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
        props.form.errors?.photo ? h('p', { class: 'mt-1 text-xs text-red-400' }, props.form.errors.photo) : null,
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

// ── Reactivate / Push / Deactivate / Delete ───────────────────────────────────
const reactivating = ref(false);
function reactivate() { reactivating.value = true; router.post(route('inserat.reactivate'), {}, { onFinish: () => { reactivating.value = false; } }); }

const pushing = ref(false);
function push() { pushing.value = true; router.post(route('inserat.push'), {}, { onFinish: () => { pushing.value = false; } }); }

const deactivating = ref(false);
function deactivateProfile() { deactivating.value = true; router.post(route('inserat.profile.deactivate'), {}, { onFinish: () => { deactivating.value = false; } }); }

const showDeleteConfirm = ref(false);
const deleteConfirmText = ref('');
const deleting          = ref(false);
function deleteProfile() {
  if (deleteConfirmText.value !== t('dashboard.delete_confirm_word')) return;
  deleting.value = true;
  router.delete(route('inserat.profile.destroy'), { onFinish: () => { deleting.value = false; } });
}
</script>
