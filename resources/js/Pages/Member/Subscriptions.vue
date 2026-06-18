<template>
  <AppLayout>
    <Head :title="t('dashboard.subs_title')" />
    <div class="max-w-4xl mx-auto px-4 py-8">
      <h1 class="text-2xl font-bold text-white mb-6">{{ t('dashboard.subs_title') }}</h1>

      <div v-if="subscriptions.length === 0" class="text-center py-16 text-gray-400">
        <div class="text-5xl mb-3">💫</div>
        <p>{{ t('dashboard.subs_empty') }}</p>
        <Link :href="route('home')" class="text-[#e35d8f] hover:underline text-sm mt-2 block">
          {{ t('dashboard.subs_discover') }}
        </Link>
      </div>

      <div v-else class="space-y-4">
        <div v-for="sub in subscriptions" :key="sub.id"
          class="bg-[#1a1a1a] rounded-xl border border-white/8 p-5 flex items-center justify-between gap-4">
          <div>
            <Link :href="route('profile.show', sub.profile.slug)"
              class="font-semibold text-white hover:text-[#e35d8f] transition">
              {{ sub.profile.display_name }}
            </Link>
            <p class="text-sm text-gray-500">{{ sub.profile.city }}</p>
          </div>

          <div class="text-right">
            <p class="font-bold text-white">
              <template v-if="sub.status === 'trialing'">
                <span class="text-purple-400">{{ t('dashboard.subs_gratis') }}</span>
              </template>
              <template v-else>
                CHF {{ sub.amount_chf }}<span class="text-xs text-gray-500 font-normal">{{ t('dashboard.subs_per_month') }}</span>
              </template>
            </p>
            <p class="text-xs mt-0.5"
              :class="{
                'text-green-400':  sub.status === 'active',
                'text-purple-400': sub.status === 'trialing',
                'text-yellow-400': !['active','trialing'].includes(sub.status),
              }">
              {{ statusLabel(sub.status) }}
            </p>
            <p v-if="sub.renews_at" class="text-xs text-gray-400">
              {{ sub.status === 'trialing' ? t('dashboard.subs_trial_until') : t('dashboard.subs_renews') }} {{ sub.renews_at }}
            </p>
          </div>

          <Link :href="route('profile.show', sub.profile.slug)"
            class="shrink-0 border border-white/10 text-gray-400 text-xs font-semibold px-3 py-1.5 rounded-lg hover:border-[#e35d8f]/50 hover:text-[#e35d8f] transition">
            {{ t('dashboard.subs_to_profile') }}
          </Link>
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

defineProps({ subscriptions: { type: Array, default: () => [] } });

function statusLabel(s) {
  return {
    active:    t('dashboard.subs_active'),
    trialing:  t('dashboard.subs_trial'),
    cancelled: t('dashboard.subs_cancelled'),
    past_due:  t('dashboard.subs_past_due'),
  }[s] ?? s;
}
</script>
