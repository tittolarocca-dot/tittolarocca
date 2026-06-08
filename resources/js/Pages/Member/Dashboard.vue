<template>
  <AppLayout>
    <Head title="Mein Konto" />
    <div class="max-w-4xl mx-auto px-4 py-8">

      <div class="mb-8">
        <h1 class="text-2xl font-bold text-white">Willkommen, {{ $page.props.auth.user.name }}</h1>
        <p class="text-gray-400 text-sm mt-1">Dein Mitglieder-Bereich – Profile abonnieren & exklusive Inhalte freischalten</p>
      </div>

      <!-- Schnellzugriff -->
      <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 mb-8">
        <Link v-for="action in actions" :key="action.label" :href="action.href"
          class="bg-[#1a1a1a] rounded-xl border border-white/8 p-4 flex items-center gap-3 hover:border-[#e35d8f]/40 transition group">
          <span class="text-2xl">{{ action.icon }}</span>
          <div>
            <p class="text-sm font-semibold text-white group-hover:text-[#e35d8f] transition">{{ action.label }}</p>
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
            <h2 class="font-semibold text-white">Meine Favoriten</h2>
            <span v-if="favoritesCount > 0" class="text-xs bg-[#e35d8f]/15 text-[#e35d8f] font-bold px-2 py-0.5 rounded-full">{{ favoritesCount }}</span>
          </div>
          <Link :href="route('konto.favorites')" class="text-xs text-[#e35d8f] hover:underline">Alle anzeigen →</Link>
        </div>

        <div v-if="favoritesPreview.length === 0" class="text-center py-8 text-gray-500">
          <svg class="w-8 h-8 text-gray-600 mx-auto mb-2" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
            <path stroke-linecap="round" stroke-linejoin="round" d="M4.318 6.318a4.5 4.5 0 016.364 0L12 7.636l1.318-1.318a4.5 4.5 0 116.364 6.364L12 20.364l-7.682-7.682a4.5 4.5 0 010-6.364z"/>
          </svg>
          <p class="text-sm">Noch keine Favoriten gespeichert.</p>
          <Link :href="route('home')" class="text-[#e35d8f] text-sm hover:underline mt-1 inline-block">
            Jetzt Profile entdecken →
          </Link>
        </div>

        <div v-else class="grid grid-cols-2 sm:grid-cols-4 gap-3">
          <Link v-for="item in favoritesPreview" :key="item.slug"
            :href="route('profile.show', item.slug)"
            class="group block rounded-xl overflow-hidden border border-white/8 hover:border-[#e35d8f]/40 transition">
            <div class="aspect-[3/4] relative overflow-hidden bg-[#111]">
              <img v-if="item.cover_url" :src="item.cover_url" :alt="item.display_name"
                class="w-full h-full object-cover group-hover:scale-105 transition duration-300" loading="lazy" />
              <div v-else class="w-full h-full flex items-center justify-center">
                <svg class="w-8 h-8 text-gray-600" fill="currentColor" viewBox="0 0 20 20">
                  <path fill-rule="evenodd" d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z" clip-rule="evenodd"/>
                </svg>
              </div>
            </div>
            <div class="px-2 py-2 flex items-center gap-1">
              <span class="text-xs font-semibold text-white truncate group-hover:text-[#e35d8f] transition">{{ item.display_name }}</span>
              <span v-if="item.verification_status === 'approved'"
                class="inline-flex items-center justify-center w-3.5 h-3.5 rounded-full bg-green-500 shrink-0">
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
          <h2 class="font-semibold text-white">Meine Abonnements</h2>
          <Link :href="route('konto.subscriptions')" class="text-xs text-[#e35d8f] hover:underline">Alle anzeigen →</Link>
        </div>

        <div v-if="subscriptions.length === 0" class="text-center py-8 text-gray-500">
          <div class="text-3xl mb-2">💫</div>
          <p class="text-sm">Du hast noch keine Abonnements.</p>
          <Link :href="route('home')" class="text-[#e35d8f] text-sm hover:underline mt-1 inline-block">
            Jetzt Profile entdecken →
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
              <span class="text-xs text-green-400">Aktiv</span>
            </div>
          </Link>
        </div>
      </div>

    </div>
  </AppLayout>
</template>

<script setup>
import { Head, Link } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';

const props = defineProps({
  subscriptions:    { type: Array,  default: () => [] },
  favoritesPreview: { type: Array,  default: () => [] },
  favoritesCount:   { type: Number, default: 0 },
});

const actions = [
  { icon: '🔍', label: 'Profile entdecken', desc: 'Kostenlos stöbern',     href: route('home') },
  { icon: '💫', label: 'Abonnements',        desc: 'Aktive Abos verwalten', href: route('konto.subscriptions') },
  { icon: '💬', label: 'Nachrichten',         desc: 'Konversationen',        href: route('konto.messages') },
  { icon: '❤️', label: 'Meine Favoriten',     desc: 'Gespeicherte Inserate', href: route('konto.favorites') },
];
</script>
