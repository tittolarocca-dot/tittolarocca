<template>
  <AppLayout>
    <Head title="Auszahlungen" />
    <div class="max-w-4xl mx-auto px-4 py-8">
      <div class="flex items-center justify-between mb-6">
        <div>
          <h1 class="text-2xl font-bold text-white">Auszahlungen</h1>
          <p class="text-sm text-gray-500 mt-1">Deine monatlichen Einnahmen (80% nach 20% Provision)</p>
        </div>
        <Link :href="route('inserat.dashboard')" class="text-sm text-[#e91e8c] hover:underline">← Dashboard</Link>
      </div>

      <!-- Pending Total -->
      <div class="bg-[#1a1a1a] rounded-xl border border-[#2a2a2a] p-6 mb-6">
        <div class="flex items-center justify-between">
          <div>
            <p class="text-sm text-gray-400">Ausstehende Auszahlung</p>
            <p class="text-3xl font-bold text-white mt-1">CHF {{ Number(pendingTotal).toFixed(2) }}</p>
          </div>
          <button @click="showBankForm = !showBankForm"
            class="bg-[#e91e8c] text-white text-sm font-bold px-4 py-2 rounded-lg hover:bg-[#c91478] transition">
            Bankdaten hinterlegen
          </button>
        </div>
      </div>

      <!-- Bank Details Form -->
      <div v-if="showBankForm" class="bg-[#1a1a1a] rounded-xl border border-[#2a2a2a] p-6 mb-6">
        <h2 class="font-semibold text-white mb-4">Bankverbindung</h2>
        <form @submit.prevent="saveBankDetails" class="space-y-4">
          <div>
            <label class="block text-sm font-medium text-gray-300 mb-1">IBAN</label>
            <input v-model="bankForm.iban" type="text" placeholder="CH56 0483 5012 3456 7800 9"
              class="w-full border border-[#2a2a2a] bg-[#111111] text-white rounded-lg px-3 py-2 text-sm placeholder-gray-600 focus:outline-none focus:border-[#e91e8c]" />
          </div>
          <div class="grid grid-cols-2 gap-4">
            <div>
              <label class="block text-sm font-medium text-gray-300 mb-1">Bank</label>
              <input v-model="bankForm.bank_name" type="text" placeholder="UBS, Raiffeisen, …"
                class="w-full border border-[#2a2a2a] bg-[#111111] text-white rounded-lg px-3 py-2 text-sm placeholder-gray-600 focus:outline-none focus:border-[#e91e8c]" />
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-300 mb-1">Kontoinhaber</label>
              <input v-model="bankForm.account_holder" type="text" placeholder="Max Mustermann"
                class="w-full border border-[#2a2a2a] bg-[#111111] text-white rounded-lg px-3 py-2 text-sm placeholder-gray-600 focus:outline-none focus:border-[#e91e8c]" />
            </div>
          </div>
          <PrimaryButton type="submit" :loading="saving">Bankdaten speichern</PrimaryButton>
        </form>
      </div>

      <!-- Payout History -->
      <div class="bg-[#1a1a1a] rounded-xl border border-[#2a2a2a]">
        <div class="px-6 py-4 border-b border-[#2a2a2a]">
          <h2 class="font-semibold text-white">Auszahlungs-Verlauf</h2>
        </div>
        <div v-if="payouts.length === 0" class="text-center py-12 text-gray-500">
          <div class="text-4xl mb-3">💰</div>
          <p>Noch keine Auszahlungen.</p>
          <p class="text-xs mt-1 text-gray-600">Auszahlungen werden monatlich generiert (Minimum CHF 50).</p>
        </div>
        <div v-else class="divide-y divide-[#2a2a2a]">
          <div v-for="p in payouts" :key="p.id" class="px-6 py-4 flex items-center justify-between">
            <div>
              <p class="font-semibold text-white">{{ p.period }}</p>
              <p class="text-xs text-gray-500 mt-0.5">
                Brutto CHF {{ p.gross_amount_chf }} · Provision CHF {{ p.commission_chf }}
              </p>
            </div>
            <div class="text-right">
              <p class="font-bold text-white">CHF {{ p.net_amount_chf }}</p>
              <span class="text-xs px-2 py-0.5 rounded-full font-semibold"
                :class="{
                  'bg-yellow-900/30 text-yellow-400': p.status === 'pending',
                  'bg-blue-900/30 text-blue-400':     p.status === 'processing',
                  'bg-green-900/30 text-green-400':   p.status === 'paid',
                  'bg-red-900/30 text-red-400':        p.status === 'failed',
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
