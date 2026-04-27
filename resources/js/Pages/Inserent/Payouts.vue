<template>
  <AppLayout>
    <Head title="Auszahlungen" />
    <div class="max-w-4xl mx-auto px-4 py-8 space-y-6">

      <div class="flex items-center justify-between">
        <div>
          <h1 class="text-2xl font-bold text-gray-900">Auszahlungen</h1>
          <p class="text-sm text-gray-500 mt-1">Deine monatlichen Einnahmen (80% nach 20% Provision)</p>
        </div>
        <Link :href="route('inserat.dashboard')" class="text-sm text-[#e91e8c] hover:underline">← Dashboard</Link>
      </div>

      <!-- ── Stripe Connect Onboarding ─────────────────────────────────── -->
      <div class="bg-white rounded-xl border shadow-sm overflow-hidden"
        :class="payoutsEnabled ? 'border-green-200' : 'border-[#e91e8c]/30'">
        <div class="px-6 py-4 border-b flex items-center gap-3"
          :class="payoutsEnabled ? 'bg-green-50 border-green-200' : 'bg-[#fff0f7] border-[#e91e8c]/20'">
          <span class="text-xl">{{ payoutsEnabled ? '✅' : '🏦' }}</span>
          <div>
            <p class="font-semibold text-sm" :class="payoutsEnabled ? 'text-green-800' : 'text-gray-800'">
              {{ payoutsEnabled ? 'Auszahlungen aktiv' : 'Automatische Auszahlungen aktivieren' }}
            </p>
            <p class="text-xs mt-0.5" :class="payoutsEnabled ? 'text-green-600' : 'text-gray-500'">
              {{ payoutsEnabled
                ? 'Stripe überweist dir automatisch 80% jeder Zahlung direkt auf dein Konto.'
                : 'Mit Stripe Connect erhältst du 80% jeder Abo-Zahlung automatisch auf dein Bankkonto.' }}
            </p>
          </div>
        </div>
        <div class="px-6 py-5">
          <template v-if="payoutsEnabled">
            <div class="flex items-center gap-2 text-sm text-green-700">
              <svg class="w-4 h-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
              </svg>
              Stripe Connect Express Account verbunden. Auszahlungen laufen automatisch.
            </div>
          </template>
          <template v-else-if="stripeAccountId">
            <p class="text-sm text-yellow-700 mb-3">
              Dein Stripe-Konto wurde erstellt, aber das Onboarding ist noch nicht abgeschlossen.
            </p>
            <form @submit.prevent="refreshOnboarding">
              <button type="submit" :disabled="onboarding"
                class="bg-[#e91e8c] hover:bg-[#c91478] disabled:opacity-50 text-white text-sm font-bold px-5 py-2.5 rounded-lg transition">
                {{ onboarding ? 'Weiterleitung…' : 'Onboarding fortsetzen' }}
              </button>
            </form>
          </template>
          <template v-else>
            <ul class="text-sm text-gray-600 space-y-1 mb-4 ml-1">
              <li class="flex items-start gap-2"><span class="text-[#e91e8c] mt-0.5">✓</span> Bankkonto / IBAN hinterlegen</li>
              <li class="flex items-start gap-2"><span class="text-[#e91e8c] mt-0.5">✓</span> Identitätsverifizierung (KYC) via Stripe</li>
              <li class="flex items-start gap-2"><span class="text-[#e91e8c] mt-0.5">✓</span> Automatische monatliche Auszahlung (80%)</li>
            </ul>
            <form @submit.prevent="startOnboarding">
              <button type="submit" :disabled="onboarding"
                class="bg-[#e91e8c] hover:bg-[#c91478] disabled:opacity-50 text-white text-sm font-bold px-5 py-2.5 rounded-lg transition">
                {{ onboarding ? 'Weiterleitung…' : 'Auszahlungen aktivieren →' }}
              </button>
            </form>
          </template>
        </div>
      </div>

      <!-- ── Ausstehende Auszahlung ─────────────────────────────────────── -->
      <div class="bg-white rounded-xl border border-gray-200 p-6 shadow-sm">
        <div class="flex items-center justify-between flex-wrap gap-4">
          <div>
            <p class="text-sm text-gray-500">Ausstehende Auszahlung</p>
            <p class="text-3xl font-bold text-gray-900 mt-1">CHF {{ Number(pendingTotal).toFixed(2) }}</p>
          </div>
          <button v-if="!payoutsEnabled" @click="showBankForm = !showBankForm"
            class="bg-[#e91e8c] text-white text-sm font-bold px-4 py-2 rounded-lg hover:bg-[#c91478] transition">
            Bankdaten hinterlegen
          </button>
        </div>
      </div>

      <!-- ── Manuelle Bankdaten (nur wenn kein Connect) ─────────────────── -->
      <div v-if="showBankForm && !payoutsEnabled" class="bg-white rounded-xl border border-gray-200 p-6 shadow-sm">
        <h2 class="font-semibold text-gray-900 mb-4">Bankverbindung (manuelle Auszahlung)</h2>
        <form @submit.prevent="saveBankDetails" class="space-y-4">
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">IBAN</label>
            <input v-model="bankForm.iban" type="text" placeholder="CH56 0483 5012 3456 7800 9"
              class="w-full border border-gray-300 bg-white text-gray-900 rounded-lg px-3 py-2 text-sm placeholder-gray-400 focus:outline-none focus:border-[#e91e8c]" />
          </div>
          <div class="grid grid-cols-2 gap-4">
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Bank</label>
              <input v-model="bankForm.bank_name" type="text" placeholder="UBS, Raiffeisen, …"
                class="w-full border border-gray-300 bg-white text-gray-900 rounded-lg px-3 py-2 text-sm placeholder-gray-400 focus:outline-none focus:border-[#e91e8c]" />
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Kontoinhaber</label>
              <input v-model="bankForm.account_holder" type="text" placeholder="Max Mustermann"
                class="w-full border border-gray-300 bg-white text-gray-900 rounded-lg px-3 py-2 text-sm placeholder-gray-400 focus:outline-none focus:border-[#e91e8c]" />
            </div>
          </div>
          <PrimaryButton type="submit" :loading="saving">Bankdaten speichern</PrimaryButton>
        </form>
      </div>

      <!-- ── Transaktionen ──────────────────────────────────────────────── -->
      <div class="bg-white rounded-xl border border-gray-200 shadow-sm">
        <div class="px-6 py-4 border-b border-gray-200 flex items-center justify-between">
          <h2 class="font-semibold text-gray-900">Einnahmen</h2>
          <span class="text-xs text-gray-400">{{ transactions.length }} Transaktionen</span>
        </div>
        <div v-if="transactions.length === 0" class="text-center py-12 text-gray-400">
          <div class="text-4xl mb-3">💳</div>
          <p>Noch keine Transaktionen.</p>
          <p class="text-xs mt-1">Sobald jemand dein Abonnement kauft, erscheint es hier.</p>
        </div>
        <div v-else class="divide-y divide-gray-100">
          <div v-for="t in transactions" :key="t.id"
            class="px-6 py-4 flex items-center justify-between gap-4 flex-wrap">
            <div>
              <p class="text-sm font-semibold text-gray-900">
                {{ t.fan_name }}
                <span class="text-xs font-normal text-gray-400 ml-1">{{ typeLabel(t.type) }}</span>
              </p>
              <p class="text-xs text-gray-500 mt-0.5">{{ t.date }}</p>
            </div>
            <div class="text-right shrink-0">
              <p class="font-bold text-gray-900">CHF {{ Number(t.creator_net_chf).toFixed(2) }}</p>
              <p class="text-xs text-gray-400">
                Brutto {{ Number(t.gross_amount_chf).toFixed(2) }} · Provision {{ Number(t.platform_fee_chf).toFixed(2) }}
              </p>
            </div>
          </div>
        </div>
      </div>

      <!-- ── Manueller Auszahlungs-Verlauf ─────────────────────────────── -->
      <div v-if="payouts.length > 0" class="bg-white rounded-xl border border-gray-200 shadow-sm">
        <div class="px-6 py-4 border-b border-gray-200">
          <h2 class="font-semibold text-gray-900">Auszahlungs-Verlauf</h2>
        </div>
        <div class="divide-y divide-gray-100">
          <div v-for="p in payouts" :key="p.id" class="px-6 py-4 flex items-center justify-between">
            <div>
              <p class="font-semibold text-gray-900">{{ p.period }}</p>
              <p class="text-xs text-gray-500 mt-0.5">
                Brutto CHF {{ p.gross_amount_chf }} · Provision CHF {{ p.commission_chf }}
              </p>
            </div>
            <div class="text-right">
              <p class="font-bold text-gray-900">CHF {{ p.net_amount_chf }}</p>
              <span class="text-xs px-2 py-0.5 rounded-full font-semibold"
                :class="{
                  'bg-yellow-50 text-yellow-700': p.status === 'pending',
                  'bg-blue-50 text-blue-700':     p.status === 'processing',
                  'bg-green-50 text-green-700':   p.status === 'paid',
                  'bg-red-50 text-red-700':        p.status === 'failed',
                }">
                {{ payoutStatusLabel(p.status) }}
              </span>
            </div>
          </div>
        </div>
      </div>

    </div>
  </AppLayout>
</template>

<script setup>
import { ref } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';

const props = defineProps({
  payouts:         { type: Array,   default: () => [] },
  pendingTotal:    { type: Number,  default: 0 },
  hasIban:         { type: Boolean, default: false },
  stripeAccountId: { type: String,  default: null },
  payoutsEnabled:  { type: Boolean, default: false },
  transactions:    { type: Array,   default: () => [] },
});

const showBankForm = ref(!props.hasIban && !props.payoutsEnabled);
const saving       = ref(false);
const onboarding   = ref(false);
const bankForm     = ref({ iban: '', bank_name: '', account_holder: '' });

function saveBankDetails() {
  saving.value = true;
  router.post(route('inserat.payouts.bank'), bankForm.value, {
    onFinish:  () => { saving.value = false; },
    onSuccess: () => { showBankForm.value = false; },
  });
}

function startOnboarding() {
  onboarding.value = true;
  router.post(route('inserat.payouts.onboard'), {}, {
    onFinish: () => { onboarding.value = false; },
  });
}

function refreshOnboarding() {
  onboarding.value = true;
  router.get(route('inserat.payouts.onboard.refresh'), {}, {
    onFinish: () => { onboarding.value = false; },
  });
}

function typeLabel(t)  {
  return { subscription: 'Abonnement', push: 'Push' }[t] ?? t;
}

function payoutStatusLabel(s) {
  return { pending: 'Ausstehend', processing: 'In Bearbeitung', paid: 'Bezahlt', failed: 'Fehlgeschlagen' }[s] ?? s;
}
</script>
