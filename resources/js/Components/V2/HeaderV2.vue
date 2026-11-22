<template>
  <header class="sticky top-0 z-50" style="background: var(--bl-grad); box-shadow: 0 3px 22px -8px rgba(0,0,0,.55);">
    <div class="max-w-7xl mx-auto px-4 flex items-center gap-3" style="height:60px;">

      <!-- Logo -->
      <Link :href="route('home')" class="flex items-center shrink-0 mr-1">
        <img src="/images/logo-booklola.png" alt="BookLola.ch" class="h-8 w-auto object-contain" />
      </Link>

      <!-- Desktop: zentrale Tab-Navigation -->
      <nav class="hidden lg:flex items-center gap-1">
        <Link v-for="item in navItems" :key="item.label" :href="item.href"
          class="relative flex items-center gap-2 px-4 py-2 rounded-full text-sm font-semibold transition"
          :class="item.active ? '' : 'text-white/90 hover:bg-white/15'"
          :style="item.active ? 'background:rgba(255,255,255,.95);color:#c44a7a;box-shadow:0 2px 10px -2px rgba(0,0,0,.25);' : ''">
          <svg class="w-[18px] h-[18px] shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" v-html="icons[item.icon]"></svg>
          <span>{{ item.label }}</span>
          <span v-if="item.badge"
            style="background:#ef4444;color:#fff;" class="min-w-[18px] h-[18px] px-1 rounded-full text-[11px] font-extrabold flex items-center justify-center shadow">{{ item.badge }}</span>
        </Link>
      </nav>

      <!-- Desktop rechts -->
      <div class="hidden lg:flex items-center gap-1.5 ml-auto">
        <button type="button" title="Benachrichtigungen" class="p-2 rounded-full text-white hover:bg-white/15 transition">
          <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 17h5l-1.4-1.4A2 2 0 0118 14.2V11a6 6 0 10-12 0v3.2a2 2 0 01-.6 1.4L4 17h5m6 0a3 3 0 11-6 0"/></svg>
        </button>

        <template v-if="user">
          <div class="relative" ref="accRef">
            <button @click="accOpen = !accOpen" class="flex items-center gap-2 pl-1 pr-2.5 py-1 rounded-full text-white hover:bg-white/15 transition text-sm font-semibold">
              <span class="w-8 h-8 rounded-full overflow-hidden bg-white/25 flex items-center justify-center font-bold shrink-0">
                <img v-if="avatar" :src="avatar" :alt="userName" class="w-full h-full object-cover" />
                <template v-else>{{ initial }}</template>
              </span>
              <span>{{ t('nav.my_account') }}</span>
              <svg class="w-3 h-3 transition-transform" :class="accOpen ? 'rotate-180' : ''" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" d="M6 9l6 6 6-6"/></svg>
            </button>
            <div v-show="accOpen" class="absolute right-0 top-full mt-2 rounded-xl overflow-hidden min-w-[190px] z-50"
              style="background:var(--bl-surface);border:1px solid var(--bl-border);box-shadow:var(--bl-shadow);">
              <Link :href="dashboardRoute" @click="accOpen = false" class="block px-4 py-2.5 text-sm hover:bg-white/5" style="color:var(--bl-text);">{{ t('nav.my_account') }}</Link>
              <Link :href="route('logout')" method="post" as="button" class="block w-full text-left px-4 py-2.5 text-sm hover:bg-white/5" style="color:var(--bl-text-muted);">{{ t('nav.logout') }}</Link>
            </div>
          </div>
        </template>
        <template v-else>
          <Link :href="route('login')" class="text-white/90 hover:text-white text-sm font-medium px-3 py-2 rounded-full hover:bg-white/15 transition">{{ t('nav.login') }}</Link>
          <Link :href="route('register')" class="bg-white text-sm font-bold px-4 py-2 rounded-full transition" style="color:#c44a7a;">{{ t('nav.register') }}</Link>
        </template>

        <!-- Sprache -->
        <div class="relative" ref="langRef">
          <button @click="langOpen = !langOpen" class="flex items-center gap-1.5 text-white px-2.5 py-2 rounded-full hover:bg-white/15 transition text-sm font-semibold">
            <span class="text-base leading-none">{{ currentFlag }}</span>
            <span class="uppercase text-xs">{{ locale }}</span>
          </button>
          <div v-show="langOpen" class="absolute right-0 top-full mt-2 rounded-xl overflow-hidden min-w-[150px] z-50"
            style="background:var(--bl-surface);border:1px solid var(--bl-border);box-shadow:var(--bl-shadow);">
            <a v-for="lang in supportedLocales" :key="lang" :href="route('lang.switch', lang)" @click="langOpen = false"
              class="flex items-center gap-2.5 px-3 py-2 text-sm hover:bg-white/5 transition"
              :style="lang === locale ? 'color:var(--bl-pink);font-weight:700;' : 'color:var(--bl-text-muted);'">
              <span class="text-base">{{ flags[lang] }}</span><span>{{ langLabels[lang] }}</span>
            </a>
          </div>
        </div>
      </div>

      <!-- Mobile rechts -->
      <div class="flex items-center gap-1 ml-auto lg:hidden">
        <button type="button" class="p-2 rounded-full text-white hover:bg-white/15 transition" title="Benachrichtigungen">
          <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 17h5l-1.4-1.4A2 2 0 0118 14.2V11a6 6 0 10-12 0v3.2a2 2 0 01-.6 1.4L4 17h5m6 0a3 3 0 11-6 0"/></svg>
        </button>
        <button @click="mobileMenu = !mobileMenu" class="p-2 rounded-full text-white hover:bg-white/15 transition">
          <svg v-if="!mobileMenu" class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" d="M4 6h16M4 12h16M4 18h16"/></svg>
          <svg v-else class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" d="M6 18L18 6M6 6l12 12"/></svg>
        </button>
      </div>
    </div>

    <!-- Mobile Dropdown -->
    <div v-show="mobileMenu" class="lg:hidden" style="background:var(--bl-surface);border-top:1px solid var(--bl-border);">
      <div class="px-4 py-3">
        <Link v-for="item in navItems" :key="item.label" :href="item.href" @click="mobileMenu = false"
          class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-semibold mb-1"
          :style="item.active ? 'background:var(--bl-grad-soft);color:var(--bl-text);' : 'color:var(--bl-text-muted);'">
          <svg class="w-[18px] h-[18px]" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" v-html="icons[item.icon]"></svg>
          <span>{{ item.label }}</span>
          <span v-if="item.badge" style="background:#ef4444;color:#fff;" class="ml-auto min-w-[18px] h-[18px] px-1 rounded-full text-[11px] font-extrabold flex items-center justify-center">{{ item.badge }}</span>
        </Link>

        <div style="border-top:1px solid var(--bl-border);" class="pt-3 mt-2 space-y-1">
          <template v-if="user">
            <Link :href="dashboardRoute" @click="mobileMenu = false" class="block px-3 py-2.5 text-sm font-semibold rounded-xl hover:bg-white/5" style="color:var(--bl-text);">{{ t('nav.my_account') }}</Link>
            <Link :href="route('logout')" method="post" as="button" @click="mobileMenu = false" class="block w-full text-left px-3 py-2.5 text-sm rounded-xl hover:bg-white/5" style="color:var(--bl-text-muted);">{{ t('nav.logout') }}</Link>
          </template>
          <template v-else>
            <Link :href="route('login')" @click="mobileMenu = false" class="block px-3 py-2.5 text-sm font-semibold rounded-xl hover:bg-white/5" style="color:var(--bl-text);">{{ t('nav.login') }}</Link>
            <Link :href="route('register')" @click="mobileMenu = false" class="block text-center px-3 py-2.5 text-sm font-bold rounded-xl mt-1" style="background:var(--bl-pink);color:#fff;">{{ t('nav.register') }}</Link>
          </template>
        </div>

        <div style="border-top:1px solid var(--bl-border);" class="pt-3 mt-3 flex flex-wrap gap-2">
          <a v-for="lang in supportedLocales" :key="lang" :href="route('lang.switch', lang)"
            class="flex items-center gap-1.5 px-2.5 py-1.5 rounded-lg text-xs font-bold transition"
            :style="lang === locale ? 'background:var(--bl-pink);color:#fff;' : 'background:var(--bl-surface-2);color:var(--bl-text-muted);'">
            <span>{{ flags[lang] }}</span><span class="uppercase">{{ lang }}</span>
          </a>
        </div>
      </div>
    </div>
  </header>
</template>

<script setup>
import { ref, computed, onMounted, onBeforeUnmount } from 'vue';
import { Link, usePage } from '@inertiajs/vue3';
import { useI18n } from '@/composables/useI18n';

const { t } = useI18n();
const page = usePage();

const user            = computed(() => page.props.auth?.user ?? null);
const userName        = computed(() => user.value?.name ?? '');
const avatar          = computed(() => user.value?.avatar ?? null);
const initial         = computed(() => (userName.value || '?').trim().charAt(0).toUpperCase());
const locale          = computed(() => page.props.locale ?? 'de');
const supportedLocales = computed(() => page.props.supportedLocales ?? []);

const dashboardRoute = computed(() =>
  user.value?.role === 'inserent' ? route('inserat.dashboard') : route('konto.dashboard')
);
const messagesRoute = computed(() =>
  user.value?.role === 'inserent' ? route('inserat.messages') : route('konto.messages')
);

const cur = (n) => { try { return route().current(n); } catch { return false; } };

// Ungelesene Nachrichten (global geteilt) → roter Badge bei "Nachrichten".
const unreadMessages = computed(() => Number(page.props.unreadMessages ?? 0));

const navItems = computed(() => {
  const items = [
    { label: 'Entdecken', icon: 'home',   href: route('home'),        active: cur('home') },
    { label: 'Feed',      icon: 'feed',   href: route('feed'),        active: cur('feed') },
    { label: 'Suche',     icon: 'search', href: route('search'),      active: cur('search') },
    { label: 'Clubs',     icon: 'clubs',  href: route('clubs.index'), active: cur('clubs.*') },
  ];
  if (user.value) {
    items.push({
      label: 'Nachrichten', icon: 'chat', href: messagesRoute.value, active: cur('*messages*'),
      badge: unreadMessages.value > 0 ? (unreadMessages.value > 9 ? '9+' : unreadMessages.value) : null,
    });
  }
  return items;
});

const mobileMenu = ref(false);
const accOpen    = ref(false);
const langOpen   = ref(false);
const accRef  = ref(null);
const langRef = ref(null);

const flags = { de: '🇩🇪', en: '🇬🇧', fr: '🇫🇷', it: '🇮🇹', es: '🇪🇸', hu: '🇭🇺', ro: '🇷🇴' };
const langLabels = { de: 'Deutsch', en: 'English', fr: 'Français', it: 'Italiano', es: 'Español', hu: 'Magyar', ro: 'Română' };
const currentFlag = computed(() => flags[locale.value] ?? '🌐');

const icons = {
  home:   '<path stroke-linecap="round" stroke-linejoin="round" d="M3 11l9-8 9 8"/><path stroke-linecap="round" stroke-linejoin="round" d="M5 10v10h14V10"/>',
  feed:   '<path stroke-linecap="round" stroke-linejoin="round" d="M4 5h16v11H7l-3 3z"/><path stroke-linecap="round" d="M8 9h8M8 12h5"/>',
  search: '<circle cx="11" cy="11" r="7"/><path stroke-linecap="round" d="M21 21l-4-4"/>',
  clubs:  '<path stroke-linecap="round" stroke-linejoin="round" d="M3 21h18M5 21V8l7-4 7 4v13M9 21v-5h6v5"/>',
  chat:   '<path stroke-linecap="round" stroke-linejoin="round" d="M21 15a2 2 0 01-2 2H8l-4 4V5a2 2 0 012-2h13a2 2 0 012 2z"/>',
};

function onOutside(e) {
  if (accRef.value && !accRef.value.contains(e.target)) accOpen.value = false;
  if (langRef.value && !langRef.value.contains(e.target)) langOpen.value = false;
}
onMounted(() => document.addEventListener('click', onOutside));
onBeforeUnmount(() => document.removeEventListener('click', onOutside));
</script>
