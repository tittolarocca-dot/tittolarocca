<template>
  <AppLayout>
    <Head title="Paket wählen" />

    <div class="max-w-4xl mx-auto px-4 py-8">

      <!-- Stepper -->
      <div class="flex items-center gap-0 mb-8">
        <div v-for="(step, i) in steps" :key="i" class="flex items-center gap-0 flex-1 last:flex-none">
          <div :class="[
            'w-7 h-7 rounded-full flex items-center justify-center text-xs font-bold shrink-0',
            i <= 1 ? 'bg-[#e91e8c] text-white' : 'bg-[#2a2a2a] text-gray-500',
          ]">{{ i < 1 ? '✓' : i + 1 }}</div>
          <span class="ml-1.5 text-xs font-medium"
            :class="i === 1 ? 'text-[#e91e8c]' : i < 1 ? 'text-green-500' : 'text-gray-500'">
            {{ step }}
          </span>
          <div v-if="i < steps.length - 1" class="flex-1 h-px mx-3"
            :class="i < 1 ? 'bg-[#e91e8c]/40' : 'bg-[#2a2a2a]'"></div>
        </div>
      </div>

      <h1 class="text-2xl font-bold text-white mb-2">Paket wählen</h1>
      <p class="text-gray-400 text-sm mb-8">
        Wähle ein Laufzeit-Paket für <strong class="text-white">„{{ profile.display_name }}"</strong>.
        Nach erfolgreicher Zahlung ist dein Inserat <span class="text-[#e91e8c] font-semibold">sofort aktiv</span>.
      </p>

      <!-- Pakete -->
      <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
        <div v-for="pkg in packages" :key="pkg.id"
          @click="selectedId = pkg.id"
          :class="[
            'relative border-2 rounded-2xl p-5 cursor-pointer transition-all',
            selectedId === pkg.id
              ? 'border-[#e91e8c] bg-[#e91e8c]/10'
              : 'border-[#2a2a2a] bg-[#1a1a1a] hover:border-[#e91e8c]/40',
          ]">

          <!-- Badge -->
          <div v-if="pkg.name === 'Standard'"
            class="absolute -top-3 left-1/2 -translate-x-1/2 bg-[#e91e8c] text-white text-xs font-bold px-3 py-1 rounded-full whitespace-nowrap">
            Beliebteste Wahl
          </div>

          <!-- Auswahl-Indikator -->
          <div :class="[
            'w-5 h-5 rounded-full border-2 mb-3 flex items-center justify-center',
            selectedId === pkg.id ? 'border-[#e91e8c] bg-[#e91e8c]' : 'border-[#3a3a3a]',
          ]">
            <div v-if="selectedId === pkg.id" class="w-2 h-2 rounded-full bg-white"></div>
          </div>

          <h3 class="font-bold text-white text-lg">{{ pkg.name }}</h3>
          <div class="mt-1 mb-3">
            <span class="text-2xl font-extrabold text-[#e91e8c]">CHF {{ pkg.price_chf }}</span>
            <span class="text-gray-500 text-sm ml-1">/ {{ pkg.duration_days }} Tage</span>
          </div>

          <ul class="space-y-1.5 mt-3">
            <li v-for="f in pkg.features" :key="f" class="flex items-start gap-1.5 text-xs text-gray-400">
              <span class="text-green-500 shrink-0 mt-0.5">✓</span>
              <span>{{ f }}</span>
            </li>
          </ul>
        </div>
      </div>

      <!-- Checkout Box -->
      <div v-if="selected" class="bg-[#1a1a1a] rounded-2xl border border-[#2a2a2a] p-6">
        <div class="flex items-center justify-between flex-wrap gap-4">
          <div>
            <p class="font-semibold text-white">{{ selected.name }}-Paket</p>
            <p class="text-sm text-gray-400">{{ selected.duration_days }} Tage Laufzeit · automatisch deaktiviert nach Ablauf</p>
            <p class="text-xs text-gray-500 mt-1">Sichere Zahlung via Stripe · Kreditkarte, TWINT (bald)</p>
          </div>
          <div class="text-right">
            <p class="text-3xl font-extrabold text-[#e91e8c]">CHF {{ selected.price_chf }}</p>
            <p class="text-xs text-gray-500">inkl. MwSt.</p>
          </div>
        </div>

        <div class="flex items-center gap-3 mt-5 pt-5 border-t border-[#2a2a2a]">
          <Link :href="route('inserat.profile.edit')"
            class="px-4 py-2 text-sm text-gray-500 hover:text-white transition">
            ← Zurück
          </Link>
          <form @submit.prevent="pay" class="ml-auto">
            <PrimaryButton type="submit" :loading="loading" class="px-8">
              Jetzt bezahlen · CHF {{ selected.price_chf }}
            </PrimaryButton>
          </form>
        </div>
      </div>

      <!-- Sicherheits-Hinweise -->
      <div class="flex flex-wrap gap-6 mt-6 justify-center text-xs text-gray-500">
        <span class="flex items-center gap-1.5">🔒 SSL-verschlüsselt</span>
        <span class="flex items-center gap-1.5">✅ Sofortige Aktivierung</span>
        <span class="flex items-center gap-1.5">💳 Stripe – sicher & zuverlässig</span>
        <span class="flex items-center gap-1.5">🔄 Keine automatische Verlängerung</span>
      </div>
    </div>
  </AppLayout>
</template>

<script setup>
import { Head, Link, router } from '@inertiajs/vue3';
import { ref, computed } from 'vue';
import AppLayout from '@/Layouts/AppLayout.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';

const props = defineProps({
  packages: Array,
  profile:  Object,
});

const steps      = ['Profil erstellen', 'Paket wählen', 'Zahlung', 'Live!'];
const selectedId = ref(props.packages.find(p => p.name === 'Standard')?.id ?? props.packages[0]?.id);
const selected   = computed(() => props.packages.find(p => p.id === selectedId.value));
const loading    = ref(false);

function pay() {
  loading.value = true;
  router.post(route('inserat.package.checkout'), { package_id: selectedId.value }, {
    onError: () => { loading.value = false; },
  });
}
</script>
