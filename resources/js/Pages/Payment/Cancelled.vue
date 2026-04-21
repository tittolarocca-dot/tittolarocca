<template>
  <AppLayout>
    <Head title="Zahlung abgebrochen" />

    <div class="min-h-[60vh] flex items-center justify-center px-4 py-16">
      <div class="bg-white rounded-2xl border border-gray-200 p-10 max-w-md w-full text-center shadow-lg">

        <div class="w-20 h-20 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-6">
          <svg class="w-10 h-10 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
          </svg>
        </div>

        <h1 class="text-2xl font-bold text-gray-900 mb-2">Zahlung abgebrochen</h1>
        <p class="text-gray-500 mb-8">
          Kein Problem – deine Daten wurden gespeichert. Du kannst jederzeit ein Paket kaufen.
        </p>

        <div class="flex flex-col sm:flex-row gap-3">
          <Link :href="route('inserat.package.select')"
            class="flex-1 bg-[#e91e8c] text-white rounded-lg px-4 py-2.5 text-sm font-bold hover:bg-[#c91478] transition text-center">
            Erneut versuchen
          </Link>
          <Link :href="route('inserat.dashboard')"
            class="flex-1 border border-gray-200 text-gray-600 rounded-lg px-4 py-2.5 text-sm font-semibold hover:border-gray-300 hover:text-gray-900 transition text-center">
            Zum Dashboard
          </Link>
        </div>
      </div>
    </div>
  </AppLayout>
</template>

<script setup>
import { Head, Link } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
</script>
