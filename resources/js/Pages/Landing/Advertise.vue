<template>
  <AppLayout>
    <Head title="Gratis inserieren" />

    <!-- Hero -->
    <section class="relative overflow-hidden bg-gradient-to-b from-[#1a1a1a] to-[#111] border-b border-white/10">
      <div class="max-w-4xl mx-auto px-4 py-16 sm:py-20 text-center">
        <span class="inline-block bg-[#e35d8f]/15 text-[#e35d8f] text-xs font-bold px-3 py-1 rounded-full tracking-wide mb-5">
          🆓 Inserieren ist &amp; bleibt kostenlos
        </span>
        <h1 class="text-3xl sm:text-4xl md:text-5xl font-black text-white leading-tight mb-4">
          Werde entdeckt – <span class="text-[#e35d8f]">gratis inserieren</span> in der ganzen Schweiz
        </h1>
        <p class="text-gray-300 text-base sm:text-lg max-w-2xl mx-auto mb-8 leading-relaxed">
          booklola.ch ist die neue Schweizer Plattform für Erotik- &amp; Begleitinserate.
          Erstelle dein Inserat in wenigen Minuten – kostenlos.
        </p>
        <Link :href="route('register')"
          class="inline-flex items-center gap-2 bg-[#e35d8f] hover:bg-[#c44a7a] text-white text-base font-bold px-8 py-4 rounded-xl transition shadow-lg shadow-[#e35d8f]/30">
          Gratis Inserat erstellen →
        </Link>
        <p class="text-gray-500 text-xs mt-4">Keine Kreditkarte · keine Verpflichtung · jederzeit löschbar</p>
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

    <!-- So präsentierst du dich -->
    <section class="bg-[#1a1a1a]/40 border-y border-white/10">
      <div class="max-w-6xl mx-auto px-4 py-14">
        <h2 class="text-2xl font-black text-white text-center mb-2">So präsentierst du dich auf BookLola</h2>
        <p class="text-gray-400 text-sm text-center max-w-2xl mx-auto mb-10">
          3 Beispielansichten, wie dein Auftritt auf BookLola aussieht.
        </p>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 items-start">

          <!-- Kachel 1: Profilseite (echter Screenshot) -->
          <div class="flex flex-col">
            <h3 class="text-white font-bold text-center mb-3">Profilseite</h3>
            <a href="/images/demo/profil.png" target="_blank" rel="noopener"
              class="relative block rounded-2xl overflow-hidden border border-white/10 shadow-lg hover:border-[#e35d8f]/50 transition h-[440px]">
              <span class="absolute top-2 right-2 z-10 bg-black/70 text-gray-300 text-[10px] px-2 py-0.5 rounded-full">Demo</span>
              <img src="/images/demo/profil.png" alt="Beispiel-Profilseite auf BookLola (Demo)"
                class="w-full h-full object-cover object-top" loading="lazy" />
            </a>
            <p class="text-gray-400 text-sm leading-relaxed mt-3 text-center">
              So sehen Besucher dein vollständiges Profil mit Bildern, Beschreibung, Verifizierung und Kontaktmöglichkeiten.
            </p>
          </div>

          <!-- Kachel 2: Übersicht (echter Screenshot) -->
          <div class="flex flex-col">
            <h3 class="text-white font-bold text-center mb-3">Übersicht</h3>
            <a href="/images/demo/uebersicht.png" target="_blank" rel="noopener"
              class="relative block rounded-2xl overflow-hidden border border-white/10 shadow-lg hover:border-[#e35d8f]/50 transition h-[440px]">
              <span class="absolute top-2 right-2 z-10 bg-black/70 text-gray-300 text-[10px] px-2 py-0.5 rounded-full">Demo</span>
              <img src="/images/demo/uebersicht.png" alt="Beispiel-Übersicht der Inserate auf BookLola (Demo)"
                class="w-full h-full object-cover object-top" loading="lazy" />
            </a>
            <p class="text-gray-400 text-sm leading-relaxed mt-3 text-center">
              So erscheint dein Profil in der Übersicht und wird von Interessenten gefunden.
            </p>
          </div>

          <!-- Kachel 3: Nachrichten (Mockup) -->
          <div class="flex flex-col">
            <h3 class="text-white font-bold text-center mb-3">Nachrichten</h3>
            <div class="relative bg-[#111] border border-white/10 rounded-2xl overflow-hidden shadow-lg hover:border-[#e35d8f]/40 transition">
              <span class="absolute top-9 right-2 z-10 bg-black/70 text-gray-300 text-[10px] px-2 py-0.5 rounded-full">Demo</span>
              <div class="bg-[#e35d8f] px-3 py-1.5 flex items-center justify-between">
                <span class="text-white text-[10px] font-black">♥ BookLola.ch</span>
                <span class="text-white/80 text-[8px]">Mein Konto</span>
              </div>
              <div class="flex items-center gap-2 px-3 py-2 border-b border-white/8">
                <div class="w-8 h-8 rounded-full bg-gradient-to-br from-[#e35d8f]/50 to-[#7c3aed]/40 flex items-center justify-center shrink-0">
                  <svg class="w-4 h-4 text-white/55" viewBox="0 0 24 24" fill="currentColor"><path d="M12 12a4.5 4.5 0 100-9 4.5 4.5 0 000 9Zm0 1.8c-3.6 0-7.2 1.8-7.2 4.5V21h14.4v-2.7c0-2.7-3.6-4.5-7.2-4.5Z"/></svg>
                </div>
                <div>
                  <p class="text-white text-[11px] font-bold leading-none mb-0.5">Sofia</p>
                  <span class="text-green-400 text-[8px]">● online</span>
                </div>
              </div>
              <div class="p-3 space-y-2" style="min-height:288px">
                <div class="max-w-[80%] bg-white/8 text-gray-200 text-[10px] px-2.5 py-1.5 rounded-2xl rounded-tl-sm">Hallo, bist du diese Woche verfügbar?</div>
                <div class="max-w-[80%] ml-auto bg-[#e35d8f] text-white text-[10px] px-2.5 py-1.5 rounded-2xl rounded-tr-sm">Ja, gerne 😊 Wann passt es dir?</div>
                <div class="max-w-[80%] bg-white/8 text-gray-200 text-[10px] px-2.5 py-1.5 rounded-2xl rounded-tl-sm">Morgen Abend? 🌙</div>
                <div class="max-w-[80%] ml-auto bg-[#e35d8f] text-white text-[10px] px-2.5 py-1.5 rounded-2xl rounded-tr-sm">Perfekt, ich freue mich!</div>
              </div>
              <div class="flex items-center gap-2 px-3 py-2 border-t border-white/8">
                <div class="flex-1 h-6 rounded-full bg-white/5 border border-white/10"></div>
                <div class="w-6 h-6 rounded-full bg-[#e35d8f] flex items-center justify-center text-white text-[10px]">➤</div>
              </div>
            </div>
            <p class="text-gray-400 text-sm leading-relaxed mt-3 text-center">
              BookLola ist mehr als ein Inserat: Nachrichten, Favoriten und weitere Funktionen sorgen für mehr Interaktion.
            </p>
          </div>

        </div>

        <p class="text-gray-600 text-xs text-center mt-8">
          Beispielhafte Demo-Ansichten mit Illustrationen – echte Profile zeigen echte Fotos.
        </p>
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
      <p class="text-gray-400 text-sm mb-7">Erstelle jetzt dein kostenloses Inserat und werde schweizweit gefunden.</p>
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
  { icon: '🆓', title: 'Kostenlos inserieren', text: 'Ein Inserat zu erstellen ist und bleibt kostenlos – keine Zahlung, keine versteckten Kosten.' },
  { icon: '🇨🇭', title: 'Schweizweit sichtbar', text: 'Auffindbar nach Stadt, Kanton und Kategorie – von Zürich bis Genf.' },
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
