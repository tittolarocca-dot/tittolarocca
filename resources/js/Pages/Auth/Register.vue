<template>
  <AuthLayout title="Konto erstellen" subtitle="Kostenlos registrieren – keine Kreditkarte nötig">
    <Head title="Registrieren" />

    <form @submit.prevent="submit" class="space-y-4" novalidate>

      <!-- Rolle wählen -->
      <div>
        <p class="text-sm font-medium text-gray-700 mb-2">Ich möchte… <span class="text-[#e91e8c]">*</span></p>
        <div class="grid grid-cols-2 gap-3">
          <label v-for="option in roleOptions" :key="option.value"
            :class="[
              'flex flex-col items-center gap-2 p-3 border-2 rounded-xl cursor-pointer transition-all select-none',
              form.role === option.value
                ? 'border-[#e91e8c] bg-[#e91e8c]/10 text-gray-900'
                : 'border-gray-200 hover:border-[#e91e8c]/50 text-gray-600',
            ]">
            <input type="radio" :value="option.value" v-model="form.role" class="sr-only" />
            <span class="text-2xl">{{ option.icon }}</span>
            <span class="text-xs font-semibold text-center leading-tight">{{ option.label }}</span>
          </label>
        </div>
        <p v-if="form.errors.role" class="mt-1 text-xs text-red-600">{{ form.errors.role }}</p>
      </div>

      <div class="border-t border-gray-200 pt-4 space-y-4">
        <!-- Name -->
        <InputField
          id="name"
          label="Name / Pseudonym"
          v-model="form.name"
          autocomplete="name"
          :error="form.errors.name"
          required
          placeholder="z.B. Max oder Susi"
        />

        <!-- E-Mail -->
        <InputField
          id="email"
          label="E-Mail-Adresse"
          v-model="form.email"
          type="email"
          autocomplete="email"
          :error="form.errors.email"
          required
          placeholder="deine@email.ch"
        />

        <!-- Passwort -->
        <InputField
          id="password"
          label="Passwort"
          v-model="form.password"
          type="password"
          autocomplete="new-password"
          :error="form.errors.password"
          hint="Mindestens 8 Zeichen, Buchstaben und Zahlen"
          required
        />

        <!-- Passwort bestätigen -->
        <InputField
          id="password_confirmation"
          label="Passwort bestätigen"
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
            class="mt-0.5 w-4 h-4 rounded border-gray-300 bg-white text-[#e91e8c] focus:ring-[#e91e8c] shrink-0" />
          <span class="text-xs text-gray-600">
            Ich bestätige, dass ich <strong class="text-gray-900">18 Jahre oder älter</strong> bin.
          </span>
        </label>
        <p v-if="form.errors.age" class="text-xs text-red-600 -mt-1 ml-6">{{ form.errors.age }}</p>

        <label class="flex items-start gap-2.5 cursor-pointer">
          <input type="checkbox" v-model="form.agb"
            class="mt-0.5 w-4 h-4 rounded border-gray-300 bg-white text-[#e91e8c] focus:ring-[#e91e8c] shrink-0" />
          <span class="text-xs text-gray-600">
            Ich akzeptiere die <a href="#" class="text-[#e91e8c] underline">AGB</a>
            und die <a href="#" class="text-[#e91e8c] underline">Datenschutzerklärung</a>.
          </span>
        </label>
        <p v-if="form.errors.agb" class="text-xs text-red-600 -mt-1 ml-6">{{ form.errors.agb }}</p>
      </div>

      <!-- Submit -->
      <PrimaryButton type="submit" :loading="form.processing" full-width class="mt-2">
        Konto erstellen
      </PrimaryButton>

      <!-- Login Link -->
      <p class="text-center text-sm text-gray-500 pt-1">
        Bereits registriert?
        <Link :href="route('login')" class="text-[#e91e8c] font-semibold hover:underline">Anmelden</Link>
      </p>
    </form>
  </AuthLayout>
</template>

<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3';
import AuthLayout from '@/Layouts/AuthLayout.vue';
import InputField from '@/Components/InputField.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';

const roleOptions = [
  { value: 'member',   icon: '👤', label: 'Profile ansehen & abonnieren' },
  { value: 'inserent', icon: '📋', label: 'Eigenes Inserat erstellen' },
];

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
