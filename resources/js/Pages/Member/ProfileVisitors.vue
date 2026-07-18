<template>
  <AppLayout>
    <Head :title="t('dashboard.visited_title')" />
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
          <h1 class="text-xl font-bold text-white">{{ t('dashboard.visited_title') }}</h1>
          <p class="text-xs text-gray-500">{{ t('dashboard.visitors_count', { count: visitors.length }) }}</p>
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
        <p class="text-gray-400 text-sm">{{ t('dashboard.no_visitors') }}</p>
        <p class="text-gray-600 text-xs mt-1">{{ t('dashboard.no_visitors_hint') }}</p>
      </div>

      <!-- Visitor list -->
      <div v-else class="space-y-3">
        <div v-for="(visitor, i) in visitors" :key="i">

          <!-- Inserent card (has a profile) -->
          <Link v-if="visitor.profile"
            :href="route('profile.show', visitor.profile.slug)"
            class="flex items-center gap-4 bg-[#1a1a1a] border border-white/8 rounded-2xl px-5 py-4 hover:border-[#e35d8f]/30 transition group block">
            <div class="w-14 h-14 rounded-xl overflow-hidden bg-[#111] shrink-0">
              <img v-if="visitor.profile.cover_url" :src="visitor.profile.cover_url"
                :alt="visitor.profile.display_name"
                class="w-full h-full object-cover object-top" loading="lazy" />
              <div v-else class="w-full h-full flex items-center justify-center">
                <svg class="w-6 h-6 text-gray-600" fill="currentColor" viewBox="0 0 20 20">
                  <path fill-rule="evenodd" d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z" clip-rule="evenodd"/>
                </svg>
              </div>
            </div>
            <div class="flex-1 min-w-0">
              <p class="text-white font-semibold text-sm group-hover:text-[#e35d8f] transition truncate">{{ visitor.profile.display_name }}</p>
              <p class="text-gray-500 text-xs mt-0.5">
                <span v-if="visitor.profile.age">{{ visitor.profile.age }} Jahre</span>
                <span v-if="visitor.profile.age && visitor.profile.city"> · </span>
                <span v-if="visitor.profile.city">{{ visitor.profile.city }}</span>
              </p>
              <p class="text-gray-600 text-xs mt-1 flex items-center gap-1">
                <svg class="w-3 h-3 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                {{ visitor.last_visited_at }}
              </p>
            </div>
            <div class="shrink-0">
              <span class="text-xs font-semibold text-[#e35d8f] border border-[#e35d8f]/40 rounded-lg px-3 py-1.5 whitespace-nowrap">
                {{ t('dashboard.view_listing') }}
              </span>
            </div>
          </Link>

          <!-- Regular member card (no inserent profile) -->
          <div v-else class="flex items-center gap-4 bg-[#1a1a1a] border border-white/8 rounded-2xl px-5 py-4">
            <div class="w-11 h-11 rounded-full flex items-center justify-center text-white text-sm font-bold shrink-0"
              :style="`background: ${avatarColor(visitor.name)}`">
              {{ visitor.name.charAt(0).toUpperCase() }}
            </div>
            <div class="flex-1 min-w-0">
              <p class="text-white font-semibold text-sm truncate">{{ visitor.name }}</p>
              <p class="text-gray-500 text-xs mt-0.5 flex items-center gap-1">
                <svg class="w-3 h-3 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                {{ visitor.last_visited_at }}
              </p>
            </div>
          </div>

        </div>
      </div>

    </div>
  </AppLayout>
</template>

<script setup>
import { Head, Link } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import { useI18n } from '@/composables/useI18n';

const { t } = useI18n();

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
