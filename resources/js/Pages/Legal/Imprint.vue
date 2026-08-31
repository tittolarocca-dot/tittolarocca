<template>
  <AppLayout>
    <Head title="Impressum" />

    <div class="max-w-3xl mx-auto px-4 py-10">
      <h1 class="text-2xl sm:text-3xl font-black text-white mb-6">Impressum</h1>

      <p class="text-sm text-gray-300 leading-relaxed mb-6">
        Angaben gemäss den gesetzlichen Anforderungen zur Anbieterkennzeichnung.
      </p>

      <h2 class="text-lg font-bold text-white mt-8 mb-2">Betreiber der Website</h2>
      <p class="text-sm text-gray-300 leading-relaxed mb-1">booklola (Beta-Phase)</p>
      <p class="text-sm text-gray-300 leading-relaxed mb-4">Schweiz</p>

      <h2 class="text-lg font-bold text-white mt-8 mb-2">Kontakt</h2>
      <p class="text-sm text-gray-300 leading-relaxed mb-4">E-Mail: support@booklola.ch</p>

      <h2 class="text-lg font-bold text-white mt-8 mb-2">Haftung für Inhalte</h2>
      <p class="text-sm text-gray-300 leading-relaxed mb-3">
        booklola.ch ist eine Inserate- und Vermittlungsplattform. Die auf der Plattform
        veröffentlichten Inserate, Profile, Fotos, Texte und Kontaktangaben stammen von den
        jeweiligen Inserierenden. Diese sind allein für die Richtigkeit, Rechtmässigkeit und
        Aktualität ihrer Inhalte verantwortlich. Der Betreiber übernimmt keine Gewähr für
        von Nutzenden eingestellte Inhalte.
      </p>
      <p class="text-sm text-gray-300 leading-relaxed mb-3">
        Trotz sorgfältiger inhaltlicher Kontrolle übernehmen wir keine Haftung für die Inhalte
        externer Links. Für den Inhalt der verlinkten Seiten sind ausschliesslich deren
        Betreiber verantwortlich.
      </p>

      <h2 class="text-lg font-bold text-white mt-8 mb-2">Urheberrecht</h2>
      <p class="text-sm text-gray-300 leading-relaxed mb-3">
        Die durch den Betreiber erstellten Inhalte und Werke auf dieser Website unterliegen dem
        Urheberrecht. Beiträge Dritter sind als solche gekennzeichnet. Jede vom Urheberrecht
        nicht zugelassene Verwertung bedarf der vorherigen schriftlichen Zustimmung des
        jeweiligen Rechteinhabers.
      </p>

      <h2 class="text-lg font-bold text-white mt-8 mb-2">Hinweis zum Alter (18+)</h2>
      <p class="text-sm text-gray-300 leading-relaxed mb-3">
        Diese Website richtet sich ausschliesslich an volljährige Personen (18 Jahre oder älter)
        und enthält Inhalte für Erwachsene.
      </p>

      <p class="text-xs text-gray-500 mt-10">Stand: 1.9.2026</p>
    </div>
  </AppLayout>
</template>

<script setup>
import { Head } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
</script>
