<template>
  <DashboardLayout role="member">
    <Head :title="t('dashboard.my_listing')" />

    <div class="space-y-5">
      <!-- Hero -->
      <section class="rounded-2xl p-5 sm:p-6 bg-gradient-to-br from-[#e35d8f]/15 to-[#7c3aed]/10 border border-[#e35d8f]/25 flex flex-col lg:flex-row lg:items-center gap-5">
        <div class="flex-1 min-w-0">
          <h1 class="text-2xl font-black text-white">{{ t('dashboard.welcome', { name: $page.props.auth.user.name }) }}</h1>
          <p class="text-gray-400 text-sm mt-1">{{ t('dashboard.subtitle') }}</p>
          <div class="flex flex-wrap gap-2 mt-4">
            <Link :href="route('home')" class="bg-[#e35d8f] hover:bg-[#c44a7a] text-white text-sm font-bold px-4 py-2.5 rounded-xl transition">{{ t('dashboard.discover') }}</Link>
            <Link :href="route('konto.messages')" class="border border-white/10 text-gray-200 hover:bg-white/5 text-sm font-bold px-4 py-2.5 rounded-xl transition">{{ t('dashboard.messages') }}</Link>
          </div>
        </div>
        <!-- Mini-Statistiken -->
        <div class="grid grid-cols-3 bg-[#1a1a1a] border border-white/8 rounded-2xl overflow-hidden shrink-0 lg:min-w-[320px]">
          <div class="px-4 py-4 text-center border-r border-white/8">
            <div class="text-2xl font-extrabold text-[#e35d8f] tabular-nums">{{ favoritesCount }}</div>
            <div class="text-xs text-gray-500 mt-0.5">{{ t('dashboard.my_favorites') }}</div>
          </div>
          <div class="px-4 py-4 text-center border-r border-white/8">
            <div class="text-2xl font-extrabold text-[#e35d8f] tabular-nums">{{ subscriptions.length }}</div>
            <div class="text-xs text-gray-500 mt-0.5">{{ t('dashboard.subscriptions') }}</div>
          </div>
          <div class="px-4 py-4 text-center">
            <div class="text-2xl font-extrabold text-[#e35d8f] tabular-nums">{{ visitorsCount }}</div>
            <div class="text-xs text-gray-500 mt-0.5">{{ t('dashboard.who_visited_me') }}</div>
          </div>
        </div>
      </section>

      <!-- Meine Favoriten -->
      <div class="bg-[#1a1a1a] rounded-2xl border border-white/8 p-5 sm:p-6">
        <div class="flex items-center justify-between mb-4">
          <div class="flex items-center gap-2">
            <svg class="w-5 h-5 text-[#e35d8f]" fill="currentColor" viewBox="0 0 24 24"><path d="M4.318 6.318a4.5 4.5 0 016.364 0L12 7.636l1.318-1.318a4.5 4.5 0 116.364 6.364L12 20.364l-7.682-7.682a4.5 4.5 0 010-6.364z"/></svg>
            <h2 class="font-semibold text-white">{{ t('dashboard.favorites_title') }}</h2>
            <span v-if="favoritesCount > 0" class="text-xs bg-[#e35d8f]/15 text-[#e35d8f] font-bold px-2 py-0.5 rounded-full">{{ favoritesCount }}</span>
          </div>
          <Link :href="route('konto.favorites')" class="text-xs text-[#e35d8f] hover:underline">{{ t('dashboard.show_all') }}</Link>
        </div>

        <div v-if="favoritesPreview.length === 0" class="text-center py-8 text-gray-500">
          <svg class="w-8 h-8 text-gray-600 mx-auto mb-2" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M4.318 6.318a4.5 4.5 0 016.364 0L12 7.636l1.318-1.318a4.5 4.5 0 116.364 6.364L12 20.364l-7.682-7.682a4.5 4.5 0 010-6.364z"/></svg>
          <p class="text-sm">{{ t('dashboard.no_favorites') }}</p>
          <Link :href="route('home')" class="text-[#e35d8f] text-sm hover:underline mt-1 inline-block">{{ t('dashboard.discover_now') }}</Link>
        </div>

        <div v-else class="grid grid-cols-2 sm:grid-cols-4 gap-3">
          <Link v-for="item in favoritesPreview" :key="item.slug" :href="route('profile.show', item.slug)"
            class="group block rounded-xl overflow-hidden border border-white/8 hover:border-[#e35d8f]/40 transition">
            <div class="aspect-[3/4] relative overflow-hidden bg-[#111]">
              <img v-if="item.cover_url" :src="item.cover_url" :alt="item.display_name" class="w-full h-full object-cover object-top transition duration-300" loading="lazy" />
              <div v-else class="w-full h-full flex items-center justify-center">
                <svg class="w-8 h-8 text-gray-600" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z" clip-rule="evenodd"/></svg>
              </div>
            </div>
            <div class="px-2 py-2 flex items-center gap-1">
              <span class="text-xs font-semibold text-white truncate group-hover:text-[#e35d8f] transition">{{ item.display_name }}</span>
              <span v-if="item.identity_verification_status === 'approved' || item.verification_status === 'approved'"
                class="inline-flex items-center justify-center w-3.5 h-3.5 rounded-full shrink-0"
                :class="item.identity_verification_status === 'approved' ? 'bg-green-500' : 'bg-sky-500'">
                <svg class="w-2 h-2 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
              </span>
            </div>
          </Link>
          <Link v-if="favoritesCount > 4" :href="route('konto.favorites')"
            class="flex items-center justify-center rounded-xl border border-dashed border-white/15 text-gray-500 hover:text-[#e35d8f] hover:border-[#e35d8f]/40 transition text-sm font-semibold aspect-[3/4]">
            +{{ favoritesCount - 4 }} mehr
          </Link>
        </div>
      </div>

      <!-- Meine Abonnements -->
      <div class="bg-[#1a1a1a] rounded-2xl border border-white/8 p-5 sm:p-6">
        <div class="flex items-center justify-between mb-4">
          <h2 class="font-semibold text-white">{{ t('dashboard.my_subscriptions') }}</h2>
          <Link :href="route('konto.subscriptions')" class="text-xs text-[#e35d8f] hover:underline">{{ t('dashboard.show_all') }}</Link>
        </div>

        <div v-if="subscriptions.length === 0" class="text-center py-8 text-gray-500">
          <div class="text-3xl mb-2">💫</div>
          <p class="text-sm">{{ t('dashboard.no_subscriptions') }}</p>
          <Link :href="route('home')" class="text-[#e35d8f] text-sm hover:underline mt-1 inline-block">{{ t('dashboard.discover_now') }}</Link>
        </div>

        <div v-else class="space-y-2">
          <Link v-for="sub in subscriptions.slice(0,5)" :key="sub.id" :href="route('profile.show', sub.profile.slug)"
            class="flex items-center justify-between px-4 py-3 rounded-xl bg-white/4 hover:bg-white/7 border border-white/6 hover:border-[#e35d8f]/30 transition group">
            <div class="flex items-center gap-3">
              <div class="w-8 h-8 rounded-full bg-gradient-to-br from-[#e35d8f] to-[#7c3aed] flex items-center justify-center text-white text-xs font-bold shrink-0">{{ sub.profile.display_name.charAt(0).toUpperCase() }}</div>
              <span class="text-sm font-semibold text-white group-hover:text-[#e35d8f] transition">{{ sub.profile.display_name }}</span>
            </div>
            <div class="text-right">
              <p class="text-xs font-bold text-gray-300">CHF {{ sub.amount_chf }}/Mo</p>
              <span class="text-xs text-green-400">{{ t('dashboard.active') }}</span>
            </div>
          </Link>
        </div>
      </div>
    </div>
  </DashboardLayout>
</template>

<script setup>
import { Head, Link } from '@inertiajs/vue3';
import DashboardLayout from '@/Layouts/DashboardLayout.vue';
import { useI18n } from '@/composables/useI18n';

const { t } = useI18n();

defineProps({
  subscriptions:    { type: Array,  default: () => [] },
  favoritesPreview: { type: Array,  default: () => [] },
  favoritesCount:   { type: Number, default: 0 },
  visitorsCount:    { type: Number, default: 0 },
});
</script>
