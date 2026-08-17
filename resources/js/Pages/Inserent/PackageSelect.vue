<template>
  <AppLayout>
    <Head title="Paket wählen" />

    <div class="max-w-4xl mx-auto px-4 py-8">

      <!-- Stepper -->
      <div class="flex items-center gap-0 mb-8">
        <div v-for="(step, i) in steps" :key="i" class="flex items-center gap-0 flex-1 last:flex-none">
          <div :class="[
            'w-7 h-7 rounded-full flex items-center justify-center text-xs font-bold shrink-0',
            i <= 1 ? 'bg-[#e35d8f] text-white' : 'bg-gray-200 text-gray-500',
          ]">{{ i < 1 ? '✓' : i + 1 }}</div>
          <span class="ml-1.5 text-xs font-medium"
            :class="i === 1 ? 'text-[#e35d8f]' : i < 1 ? 'text-green-600' : 'text-gray-400'">
            {{ step }}
          </span>
          <div v-if="i < steps.length - 1" class="flex-1 h-px mx-3"
            :class="i < 1 ? 'bg-[#e35d8f]/40' : 'bg-gray-200'"></div>
        </div>
      </div>

      <h1 class="text-2xl font-bold text-gray-900 mb-2">Paket wählen</h1>
      <p class="text-gray-500 text-sm mb-8">
        Wähle ein Laufzeit-Paket für <strong class="text-gray-900">„{{ profile.display_name }}"</strong>.
        <span v-if="launchMode">Im Launch-Modus ist dein Inserat <span class="text-[#e35d8f] font-semibold">gratis und sofort aktiv</span>.</span>
        <span v-else>Nach erfolgreicher Zahlung ist dein Inserat <span class="text-[#e35d8f] font-semibold">sofort aktiv</span>.</span>
      </p>

      <!-- Pakete -->
      <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
        <div v-for="pkg in packages" :key="pkg.id"
          @click="selectedId = pkg.id"
          :class="[
            'relative border-2 rounded-2xl p-5 cursor-pointer transition-all',
            selectedId === pkg.id
              ? 'border-[#e35d8f] bg-[#e35d8f]/5'
              : 'border-gray-200 bg-white hover:border-[#e35d8f]/40 shadow-sm',
          ]">

          <!-- Badge -->
          <div v-if="pkg.price_chf == 0"
            class="absolute -top-3 left-1/2 -translate-x-1/2 bg-green-600 text-white text-xs font-bold px-3 py-1 rounded-full whitespace-nowrap">
            Testmodus
          </div>
          <div v-else-if="pkg.name === 'Standard'"
            class="absolute -top-3 left-1/2 -translate-x-1/2 bg-[#e35d8f] text-white text-xs font-bold px-3 py-1 rounded-full whitespace-nowrap">
            Beliebteste Wahl
          </div>

          <!-- Auswahl-Indikator -->
          <div :class="[
            'w-5 h-5 rounded-full border-2 mb-3 flex items-center justify-center',
            selectedId === pkg.id ? 'border-[#e35d8f] bg-[#e35d8f]' : 'border-gray-300',
          ]">
            <div v-if="selectedId === pkg.id" class="w-2 h-2 rounded-full bg-white"></div>
          </div>

          <h3 class="font-bold text-gray-900 text-lg">{{ pkg.name }}</h3>
          <div class="mt-1 mb-3">
            <span v-if="pkg.price_chf == 0" class="text-2xl font-extrabold text-green-600">GRATIS</span>
            <span v-else class="text-2xl font-extrabold text-[#e35d8f]">CHF {{ pkg.price_chf }}</span>
            <span class="text-gray-400 text-sm ml-1">/ {{ pkg.duration_days }} Tage</span>
          </div>

          <ul class="space-y-1.5 mt-3">
            <li v-for="f in pkg.features" :key="f" class="flex items-start gap-1.5 text-xs text-gray-600">
              <span class="text-green-600 shrink-0 mt-0.5">✓</span>
              <span>{{ f }}</span>
            </li>
          </ul>
        </div>
      </div>

      <!-- Checkout Box -->
      <div v-if="selected" :class="[
        'rounded-2xl border p-6',
        selected.price_chf == 0
          ? 'bg-green-50 border-green-200'
          : 'bg-white border-gray-200 shadow-sm'
      ]">
        <div class="flex items-center justify-between flex-wrap gap-4">
          <div>
            <p class="font-semibold text-gray-900">{{ selected.name }}-Paket</p>
            <p class="text-sm text-gray-500">{{ selected.duration_days }} Tage Laufzeit · automatisch deaktiviert nach Ablauf</p>
            <p v-if="selected.price_chf == 0" class="text-xs text-green-700 mt-1">✓ Testmodus – keine Zahlung erforderlich</p>
            <p v-else class="text-xs text-gray-400 mt-1">Sichere Zahlung via Stripe · Kreditkarte, TWINT (bald)</p>
          </div>
          <div class="text-right">
            <p v-if="selected.price_chf == 0" class="text-3xl font-extrabold text-green-600">GRATIS</p>
            <p v-else class="text-3xl font-extrabold text-[#e35d8f]">CHF {{ selected.price_chf }}</p>
            <p class="text-xs text-gray-400">{{ selected.price_chf == 0 ? '14 Tage kostenlos' : 'inkl. MwSt.' }}</p>
          </div>
        </div>

        <div class="flex items-center gap-3 mt-5 pt-5 border-t border-gray-200">
          <Link :href="route('inserat.profile.edit')"
            class="px-4 py-2 text-sm text-gray-400 hover:text-gray-900 transition">
            ← Zurück
          </Link>
          <form @submit.prevent="pay" class="ml-auto">
            <button type="submit" :disabled="loading"
              :class="[
                'px-8 py-2.5 rounded-md text-sm font-bold transition',
                selected.price_chf == 0
                  ? 'bg-green-600 hover:bg-green-700 text-white'
                  : 'bg-[#e35d8f] hover:bg-[#c44a7a] text-white',
                loading ? 'opacity-50 cursor-not-allowed' : ''
              ]">
              {{ selected.price_chf == 0 ? 'Gratis aktivieren' : `Jetzt bezahlen · CHF ${selected.price_chf}` }}
            </button>
          </form>
        </div>
      </div>

      <!-- Sicherheits-Hinweise -->
      <div class="flex flex-wrap gap-6 mt-6 justify-center text-xs text-gray-400">
        <span class="flex items-center gap-1.5">🔒 SSL-verschlüsselt</span>
        <span class="flex items-center gap-1.5">✅ Sofortige Aktivierung</span>
        <span v-if="selected?.price_chf != 0" class="flex items-center gap-1.5">💳 Stripe – sicher & zuverlässig</span>
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
  packages:  Array,
  profile:   Object,
  launchMode: { type: Boolean, default: false },
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
