<template>
  <AppLayout>
    <Head :title="member.name" />
    <div class="max-w-xl mx-auto px-4 py-12">
      <div class="bg-[#1a1a1a] border border-white/8 rounded-2xl p-8 text-center">
        <!-- Avatar -->
        <div class="w-20 h-20 rounded-full flex items-center justify-center text-white text-3xl font-bold mx-auto mb-4"
          :style="`background: ${avatarColor(member.name)}`">
          {{ member.name.charAt(0).toUpperCase() }}
        </div>
        <h1 class="text-xl font-bold text-white mb-1">{{ member.name }}</h1>
        <p class="text-xs text-gray-500">{{ t('dashboard.member_since', { date: member.member_since }) }}</p>
      </div>
    </div>
  </AppLayout>
</template>

<script setup>
import { Head } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import { useI18n } from '@/composables/useI18n';

const { t } = useI18n();

defineProps({
  member: { type: Object, required: true },
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
