<template>
  <div class="min-h-screen bg-[#0f0f0f] text-white">
    <FlashMessage />

    <!-- Navbar -->
    <header class="bg-[#1a1a1a] border-b border-[#2a2a2a] sticky top-0 z-50">
      <div class="max-w-7xl mx-auto px-4 flex items-center h-14 gap-6">
        <Link href="/" class="text-2xl font-black shrink-0">
          <span class="text-white">inserate</span><span class="text-[#e91e8c]">®</span>
        </Link>
        <nav class="hidden md:flex items-center gap-5 text-sm text-gray-400">
          <Link v-for="city in cities.slice(0,6)" :key="city.id"
            :href="route('city', city.slug)"
            class="hover:text-white transition"
            :class="{ 'text-white font-semibold': activeCitySlug === city.slug }">
            {{ city.name }}
          </Link>
        </nav>
        <div class="flex items-center gap-3 ml-auto shrink-0">
          <template v-if="$page.props.auth.user">
            <Link :href="dashboardRoute" class="text-sm text-gray-300 hover:text-white transition">
              Mein Konto
            </Link>
            <Link :href="route('logout')" method="post" as="button" class="text-sm text-gray-400 hover:text-white transition">
              Abmelden
            </Link>
          </template>
          <template v-else>
            <Link :href="route('login')" class="text-sm text-gray-300 hover:text-white transition flex items-center gap-1.5">
              <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
              Anmelden
            </Link>
            <Link :href="route('register')"
              class="bg-[#e91e8c] hover:bg-[#c91478] text-white text-sm font-bold px-4 py-2 rounded transition">
              Inserat aufgeben
            </Link>
          </template>
        </div>
      </div>
    </header>

    <main><slot /></main>

    <!-- Footer -->
    <footer class="bg-[#0a0a0a] border-t border-[#1a1a1a] mt-16 py-10">
      <div class="max-w-7xl mx-auto px-4 grid grid-cols-2 md:grid-cols-4 gap-6 text-sm text-gray-500">
        <div>
          <h5 class="text-gray-300 font-semibold mb-3 uppercase text-xs tracking-wide">Plattform</h5>
          <div class="space-y-2">
            <a href="#" class="block hover:text-[#e91e8c] transition">Über uns</a>
            <a href="#" class="block hover:text-[#e91e8c] transition">Kontakt</a>
          </div>
        </div>
        <div>
          <h5 class="text-gray-300 font-semibold mb-3 uppercase text-xs tracking-wide">Inserieren</h5>
          <div class="space-y-2">
            <Link :href="route('register')" class="block hover:text-[#e91e8c] transition">Inserat erstellen</Link>
            <a href="#" class="block hover:text-[#e91e8c] transition">Preise</a>
          </div>
        </div>
        <div>
          <h5 class="text-gray-300 font-semibold mb-3 uppercase text-xs tracking-wide">Städte</h5>
          <div class="space-y-1">
            <Link v-for="city in cities.slice(0,6)" :key="city.id" :href="route('city', city.slug)"
              class="block hover:text-[#e91e8c] transition">{{ city.name }}</Link>
          </div>
        </div>
        <div>
          <h5 class="text-gray-300 font-semibold mb-3 uppercase text-xs tracking-wide">Rechtliches</h5>
          <div class="space-y-2">
            <a href="#" class="block hover:text-[#e91e8c] transition">Impressum</a>
            <a href="#" class="block hover:text-[#e91e8c] transition">Datenschutz</a>
            <a href="#" class="block hover:text-[#e91e8c] transition">AGB</a>
          </div>
        </div>
      </div>
      <div class="max-w-7xl mx-auto px-4 mt-8 pt-6 border-t border-[#1a1a1a] text-xs text-center text-gray-600">
        © {{ new Date().getFullYear() }} Inserate Plattform – Nur für Erwachsene ab 18 Jahren.
      </div>
    </footer>
  </div>
</template>

<script setup>
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
</script>
