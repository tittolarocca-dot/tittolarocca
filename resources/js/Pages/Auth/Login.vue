<template>
  <AuthLayout title="Anmelden" subtitle="Willkommen zurück">
    <Head title="Login" />

    <form @submit.prevent="submit" class="space-y-4" novalidate>

      <InputField
        id="email"
        label="E-Mail-Adresse"
        v-model="form.email"
        type="email"
        autocomplete="email"
        :error="form.errors.email"
        required
        autofocus
        placeholder="deine@email.ch"
      />

      <div>
        <div class="flex justify-between items-baseline mb-1">
          <label for="password" class="block text-sm font-medium text-gray-300">
            Passwort <span class="text-[#e91e8c]">*</span>
          </label>
          <a href="#" class="text-xs text-[#e91e8c] hover:underline">Passwort vergessen?</a>
        </div>
        <input
          id="password"
          v-model="form.password"
          type="password"
          autocomplete="current-password"
          :class="[
            'w-full px-3 py-2 border rounded-md text-sm bg-[#111111] text-white placeholder-gray-600 focus:outline-none focus:ring-2 focus:ring-[#e91e8c] focus:border-[#e91e8c] transition',
            form.errors.password ? 'border-red-500 bg-red-900/20' : 'border-[#2a2a2a]',
          ]"
        />
        <p v-if="form.errors.password" class="mt-1 text-xs text-red-400">{{ form.errors.password }}</p>
      </div>

      <!-- Remember me -->
      <label class="flex items-center gap-2 cursor-pointer">
        <input type="checkbox" v-model="form.remember"
          class="w-4 h-4 rounded border-[#2a2a2a] bg-[#111111] text-[#e91e8c] focus:ring-[#e91e8c]" />
        <span class="text-sm text-gray-400">Angemeldet bleiben</span>
      </label>

      <!-- Allgemeiner Fehler -->
      <div v-if="form.errors.email && !form.errors.password"
        class="bg-red-900/20 border border-red-500/30 rounded-md px-3 py-2 text-sm text-red-400">
        {{ form.errors.email }}
      </div>

      <PrimaryButton type="submit" :loading="form.processing" full-width>
        Anmelden
      </PrimaryButton>

      <p class="text-center text-sm text-gray-500 pt-1">
        Noch kein Konto?
        <Link :href="route('register')" class="text-[#e91e8c] font-semibold hover:underline">Jetzt registrieren</Link>
      </p>
    </form>
  </AuthLayout>
</template>

<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3';
import AuthLayout from '@/Layouts/AuthLayout.vue';
import InputField from '@/Components/InputField.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';

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
