<template>
  <AppLayout>
    <Head title="Meine Berichte" />

    <div class="max-w-3xl mx-auto px-4 py-8">
      <div class="mb-6">
        <h1 class="text-2xl font-bold text-white">Meine Berichte</h1>
        <p class="text-gray-400 text-sm mt-1">Deine geschriebenen Bewertungen zu den Inseraten.</p>
      </div>

      <div v-if="reviews.length === 0" class="text-center py-16 text-gray-500">
        <div class="text-5xl mb-3">📝</div>
        <p class="text-sm">Du hast noch keine Bewertungen geschrieben.</p>
        <Link :href="route('home')" class="text-[#e35d8f] text-sm hover:underline mt-1 inline-block">Inserate entdecken</Link>
      </div>

      <div v-else class="space-y-3">
        <div v-for="r in reviews" :key="r.id" class="bg-[#1a1a1a] rounded-xl border border-white/8 p-4">
          <div class="flex items-center gap-2 mb-1.5 flex-wrap">
            <Link v-if="r.profile" :href="route('profile.show', r.profile.slug)"
              class="text-sm font-bold text-white hover:text-[#e35d8f] transition">{{ r.profile.display_name }}</Link>
            <span v-else class="text-sm font-bold text-gray-500">Inserat entfernt</span>
            <span class="text-yellow-400 text-sm">{{ '★'.repeat(r.stars) }}{{ '☆'.repeat(5 - r.stars) }}</span>
            <span class="ml-auto text-[11px] font-semibold px-2 py-0.5 rounded-full" :class="badge(r.status)">{{ statusLabel(r.status) }}</span>
          </div>
          <p v-if="r.comment" class="text-sm text-gray-400">{{ r.comment }}</p>
          <div v-if="r.reply" class="mt-2 ml-3 pl-3 border-l-2 border-[#e35d8f]/40 text-sm text-gray-500 italic">
            <span class="font-semibold text-[#e35d8f]">Antwort: </span>{{ r.reply }}
          </div>
          <p class="text-[11px] text-gray-600 mt-2">{{ r.created_at }}</p>
        </div>
      </div>
    </div>
  </AppLayout>
</template>

<script setup>
import { Head, Link } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';

defineProps({
  reviews: { type: Array, default: () => [] },
});

function statusLabel(s) {
  return { pending: 'In Prüfung', approved: 'Freigeschaltet', rejected: 'Abgelehnt' }[s] ?? s;
}
function badge(s) {
  return {
    pending:  'bg-yellow-500/15 text-yellow-300',
    approved: 'bg-green-500/15 text-green-300',
    rejected: 'bg-red-500/15 text-red-300',
  }[s] ?? 'bg-white/10 text-gray-400';
}
</script>
