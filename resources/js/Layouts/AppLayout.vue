<template>
  <div class="min-h-screen bg-[#0f0f0f] text-white">
    <FlashMessage />

    <!-- Navbar -->
    <header class="bg-[#e35d8f] sticky top-0 z-50 shadow-lg">
      <div class="max-w-7xl mx-auto px-4 flex items-center h-13 gap-4">

        <!-- Logo -->
        <Link :href="route('home')" class="flex items-center gap-2.5 shrink-0">
          <img src="/images/logo.png" alt="Logo" class="h-8 w-8 rounded-xl object-cover" />
          <span class="text-2xl font-black text-white">booklola<span class="text-white/70">.ch</span></span>
        </Link>

        <!-- Desktop right: language switcher + auth -->
        <div class="hidden md:flex items-center gap-3 ml-auto shrink-0">

          <!-- Language Switcher -->
          <div class="relative" ref="langDropdownRef">
            <button @click="langOpen = !langOpen"
              class="flex items-center gap-1.5 text-sm text-white/80 hover:text-white transition font-medium px-2 py-1 rounded-lg hover:bg-white/10">
              <span class="text-base leading-none">{{ currentFlag }}</span>
              <span class="uppercase text-xs font-bold">{{ locale }}</span>
              <svg class="w-3 h-3 transition-transform" :class="langOpen ? 'rotate-180' : ''" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 9l-7 7-7-7"/>
              </svg>
            </button>

            <div v-show="langOpen"
              class="absolute right-0 top-full mt-1.5 bg-[#1a1a1a] border border-white/10 rounded-xl shadow-2xl overflow-hidden min-w-[130px] z-50">
              <a v-for="lang in supportedLocales" :key="lang"
                :href="route('lang.switch', lang)"
                @click="langOpen = false"
                class="flex items-center gap-2.5 px-3 py-2 text-sm hover:bg-white/5 transition"
                :class="lang === locale ? 'text-[#e35d8f] font-bold bg-[#e35d8f]/10' : 'text-gray-300'">
                <span class="text-base">{{ flags[lang] }}</span>
                <span>{{ langLabels[lang] }}</span>
              </a>
            </div>
          </div>

          <template v-if="$page.props.auth.user">
            <Link :href="dashboardRoute" class="text-sm text-white/90 hover:text-white transition font-medium">
              {{ t('nav.my_account') }}
            </Link>
            <Link :href="route('logout')" method="post" as="button"
              class="text-sm text-white/70 hover:text-white transition">
              {{ t('nav.logout') }}
            </Link>
          </template>
          <template v-else>
            <Link :href="route('login')"
              class="text-sm text-white/90 hover:text-white transition flex items-center gap-1.5 font-medium">
              <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
              </svg>
              {{ t('nav.login') }}
            </Link>
            <Link :href="route('register')"
              class="bg-white text-[#e35d8f] text-sm font-bold px-4 py-1.5 rounded-full hover:bg-white/90 transition">
              {{ t('nav.register') }}
            </Link>
          </template>
        </div>

        <!-- Mobile right side -->
        <div class="flex items-center gap-2 ml-auto md:hidden">
          <template v-if="!$page.props.auth.user">
            <Link :href="route('register')"
              class="bg-white text-[#e35d8f] text-xs font-bold px-3 py-1.5 rounded-full transition">
              {{ t('nav.register') }}
            </Link>
          </template>
          <button @click="mobileMenu = !mobileMenu"
            class="p-2 text-white hover:text-white/70 transition rounded-lg">
            <svg v-if="!mobileMenu" class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
            </svg>
            <svg v-else class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
            </svg>
          </button>
        </div>
      </div>

      <!-- Mobile dropdown -->
      <div v-show="mobileMenu" class="md:hidden bg-[#1a1a1a] border-t border-white/10 px-4 py-4">

        <!-- Mobile language switcher -->
        <div class="border-t border-white/10 pt-3 mb-3">
          <p class="text-xs text-gray-500 uppercase tracking-wide font-semibold mb-2">{{ t('nav.language') }}</p>
          <div class="flex flex-wrap gap-2">
            <a v-for="lang in supportedLocales" :key="lang"
              :href="route('lang.switch', lang)"
              class="flex items-center gap-1.5 px-2.5 py-1.5 rounded-lg text-xs font-bold transition"
              :class="lang === locale
                ? 'bg-[#e35d8f] text-white'
                : 'bg-white/5 text-gray-300 hover:bg-white/10'">
              <span>{{ flags[lang] }}</span>
              <span class="uppercase">{{ lang }}</span>
            </a>
          </div>
        </div>

        <div class="border-t border-white/10 pt-3 space-y-1">
          <template v-if="$page.props.auth.user">
            <Link :href="dashboardRoute" @click="mobileMenu = false"
              class="flex items-center gap-3 py-2.5 px-1 text-sm font-semibold text-gray-200 hover:text-[#e35d8f] transition">
              <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
              </svg>
              {{ t('nav.my_account') }}
            </Link>
            <Link :href="route('logout')" method="post" as="button" @click="mobileMenu = false"
              class="flex items-center gap-3 py-2.5 px-1 text-sm text-gray-400 hover:text-red-400 transition w-full">
              <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
              </svg>
              {{ t('nav.logout') }}
            </Link>
          </template>
          <template v-else>
            <Link :href="route('login')" @click="mobileMenu = false"
              class="flex items-center gap-3 py-2.5 px-1 text-sm font-semibold text-gray-200 hover:text-[#e35d8f] transition">
              <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
              </svg>
              {{ t('nav.login') }}
            </Link>
          </template>
        </div>
      </div>
    </header>

    <main><slot /></main>

    <!-- Footer -->
    <footer class="mt-16 py-10 bg-[#1a1a1a] border-t border-white/10">
      <div class="max-w-7xl mx-auto px-4 grid grid-cols-2 md:grid-cols-4 gap-6 text-sm text-gray-400">
        <div>
          <h5 class="text-white font-semibold mb-3 uppercase text-xs tracking-wide">{{ t('nav.footer_platform') }}</h5>
          <div class="space-y-2">
            <a href="#" class="block hover:text-[#e35d8f] transition">{{ t('nav.about') }}</a>
            <a href="#" class="block hover:text-[#e35d8f] transition">{{ t('nav.contact') }}</a>
          </div>
        </div>
        <div>
          <h5 class="text-white font-semibold mb-3 uppercase text-xs tracking-wide">{{ t('nav.footer_list') }}</h5>
          <div class="space-y-2">
            <Link :href="route('register')" class="block hover:text-[#e35d8f] transition">{{ t('nav.create_listing') }}</Link>
            <a href="#" class="block hover:text-[#e35d8f] transition">{{ t('nav.prices') }}</a>
          </div>
        </div>
        <div>
          <h5 class="text-white font-semibold mb-3 uppercase text-xs tracking-wide">{{ t('nav.cities') }}</h5>
          <div class="space-y-1">
            <Link v-for="city in cities.slice(0,6)" :key="city.id"
              :href="route('city', { city: city.slug })"
              class="block hover:text-[#e35d8f] transition">{{ city.name }}</Link>
          </div>
        </div>
        <div>
          <h5 class="text-white font-semibold mb-3 uppercase text-xs tracking-wide">{{ t('nav.footer_legal') }}</h5>
          <div class="space-y-2">
            <a href="#" class="block hover:text-[#e35d8f] transition">{{ t('nav.imprint') }}</a>
            <a href="#" class="block hover:text-[#e35d8f] transition">{{ t('nav.privacy') }}</a>
            <a href="#" class="block hover:text-[#e35d8f] transition">{{ t('nav.terms') }}</a>
          </div>
        </div>
      </div>
      <div class="max-w-7xl mx-auto px-4 mt-8 pt-6 border-t border-white/10 text-xs text-center text-gray-600">
        {{ t('nav.copyright', { year: new Date().getFullYear() }) }}
      </div>
    </footer>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, onBeforeUnmount } from 'vue';
import { Link, usePage } from '@inertiajs/vue3';
import FlashMessage from '@/Components/FlashMessage.vue';
import { useI18n } from '@/composables/useI18n';

const { t } = useI18n();
const page  = usePage();

const locale          = computed(() => page.props.locale ?? 'de');
const supportedLocales = computed(() => page.props.supportedLocales ?? []);
// nur Städte mit slug (route('city', slug) würde sonst die ganze Seite crashen)
const cities          = computed(() => (page.props.cities ?? []).filter((c) => c?.slug));

const dashboardRoute = computed(() =>
  page.props.auth.user?.role === 'inserent'
    ? route('inserat.dashboard')
    : route('konto.dashboard')
);

const mobileMenu = ref(false);
const langOpen   = ref(false);
const langDropdownRef = ref(null);

const flags = {
  de: '🇩🇪', en: '🇬🇧', fr: '🇫🇷',
  it: '🇮🇹', es: '🇪🇸', hu: '🇭🇺', ro: '🇷🇴',
};
const langLabels = {
  de: 'Deutsch', en: 'English', fr: 'Français',
  it: 'Italiano', es: 'Español', hu: 'Magyar', ro: 'Română',
};
const currentFlag = computed(() => flags[locale.value] ?? '🌐');

function closeOnOutsideClick(e) {
  if (langDropdownRef.value && !langDropdownRef.value.contains(e.target)) {
    langOpen.value = false;
  }
}
onMounted(()        => document.addEventListener('click', closeOnOutsideClick));
onBeforeUnmount(()  => document.removeEventListener('click', closeOnOutsideClick));
</script>
