<template>
  <AuthLayout :title="t('auth.login_title')" :subtitle="t('auth.login_subtitle')">
    <Head :title="t('auth.login_title')" />

    <form @submit.prevent="submit" class="space-y-4" novalidate>

      <InputField
        id="email"
        :label="t('auth.email_label')"
        v-model="form.email"
        type="email"
        autocomplete="email"
        :error="form.errors.email"
        required
        autofocus
        :placeholder="t('auth.email_placeholder')"
      />

      <div>
        <div class="flex justify-between items-baseline mb-1">
          <label for="password" class="block text-sm font-medium text-gray-300">
            {{ t('auth.password_label') }} <span class="text-[#e35d8f]">*</span>
          </label>
          <a href="#" class="text-xs text-[#e35d8f] hover:underline">{{ t('auth.forgot_password') }}</a>
        </div>
        <input
          id="password"
          v-model="form.password"
          type="password"
          autocomplete="current-password"
          :class="[
            'w-full px-3 py-2 border rounded-md text-sm bg-[#111] text-white placeholder-gray-500 focus:outline-none focus:ring-2 focus:ring-[#e35d8f] focus:border-[#e35d8f] transition',
            form.errors.password ? 'border-red-500' : 'border-white/10',
          ]"
        />
        <p v-if="form.errors.password" class="mt-1 text-xs text-red-400">{{ form.errors.password }}</p>
      </div>

      <label class="flex items-center gap-2 cursor-pointer">
        <input type="checkbox" v-model="form.remember"
          class="w-4 h-4 rounded border-white/20 bg-[#111] text-[#e35d8f] focus:ring-[#e35d8f]" />
        <span class="text-sm text-gray-400">{{ t('auth.stay_logged_in') }}</span>
      </label>

      <div v-if="form.errors.email && !form.errors.password"
        class="bg-red-900/20 border border-red-500/30 rounded-md px-3 py-2 text-sm text-red-400">
        {{ form.errors.email }}
      </div>

      <PrimaryButton type="submit" :loading="form.processing" full-width>
        {{ t('auth.login_btn') }}
      </PrimaryButton>

      <p class="text-center text-sm text-gray-500 pt-1">
        {{ t('auth.no_account') }}
        <Link :href="route('register')" class="text-[#e35d8f] font-semibold hover:underline">
          {{ t('auth.register_now') }}
        </Link>
      </p>
    </form>
  </AuthLayout>
</template>

<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3';
import AuthLayout from '@/Layouts/AuthLayout.vue';
import InputField from '@/Components/InputField.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import { useI18n } from '@/composables/useI18n';

const { t } = useI18n();

const form = useForm({
  email:    '',
  password: '',
  remember: false,
});

function submit() {
  form.post(route('login'), {
    onError: () => form.reset('password'),
  });
}
</script>
