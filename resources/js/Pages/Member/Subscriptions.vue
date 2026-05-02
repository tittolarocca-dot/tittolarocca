<template>
  <AppLayout>
    <Head title="Meine Abonnements" />
    <div class="max-w-4xl mx-auto px-4 py-8">
      <h1 class="text-2xl font-bold text-gray-900 mb-6">Meine Abonnements</h1>

      <div v-if="subscriptions.length === 0" class="text-center py-16 text-gray-400">
        <div class="text-5xl mb-3">💫</div>
        <p>Du hast noch keine Abonnements.</p>
        <Link :href="route('home')" class="text-[#e35d8f] hover:underline text-sm mt-2 block">
          Profile entdecken →
        </Link>
      </div>

      <div v-else class="space-y-4">
        <div v-for="sub in subscriptions" :key="sub.id"
          class="bg-white rounded-xl border border-gray-200 p-5 flex items-center justify-between gap-4 shadow-sm">
          <div>
            <Link :href="route('profile.show', sub.profile.slug)"
              class="font-semibold text-gray-900 hover:text-[#e35d8f] transition">
              {{ sub.profile.display_name }}
            </Link>
            <p class="text-sm text-gray-500">{{ sub.profile.city }}</p>
          </div>

          <div class="text-right">
            <p class="font-bold text-gray-900">
              <template v-if="sub.status === 'trialing'">
                <span class="text-purple-600">GRATIS</span>
              </template>
              <template v-else>
                CHF {{ sub.amount_chf }}<span class="text-xs text-gray-500 font-normal">/Monat</span>
              </template>
            </p>
            <p class="text-xs mt-0.5"
              :class="{
                'text-green-600':  sub.status === 'active',
                'text-purple-600': sub.status === 'trialing',
                'text-yellow-600': !['active','trialing'].includes(sub.status),
              }">
              {{ statusLabel(sub.status) }}
            </p>
            <p v-if="sub.renews_at" class="text-xs text-gray-400">
              {{ sub.status === 'trialing' ? 'Gratis bis' : 'Verlängert am' }} {{ sub.renews_at }}
            </p>
          </div>

          <Link :href="route('profile.show', sub.profile.slug)"
            class="shrink-0 border border-gray-200 text-gray-600 text-xs font-semibold px-3 py-1.5 rounded-lg hover:border-[#e35d8f]/50 hover:text-[#e35d8f] transition">
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
  return {
    active:    'Aktiv',
    trialing:  '🎁 Gratis-Test',
    cancelled: 'Gekündigt',
    past_due:  'Zahlung ausstehend',
  }[s] ?? s;
}
</script>
