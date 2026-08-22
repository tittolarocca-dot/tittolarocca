<template>
  <AppLayout>
    <Head title="Gratis inserieren" />

    <!-- Hero -->
    <section class="relative overflow-hidden bg-gradient-to-b from-[#1a1a1a] to-[#111] border-b border-white/10">
      <div class="max-w-4xl mx-auto px-4 py-16 sm:py-20 text-center">
        <span class="inline-block bg-[#e35d8f]/15 text-[#e35d8f] text-xs font-bold px-3 py-1 rounded-full tracking-wide mb-5">
          🚀 Startphase – aktuell 100 % gratis
        </span>
        <h1 class="text-3xl sm:text-4xl md:text-5xl font-black text-white leading-tight mb-4">
          Werde entdeckt – <span class="text-[#e35d8f]">gratis inserieren</span> in der ganzen Schweiz
        </h1>
        <p class="text-gray-300 text-base sm:text-lg max-w-2xl mx-auto mb-8 leading-relaxed">
          booklola.ch ist die neue Schweizer Plattform für Erotik- &amp; Begleitinserate.
          Erstelle dein Inserat in wenigen Minuten – in der Startphase komplett kostenlos.
        </p>
        <Link :href="route('register')"
          class="inline-flex items-center gap-2 bg-[#e35d8f] hover:bg-[#c44a7a] text-white text-base font-bold px-8 py-4 rounded-xl transition shadow-lg shadow-[#e35d8f]/30">
          Gratis Inserat erstellen →
        </Link>
        <p class="text-gray-500 text-xs mt-4">Keine Zahlung · 30 Tage gratis aktiv · jederzeit kündbar</p>
      </div>
    </section>

    <!-- Vorteile -->
    <section class="max-w-5xl mx-auto px-4 py-14">
      <h2 class="text-2xl font-black text-white text-center mb-10">Warum booklola.ch?</h2>
      <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-5">
        <div v-for="b in benefits" :key="b.title"
          class="bg-[#1a1a1a] border border-white/8 rounded-2xl p-6 hover:border-[#e35d8f]/40 transition">
          <div class="text-3xl mb-3">{{ b.icon }}</div>
          <h3 class="text-white font-bold text-lg mb-1.5">{{ b.title }}</h3>
          <p class="text-gray-400 text-sm leading-relaxed">{{ b.text }}</p>
        </div>
      </div>
    </section>

    <!-- So funktioniert's -->
    <section class="bg-[#1a1a1a]/50 border-y border-white/10">
      <div class="max-w-4xl mx-auto px-4 py-14">
        <h2 class="text-2xl font-black text-white text-center mb-10">In 3 Schritten live</h2>
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-6">
          <div v-for="(s, i) in steps" :key="i" class="text-center">
            <div class="w-11 h-11 mx-auto mb-3 rounded-full bg-[#e35d8f] text-white font-black flex items-center justify-center">
              {{ i + 1 }}
            </div>
            <h3 class="text-white font-bold mb-1">{{ s.title }}</h3>
            <p class="text-gray-400 text-sm leading-relaxed">{{ s.text }}</p>
          </div>
        </div>
      </div>
    </section>

    <!-- Abschluss-CTA -->
    <section class="max-w-3xl mx-auto px-4 py-16 text-center">
      <h2 class="text-2xl sm:text-3xl font-black text-white mb-3">Bereit? Erstelle jetzt dein gratis Inserat.</h2>
      <p class="text-gray-400 text-sm mb-7">Sichere dir deinen Platz, solange die Startphase kostenlos ist.</p>
      <Link :href="route('register')"
        class="inline-flex items-center gap-2 bg-[#e35d8f] hover:bg-[#c44a7a] text-white text-base font-bold px-8 py-4 rounded-xl transition shadow-lg shadow-[#e35d8f]/30">
        Gratis Inserat erstellen →
      </Link>
      <p class="text-gray-600 text-xs mt-6">Nur für Erwachsene ab 18 Jahren.</p>
    </section>
  </AppLayout>
</template>

<script setup>
import { Head, Link } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';

const benefits = [
  { icon: '🆓', title: 'Gratis in der Startphase', text: 'Inserate sind aktuell komplett kostenlos – kein Stripe, keine versteckten Kosten. 30 Tage aktiv.' },
  { icon: '🇨🇭', title: 'Schweizweite Reichweite', text: 'Auffindbar nach Stadt, Kanton und Kategorie – von Zürich bis Genf.' },
  { icon: '🔒', title: 'Diskret & sicher', text: 'Private Galerie nur für Freigeschaltete, Länder-Sperren (Geoblocking) – du behältst die Kontrolle.' },
  { icon: '✅', title: 'Verifiziert = mehr Vertrauen', text: 'Foto- und ID-Verifizierung mit Badge hebt dich von anonymen Inseraten ab.' },
  { icon: '⚡', title: 'In Minuten erstellt', text: 'Registrieren, Profil ausfüllen, Fotos hochladen – fertig und sofort live.' },
  { icon: '💬', title: 'Direkter Kontakt', text: 'Chatte mit Interessenten, blockiere unerwünschte Anfragen, deaktiviere jederzeit.' },
];

const steps = [
  { title: 'Kostenlos registrieren', text: 'Lege in einer Minute ein Konto als Inserent:in an.' },
  { title: 'Profil & Fotos', text: 'Fülle dein Profil aus und lade deine Fotos hoch.' },
  { title: 'Gratis aktivieren', text: 'Inserat gratis freischalten – sofort schweizweit sichtbar.' },
];
</script>
