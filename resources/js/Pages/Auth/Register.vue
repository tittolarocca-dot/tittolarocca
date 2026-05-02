<template>
  <AuthLayout :title="t('auth.register_title')" :subtitle="t('auth.register_subtitle')">
    <Head :title="t('auth.register_title')" />

    <form @submit.prevent="submit" class="space-y-4" novalidate>

      <!-- Rolle wählen -->
      <div>
        <p class="text-sm font-medium text-gray-300 mb-2">
          {{ t('auth.i_want') }} <span class="text-[#e35d8f]">*</span>
        </p>
        <div class="grid grid-cols-2 gap-3">
          <label v-for="option in roleOptions" :key="option.value"
            :class="[
              'flex flex-col items-center gap-2 p-3 border-2 rounded-xl cursor-pointer transition-all select-none',
              form.role === option.value
                ? 'border-[#e35d8f] bg-[#e35d8f]/10 text-white'
                : 'border-white/10 bg-white/5 hover:border-[#e35d8f]/50 text-gray-400',
            ]">
            <input type="radio" :value="option.value" v-model="form.role" class="sr-only" />
            <span class="text-2xl">{{ option.icon }}</span>
            <span class="text-xs font-semibold text-center leading-tight">{{ option.label }}</span>
          </label>
        </div>
        <p v-if="form.errors.role" class="mt-1 text-xs text-red-400">{{ form.errors.role }}</p>
      </div>

      <div class="border-t border-white/10 pt-4 space-y-4">
        <InputField
          id="name"
          :label="t('auth.name_label')"
          v-model="form.name"
          autocomplete="name"
          :error="form.errors.name"
          required
          :placeholder="t('auth.name_placeholder')"
        />

        <InputField
          id="email"
          :label="t('auth.email_label')"
          v-model="form.email"
          type="email"
          autocomplete="email"
          :error="form.errors.email"
          required
          :placeholder="t('auth.email_placeholder')"
        />

        <InputField
          id="password"
          :label="t('auth.password_label')"
          v-model="form.password"
          type="password"
          autocomplete="new-password"
          :error="form.errors.password"
          :hint="t('auth.password_hint')"
          required
        />

        <InputField
          id="password_confirmation"
          :label="t('auth.password_confirm')"
          v-model="form.password_confirmation"
          type="password"
          autocomplete="new-password"
          :error="form.errors.password_confirmation"
          required
        />
      </div>

      <!-- Checkboxen -->
      <div class="space-y-3 pt-1">
        <label class="flex items-start gap-2.5 cursor-pointer">
          <input type="checkbox" v-model="form.age"
            class="mt-0.5 w-4 h-4 rounded border-white/20 bg-[#111] text-[#e35d8f] focus:ring-[#e35d8f] shrink-0" />
          <span class="text-xs text-gray-400">
            {{ t('auth.age_confirm') }}
          </span>
        </label>
        <p v-if="form.errors.age" class="text-xs text-red-400 -mt-1 ml-6">{{ form.errors.age }}</p>

        <label class="flex items-start gap-2.5 cursor-pointer">
          <input type="checkbox" v-model="form.agb"
            class="mt-0.5 w-4 h-4 rounded border-white/20 bg-[#111] text-[#e35d8f] focus:ring-[#e35d8f] shrink-0" />
          <span class="text-xs text-gray-400">
            {{ t('auth.i_accept') }}
            <a href="#" class="text-[#e35d8f] underline">{{ t('auth.agb') }}</a>
            {{ t('auth.and_the') }}
            <a href="#" class="text-[#e35d8f] underline">{{ t('auth.privacy') }}</a>.
          </span>
        </label>
        <p v-if="form.errors.agb" class="text-xs text-red-400 -mt-1 ml-6">{{ form.errors.agb }}</p>
      </div>

      <PrimaryButton type="submit" :loading="form.processing" full-width class="mt-2">
        {{ t('auth.create_account_btn') }}
      </PrimaryButton>

      <p class="text-center text-sm text-gray-500 pt-1">
        {{ t('auth.already_registered') }}
        <Link :href="route('login')" class="text-[#e35d8f] font-semibold hover:underline">
          {{ t('auth.sign_in_link') }}
        </Link>
      </p>
    </form>
  </AuthLayout>
</template>

<script setup>
import { computed } from 'vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import AuthLayout from '@/Layouts/AuthLayout.vue';
import InputField from '@/Components/InputField.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import { useI18n } from '@/composables/useI18n';

const { t } = useI18n();

const roleOptions = computed(() => [
  { value: 'member',   icon: '👤', label: t('auth.role_member') },
  { value: 'inserent', icon: '📋', label: t('auth.role_inserent') },
]);

const form = useForm({
  name:                  '',
  email:                 '',
  password:              '',
  password_confirmation: '',
  role:                  'member',
  agb:                   false,
  age:                   false,
});

function submit() {
  form.post(route('register'), {
    onError: () => {
      form.reset('password', 'password_confirmation');
    },
  });
}
</script>
