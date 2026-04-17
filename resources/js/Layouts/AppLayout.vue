<template>
  <div class="min-h-screen bg-gray-50">
    <FlashMessage />
    <!-- Navbar -->
    <header class="bg-pink-600 sticky top-0 z-50 shadow-md">
      <div class="max-w-7xl mx-auto px-4 flex items-center h-12 gap-0">
        <Link href="/" class="text-white font-bold text-xl mr-6 shrink-0">
          Inserate<span class="text-pink-200">Plattform</span>
        </Link>
        <nav class="hidden md:flex flex-1 h-full">
          <Link v-for="city in cities" :key="city.id"
            :href="route('city', city.slug)"
            class="flex items-center px-3 h-full text-white text-xs font-semibold hover:bg-pink-700 border-r border-pink-500 transition-colors"
            :class="{ 'bg-pink-700': activeCitySlug === city.slug }">
            {{ city.name }}
          </Link>
        </nav>
        <div class="flex items-center gap-2 ml-auto shrink-0">
          <template v-if="$page.props.auth.user">
            <Link :href="route('inserat.dashboard')" class="text-white text-xs font-semibold px-3 py-1.5 hover:bg-pink-500 rounded transition">
              Mein Inserat
            </Link>
            <Link :href="route('logout')" method="post" as="button"
              class="text-white text-xs font-semibold px-3 py-1.5 hover:bg-pink-500 rounded transition">
              Logout
            </Link>
          </template>
          <template v-else>
            <Link :href="route('register')" class="text-white text-xs font-semibold px-3 py-1.5 hover:bg-pink-500 rounded transition">
              Registrieren
            </Link>
            <Link :href="route('login')" class="bg-white text-pink-600 text-xs font-bold px-3 py-1.5 rounded hover:bg-pink-50 transition">
              Login
            </Link>
          </template>
        </div>
      </div>
    </header>

    <!-- Page Content -->
    <main>
      <slot />
    </main>

    <!-- Footer -->
    <footer class="bg-gray-800 text-gray-400 mt-16 py-10">
      <div class="max-w-7xl mx-auto px-4 grid grid-cols-2 md:grid-cols-4 gap-6 text-sm">
        <div>
          <h5 class="text-white font-semibold mb-3 uppercase text-xs tracking-wide">Plattform</h5>
          <div class="space-y-2">
            <a href="#" class="block hover:text-pink-400 transition">Über uns</a>
            <a href="#" class="block hover:text-pink-400 transition">Kontakt</a>
          </div>
        </div>
        <div>
          <h5 class="text-white font-semibold mb-3 uppercase text-xs tracking-wide">Inserieren</h5>
          <div class="space-y-2">
            <Link :href="route('register')" class="block hover:text-pink-400 transition">Inserat erstellen</Link>
            <a href="#" class="block hover:text-pink-400 transition">Preise</a>
          </div>
        </div>
        <div>
          <h5 class="text-white font-semibold mb-3 uppercase text-xs tracking-wide">Städte</h5>
          <div class="space-y-1">
            <Link v-for="city in cities.slice(0,6)" :key="city.id"
              :href="route('city', city.slug)"
              class="block hover:text-pink-400 transition">{{ city.name }}</Link>
          </div>
        </div>
        <div>
          <h5 class="text-white font-semibold mb-3 uppercase text-xs tracking-wide">Rechtliches</h5>
          <div class="space-y-2">
            <a href="#" class="block hover:text-pink-400 transition">Impressum</a>
            <a href="#" class="block hover:text-pink-400 transition">Datenschutz</a>
            <a href="#" class="block hover:text-pink-400 transition">AGB</a>
          </div>
        </div>
      </div>
      <div class="max-w-7xl mx-auto px-4 mt-8 pt-6 border-t border-gray-700 text-xs text-center text-gray-500">
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
</script>
