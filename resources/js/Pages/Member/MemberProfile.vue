<template>
  <AppLayout>
    <Head :title="member.name" />
    <div class="max-w-xl mx-auto px-4 py-12 space-y-5">
      <div class="bg-[#1a1a1a] border border-white/8 rounded-2xl p-8 text-center">
        <!-- Avatar -->
        <div class="w-20 h-20 rounded-full flex items-center justify-center text-white text-3xl font-bold mx-auto mb-4"
          :style="`background: ${avatarColor(member.name)}`">
          {{ member.name.charAt(0).toUpperCase() }}
        </div>
        <h1 class="text-xl font-bold text-white mb-1">{{ member.name }}</h1>
        <p class="text-xs text-gray-500">{{ t('dashboard.member_since', { date: member.member_since }) }}</p>

        <!-- Eckdaten -->
        <div v-if="facts.length" class="flex flex-wrap justify-center gap-2 mt-4">
          <span v-for="f in facts" :key="f" class="text-xs text-gray-300 bg-white/5 border border-white/8 px-3 py-1.5 rounded-full">{{ f }}</span>
        </div>
      </div>

      <!-- Über mich -->
      <div v-if="member.bio" class="bg-[#1a1a1a] border border-white/8 rounded-2xl p-5">
        <h2 class="text-sm font-bold text-gray-400 uppercase tracking-wider mb-2">Über mich</h2>
        <p class="text-white text-sm leading-relaxed whitespace-pre-line">{{ member.bio }}</p>
      </div>

      <!-- Vorlieben -->
      <div v-if="member.preferences" class="bg-[#1a1a1a] border border-white/8 rounded-2xl p-5">
        <h2 class="text-sm font-bold text-gray-400 uppercase tracking-wider mb-2">Vorlieben</h2>
        <p class="text-white text-sm leading-relaxed whitespace-pre-line">{{ member.preferences }}</p>
      </div>

      <!-- Sprachen -->
      <div v-if="member.languages && member.languages.length" class="bg-[#1a1a1a] border border-white/8 rounded-2xl p-5">
        <h2 class="text-sm font-bold text-gray-400 uppercase tracking-wider mb-3">Sprachen</h2>
        <div class="flex flex-wrap gap-2">
          <span v-for="l in member.languages" :key="l" class="text-xs text-[#e35d8f] bg-[#e35d8f]/10 border border-[#e35d8f]/25 px-3 py-1.5 rounded-full">{{ l }}</span>
        </div>
      </div>
    </div>
  </AppLayout>
</template>

<script setup>
import { computed } from 'vue';
import { Head } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import { useI18n } from '@/composables/useI18n';

const { t } = useI18n();

const props = defineProps({
  member: { type: Object, required: true },
});

const genderLabels = { frau: 'Frau', mann: 'Mann', paar: 'Paar', trans: 'Trans*', divers: 'Divers' };

const facts = computed(() => {
  const m = props.member;
  return [
    m.gender && (genderLabels[m.gender] ?? m.gender),
    m.age && `${m.age} Jahre`,
    m.city,
    m.height_cm && `${m.height_cm} cm`,
    m.weight_kg && `${m.weight_kg} kg`,
    m.smoking === true ? 'Raucher*in' : m.smoking === false ? 'Nichtraucher*in' : null,
  ].filter(Boolean);
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
