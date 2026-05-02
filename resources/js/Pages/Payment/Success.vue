<template>
  <AppLayout>
    <Head title="Zahlung erfolgreich" />

    <div class="min-h-[60vh] flex items-center justify-center px-4 py-16">
      <div class="bg-white rounded-2xl border border-green-200 p-10 max-w-md w-full text-center shadow-lg">

        <!-- Checkmark -->
        <div class="w-20 h-20 bg-green-50 rounded-full flex items-center justify-center mx-auto mb-6">
          <svg class="w-10 h-10 text-green-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
            <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
          </svg>
        </div>

        <h1 class="text-2xl font-bold text-gray-900 mb-2">Zahlung erfolgreich!</h1>
        <p class="text-gray-500 mb-6">
          Dein Inserat ist jetzt <span class="text-green-600 font-semibold">sofort aktiv</span>
          und für alle Besucher sichtbar.
        </p>

        <!-- Nächste Schritte -->
        <div class="bg-gray-50 rounded-xl p-4 text-left space-y-3 mb-8">
          <p class="text-xs font-semibold text-gray-400 uppercase tracking-wide mb-2">Nächste Schritte</p>
          <div v-for="step in nextSteps" :key="step.text" class="flex items-start gap-3">
            <span class="text-lg shrink-0">{{ step.icon }}</span>
            <div>
              <p class="text-sm font-medium text-gray-800">{{ step.text }}</p>
              <p class="text-xs text-gray-500">{{ step.sub }}</p>
            </div>
          </div>
        </div>

        <div class="flex flex-col sm:flex-row gap-3">
          <Link :href="route('inserat.media.index')"
            class="flex-1 bg-[#e35d8f] text-white rounded-lg px-4 py-2.5 text-sm font-bold hover:bg-[#c44a7a] transition text-center">
            Fotos hochladen
          </Link>
          <Link :href="route('inserat.dashboard')"
            class="flex-1 border border-gray-200 text-gray-600 rounded-lg px-4 py-2.5 text-sm font-semibold hover:border-gray-300 hover:text-gray-900 transition text-center">
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
