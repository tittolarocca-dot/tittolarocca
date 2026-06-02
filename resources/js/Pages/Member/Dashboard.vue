<template>
  <AppLayout>
    <Head title="Mein Konto" />
    <div class="max-w-4xl mx-auto px-4 py-8">

      <div class="mb-8">
        <h1 class="text-2xl font-bold text-gray-900">Willkommen, {{ $page.props.auth.user.name }}</h1>
        <p class="text-gray-500 text-sm mt-1">Dein Mitglieder-Bereich – Profile abonnieren & exklusive Inhalte freischalten</p>
      </div>

      <!-- Schnellzugriff -->
      <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-8">
        <Link v-for="action in actions" :key="action.label" :href="action.href"
          class="bg-white rounded-xl border border-gray-200 p-5 flex items-center gap-4 hover:border-[#e35d8f]/40 transition group shadow-sm">
          <span class="text-3xl">{{ action.icon }}</span>
          <div>
            <p class="text-sm font-semibold text-gray-800 group-hover:text-[#e35d8f] transition">{{ action.label }}</p>
            <p class="text-xs text-gray-500 mt-0.5">{{ action.desc }}</p>
          </div>
        </Link>
      </div>

      <!-- Aktive Abonnements Vorschau -->
      <div class="bg-white rounded-xl border border-gray-200 p-6 shadow-sm">
        <div class="flex items-center justify-between mb-4">
          <h2 class="font-semibold text-gray-900">Meine Abonnements</h2>
          <Link :href="route('konto.subscriptions')" class="text-xs text-[#e35d8f] hover:underline">Alle anzeigen →</Link>
        </div>

        <div v-if="subscriptions.length === 0" class="text-center py-10 text-gray-400">
          <div class="text-4xl mb-3">💫</div>
          <p class="text-sm">Du hast noch keine Abonnements.</p>
          <Link :href="route('home')" class="text-[#e35d8f] text-sm hover:underline mt-2 inline-block">
            Jetzt Profile entdecken →
          </Link>
        </div>

        <div v-else class="space-y-3">
          <div v-for="sub in subscriptions.slice(0,3)" :key="sub.id"
            class="flex items-center justify-between py-3 border-b border-gray-100 last:border-0">
            <Link :href="route('profile.show', sub.profile.slug)"
              class="font-semibold text-gray-900 hover:text-[#e35d8f] transition text-sm">
              {{ sub.profile.display_name }}
            </Link>
            <div class="text-right">
              <p class="text-xs font-bold text-gray-900">CHF {{ sub.amount_chf }}/Mo</p>
              <span class="text-xs" :class="sub.status === 'active' ? 'text-green-600' : 'text-yellow-600'">
                {{ sub.status === 'active' ? 'Aktiv' : 'Inaktiv' }}
              </span>
            </div>
          </div>
        </div>
      </div>

      <!-- Meine Favoriten -->
      <div class="bg-white rounded-xl border border-gray-200 p-6 shadow-sm mt-6">
        <div class="flex items-center justify-between mb-4">
          <div class="flex items-center gap-2">
            <svg class="w-5 h-5 text-[#e35d8f]" fill="currentColor" viewBox="0 0 24 24">
              <path d="M4.318 6.318a4.5 4.5 0 016.364 0L12 7.636l1.318-1.318a4.5 4.5 0 116.364 6.364L12 20.364l-7.682-7.682a4.5 4.5 0 010-6.364z"/>
            </svg>
            <h2 class="font-semibold text-white">Meine Favoriten</h2>
            <span v-if="favoritesCount > 0" class="text-xs bg-[#e35d8f]/10 text-[#e35d8f] font-bold px-2 py-0.5 rounded-full">{{ favoritesCount }}</span>
          </div>
          <Link :href="route('konto.favorites')" class="text-xs text-[#e35d8f] hover:underline">Alle anzeigen →</Link>
        </div>

        <div v-if="favoritesCount === 0" class="text-center py-8 text-gray-400">
          <svg class="w-8 h-8 text-gray-300 mx-auto mb-2" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
            <path stroke-linecap="round" stroke-linejoin="round" d="M4.318 6.318a4.5 4.5 0 016.364 0L12 7.636l1.318-1.318a4.5 4.5 0 116.364 6.364L12 20.364l-7.682-7.682a4.5 4.5 0 010-6.364z"/>
          </svg>
          <p class="text-sm">Noch keine Favoriten gespeichert.</p>
          <Link :href="route('home')" class="text-[#e35d8f] text-sm hover:underline mt-1 inline-block">
            Jetzt Profile entdecken →
          </Link>
        </div>
        <div v-else class="text-sm text-gray-500">
          Du hast <span class="font-semibold text-gray-800">{{ favoritesCount }}</span> {{ favoritesCount === 1 ? 'Inserat' : 'Inserate' }} als Favorit gespeichert.
          <Link :href="route('konto.favorites')" class="text-[#e35d8f] hover:underline ml-1">Alle anzeigen →</Link>
        </div>
      </div>

      <!-- Hinweis: Wie funktioniert es? -->
      <div class="mt-6 bg-gray-50 rounded-xl border border-gray-200 p-5">
        <h3 class="font-semibold text-gray-900 text-sm mb-3">So funktioniert es</h3>
        <div class="grid sm:grid-cols-3 gap-4 text-xs text-gray-600">
          <div class="flex gap-3">
            <span class="text-xl shrink-0">🔍</span>
            <div>
              <p class="font-semibold text-gray-800 mb-0.5">Profile kostenlos ansehen</p>
              <p>Alle öffentlichen Inserate sind gratis zugänglich – ohne Abo.</p>
            </div>
          </div>
          <div class="flex gap-3">
            <span class="text-xl shrink-0">🔒</span>
            <div>
              <p class="font-semibold text-gray-800 mb-0.5">Exklusive Inhalte freischalten</p>
              <p>Abonniere ein Profil für CHF X/Monat, um private Fotos & Videos zu sehen.</p>
            </div>
          </div>
          <div class="flex gap-3">
            <span class="text-xl shrink-0">💬</span>
            <div>
              <p class="font-semibold text-gray-800 mb-0.5">Direkt Schreiben</p>
              <p>Als Abonnent kannst du der Person direkt Nachrichten senden.</p>
            </div>
          </div>
        </div>
      </div>

    </div>
  </AppLayout>
</template>

<script setup>
import { Head, Link, usePage } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';

const props = defineProps({
  subscriptions:  { type: Array,  default: () => [] },
  favoritesCount: { type: Number, default: 0 },
});

const actions = [
  { icon: '🔍', label: 'Profile entdecken', desc: 'Kostenlos stöbern',        href: route('home') },
  { icon: '💫', label: 'Meine Abonnements', desc: 'Aktive Abos verwalten',     href: route('konto.subscriptions') },
  { icon: '💬', label: 'Nachrichten',        desc: 'Konversationen ansehen',    href: route('konto.messages') },
  { icon: '❤️', label: 'Meine Favoriten',    desc: 'Gespeicherte Inserate',     href: route('konto.favorites') },
];
</script>
