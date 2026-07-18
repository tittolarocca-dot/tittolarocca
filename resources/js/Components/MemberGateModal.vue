<template>
  <Teleport to="body">
    <Transition name="mg-fade">
      <div v-if="open" class="fixed inset-0 z-[60] flex items-center justify-center p-4"
        @click.self="$emit('close')">
        <!-- Backdrop -->
        <div class="absolute inset-0 bg-black/80 backdrop-blur-sm"></div>

        <!-- Dialog -->
        <div class="relative w-full max-w-md bg-[#161616] border border-white/10 rounded-2xl shadow-2xl shadow-black/60 overflow-hidden">
          <!-- Header -->
          <div class="flex items-center justify-between px-5 py-4 border-b border-white/8">
            <h3 class="text-white font-bold text-lg">{{ t('gate.title') }}</h3>
            <button type="button" @click="$emit('close')"
              class="text-gray-400 hover:text-white transition p-1 -mr-1" aria-label="Close">
              <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
              </svg>
            </button>
          </div>

          <!-- Body -->
          <div class="px-6 py-5">
            <h4 class="text-center text-[#e35d8f] font-black text-xl leading-snug mb-4">
              {{ t('gate.headline') }}
            </h4>
            <p class="text-center text-sm text-gray-300 leading-relaxed mb-5">
              {{ t('gate.intro') }}
            </p>

            <ul class="space-y-3.5 mb-6">
              <li class="flex items-start gap-3">
                <svg class="w-5 h-5 shrink-0 text-[#e35d8f] mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><circle cx="12" cy="12" r="9"/><path stroke-linecap="round" stroke-linejoin="round" d="M12 7v5l3 2"/></svg>
                <span class="text-sm text-gray-200">{{ t('gate.benefit_fast') }}</span>
              </li>
              <li class="flex items-start gap-3">
                <svg class="w-5 h-5 shrink-0 text-[#e35d8f] mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-1a4 4 0 00-4-4h-1m-6 5H2v-1a4 4 0 014-4h1m8-4a3 3 0 11-6 0 3 3 0 016 0zm6-3a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0zM7 9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z"/></svg>
                <span class="text-sm text-gray-200">{{ t('gate.benefit_favorite') }}</span>
              </li>
              <li class="flex items-start gap-3">
                <svg class="w-5 h-5 shrink-0 text-[#e35d8f] mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                <span class="text-sm text-gray-200">{{ t('gate.benefit_review') }}</span>
              </li>
              <li class="flex items-start gap-3">
                <svg class="w-5 h-5 shrink-0 text-[#e35d8f] mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/></svg>
                <span class="text-sm text-gray-200">{{ t('gate.benefit_notify') }}</span>
              </li>
            </ul>

            <!-- Actions -->
            <div class="flex items-center justify-center gap-3">
              <Link :href="route('login')"
                class="flex items-center justify-center gap-2 bg-[#e35d8f] hover:bg-[#c44a7a] text-white text-sm font-bold px-5 py-3 rounded-xl transition">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h5a3 3 0 013 3v1"/></svg>
                {{ t('gate.login') }}
              </Link>
              <Link :href="route('register')"
                class="flex items-center justify-center gap-2 bg-white/8 hover:bg-white/12 border border-white/12 text-white text-sm font-bold px-5 py-3 rounded-xl transition">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"/></svg>
                {{ t('gate.register') }}
              </Link>
            </div>
          </div>
        </div>
      </div>
    </Transition>
  </Teleport>
</template>

<script setup>
import { Link } from '@inertiajs/vue3';
import { useI18n } from '@/composables/useI18n';

const { t } = useI18n();

defineProps({
  open: { type: Boolean, default: false },
});
defineEmits(['close']);
</script>

<style scoped>
.mg-fade-enter-active, .mg-fade-leave-active { transition: opacity 0.2s ease; }
.mg-fade-enter-from, .mg-fade-leave-to { opacity: 0; }
</style>
