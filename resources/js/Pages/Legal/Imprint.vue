<template>
  <AppLayout>
    <Head title="Impressum" />

    <div class="max-w-3xl mx-auto px-4 py-10">
      <h1 class="text-2xl sm:text-3xl font-black text-white mb-6">Impressum</h1>

      <p class="text-sm text-gray-300 leading-relaxed mb-6">
        Angaben gemäss den gesetzlichen Anforderungen zur Anbieterkennzeichnung.
      </p>

      <h2 class="text-lg font-bold text-white mt-8 mb-2">Betreiber der Website</h2>
      <p class="text-sm text-gray-300 leading-relaxed mb-1"><span class="text-amber-400">[Name des Betreibers / Firmenname]</span></p>
      <p class="text-sm text-gray-300 leading-relaxed mb-1"><span class="text-amber-400">[Rechtsform, z. B. Einzelunternehmen / GmbH / AG]</span></p>
      <p class="text-sm text-gray-300 leading-relaxed mb-1"><span class="text-amber-400">[Strasse und Hausnummer]</span></p>
      <p class="text-sm text-gray-300 leading-relaxed mb-1"><span class="text-amber-400">[PLZ Ort]</span></p>
      <p class="text-sm text-gray-300 leading-relaxed mb-4"><span class="text-amber-400">[Land, z. B. Schweiz]</span></p>

      <h2 class="text-lg font-bold text-white mt-8 mb-2">Kontakt</h2>
      <p class="text-sm text-gray-300 leading-relaxed mb-1">E-Mail: support@booklola.ch</p>
      <p class="text-sm text-gray-300 leading-relaxed mb-4">Telefon: <span class="text-amber-400">[optional: Telefonnummer]</span></p>

      <h2 class="text-lg font-bold text-white mt-8 mb-2">Vertretungsberechtigte Person</h2>
      <p class="text-sm text-gray-300 leading-relaxed mb-4"><span class="text-amber-400">[Vor- und Nachname der vertretungsberechtigten Person]</span></p>

      <h2 class="text-lg font-bold text-white mt-8 mb-2">Handelsregister / UID</h2>
      <p class="text-sm text-gray-300 leading-relaxed mb-4">
        <span class="text-amber-400">[UID-Nummer (CHE-xxx.xxx.xxx) und Handelsregistereintrag, sofern vorhanden]</span>
      </p>

      <h2 class="text-lg font-bold text-white mt-8 mb-2">Verantwortlich für den Inhalt</h2>
      <p class="text-sm text-gray-300 leading-relaxed mb-4"><span class="text-amber-400">[Name der verantwortlichen Person]</span></p>

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

      <p class="text-xs text-gray-500 mt-10">Stand: <span class="text-amber-400">[Datum der letzten Aktualisierung]</span></p>
    </div>
  </AppLayout>
</template>

<script setup>
import { Head } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
</script>
