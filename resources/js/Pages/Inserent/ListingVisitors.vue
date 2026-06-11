<template>
  <AppLayout>
    <Head title="Inserat-Besucher" />
    <div class="max-w-3xl mx-auto px-4 py-8">

      <!-- Header -->
      <div class="flex items-center gap-3 mb-6">
        <div class="w-11 h-11 rounded-full bg-[#e35d8f]/15 flex items-center justify-center shrink-0">
          <svg class="w-6 h-6 text-[#e35d8f]" viewBox="0 0 64 64" fill="currentColor">
            <ellipse cx="22" cy="14" rx="7" ry="9" transform="rotate(-15 22 14)"/>
            <ellipse cx="36" cy="10" rx="4.5" ry="6" transform="rotate(10 36 10)"/>
            <ellipse cx="47" cy="16" rx="4" ry="5.5" transform="rotate(20 47 16)"/>
            <ellipse cx="54" cy="25" rx="3.5" ry="5" transform="rotate(30 54 25)"/>
            <path d="M10 35 Q14 22 28 26 Q38 30 34 44 Q30 56 18 52 Q8 48 10 35z"/>
            <ellipse cx="42" cy="44" rx="7" ry="9" transform="rotate(15 42 44)"/>
            <ellipse cx="28" cy="48" rx="4.5" ry="6" transform="rotate(-10 28 48)"/>
            <ellipse cx="17" cy="42" rx="4" ry="5.5" transform="rotate(-20 17 42)"/>
            <ellipse cx="10" cy="33" rx="3.5" ry="5" transform="rotate(-30 10 33)"/>
            <path d="M54 29 Q50 42 36 38 Q26 34 30 20 Q34 8 46 12 Q56 16 54 29z"/>
          </svg>
        </div>
        <div>
          <h1 class="text-xl font-bold text-white">Wer hat mein Inserat besucht</h1>
          <p class="text-xs text-gray-500">{{ visitors.length }} {{ visitors.length === 1 ? 'Besucher' : 'Besucher' }} in deiner Liste</p>
        </div>
      </div>

      <!-- Empty state -->
      <div v-if="visitors.length === 0" class="bg-[#1a1a1a] border border-white/8 rounded-2xl p-10 text-center">
        <div class="w-14 h-14 rounded-full bg-[#e35d8f]/10 flex items-center justify-center mx-auto mb-4">
          <svg class="w-7 h-7 text-[#e35d8f]/40" viewBox="0 0 64 64" fill="currentColor">
            <ellipse cx="22" cy="14" rx="7" ry="9" transform="rotate(-15 22 14)"/>
            <ellipse cx="36" cy="10" rx="4.5" ry="6" transform="rotate(10 36 10)"/>
            <ellipse cx="47" cy="16" rx="4" ry="5.5" transform="rotate(20 47 16)"/>
            <ellipse cx="54" cy="25" rx="3.5" ry="5" transform="rotate(30 54 25)"/>
            <path d="M10 35 Q14 22 28 26 Q38 30 34 44 Q30 56 18 52 Q8 48 10 35z"/>
          </svg>
        </div>
        <p class="text-gray-400 text-sm">Noch keine Besucher.</p>
        <p class="text-gray-600 text-xs mt-1">Sobald jemand dein Inserat besucht, erscheint er hier.</p>
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

          <!-- Footprint badge -->
          <div class="w-8 h-8 rounded-full bg-[#e35d8f]/10 flex items-center justify-center shrink-0">
            <svg class="w-4 h-4 text-[#e35d8f]" viewBox="0 0 64 64" fill="currentColor">
              <ellipse cx="22" cy="14" rx="7" ry="9" transform="rotate(-15 22 14)"/>
              <path d="M10 35 Q14 22 28 26 Q38 30 34 44 Q30 56 18 52 Q8 48 10 35z"/>
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
  visitors: { type: Array, default: () => [] },
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
