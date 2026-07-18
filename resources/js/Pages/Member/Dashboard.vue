<template>
  <AppLayout>
    <Head :title="t('dashboard.my_listing')" />
    <div class="max-w-4xl mx-auto px-4 py-8">

      <div class="mb-8">
        <h1 class="text-2xl font-bold text-white">{{ t('dashboard.welcome', { name: $page.props.auth.user.name }) }}</h1>
        <p class="text-gray-400 text-sm mt-1">{{ t('dashboard.subtitle') }}</p>
      </div>

      <!-- Schnellzugriff -->
      <div class="grid grid-cols-2 sm:grid-cols-3 gap-3 mb-8">
        <Link v-for="action in actions" :key="action.label" :href="action.href"
          class="bg-[#1a1a1a] rounded-xl border border-white/8 p-4 flex items-center gap-3 hover:border-[#e35d8f]/40 transition group">
          <!-- Footprint SVG icon for visitors tile -->
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
          <!-- Personen-Icon für "Mein Profil" -->
          <span v-else-if="action.userIcon" class="w-8 h-8 shrink-0 flex items-center justify-center">
            <svg class="w-7 h-7 text-[#e35d8f]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
              <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632z"/>
            </svg>
          </span>
          <span v-else class="text-2xl">{{ action.icon }}</span>
          <div class="min-w-0">
            <p class="text-sm font-semibold text-white group-hover:text-[#e35d8f] transition leading-tight">{{ action.label }}</p>
            <p class="text-xs text-gray-500 mt-0.5">{{ action.desc }}</p>
          </div>
        </Link>
      </div>

      <!-- Meine Favoriten -->
      <div class="bg-[#1a1a1a] rounded-xl border border-white/8 p-6 mb-5">
        <div class="flex items-center justify-between mb-4">
          <div class="flex items-center gap-2">
            <svg class="w-5 h-5 text-[#e35d8f]" fill="currentColor" viewBox="0 0 24 24">
              <path d="M4.318 6.318a4.5 4.5 0 016.364 0L12 7.636l1.318-1.318a4.5 4.5 0 116.364 6.364L12 20.364l-7.682-7.682a4.5 4.5 0 010-6.364z"/>
            </svg>
            <h2 class="font-semibold text-white">{{ t('dashboard.favorites_title') }}</h2>
            <span v-if="favoritesCount > 0" class="text-xs bg-[#e35d8f]/15 text-[#e35d8f] font-bold px-2 py-0.5 rounded-full">{{ favoritesCount }}</span>
          </div>
          <Link :href="route('konto.favorites')" class="text-xs text-[#e35d8f] hover:underline">{{ t('dashboard.show_all') }}</Link>
        </div>

        <div v-if="favoritesPreview.length === 0" class="text-center py-8 text-gray-500">
          <svg class="w-8 h-8 text-gray-600 mx-auto mb-2" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
            <path stroke-linecap="round" stroke-linejoin="round" d="M4.318 6.318a4.5 4.5 0 016.364 0L12 7.636l1.318-1.318a4.5 4.5 0 116.364 6.364L12 20.364l-7.682-7.682a4.5 4.5 0 010-6.364z"/>
          </svg>
          <p class="text-sm">{{ t('dashboard.no_favorites') }}</p>
          <Link :href="route('home')" class="text-[#e35d8f] text-sm hover:underline mt-1 inline-block">
            {{ t('dashboard.discover_now') }}
          </Link>
        </div>

        <div v-else class="grid grid-cols-2 sm:grid-cols-4 gap-3">
          <Link v-for="item in favoritesPreview" :key="item.slug"
            :href="route('profile.show', item.slug)"
            class="group block rounded-xl overflow-hidden border border-white/8 hover:border-[#e35d8f]/40 transition">
            <div class="aspect-[3/4] relative overflow-hidden bg-[#111]">
              <img v-if="item.cover_url" :src="item.cover_url" :alt="item.display_name"
                class="w-full h-full object-cover object-top group-hover:scale-105 transition duration-300" loading="lazy" />
              <div v-else class="w-full h-full flex items-center justify-center">
                <svg class="w-8 h-8 text-gray-600" fill="currentColor" viewBox="0 0 20 20">
                  <path fill-rule="evenodd" d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z" clip-rule="evenodd"/>
                </svg>
              </div>
            </div>
            <div class="px-2 py-2 flex items-center gap-1">
              <span class="text-xs font-semibold text-white truncate group-hover:text-[#e35d8f] transition">{{ item.display_name }}</span>
              <span v-if="item.identity_verification_status === 'approved' || item.verification_status === 'approved'"
                class="inline-flex items-center justify-center w-3.5 h-3.5 rounded-full shrink-0"
                :class="item.identity_verification_status === 'approved' ? 'bg-green-500' : 'bg-sky-500'">
                <svg class="w-2 h-2 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                </svg>
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
      <div class="bg-[#1a1a1a] rounded-xl border border-white/8 p-6 mb-5">
        <div class="flex items-center justify-between mb-4">
          <h2 class="font-semibold text-white">{{ t('dashboard.my_subscriptions') }}</h2>
          <Link :href="route('konto.subscriptions')" class="text-xs text-[#e35d8f] hover:underline">{{ t('dashboard.show_all') }}</Link>
        </div>

        <div v-if="subscriptions.length === 0" class="text-center py-8 text-gray-500">
          <div class="text-3xl mb-2">💫</div>
          <p class="text-sm">{{ t('dashboard.no_subscriptions') }}</p>
          <Link :href="route('home')" class="text-[#e35d8f] text-sm hover:underline mt-1 inline-block">
            {{ t('dashboard.discover_now') }}
          </Link>
        </div>

        <div v-else class="space-y-2">
          <Link v-for="sub in subscriptions.slice(0,5)" :key="sub.id"
            :href="route('profile.show', sub.profile.slug)"
            class="flex items-center justify-between px-4 py-3 rounded-xl bg-white/4 hover:bg-white/7 border border-white/6 hover:border-[#e35d8f]/30 transition group">
            <div class="flex items-center gap-3">
              <div class="w-8 h-8 rounded-full bg-gradient-to-br from-[#e35d8f] to-[#7c3aed] flex items-center justify-center text-white text-xs font-bold shrink-0">
                {{ sub.profile.display_name.charAt(0).toUpperCase() }}
              </div>
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
  </AppLayout>
</template>

<script setup>
import { computed } from 'vue';
import { Head, Link } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import { useI18n } from '@/composables/useI18n';

const { t } = useI18n();

const props = defineProps({
  subscriptions:    { type: Array,  default: () => [] },
  favoritesPreview: { type: Array,  default: () => [] },
  favoritesCount:   { type: Number, default: 0 },
  visitorsCount:    { type: Number, default: 0 },
});

const actions = computed(() => [
  { icon: null, userIcon: true, label: t('dashboard.my_profile'), desc: t('dashboard.my_profile_desc'), href: route('konto.account.edit') },
  { icon: '📝', label: t('dashboard.my_reports'),    desc: t('dashboard.my_reports_desc'),      href: route('konto.reviews.mine') },
  { icon: '🔍', label: t('dashboard.discover'),      desc: t('dashboard.discover_desc'),       href: route('home') },
  { icon: '💫', label: t('dashboard.subscriptions'), desc: t('dashboard.subscriptions_desc'),  href: route('konto.subscriptions') },
  { icon: '💬', label: t('dashboard.messages'),      desc: t('dashboard.messages_desc'),        href: route('konto.messages') },
  { icon: '❤️', label: t('dashboard.my_favorites'),  desc: t('dashboard.favorites_desc'),       href: route('konto.favorites') },
  { icon: null,  label: t('dashboard.who_visited_me'), desc: t('dashboard.who_visited_desc'),   href: route('konto.visitors'), footprint: true },
]);
</script>
