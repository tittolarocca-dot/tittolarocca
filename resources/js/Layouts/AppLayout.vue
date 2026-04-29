<template>
  <div class="min-h-screen bg-[#0f0f0f] text-white">
    <FlashMessage />

    <!-- Navbar (pink bar like fgirl.ch) -->
    <header class="bg-[#e91e8c] sticky top-0 z-50 shadow-lg">
      <div class="max-w-7xl mx-auto px-4 flex items-center h-13 gap-4">

        <!-- Logo -->
        <Link href="/" class="flex items-center gap-2.5 shrink-0">
          <img src="/images/logo.png" alt="Logo" class="h-8 w-8 rounded-xl object-cover" />
          <span class="text-lg font-black text-white">inserate<span class="text-white/70">®</span></span>
        </Link>

        <!-- Desktop city nav -->
        <nav class="hidden md:flex items-center gap-5 text-sm text-white/80 flex-1">
          <Link v-for="city in cities.slice(0,6)" :key="city.id"
            :href="route('city', city.slug)"
            class="hover:text-white transition whitespace-nowrap font-medium"
            :class="{ 'text-white font-bold underline underline-offset-4': activeCitySlug === city.slug }">
            {{ city.name }}
          </Link>
        </nav>

        <!-- Desktop auth -->
        <div class="hidden md:flex items-center gap-3 ml-auto shrink-0">
          <template v-if="$page.props.auth.user">
            <Link :href="dashboardRoute" class="text-sm text-white/90 hover:text-white transition font-medium">
              Mein Konto
            </Link>
            <Link :href="route('logout')" method="post" as="button"
              class="text-sm text-white/70 hover:text-white transition">
              Abmelden
            </Link>
          </template>
          <template v-else>
            <Link :href="route('login')"
              class="text-sm text-white/90 hover:text-white transition flex items-center gap-1.5 font-medium">
              <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
              </svg>
              Anmelden
            </Link>
            <Link :href="route('register')"
              class="bg-white text-[#e91e8c] text-sm font-bold px-4 py-1.5 rounded-full hover:bg-white/90 transition">
              Inserat aufgeben
            </Link>
          </template>
        </div>

        <!-- Mobile right side -->
        <div class="flex items-center gap-2 ml-auto md:hidden">
          <template v-if="!$page.props.auth.user">
            <Link :href="route('register')"
              class="bg-white text-[#e91e8c] text-xs font-bold px-3 py-1.5 rounded-full transition">
              Inserieren
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
        <p class="text-xs text-gray-500 uppercase tracking-wide font-semibold mb-2">Städte</p>
        <div class="grid grid-cols-3 gap-2 mb-4">
          <Link v-for="city in cities.slice(0,6)" :key="city.id"
            :href="route('city', city.slug)"
            @click="mobileMenu = false"
            class="text-sm text-center py-2 px-1 rounded-lg bg-white/5 hover:bg-[#e91e8c]/20 hover:text-[#e91e8c] transition text-gray-300 font-medium">
            {{ city.name }}
          </Link>
        </div>
        <div class="border-t border-white/10 pt-3 space-y-1">
          <template v-if="$page.props.auth.user">
            <Link :href="dashboardRoute" @click="mobileMenu = false"
              class="flex items-center gap-3 py-2.5 px-1 text-sm font-semibold text-gray-200 hover:text-[#e91e8c] transition">
              <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
              </svg>
              Mein Konto
            </Link>
            <Link :href="route('logout')" method="post" as="button" @click="mobileMenu = false"
              class="flex items-center gap-3 py-2.5 px-1 text-sm text-gray-400 hover:text-red-400 transition w-full">
              <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
              </svg>
              Abmelden
            </Link>
          </template>
          <template v-else>
            <Link :href="route('login')" @click="mobileMenu = false"
              class="flex items-center gap-3 py-2.5 px-1 text-sm font-semibold text-gray-200 hover:text-[#e91e8c] transition">
              <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
              </svg>
              Anmelden
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
          <h5 class="text-white font-semibold mb-3 uppercase text-xs tracking-wide">Plattform</h5>
          <div class="space-y-2">
            <a href="#" class="block hover:text-[#e91e8c] transition">Über uns</a>
            <a href="#" class="block hover:text-[#e91e8c] transition">Kontakt</a>
          </div>
        </div>
        <div>
          <h5 class="text-white font-semibold mb-3 uppercase text-xs tracking-wide">Inserieren</h5>
          <div class="space-y-2">
            <Link :href="route('register')" class="block hover:text-[#e91e8c] transition">Inserat erstellen</Link>
            <a href="#" class="block hover:text-[#e91e8c] transition">Preise</a>
          </div>
        </div>
        <div>
          <h5 class="text-white font-semibold mb-3 uppercase text-xs tracking-wide">Städte</h5>
          <div class="space-y-1">
            <Link v-for="city in cities.slice(0,6)" :key="city.id" :href="route('city', city.slug)"
              class="block hover:text-[#e91e8c] transition">{{ city.name }}</Link>
          </div>
        </div>
        <div>
          <h5 class="text-white font-semibold mb-3 uppercase text-xs tracking-wide">Rechtliches</h5>
          <div class="space-y-2">
            <a href="#" class="block hover:text-[#e91e8c] transition">Impressum</a>
            <a href="#" class="block hover:text-[#e91e8c] transition">Datenschutz</a>
            <a href="#" class="block hover:text-[#e91e8c] transition">AGB</a>
          </div>
        </div>
      </div>
      <div class="max-w-7xl mx-auto px-4 mt-8 pt-6 border-t border-white/10 text-xs text-center text-gray-600">
        © {{ new Date().getFullYear() }} Inserate Plattform – Nur für Erwachsene ab 18 Jahren.
      </div>
    </footer>
  </div>
</template>

<script setup>
import { ref } from 'vue';
import { Link, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';
import FlashMessage from '@/Components/FlashMessage.vue';

const page = usePage();
const cities = computed(() => page.props.cities ?? []);
const activeCitySlug = computed(() => page.props.activeCity?.slug);
const dashboardRoute = computed(() =>
  page.props.auth.user?.role === 'inserent'
    ? route('inserat.dashboard')
    : route('konto.dashboard')
);

const mobileMenu = ref(false);
</script>
