<template>
  <AppLayout>
    <Head title="Meine Abonnements" />
    <div class="max-w-4xl mx-auto px-4 py-8">
      <h1 class="text-2xl font-bold text-gray-900 mb-6">Meine Abonnements</h1>

      <div v-if="subscriptions.length === 0" class="text-center py-16 text-gray-400">
        <div class="text-5xl mb-3">💫</div>
        <p>Du hast noch keine Abonnements.</p>
        <Link :href="route('home')" class="text-[#e91e8c] hover:underline text-sm mt-2 block">
          Profile entdecken →
        </Link>
      </div>

      <div v-else class="space-y-4">
        <div v-for="sub in subscriptions" :key="sub.id"
          class="bg-white rounded-xl border border-gray-200 p-5 flex items-center justify-between gap-4 shadow-sm">
          <div>
            <Link :href="route('profile.show', sub.profile.slug)"
              class="font-semibold text-gray-900 hover:text-[#e91e8c] transition">
              {{ sub.profile.display_name }}
            </Link>
            <p class="text-sm text-gray-500">{{ sub.profile.city }}</p>
          </div>

          <div class="text-right">
            <p class="font-bold text-gray-900">CHF {{ sub.amount_chf }}<span class="text-xs text-gray-500 font-normal">/Monat</span></p>
            <p class="text-xs mt-0.5"
              :class="sub.status === 'active' ? 'text-green-600' : 'text-yellow-600'">
              {{ statusLabel(sub.status) }}
            </p>
            <p v-if="sub.renews_at" class="text-xs text-gray-400">Verlängert am {{ sub.renews_at }}</p>
          </div>

          <Link :href="route('profile.show', sub.profile.slug)"
            class="shrink-0 border border-gray-200 text-gray-600 text-xs font-semibold px-3 py-1.5 rounded-lg hover:border-[#e91e8c]/50 hover:text-[#e91e8c] transition">
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
