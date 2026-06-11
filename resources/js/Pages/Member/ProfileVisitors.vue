<template>
  <AppLayout>
    <Head title="Profilbesucher" />
    <div class="max-w-3xl mx-auto px-4 py-8">

      <!-- Header -->
      <div class="flex items-center gap-3 mb-6">
        <div class="w-11 h-11 rounded-full bg-[#e35d8f]/15 flex items-center justify-center shrink-0">
          <svg class="w-5 h-5 text-[#e35d8f]" fill="currentColor" viewBox="0 0 24 24">
            <path d="M13.5 8.5c0 1.657-1.343 3-3 3S7.5 10.157 7.5 8.5 8.843 5.5 10.5 5.5s3 1.343 3 3zM3 19c0-3.314 3.134-6 7-6h1c3.866 0 7 2.686 7 6H3z"/>
            <path d="M18 8c0 1.105-.895 2-2 2-.552 0-1.053-.224-1.414-.586C15.165 8.86 15.5 8 15.5 8c0-1.105-.895-2-2-2 .362 0 .698.097 1-.25C15.447 6.224 15.948 6 16.5 6c1.105 0 2 .895 2 2zm1.5 5h-.5c.827.935 1.5 2.066 1.5 3.5V19h2v-.5c0-2.985-1.79-4.662-3-5z"/>
          </svg>
        </div>
        <div>
          <h1 class="text-xl font-bold text-white">Wer hat mein Profil besucht</h1>
          <p class="text-xs text-gray-500">{{ visitors.length }} {{ visitors.length === 1 ? 'Besucher' : 'Besucher' }} in deiner Liste</p>
        </div>
      </div>

      <!-- No profile state -->
      <div v-if="!hasProfile" class="bg-[#1a1a1a] border border-white/8 rounded-2xl p-10 text-center">
        <div class="w-14 h-14 rounded-full bg-white/5 flex items-center justify-center mx-auto mb-4">
          <svg class="w-7 h-7 text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
            <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632z"/>
          </svg>
        </div>
        <p class="text-gray-400 text-sm mb-3">Du hast noch kein Inserat erstellt.</p>
        <p class="text-gray-600 text-xs">Diese Funktion steht nur Inserenten mit einem aktiven Profil zur Verfügung.</p>
      </div>

      <!-- Empty state -->
      <div v-else-if="visitors.length === 0" class="bg-[#1a1a1a] border border-white/8 rounded-2xl p-10 text-center">
        <div class="w-14 h-14 rounded-full bg-[#e35d8f]/10 flex items-center justify-center mx-auto mb-4">
          <svg class="w-7 h-7 text-[#e35d8f]/50" fill="currentColor" viewBox="0 0 24 24">
            <path d="M13.5 8.5c0 1.657-1.343 3-3 3S7.5 10.157 7.5 8.5 8.843 5.5 10.5 5.5s3 1.343 3 3zM3 19c0-3.314 3.134-6 7-6h1c3.866 0 7 2.686 7 6H3z"/>
          </svg>
        </div>
        <p class="text-gray-400 text-sm">Noch keine Besucher.</p>
        <p class="text-gray-600 text-xs mt-1">Sobald jemand dein Profil besucht, erscheint er hier.</p>
      </div>

      <!-- Visitor list -->
      <div v-else class="space-y-3">
        <div v-for="(visitor, i) in visitors" :key="i"
          class="flex items-center gap-4 bg-[#1a1a1a] border border-white/8 rounded-2xl px-5 py-4 hover:border-[#e35d8f]/30 transition">

          <!-- Avatar -->
          <div class="w-11 h-11 rounded-full flex items-center justify-center text-white text-sm font-bold shrink-0"
            :style="`background: ${avatarColor(visitor.name)}`">
            {{ visitor.name.charAt(0).toUpperCase() }}
          </div>

          <!-- Info -->
          <div class="flex-1 min-w-0">
            <p class="text-white font-semibold text-sm truncate">{{ visitor.name }}</p>
            <p class="text-gray-500 text-xs mt-0.5 flex items-center gap-1">
              <svg class="w-3 h-3 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
              </svg>
              {{ visitor.last_visited_at }}
            </p>
          </div>

          <!-- Footprint icon -->
          <div class="w-8 h-8 rounded-full bg-[#e35d8f]/10 flex items-center justify-center shrink-0">
            <svg class="w-4 h-4 text-[#e35d8f]" fill="currentColor" viewBox="0 0 24 24">
              <path d="M13.5 8.5c0 1.657-1.343 3-3 3S7.5 10.157 7.5 8.5 8.843 5.5 10.5 5.5s3 1.343 3 3zM3 19c0-3.314 3.134-6 7-6h1c3.866 0 7 2.686 7 6H3z"/>
            </svg>
          </div>
        </div>
      </div>

    </div>
  </AppLayout>
</template>

<script setup>
import { Head } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';

defineProps({
  visitors:   { type: Array,   default: () => [] },
  hasProfile: { type: Boolean, default: false },
});

const avatarColors = [
  'linear-gradient(135deg,#e35d8f,#c44a7a)',
  'linear-gradient(135deg,#7c3aed,#6d28d9)',
  'linear-gradient(135deg,#0ea5e9,#0284c7)',
  'linear-gradient(135deg,#f59e0b,#d97706)',
  'linear-gradient(135deg,#10b981,#059669)',
  'linear-gradient(135deg,#f43f5e,#e11d48)',
];

function avatarColor(name) {
  const idx = name.charCodeAt(0) % avatarColors.length;
  return avatarColors[idx];
}
</script>
