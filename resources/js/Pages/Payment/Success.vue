<template>
  <AppLayout>
    <Head title="Zahlung erfolgreich" />

    <div class="min-h-[60vh] flex items-center justify-center px-4 py-16">
      <div class="bg-[#1a1a1a] rounded-2xl border border-green-700/30 p-10 max-w-md w-full text-center">

        <!-- Checkmark -->
        <div class="w-20 h-20 bg-green-900/30 rounded-full flex items-center justify-center mx-auto mb-6">
          <svg class="w-10 h-10 text-green-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
            <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
          </svg>
        </div>

        <h1 class="text-2xl font-bold text-white mb-2">Zahlung erfolgreich!</h1>
        <p class="text-gray-400 mb-6">
          Dein Inserat ist jetzt <span class="text-green-500 font-semibold">sofort aktiv</span>
          und für alle Besucher sichtbar.
        </p>

        <!-- Nächste Schritte -->
        <div class="bg-[#111111] rounded-xl p-4 text-left space-y-3 mb-8">
          <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide mb-2">Nächste Schritte</p>
          <div v-for="step in nextSteps" :key="step.text" class="flex items-start gap-3">
            <span class="text-lg shrink-0">{{ step.icon }}</span>
            <div>
              <p class="text-sm font-medium text-gray-200">{{ step.text }}</p>
              <p class="text-xs text-gray-500">{{ step.sub }}</p>
            </div>
          </div>
        </div>

        <div class="flex flex-col sm:flex-row gap-3">
          <Link :href="route('inserat.media.index')"
            class="flex-1 bg-[#e91e8c] text-white rounded-lg px-4 py-2.5 text-sm font-bold hover:bg-[#c91478] transition text-center">
            Fotos hochladen
          </Link>
          <Link :href="route('inserat.dashboard')"
            class="flex-1 border border-[#2a2a2a] text-gray-400 rounded-lg px-4 py-2.5 text-sm font-semibold hover:border-[#3a3a3a] hover:text-white transition text-center">
            Zum Dashboard
          </Link>
        </div>
      </div>
    </div>
  </AppLayout>
</template>

<script setup>
import { Head, Link } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';

const nextSteps = [
  { icon: '🖼️', text: 'Fotos hochladen',        sub: 'Öffentliche Fotos + private für Abonnenten' },
  { icon: '✏️', text: 'Profil vervollständigen', sub: 'Beschreibung und Tags prüfen' },
  { icon: '📣', text: 'Inserat teilen',           sub: 'Link kopieren und weiterteilen' },
];
</script>
