<template>
  <DashboardLayout role="inserent">
    <Head :title="t('dashboard.photo_verif_title')" />

    <div class="space-y-5">
      <div>
        <h1 class="text-2xl font-black text-white">{{ t('dashboard.photo_verif_title') }}</h1>
        <p class="text-gray-400 text-sm mt-1">{{ t('dashboard.photo_verif_desc') }}</p>
      </div>

      <!-- Foto-Verifizierung -->
      <div class="bg-[#1a1a1a] rounded-2xl border border-white/8 overflow-hidden">
        <div class="bg-[#111] border-b border-white/8 px-5 py-3 flex items-center justify-between">
          <h2 class="text-sm font-semibold text-white flex items-center gap-2">📷 {{ t('dashboard.photo_verif_title') }}</h2>
          <span :class="verificationBadgeClass">{{ verificationBadgeLabel }}</span>
        </div>

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

      <!-- Identität & Alter (Veriff) -->
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
    </div>
  </DashboardLayout>
</template>

<script setup>
import { Head, router, useForm } from '@inertiajs/vue3';
import { ref, computed, defineComponent, h } from 'vue';
import DashboardLayout from '@/Layouts/DashboardLayout.vue';
import { useI18n } from '@/composables/useI18n';

const { t } = useI18n();

const props = defineProps({ profile: Object });

const verifyForm   = useForm({ photo: null });
const showResubmit = ref(false);

function submitVerification() {
  verifyForm.post(route('inserat.verification.store'), {
    forceFormData: true,
    onSuccess: () => { verifyForm.reset(); showResubmit.value = false; },
  });
}

const verificationBadgeClass = computed(() => {
  const colors = { unverified: 'bg-gray-800 text-gray-400', pending: 'bg-blue-900/50 text-blue-400', approved: 'bg-green-900/50 text-green-400', rejected: 'bg-red-900/50 text-red-400' };
  return `text-xs font-semibold px-2.5 py-0.5 rounded-full ${colors[props.profile?.verification_status] ?? colors.unverified}`;
});
const verificationBadgeLabel = computed(() => ({ unverified: t('dashboard.v_unverified'), pending: t('dashboard.v_pending_badge'), approved: t('dashboard.v_approved_badge'), rejected: t('dashboard.v_rejected_badge') }[props.profile?.verification_status] ?? t('dashboard.v_unverified')));

const startingVeriff = ref(false);
function startVeriff() {
  startingVeriff.value = true;
  router.post(route('inserat.veriff.start'), {}, { onFinish: () => { startingVeriff.value = false; } });
}

const identityBadgeClass = computed(() => {
  const colors = { pending: 'bg-blue-900/50 text-blue-400', approved: 'bg-green-900/50 text-green-400', rejected: 'bg-red-900/50 text-red-400' };
  return `text-xs font-semibold px-2.5 py-0.5 rounded-full ${colors[props.profile?.identity_verification_status] ?? 'bg-gray-800 text-gray-400'}`;
});
const identityBadgeLabel = computed(() => ({ pending: t('dashboard.id_status_pending'), approved: t('dashboard.id_status_approved'), rejected: t('dashboard.id_status_rejected') }[props.profile?.identity_verification_status] ?? t('dashboard.id_status_none')));

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
</script>
