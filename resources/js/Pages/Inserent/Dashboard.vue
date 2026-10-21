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
        <ProgressRing :percent="completeness" :label="t('shell.completeness_label')" class="shrink-0 mx-auto lg:mx-0" />
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
            <h2 class="text-[15px] font-bold text-white">{{ t('shell.next_steps') }}</h2>
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
                  {{ tip.done ? t('shell.tip_done') : t('shell.tip_do') + ' →' }}
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
    </div>
  </DashboardLayout>
</template>

<script setup>
import { Head, Link, router } from '@inertiajs/vue3';
import { ref, computed, onMounted, onUnmounted } from 'vue';
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

// ── Stats ─────────────────────────────────────────────────────────────────────
const statCards = computed(() => props.stats ? [
  { label: t('dashboard.stat_views'),       value: props.stats.views },
  { label: t('dashboard.stat_subscribers'), value: props.stats.subscribers },
  { label: t('dashboard.stat_media'),       value: props.stats.mediaCount },
  { label: t('dashboard.stat_visitors'),    value: props.stats.visitorsCount },
] : []);

// ── Reactivate / Push ──────────────────────────────────────────────────────────
const reactivating = ref(false);
function reactivate() { reactivating.value = true; router.post(route('inserat.reactivate'), {}, { onFinish: () => { reactivating.value = false; } }); }

const pushing = ref(false);
function push() { pushing.value = true; router.post(route('inserat.push'), {}, { onFinish: () => { pushing.value = false; } }); }
</script>
