<template>
  <AppLayout>
    <Head title="Meine Abonnements" />
    <div class="max-w-4xl mx-auto px-4 py-8">
      <h1 class="text-2xl font-bold text-white mb-6">Meine Abonnements</h1>

      <div v-if="subscriptions.length === 0" class="text-center py-16 text-gray-500">
        <div class="text-5xl mb-3">💫</div>
        <p>Du hast noch keine Abonnements.</p>
        <Link :href="route('home')" class="text-[#e91e8c] hover:underline text-sm mt-2 block">
          Profile entdecken →
        </Link>
      </div>

      <div v-else class="space-y-4">
        <div v-for="sub in subscriptions" :key="sub.id"
          class="bg-[#1a1a1a] rounded-xl border border-[#2a2a2a] p-5 flex items-center justify-between gap-4">
          <div>
            <Link :href="route('profile.show', sub.profile.slug)"
              class="font-semibold text-white hover:text-[#e91e8c] transition">
              {{ sub.profile.display_name }}
            </Link>
            <p class="text-sm text-gray-500">{{ sub.profile.city }}</p>
          </div>

          <div class="text-right">
            <p class="font-bold text-white">CHF {{ sub.amount_chf }}<span class="text-xs text-gray-500 font-normal">/Monat</span></p>
            <p class="text-xs mt-0.5"
              :class="sub.status === 'active' ? 'text-green-500' : 'text-yellow-500'">
              {{ statusLabel(sub.status) }}
            </p>
            <p v-if="sub.renews_at" class="text-xs text-gray-500">Verlängert am {{ sub.renews_at }}</p>
          </div>

          <Link :href="route('profile.show', sub.profile.slug)"
            class="shrink-0 border border-[#2a2a2a] text-gray-400 text-xs font-semibold px-3 py-1.5 rounded-lg hover:border-[#e91e8c]/50 hover:text-[#e91e8c] transition">
            Zum Profil
          </Link>
        </div>
      </div>
    </div>
  </AppLayout>
</template>

<script setup>
import { Head, Link } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';

defineProps({ subscriptions: { type: Array, default: () => [] } });

function statusLabel(s) {
  return { active: 'Aktiv', cancelled: 'Gekündigt', past_due: 'Zahlung ausstehend' }[s] ?? s;
}
</script>
