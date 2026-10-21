<template>
  <AppLayout>
    <div class="max-w-7xl mx-auto px-4 py-5 sm:py-6">
      <!-- Mobile: Menü-Button -->
      <button type="button" @click="open = true"
        class="lg:hidden inline-flex items-center gap-2 text-sm font-semibold text-gray-200 bg-[#1a1a1a] border border-white/10 rounded-lg px-3 py-2 mb-4">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" d="M4 6h16M4 12h16M4 18h16"/></svg>
        {{ t('shell.menu') }}
      </button>

      <div class="lg:grid lg:grid-cols-[250px_1fr] lg:gap-6">
        <!-- Sidebar -->
        <aside
          class="bg-[#1a1a1a] border border-white/8 rounded-2xl p-4 flex flex-col gap-1
                 fixed inset-y-0 left-0 z-50 w-[270px] rounded-none overflow-y-auto transition-transform duration-200
                 lg:static lg:z-auto lg:w-auto lg:rounded-2xl lg:self-start lg:sticky lg:top-[68px] lg:translate-x-0"
          :class="open ? 'translate-x-0' : '-translate-x-[105%]'">
          <!-- Nutzerblock -->
          <div class="flex items-center gap-3 px-1.5 pb-3.5 mb-2 border-b border-white/8">
            <span class="w-10 h-10 rounded-full overflow-hidden bg-gradient-to-br from-[#e35d8f] to-[#7c3aed] flex items-center justify-center text-white font-extrabold shrink-0">
              <img v-if="avatarUrl" :src="avatarUrl" :alt="userName" class="w-full h-full object-cover" />
              <template v-else>{{ initial }}</template>
            </span>
            <div class="min-w-0">
              <p class="text-sm font-bold text-white truncate leading-tight">{{ userName }}</p>
              <p class="text-xs text-gray-500">{{ roleLabel }}</p>
            </div>
            <button type="button" @click="open = false" class="lg:hidden ml-auto text-gray-500 hover:text-white p-1">✕</button>
          </div>

          <!-- Navigation -->
          <nav class="flex flex-col gap-0.5">
            <Link v-for="item in items" :key="item.label" :href="item.href"
              class="relative flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-semibold transition"
              :class="item.active
                ? 'text-white bg-[#e35d8f]/14'
                : 'text-gray-400 hover:text-white hover:bg-white/5'">
              <span v-if="item.active" class="absolute -left-4 top-2 bottom-2 w-[3px] rounded-r bg-[#e35d8f]"></span>
              <svg class="w-[18px] h-[18px] shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" v-html="icons[item.icon]"></svg>
              {{ item.label }}
              <span v-if="item.badge" class="ml-auto min-w-[19px] h-[19px] px-1.5 rounded-full bg-[#e35d8f] text-white text-[11px] font-extrabold flex items-center justify-center">{{ item.badge > 99 ? '99+' : item.badge }}</span>
            </Link>

            <div class="h-px bg-white/8 my-2 mx-1"></div>

            <a :href="route('home')"
              class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-semibold text-gray-400 hover:text-white hover:bg-white/5 transition">
              <svg class="w-[18px] h-[18px] shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" v-html="icons.home"></svg>
              {{ t('shell.website') }}
            </a>
            <button type="button" @click="logout"
              class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-semibold text-red-400 hover:bg-red-500/10 transition text-left">
              <svg class="w-[18px] h-[18px] shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" v-html="icons.logout"></svg>
              {{ t('shell.logout') }}
            </button>
          </nav>
        </aside>

        <!-- Scrim (Mobile) -->
        <div v-if="open" class="fixed inset-0 bg-black/60 z-40 lg:hidden" @click="open = false"></div>

        <!-- Hauptbereich -->
        <main class="min-w-0">
          <slot />
        </main>
      </div>
    </div>
  </AppLayout>
</template>

<script setup>
import { ref, computed } from 'vue';
import { Link, router, usePage } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import { useI18n } from '@/composables/useI18n';

const { t } = useI18n();

const props = defineProps({
  role:   { type: String, default: 'inserent' }, // 'inserent' | 'member'
  badges: { type: Object, default: () => ({}) },  // { messages, gallery }
});

const page = usePage();
const open = ref(false);

const userName  = computed(() => page.props.auth?.user?.name ?? '');
const avatarUrl = computed(() => page.props.auth?.user?.avatar ?? null);
const initial   = computed(() => (userName.value || '?').trim().charAt(0).toUpperCase());
const roleLabel = computed(() => props.role === 'member' ? t('shell.role_member') : t('shell.role_inserent'));

const cur = (name) => { try { return route().current(name); } catch { return false; } };

const items = computed(() => {
  if (props.role === 'member') {
    return [
      { label: t('shell.dashboard'),     icon: 'grid',     href: route('konto.dashboard'),     active: cur('konto.dashboard') },
      { label: t('shell.my_profile'),    icon: 'user',     href: route('konto.account.edit'),  active: cur('konto.account.*') },
      { label: t('shell.discover'),      icon: 'search',   href: route('home'),                active: false },
      { label: t('shell.messages'),      icon: 'chat',     href: route('konto.messages'),      active: cur('konto.messages*') },
      { label: t('shell.favorites'),     icon: 'heart',    href: route('konto.favorites'),     active: cur('konto.favorites*') },
      { label: t('shell.subscriptions'), icon: 'sub',      href: route('konto.subscriptions'), active: cur('konto.subscriptions') },
      { label: t('shell.visitors'),      icon: 'visitors', href: route('konto.visitors'),      active: cur('konto.visitors') },
      { label: t('shell.reviews'),       icon: 'star',     href: route('konto.reviews.mine'),  active: cur('konto.reviews.*') },
    ];
  }
  return [
    { label: t('shell.dashboard'),        icon: 'grid',     href: route('inserat.dashboard'),        active: cur('inserat.dashboard') },
    { label: t('shell.edit_profile'),     icon: 'edit',     href: route('inserat.profile.edit'),     active: cur('inserat.profile.*') },
    { label: t('shell.media'),            icon: 'image',    href: route('inserat.media.index'),      active: cur('inserat.media.*') },
    { label: t('shell.posts'),            icon: 'post',     href: route('inserat.posts.index'),      active: cur('inserat.posts.*') },
    { label: t('shell.messages'),         icon: 'chat',     href: route('inserat.messages'),         active: cur('inserat.messages*'), badge: props.badges.messages },
    { label: t('shell.gallery_requests'), icon: 'lock',     href: route('inserat.gallery.requests'), active: cur('inserat.gallery.*'), badge: props.badges.gallery },
    { label: t('shell.visitors'),         icon: 'visitors', href: route('inserat.visitors'),         active: cur('inserat.visitors') },
    { label: t('shell.verification'),     icon: 'shield',   href: route('inserat.verification.page'), active: cur('inserat.verification.page') },
    { label: t('shell.age_identity'),     icon: 'idcard',   href: route('inserat.age.page'),         active: cur('inserat.age.page') },
    { label: t('shell.settings'),         icon: 'gear',     href: route('inserat.settings'),         active: cur('inserat.settings') },
  ];
});

function logout() {
  router.post(route('logout'));
}

const icons = {
  grid: '<rect x="3" y="3" width="7" height="7" rx="1.5"/><rect x="14" y="3" width="7" height="7" rx="1.5"/><rect x="3" y="14" width="7" height="7" rx="1.5"/><rect x="14" y="14" width="7" height="7" rx="1.5"/>',
  edit: '<path d="M12 20h9"/><path d="M16.5 3.5a2.1 2.1 0 013 3L7 19l-4 1 1-4z"/>',
  image: '<rect x="3" y="4" width="18" height="16" rx="2"/><circle cx="9" cy="10" r="2"/><path d="M3 17l5-4 4 3 3-2 6 5"/>',
  post: '<path d="M4 5h16v11H7l-3 3z"/><path d="M8 9h8M8 12h5"/>',
  chat: '<path d="M21 15a2 2 0 01-2 2H8l-4 4V5a2 2 0 012-2h13a2 2 0 012 2z"/>',
  lock: '<rect x="5" y="11" width="14" height="10" rx="2"/><path d="M8 11V8a4 4 0 018 0v3"/>',
  visitors: '<circle cx="9" cy="7" r="3"/><path d="M3 20a6 6 0 0112 0"/><circle cx="17.5" cy="9" r="2.3"/><path d="M15 20a5 5 0 016-4.6"/>',
  user: '<circle cx="12" cy="8" r="3.5"/><path d="M5 20a7 7 0 0114 0"/>',
  search: '<circle cx="11" cy="11" r="7"/><path d="M21 21l-4-4"/>',
  heart: '<path d="M12 20s-6.5-4-9-7.5C1.4 10 2.3 6.5 6 6.5c1.9 0 3.1 1.1 4 2.3.9-1.2 2.1-2.3 4-2.3 3.7 0 4.6 3.5 3 6-2.5 3.5-9 7.5-9 7.5z"/>',
  sub: '<path d="M4 12a8 8 0 018-8M20 12a8 8 0 01-8 8"/><path d="M12 4l3 3-3 3M12 20l-3-3 3-3"/>',
  star: '<path d="M12 3l2.6 5.3 5.9.9-4.3 4.1 1 5.8L12 16.9 6.8 19.2l1-5.8L3.5 9.2l5.9-.9z"/>',
  home: '<path d="M3 11l9-8 9 8"/><path d="M5 10v10h14V10"/>',
  shield: '<path d="M12 3l7 3v6c0 4.5-3 7.5-7 9-4-1.5-7-4.5-7-9V6z"/><path d="M9 12l2 2 4-4"/>',
  idcard: '<rect x="3" y="5" width="18" height="14" rx="2"/><circle cx="9" cy="11" r="2"/><path d="M6 16a3 3 0 016 0M14 9h4M14 13h3"/>',
  gear: '<circle cx="12" cy="12" r="3"/><path d="M19 12a7 7 0 00-.1-1l2-1.5-2-3.5-2.3 1a7 7 0 00-1.7-1l-.3-2.5h-4l-.3 2.5a7 7 0 00-1.7 1l-2.3-1-2 3.5L4.1 11a7 7 0 000 2l-2 1.5 2 3.5 2.3-1a7 7 0 001.7 1l.3 2.5h4l.3-2.5a7 7 0 001.7-1l2.3 1 2-3.5-2-1.5c.07-.33.1-.66.1-1z"/>',
  logout: '<path d="M9 21H5a2 2 0 01-2-2V5a2 2 0 012-2h4"/><path d="M16 17l5-5-5-5M21 12H9"/>',
};
</script>
