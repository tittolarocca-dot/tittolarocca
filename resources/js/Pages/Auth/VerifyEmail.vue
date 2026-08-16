<template>
  <AuthLayout :title="t('auth.verify_title')" :subtitle="t('auth.verify_subtitle')">
    <Head :title="t('auth.verify_title')" />

    <div class="space-y-5 text-sm text-gray-300">
      <div class="flex justify-center">
        <span class="text-5xl">📧</span>
      </div>

      <p class="text-center leading-relaxed">
        {{ t('auth.verify_intro') }}
        <span class="block mt-1 font-semibold text-white break-all">{{ email }}</span>
      </p>

      <p class="text-center text-gray-400 leading-relaxed">
        {{ t('auth.verify_hint') }}
      </p>

      <button
        type="button"
        @click="resend"
        :disabled="form.processing"
        class="w-full py-3 rounded-xl bg-[#e35d8f] hover:bg-[#d14d7f] disabled:opacity-60 text-white font-semibold transition"
      >
        {{ form.processing ? t('auth.verify_sending') : t('auth.verify_resend') }}
      </button>

      <div class="text-center pt-2">
        <Link
          href="/logout"
          method="post"
          as="button"
          class="text-gray-500 text-xs hover:text-gray-300 transition"
        >
          {{ t('auth.verify_logout') }}
        </Link>
      </div>
    </div>
  </AuthLayout>
</template>

<script setup>
import { computed } from 'vue';
import { Head, Link, usePage, useForm } from '@inertiajs/vue3';
import AuthLayout from '@/Layouts/AuthLayout.vue';
import { useI18n } from '@/composables/useI18n';

const { t } = useI18n();
const page = usePage();

const email = computed(() => page.props.auth?.user?.email ?? '');

const form = useForm({});
function resend() {
  form.post('/email/verifizieren/senden', { preserveScroll: true });
}
</script>
