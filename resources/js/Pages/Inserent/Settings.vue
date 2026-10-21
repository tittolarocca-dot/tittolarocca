<template>
  <DashboardLayout role="inserent">
    <Head :title="t('shell.settings_title')" />

    <div class="space-y-5">
      <div>
        <h1 class="text-2xl font-black text-white">{{ t('shell.settings_title') }}</h1>
        <p class="text-gray-400 text-sm mt-1">{{ t('shell.settings_desc') }}</p>
      </div>

      <!-- Geoblocking -->
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

      <!-- Gefahrenzone -->
      <div class="border border-red-900/40 rounded-2xl overflow-hidden">
        <div class="bg-red-950/30 px-5 py-3 border-b border-red-900/40">
          <h2 class="text-sm font-semibold text-red-400">{{ t('dashboard.danger_zone') }}</h2>
        </div>
        <div class="bg-[#1a1a1a] px-5 py-4 flex items-center justify-between gap-4 border-b border-red-900/20">
          <div>
            <p class="text-sm font-semibold text-white">{{ t('dashboard.deactivate_title') }}</p>
            <p class="text-xs text-gray-500 mt-0.5">{{ t('dashboard.deactivate_desc') }}</p>
          </div>
          <button @click="deactivateProfile" :disabled="deactivating || !isActive"
            class="shrink-0 border border-orange-700/50 text-orange-400 text-sm font-semibold px-4 py-2 rounded-lg hover:bg-orange-950/40 disabled:opacity-40 disabled:cursor-not-allowed transition whitespace-nowrap">
            {{ deactivating ? t('dashboard.deactivating') : isActive ? t('dashboard.deactivate_btn') : t('dashboard.already_inactive') }}
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
import { Head, router } from '@inertiajs/vue3';
import { ref, computed } from 'vue';
import DashboardLayout from '@/Layouts/DashboardLayout.vue';
import { useI18n } from '@/composables/useI18n';

const { t } = useI18n();

const props = defineProps({
  profile:   Object,
  countries: { type: Object,  default: () => ({}) },
  isActive:  { type: Boolean, default: false },
});

// ── Geoblocking ──────────────────────────────────────────────────────────────
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
  const code = e.target.value; e.target.value = '';
  if (code && !blockedCountries.value.includes(code) && blockedCountries.value.length < 5) blockedCountries.value.push(code);
}
function removeCountry(code) { blockedCountries.value = blockedCountries.value.filter(c => c !== code); }
function saveCountries() {
  savingCountries.value = true; countriesSaved.value = false;
  router.post(route('inserat.countries.update'), { blocked_countries: blockedCountries.value }, {
    preserveScroll: true,
    onSuccess: () => { countriesSaved.value = true; setTimeout(() => { countriesSaved.value = false; }, 2500); },
    onFinish:  () => { savingCountries.value = false; },
  });
}

// ── Deactivate / Delete ──────────────────────────────────────────────────────
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
