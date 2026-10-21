<template>
  <DashboardLayout role="inserent">
    <Head :title="t('age.title')" />

    <div class="space-y-5">
      <!-- Kopf -->
      <div>
        <h1 class="text-2xl font-black text-white flex items-center gap-2">
          <svg class="w-6 h-6 text-[#e35d8f]" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 3l7 3v6c0 4.5-3 7.5-7 9-4-1.5-7-4.5-7-9V6z"/><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4"/></svg>
          {{ t('age.title') }}
        </h1>
        <p class="text-gray-400 text-sm mt-2 leading-relaxed">{{ t('age.intro') }}</p>
      </div>

      <!-- Info-Box: Du benötigst -->
      <div class="bg-[#e35d8f]/8 border border-[#e35d8f]/25 rounded-2xl p-4">
        <p class="text-sm font-semibold text-white mb-2">{{ t('age.need_title') }}</p>
        <ul class="text-sm text-gray-300 space-y-1.5">
          <li class="flex items-start gap-2"><span class="text-[#e35d8f] mt-0.5">•</span>{{ t('age.need_doc') }}</li>
          <li class="flex items-start gap-2"><span class="text-[#e35d8f] mt-0.5">•</span>{{ t('age.need_video') }}</li>
        </ul>
      </div>

      <!-- Status: verified -->
      <div v-if="status === 'verified'" class="bg-[#1a1a1a] rounded-2xl border border-green-600/30 overflow-hidden">
        <div class="p-5 flex items-center gap-3">
          <div class="w-11 h-11 rounded-full bg-green-900/40 border border-green-600/40 flex items-center justify-center shrink-0 text-xl">✅</div>
          <div>
            <p class="font-bold text-white text-sm flex items-center gap-2">
              <span class="inline-flex items-center gap-1 text-xs font-bold px-2.5 py-0.5 rounded-full bg-green-900/50 text-green-400">{{ t('age.badge_verified') }}</span>
            </p>
            <p class="text-xs text-gray-400 mt-1">{{ t('age.status_verified_desc') }}</p>
          </div>
        </div>
      </div>

      <!-- Status: pending -->
      <div v-else-if="status === 'pending'" class="bg-[#1a1a1a] rounded-2xl border border-blue-600/25 overflow-hidden">
        <div class="p-5 flex items-center gap-3">
          <div class="w-11 h-11 rounded-full bg-blue-900/40 border border-blue-600/40 flex items-center justify-center shrink-0 text-xl">⏳</div>
          <div>
            <p class="font-bold text-white text-sm">{{ t('age.status_pending_title') }}</p>
            <p class="text-xs text-gray-400 mt-1">{{ t('age.status_pending_desc') }}</p>
            <p v-if="av.submitted_at" class="text-[11px] text-gray-600 mt-1.5">{{ av.submitted_at }}</p>
          </div>
        </div>
      </div>

      <!-- Formular: not_submitted ODER rejected -->
      <template v-else>
        <!-- Ablehnungsgrund -->
        <div v-if="status === 'rejected' && av.rejection_reason" class="bg-red-950/40 border border-red-700/40 rounded-2xl p-4 text-sm text-red-400">
          <strong class="text-red-300">{{ t('age.status_rejected_prefix') }}</strong> {{ av.rejection_reason }}
        </div>

        <!-- Persönliche Angaben -->
        <div class="bg-[#1a1a1a] rounded-2xl border border-white/8 overflow-hidden">
          <div class="bg-[#111] border-b border-white/8 px-5 py-3">
            <h2 class="text-sm font-semibold text-white">{{ t('age.personal_title') }}</h2>
          </div>
          <div class="p-5 grid sm:grid-cols-2 gap-4">
            <div>
              <label class="block text-xs font-medium text-gray-400 mb-1.5">{{ t('age.first_name') }} *</label>
              <input v-model="form.first_name" type="text" autocomplete="given-name"
                class="w-full bg-[#111] border border-white/10 rounded-lg px-3 py-2.5 text-sm text-white placeholder-gray-600 focus:border-[#e35d8f] focus:outline-none" />
            </div>
            <div>
              <label class="block text-xs font-medium text-gray-400 mb-1.5">{{ t('age.last_name') }} *</label>
              <input v-model="form.last_name" type="text" autocomplete="family-name"
                class="w-full bg-[#111] border border-white/10 rounded-lg px-3 py-2.5 text-sm text-white placeholder-gray-600 focus:border-[#e35d8f] focus:outline-none" />
            </div>
            <div class="sm:col-span-2">
              <label class="block text-xs font-medium text-gray-400 mb-1.5">{{ t('age.birth_date') }} *</label>
              <input v-model="form.birth_date" type="date" :max="maxBirthDate"
                class="w-full bg-[#111] border border-white/10 rounded-lg px-3 py-2.5 text-sm text-white focus:border-[#e35d8f] focus:outline-none" />
              <p v-if="birthDateProvided && !isAdult" class="mt-1.5 text-xs text-red-400">{{ t('age.under18_error') }}</p>
              <p v-if="errors.birth_date" class="mt-1.5 text-xs text-red-400">{{ errors.birth_date }}</p>
            </div>
          </div>
        </div>

        <!-- Card: Ausweisdokument -->
        <div class="bg-[#1a1a1a] rounded-2xl border border-white/8 overflow-hidden">
          <div class="bg-[#111] border-b border-white/8 px-5 py-3 flex items-center justify-between">
            <h2 class="text-sm font-semibold text-white flex items-center gap-2">🪪 {{ t('age.doc_title') }}</h2>
            <span v-if="av.has_document" class="text-xs font-semibold px-2.5 py-0.5 rounded-full bg-green-900/50 text-green-400">✓</span>
          </div>
          <div class="p-5 space-y-4">
            <p class="text-sm text-gray-400">{{ t('age.doc_desc') }}</p>

            <!-- Vorschau -->
            <div v-if="av.has_document && av.document_url" class="rounded-xl overflow-hidden border border-white/10 bg-black/30 max-w-xs">
              <img v-if="isImageDoc" :src="av.document_url" alt="" class="w-full h-auto max-h-56 object-contain" />
              <a v-else :href="av.document_url" target="_blank" rel="noopener" class="block px-4 py-6 text-center text-sm text-[#e35d8f] hover:underline">{{ t('age.preview_doc') }}</a>
            </div>

            <p class="text-[11px] text-gray-600">{{ t('age.doc_formats') }}: {{ limits.doc_exts.join(', ') }} · max {{ limits.document_mb }} MB</p>

            <div class="flex flex-wrap items-center gap-3">
              <label class="inline-flex items-center gap-2 bg-[#e35d8f] hover:bg-[#c44a7a] text-white text-sm font-bold px-5 py-2.5 rounded-xl cursor-pointer transition shadow-lg shadow-[#e35d8f]/20"
                :class="{ 'opacity-50 pointer-events-none': docUploading }">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4 16v2a2 2 0 002 2h12a2 2 0 002-2v-2M7 9l5-5 5 5M12 4v12"/></svg>
                {{ av.has_document ? t('age.doc_replace') : t('age.doc_upload') }}
                <input type="file" class="hidden" :accept="docAccept" @change="onDocChange" />
              </label>
              <span v-if="docUploading" class="text-xs text-gray-400">{{ t('age.uploading') }} {{ docProgress }}%</span>
            </div>
            <div v-if="docUploading" class="h-1.5 bg-white/10 rounded-full overflow-hidden">
              <div class="h-full bg-[#e35d8f] transition-all" :style="{ width: docProgress + '%' }"></div>
            </div>
            <p v-if="errors.document" class="text-xs text-red-400">{{ errors.document }}</p>
          </div>
        </div>

        <!-- Card: Identifikationsvideo -->
        <div class="bg-[#1a1a1a] rounded-2xl border border-white/8 overflow-hidden">
          <div class="bg-[#111] border-b border-white/8 px-5 py-3 flex items-center justify-between">
            <h2 class="text-sm font-semibold text-white flex items-center gap-2">🎥 {{ t('age.video_title') }}</h2>
            <span v-if="av.has_video" class="text-xs font-semibold px-2.5 py-0.5 rounded-full bg-green-900/50 text-green-400">✓</span>
          </div>
          <div class="p-5 space-y-4">
            <p class="text-sm text-gray-400">{{ t('age.video_desc') }}</p>
            <div class="bg-amber-950/30 border border-amber-700/30 rounded-lg p-3 text-xs text-amber-400/90">{{ t('age.video_hint') }}</div>

            <!-- Vorschau -->
            <div v-if="av.has_video && av.video_url" class="rounded-xl overflow-hidden border border-white/10 bg-black/30 max-w-xs">
              <video :src="av.video_url" controls playsinline class="w-full h-auto max-h-56"></video>
            </div>

            <p class="text-[11px] text-gray-600">{{ t('age.video_formats') }}: {{ limits.video_exts.join(', ') }} · max {{ limits.video_mb }} MB</p>

            <div class="flex flex-wrap items-center gap-3">
              <label class="inline-flex items-center gap-2 bg-[#e35d8f] hover:bg-[#c44a7a] text-white text-sm font-bold px-5 py-2.5 rounded-xl cursor-pointer transition shadow-lg shadow-[#e35d8f]/20"
                :class="{ 'opacity-50 pointer-events-none': videoUploading }">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4 16v2a2 2 0 002 2h12a2 2 0 002-2v-2M7 9l5-5 5 5M12 4v12"/></svg>
                {{ av.has_video ? t('age.video_replace') : t('age.video_upload') }}
                <input type="file" class="hidden" :accept="videoAccept" capture="user" @change="onVideoChange" />
              </label>
              <span v-if="videoUploading" class="text-xs text-gray-400">{{ t('age.uploading') }} {{ videoProgress }}%</span>
            </div>
            <div v-if="videoUploading" class="h-1.5 bg-white/10 rounded-full overflow-hidden">
              <div class="h-full bg-[#e35d8f] transition-all" :style="{ width: videoProgress + '%' }"></div>
            </div>
            <p v-if="errors.video" class="text-xs text-red-400">{{ errors.video }}</p>
          </div>
        </div>

        <!-- Zusammenfassung + Einreichen -->
        <div class="bg-[#1a1a1a] rounded-2xl border border-white/8 p-5 space-y-4">
          <ul class="space-y-2 text-sm">
            <li class="flex items-center gap-2" :class="personalComplete ? 'text-green-400' : 'text-gray-500'">
              <span>{{ personalComplete ? '✓' : '○' }}</span>{{ t('age.summary_personal') }}
            </li>
            <li class="flex items-center gap-2" :class="av.has_document ? 'text-green-400' : 'text-gray-500'">
              <span>{{ av.has_document ? '✓' : '○' }}</span>{{ t('age.summary_doc') }}
            </li>
            <li class="flex items-center gap-2" :class="av.has_video ? 'text-green-400' : 'text-gray-500'">
              <span>{{ av.has_video ? '✓' : '○' }}</span>{{ t('age.summary_video') }}
            </li>
          </ul>

          <button type="button" :disabled="!canSubmit || submitting" @click="submit"
            class="w-full sm:w-auto inline-flex items-center justify-center gap-2 bg-[#e35d8f] hover:bg-[#c44a7a] disabled:opacity-40 disabled:cursor-not-allowed text-white text-sm font-bold px-6 py-3 rounded-xl transition shadow-lg shadow-[#e35d8f]/20">
            {{ submitting ? t('age.submitting') : t('age.submit') }}
          </button>
        </div>
      </template>
    </div>
  </DashboardLayout>
</template>

<script setup>
import { Head, router, usePage } from '@inertiajs/vue3';
import { ref, reactive, computed } from 'vue';
import DashboardLayout from '@/Layouts/DashboardLayout.vue';
import { useI18n } from '@/composables/useI18n';

const { t } = useI18n();
const page = usePage();

const props = defineProps({
  av:     { type: Object, default: () => ({ status: 'not_submitted', has_document: false, has_video: false }) },
  limits: { type: Object, default: () => ({ document_mb: 15, video_mb: 80, doc_exts: [], video_exts: [] }) },
});

const av     = computed(() => props.av);
const limits = computed(() => props.limits);
const status = computed(() => props.av?.status ?? 'not_submitted');

const form = reactive({
  first_name: props.av?.first_name ?? '',
  last_name:  props.av?.last_name ?? '',
  birth_date: props.av?.birth_date ?? '',
});

const errors = computed(() => page.props.errors ?? {});

// 18+ Berechnung (clientseitig nur als Hinweis; Server erzwingt es verbindlich).
const maxBirthDate = new Date().toISOString().slice(0, 10);
const birthDateProvided = computed(() => !!form.birth_date);
const age = computed(() => {
  if (!form.birth_date) return null;
  const b = new Date(form.birth_date);
  if (isNaN(b)) return null;
  const now = new Date();
  let a = now.getFullYear() - b.getFullYear();
  const m = now.getMonth() - b.getMonth();
  if (m < 0 || (m === 0 && now.getDate() < b.getDate())) a--;
  return a;
});
const isAdult = computed(() => age.value !== null && age.value >= 18);

const personalComplete = computed(() => form.first_name.trim() && form.last_name.trim() && isAdult.value);
const canSubmit = computed(() => personalComplete.value && av.value.has_document && av.value.has_video);

const docAccept   = computed(() => limits.value.doc_exts.map(e => '.' + e).join(',') + ',image/*,application/pdf');
const videoAccept = computed(() => limits.value.video_exts.map(e => '.' + e).join(',') + ',video/*');
const isImageDoc  = computed(() => (av.value.document_mime || '').startsWith('image/') || /\.(jpe?g|png|webp|gif|bmp|tiff?)$/i.test(av.value.document_url || ''));

// ── Uploads ────────────────────────────────────────────────────────────────
const docUploading = ref(false);
const docProgress  = ref(0);
function onDocChange(e) {
  const file = e.target.files?.[0];
  e.target.value = '';
  if (!file) return;
  docUploading.value = true; docProgress.value = 0;
  router.post(route('inserat.age.document.upload'), { document: file }, {
    forceFormData: true, preserveScroll: true,
    onProgress: (ev) => { if (ev?.percentage != null) docProgress.value = Math.round(ev.percentage); },
    onFinish: () => { docUploading.value = false; },
  });
}

const videoUploading = ref(false);
const videoProgress  = ref(0);
function onVideoChange(e) {
  const file = e.target.files?.[0];
  e.target.value = '';
  if (!file) return;
  videoUploading.value = true; videoProgress.value = 0;
  router.post(route('inserat.age.video.upload'), { video: file }, {
    forceFormData: true, preserveScroll: true,
    onProgress: (ev) => { if (ev?.percentage != null) videoProgress.value = Math.round(ev.percentage); },
    onFinish: () => { videoUploading.value = false; },
  });
}

// ── Einreichen ───────────────────────────────────────────────────────────────
const submitting = ref(false);
function submit() {
  if (!canSubmit.value) return;
  submitting.value = true;
  router.post(route('inserat.age.submit'), { ...form }, {
    preserveScroll: true,
    onFinish: () => { submitting.value = false; },
  });
}
</script>
