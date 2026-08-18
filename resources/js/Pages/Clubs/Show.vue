<template>
  <AppLayout>
    <Head :title="meta.title" />

    <div class="max-w-3xl mx-auto px-4 py-6">
      <!-- Zurück -->
      <Link :href="route('clubs.index')" class="inline-flex items-center gap-1.5 text-sm text-gray-400 hover:text-[#e35d8f] transition mb-4">
        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/></svg>
        {{ t('clubs.back_to_list') }}
      </Link>

      <!-- Kopf -->
      <div class="bg-[#1a1a1a] border border-white/8 rounded-2xl p-6 mb-4">
        <div class="flex items-start justify-between gap-3 mb-2">
          <h1 class="text-2xl font-black text-white">{{ club.name }}</h1>
          <span v-if="club.is_open_now" class="shrink-0 text-[11px] font-bold text-green-400 bg-green-500/10 border border-green-500/30 px-2.5 py-1 rounded-full">{{ t('clubs.open_now') }}</span>
        </div>
        <div class="flex flex-wrap gap-1.5 mb-4">
          <span class="text-[11px] font-semibold text-[#e35d8f] bg-[#e35d8f]/10 border border-[#e35d8f]/30 px-2 py-0.5 rounded-full">{{ club.category }}</span>
          <span v-if="club.is_premium" class="text-[11px] font-bold text-amber-300 bg-amber-400/10 border border-amber-400/40 px-2 py-0.5 rounded-full">★ {{ t('clubs.premium') }}</span>
          <span v-if="club.is_verified" class="text-[11px] font-bold text-sky-300 bg-sky-400/10 border border-sky-400/40 px-2 py-0.5 rounded-full">✓ {{ t('clubs.verified') }}</span>
          <span v-if="club.rating_average" class="text-[11px] font-bold text-yellow-400 bg-yellow-400/10 border border-yellow-400/30 px-2 py-0.5 rounded-full">★ {{ Number(club.rating_average).toFixed(1) }} ({{ club.rating_count }})</span>
        </div>

        <p v-if="club.description" class="text-[15px] text-gray-300 leading-relaxed whitespace-pre-line mb-5">{{ club.description }}</p>

        <a v-if="club.website_url" :href="route('clubs.visit', club.slug)"
          target="_blank" rel="nofollow noopener noreferrer"
          class="inline-flex items-center justify-center gap-2 bg-[#e35d8f] hover:bg-[#c44a7a] text-white text-sm font-bold px-6 py-3 rounded-xl transition">
          <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M13.828 10.172a4 4 0 010 5.656l-3 3a4 4 0 01-5.656-5.656l1.5-1.5m8.656-2.828a4 4 0 010-5.656l3-3a4 4 0 015.656 5.656l-1.5 1.5"/></svg>
          {{ t('clubs.open_website') }}
        </a>
      </div>

      <!-- Infos -->
      <div class="bg-[#1a1a1a] border border-white/8 rounded-2xl p-6 mb-4 space-y-3 text-sm">
        <div class="flex gap-3">
          <span class="w-28 shrink-0 text-gray-500">{{ t('clubs.address') }}</span>
          <span class="text-gray-200">
            {{ [club.address, club.postal_code, club.city].filter(Boolean).join(', ') }} · {{ club.canton_name }}
          </span>
        </div>
        <div v-if="club.phone" class="flex gap-3">
          <span class="w-28 shrink-0 text-gray-500">{{ t('clubs.phone') }}</span>
          <a :href="`tel:${club.phone}`" class="text-[#e35d8f] hover:underline">{{ club.phone }}</a>
        </div>
        <div v-if="club.email" class="flex gap-3">
          <span class="w-28 shrink-0 text-gray-500">E-Mail</span>
          <a :href="`mailto:${club.email}`" class="text-[#e35d8f] hover:underline">{{ club.email }}</a>
        </div>
      </div>

      <!-- Öffnungszeiten -->
      <div v-if="club.opening_hours && club.opening_hours.length" class="bg-[#1a1a1a] border border-white/8 rounded-2xl p-6 mb-4">
        <h2 class="text-sm font-bold text-gray-400 uppercase tracking-wider mb-4">{{ t('clubs.opening_hours') }}</h2>
        <div v-if="club.is_24h" class="text-sm text-green-400 font-semibold">{{ t('clubs.hours_24h') }}</div>
        <div v-else class="space-y-1.5 text-sm">
          <div v-for="d in week" :key="d.code" class="flex justify-between">
            <span class="text-gray-400">{{ d.label }}</span>
            <span :class="d.text ? 'text-gray-200' : 'text-gray-600'">{{ d.text || t('clubs.closed') }}</span>
          </div>
        </div>
      </div>

      <!-- Rechtlicher Hinweis + Melden -->
      <div class="bg-[#141414] border border-white/8 rounded-2xl p-5 text-xs text-gray-500 leading-relaxed">
        <p>{{ t('clubs.legal_notice') }}</p>

        <p v-if="reportSent" class="mt-3 text-green-400 font-semibold">✓ {{ t('clubs.report_sent') }}</p>

        <div v-else-if="!reportType" class="flex flex-wrap gap-2 mt-3">
          <button type="button" @click="openReport('report')" class="border border-white/10 text-gray-300 px-3 py-1.5 rounded-lg hover:border-[#e35d8f] hover:text-[#e35d8f] transition">{{ t('clubs.report_entry') }}</button>
          <button type="button" @click="openReport('claim')" class="border border-white/10 text-gray-300 px-3 py-1.5 rounded-lg hover:border-[#e35d8f] hover:text-[#e35d8f] transition">{{ t('clubs.claim_entry') }}</button>
        </div>

        <form v-else @submit.prevent="submitReport" class="mt-3 space-y-2">
          <p class="text-gray-300 font-semibold">{{ reportType === 'claim' ? t('clubs.claim_entry') : t('clubs.report_entry') }}</p>
          <textarea v-model="reportForm.message" rows="3" maxlength="2000" :placeholder="t('clubs.report_message_ph')"
            class="w-full bg-[#1a1a1a] border border-white/10 rounded-lg px-3 py-2 text-gray-200 text-sm focus:outline-none focus:border-[#e35d8f] resize-none"></textarea>
          <input v-model="reportForm.email" type="email" :placeholder="t('clubs.report_email_ph')"
            class="w-full bg-[#1a1a1a] border border-white/10 rounded-lg px-3 py-2 text-gray-200 text-sm focus:outline-none focus:border-[#e35d8f]" />
          <div class="flex gap-2">
            <button type="submit" :disabled="reportSending || !reportForm.message"
              class="bg-[#e35d8f] hover:bg-[#c44a7a] disabled:opacity-50 text-white text-sm font-bold px-4 py-2 rounded-lg transition">{{ t('clubs.report_submit') }}</button>
            <button type="button" @click="reportType = null" class="text-gray-400 text-sm px-3 py-2 hover:text-white transition">{{ t('clubs.report_cancel') }}</button>
          </div>
        </form>
      </div>
    </div>
  </AppLayout>
</template>

<script setup>
import { computed, reactive, ref } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import { useI18n } from '@/composables/useI18n';

const { t } = useI18n();

const props = defineProps({
  club:         { type: Object, required: true },
  supportEmail: { type: String, default: null },
  meta:         { type: Object, default: () => ({ title: '', description: '' }) },
});

const DAYS = ['mon', 'tue', 'wed', 'thu', 'fri', 'sat', 'sun'];

const week = computed(() => DAYS.map((code) => {
  const entry = (props.club.opening_hours || []).find(e => e.day === code);
  let text = '';
  if (entry) {
    text = (entry.from === entry.to) ? '24h' : `${entry.from}–${entry.to}`;
  }
  return { code, label: t('clubs.day_' + code), text };
}));

// ── Meldung / Beanspruchen ──────────────────────────────────────────────────
const reportType    = ref(null);         // 'report' | 'claim'
const reportSending = ref(false);
const reportSent    = ref(false);
const reportForm    = reactive({ message: '', email: '' });

function openReport(type) {
  reportType.value = type;
  reportSent.value = false;
  reportForm.message = '';
  reportForm.email = '';
}

function submitReport() {
  if (! reportForm.message || reportSending.value) return;
  reportSending.value = true;
  router.post(route('clubs.report', props.club.slug), {
    type: reportType.value, message: reportForm.message, email: reportForm.email,
  }, {
    preserveScroll: true,
    onSuccess: () => { reportSent.value = true; reportType.value = null; },
    onFinish:  () => { reportSending.value = false; },
  });
}
</script>
