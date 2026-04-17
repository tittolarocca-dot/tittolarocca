<template>
  <AppLayout>
    <Head title="Auszahlungen" />
    <div class="max-w-4xl mx-auto px-4 py-8">
      <div class="flex items-center justify-between mb-6">
        <div>
          <h1 class="text-2xl font-bold text-gray-900">Auszahlungen</h1>
          <p class="text-sm text-gray-500 mt-1">Deine monatlichen Einnahmen (80% nach 20% Provision)</p>
        </div>
        <Link :href="route('inserat.dashboard')" class="text-sm text-pink-600 hover:underline">← Dashboard</Link>
      </div>

      <!-- Pending Total -->
      <div class="bg-white rounded-xl border border-gray-200 p-6 mb-6">
        <div class="flex items-center justify-between">
          <div>
            <p class="text-sm text-gray-500">Ausstehende Auszahlung</p>
            <p class="text-3xl font-bold text-gray-900 mt-1">CHF {{ Number(pendingTotal).toFixed(2) }}</p>
          </div>
          <button @click="showBankForm = !showBankForm"
            class="bg-pink-600 text-white text-sm font-semibold px-4 py-2 rounded-lg hover:bg-pink-700 transition">
            Bankdaten hinterlegen
          </button>
        </div>
      </div>

      <!-- Bank Details Form -->
      <div v-if="showBankForm" class="bg-white rounded-xl border border-gray-200 p-6 mb-6">
        <h2 class="font-semibold text-gray-900 mb-4">Bankverbindung</h2>
        <form @submit.prevent="saveBankDetails" class="space-y-4">
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">IBAN</label>
            <input v-model="bankForm.iban" type="text" placeholder="CH56 0483 5012 3456 7800 9"
              class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:border-pink-400" />
          </div>
          <div class="grid grid-cols-2 gap-4">
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Bank</label>
              <input v-model="bankForm.bank_name" type="text" placeholder="UBS, Raiffeisen, …"
                class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:border-pink-400" />
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Kontoinhaber</label>
              <input v-model="bankForm.account_holder" type="text" placeholder="Max Mustermann"
                class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:border-pink-400" />
            </div>
          </div>
          <PrimaryButton type="submit" :loading="saving">Bankdaten speichern</PrimaryButton>
        </form>
      </div>

      <!-- Payout History -->
      <div class="bg-white rounded-xl border border-gray-200">
        <div class="px-6 py-4 border-b border-gray-100">
          <h2 class="font-semibold text-gray-900">Auszahlungs-Verlauf</h2>
        </div>
        <div v-if="payouts.length === 0" class="text-center py-12 text-gray-400">
          <div class="text-4xl mb-3">💰</div>
          <p>Noch keine Auszahlungen.</p>
          <p class="text-xs mt-1">Auszahlungen werden monatlich generiert (Minimum CHF 50).</p>
        </div>
        <div v-else class="divide-y divide-gray-100">
          <div v-for="p in payouts" :key="p.id" class="px-6 py-4 flex items-center justify-between">
            <div>
              <p class="font-semibold text-gray-800">{{ p.period }}</p>
              <p class="text-xs text-gray-500 mt-0.5">
                Brutto CHF {{ p.gross_amount_chf }} · Provision CHF {{ p.commission_chf }}
              </p>
            </div>
            <div class="text-right">
              <p class="font-bold text-gray-900">CHF {{ p.net_amount_chf }}</p>
              <span class="text-xs px-2 py-0.5 rounded-full font-semibold"
                :class="{
                  'bg-yellow-100 text-yellow-800': p.status === 'pending',
                  'bg-blue-100 text-blue-800':     p.status === 'processing',
                  'bg-green-100 text-green-800':   p.status === 'paid',
                  'bg-red-100 text-red-800':        p.status === 'failed',
                }">
                {{ statusLabel(p.status) }}
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
  payouts:      { type: Array,  default: () => [] },
  pendingTotal: { type: Number, default: 0 },
  hasIban:      { type: Boolean, default: false },
});

const showBankForm = ref(!props.hasIban);
const saving       = ref(false);
const bankForm     = ref({ iban: '', bank_name: '', account_holder: '' });

function saveBankDetails() {
  saving.value = true;
  router.post(route('inserat.payouts.bank'), bankForm.value, {
    onFinish: () => { saving.value = false; },
    onSuccess: () => { showBankForm.value = false; },
  });
}

function statusLabel(s) {
  return { pending: 'Ausstehend', processing: 'In Bearbeitung', paid: 'Bezahlt', failed: 'Fehlgeschlagen' }[s] ?? s;
}
</script>
