<template>
  <AppLayout>
    <Head :title="t('dashboard.gallery_requests_title')" />
    <div class="max-w-7xl mx-auto px-4 py-8">

      <div class="mb-6">
        <Link :href="route('inserat.dashboard')" class="text-xs text-gray-400 hover:text-[#e35d8f] transition">← {{ t('dashboard.action_edit') }}</Link>
        <h1 class="text-2xl font-bold text-white mt-2">{{ t('dashboard.gallery_requests_title') }}</h1>
        <p class="text-gray-400 text-sm mt-1">{{ t('dashboard.gallery_requests_intro') }}</p>
      </div>

      <div v-if="requests.length === 0" class="bg-[#1a1a1a] rounded-xl border border-white/8 p-10 text-center text-gray-500">
        <div class="text-3xl mb-2">📭</div>
        <p class="text-sm">{{ t('dashboard.gallery_requests_empty') }}</p>
      </div>

      <div v-else class="space-y-2">
        <div v-for="r in requests" :key="r.id"
          class="bg-[#1a1a1a] rounded-xl border border-white/8 p-4 flex items-center justify-between gap-3">
          <div class="flex items-center gap-3 min-w-0">
            <span class="shrink-0 w-9 h-9 rounded-full bg-gradient-to-br from-[#e35d8f] to-[#7c3aed] flex items-center justify-center text-white text-xs font-bold">{{ initials(r.name) }}</span>
            <div class="min-w-0">
              <p class="text-sm font-semibold text-white truncate">{{ r.name }}</p>
              <p class="text-xs text-gray-500">{{ r.created_at }}</p>
            </div>
          </div>

          <div class="flex items-center gap-2 shrink-0">
            <template v-if="r.status === 'pending'">
              <button @click="decline(r)" :disabled="busyId === r.id"
                class="text-xs font-semibold text-gray-300 border border-white/15 px-3 py-1.5 rounded-lg hover:border-white/40 disabled:opacity-50 transition">
                {{ t('dashboard.gallery_requests_decline') }}
              </button>
              <button @click="approve(r)" :disabled="busyId === r.id"
                class="text-xs font-bold text-white bg-[#e35d8f] px-3 py-1.5 rounded-lg hover:bg-[#c44a7a] disabled:opacity-50 transition">
                {{ busyId === r.id ? '…' : t('dashboard.gallery_requests_approve') }}
              </button>
            </template>
            <span v-else-if="r.status === 'approved'" class="text-xs font-semibold text-green-400">✓ {{ t('dashboard.gallery_requests_approved') }}</span>
            <span v-else class="text-xs font-semibold text-gray-500">{{ t('dashboard.gallery_requests_declined') }}</span>
          </div>
        </div>
      </div>

    </div>
  </AppLayout>
</template>

<script setup>
import { ref } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import { useI18n } from '@/composables/useI18n';

const { t } = useI18n();

defineProps({
  requests: { type: Array, default: () => [] },
});

const busyId = ref(null);

function initials(name) {
  return (name || '?').trim().split(/\s+/).map(w => w[0]).slice(0, 2).join('').toUpperCase();
}

function approve(r) {
  if (busyId.value) return;
  busyId.value = r.id;
  router.post(route('inserat.gallery.requests.approve', r.id), {}, {
    preserveScroll: true,
    onFinish: () => { busyId.value = null; },
  });
}

function decline(r) {
  if (busyId.value) return;
  busyId.value = r.id;
  router.post(route('inserat.gallery.requests.decline', r.id), {}, {
    preserveScroll: true,
    onFinish: () => { busyId.value = null; },
  });
}
</script>
