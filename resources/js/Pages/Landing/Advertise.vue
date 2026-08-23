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

    <!-- So präsentierst du dich (Demo-Ansichten, dem echten Design nachempfunden) -->
    <section class="bg-[#1a1a1a]/40 border-y border-white/10">
      <div class="max-w-6xl mx-auto px-4 py-14">
        <h2 class="text-2xl font-black text-white text-center mb-2">So präsentierst du dich auf BookLola</h2>
        <p class="text-gray-400 text-sm text-center max-w-2xl mx-auto mb-10">
          3 Beispielansichten, wie dein Auftritt auf BookLola aussieht.
        </p>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 items-start">

          <!-- Kachel 1: Profilseite -->
          <div class="flex flex-col">
            <h3 class="text-white font-bold text-center mb-3">Profilseite</h3>
            <div class="relative bg-[#111] border border-white/10 rounded-2xl overflow-hidden shadow-lg hover:border-[#e35d8f]/40 transition">
              <span class="absolute top-8 right-2 z-10 bg-black/70 text-gray-300 text-[9px] px-2 py-0.5 rounded-full">Demo</span>
              <!-- BookLola-Kopfleiste -->
              <div class="bg-[#e35d8f] px-3 py-1.5 flex items-center justify-between">
                <span class="text-white text-[10px] font-black">♥ BookLola.ch</span>
                <span class="text-white/80 text-[8px]">DE ·  Anmelden</span>
              </div>
              <!-- Galerie -->
              <div class="p-1.5 flex gap-1">
                <div class="h-16 flex-1 rounded bg-gradient-to-br from-[#e35d8f]/40 to-[#7c3aed]/25 flex items-center justify-center">
                  <svg class="w-6 h-6 text-white/45" viewBox="0 0 24 24" fill="currentColor"><path d="M12 12a4.5 4.5 0 100-9 4.5 4.5 0 000 9Zm0 1.8c-3.6 0-7.2 1.8-7.2 4.5V21h14.4v-2.7c0-2.7-3.6-4.5-7.2-4.5Z"/></svg>
                </div>
                <div class="h-16 flex-1 rounded bg-gradient-to-br from-[#7c3aed]/35 to-[#e35d8f]/20"></div>
                <div class="h-16 flex-1 rounded bg-gradient-to-br from-[#e35d8f]/25 to-[#7c3aed]/30"></div>
              </div>
              <div class="px-3 pt-1 pb-3">
                <!-- Name + Badges -->
                <div class="flex items-center gap-1.5 flex-wrap">
                  <span class="text-white font-black text-sm">Sofia</span>
                  <span class="bg-green-500/20 text-green-400 text-[8px] font-bold px-1.5 py-0.5 rounded-full">✓ Foto bestätigt</span>
                  <span class="text-yellow-400 text-[9px] font-bold">★ 5.0</span>
                </div>
                <p class="text-gray-500 text-[10px] mt-1">📍 Bern · 26 Jahre · 139 Aufrufe</p>
                <!-- Kontakt-Buttons -->
                <div class="space-y-1.5 mt-2.5">
                  <div class="bg-green-600 text-white text-[10px] font-bold text-center py-1.5 rounded-lg">WhatsApp schreiben</div>
                  <div class="bg-[#e35d8f] text-white text-[10px] font-bold text-center py-1.5 rounded-lg">Nachricht senden</div>
                  <div class="bg-white/5 border border-white/10 text-gray-300 text-[10px] text-center py-1.5 rounded-lg">Profil teilen</div>
                </div>
                <!-- Über -->
                <p class="text-gray-400 text-[9px] font-bold mt-3 mb-1 uppercase tracking-wide">Über Sofia</p>
                <div class="space-y-1">
                  <div class="h-1.5 rounded bg-white/10 w-full"></div>
                  <div class="h-1.5 rounded bg-white/10 w-11/12"></div>
                  <div class="h-1.5 rounded bg-white/10 w-3/4"></div>
                </div>
                <!-- Services -->
                <p class="text-gray-400 text-[9px] font-bold mt-3 mb-1 uppercase tracking-wide">Angebote &amp; Services</p>
                <div class="grid grid-cols-2 gap-x-2 gap-y-1">
                  <span v-for="s in ['GFE','Massage','Dinner Date','Reisebegleitung','Tantra','Safe Sex']" :key="s"
                    class="text-gray-300 text-[9px] flex items-center gap-1">
                    <span class="text-[#e35d8f]">✓</span>{{ s }}
                  </span>
                </div>
              </div>
            </div>
            <p class="text-gray-400 text-sm leading-relaxed mt-3 text-center">
              So sehen Besucher dein vollständiges Profil mit Bildern, Beschreibung, Verifizierung und Kontaktmöglichkeiten.
            </p>
          </div>

          <!-- Kachel 2: Übersicht -->
          <div class="flex flex-col">
            <h3 class="text-white font-bold text-center mb-3">Übersicht</h3>
            <div class="relative bg-[#111] border border-white/10 rounded-2xl overflow-hidden shadow-lg hover:border-[#e35d8f]/40 transition">
              <span class="absolute top-8 right-2 z-10 bg-black/70 text-gray-300 text-[9px] px-2 py-0.5 rounded-full">Demo</span>
              <!-- BookLola-Kopfleiste -->
              <div class="bg-[#e35d8f] px-3 py-1.5 flex items-center justify-between">
                <span class="text-white text-[10px] font-black">♥ BookLola.ch</span>
                <span class="text-white/80 text-[8px]">Inserieren</span>
              </div>
              <div class="px-2 pt-2 pb-1">
                <p class="text-[#e35d8f] text-[10px] font-black mb-2">Anschauen oder treffen – du entscheidest</p>
                <!-- TOP AD -->
                <div class="relative flex gap-2 bg-[#1a1a1a] border border-white/8 rounded-lg p-1.5 mb-2">
                  <span class="absolute top-1 left-1 bg-[#e35d8f] text-white text-[6px] font-black px-1 py-0.5 rounded-full z-10">TOP AD</span>
                  <div class="w-14 h-16 rounded-md bg-gradient-to-br from-[#e35d8f]/40 to-[#7c3aed]/30 flex items-center justify-center shrink-0">
                    <svg class="w-6 h-6 text-white/45" viewBox="0 0 24 24" fill="currentColor"><path d="M12 12a4.5 4.5 0 100-9 4.5 4.5 0 000 9Zm0 1.8c-3.6 0-7.2 1.8-7.2 4.5V21h14.4v-2.7c0-2.7-3.6-4.5-7.2-4.5Z"/></svg>
                  </div>
                  <div class="min-w-0 flex-1 py-0.5">
                    <div class="flex items-center gap-1">
                      <span class="text-white text-[11px] font-bold">Lena</span>
                      <span class="w-3 h-3 rounded-full bg-green-500 flex items-center justify-center text-white text-[7px]">✓</span>
                    </div>
                    <p class="text-gray-500 text-[9px]">24 J. · 📍 Zürich · <span class="text-[#e35d8f]">Escort</span></p>
                    <div class="h-1 rounded bg-white/10 w-4/5 mt-1.5"></div>
                    <div class="h-1 rounded bg-white/10 w-3/5 mt-1"></div>
                  </div>
                </div>
                <!-- Grid-Karten -->
                <div v-for="p in overviewDemo" :key="p.name" class="flex gap-2 bg-[#1a1a1a] border border-white/8 rounded-lg p-1.5 mb-2">
                  <div class="w-12 h-14 rounded-md bg-gradient-to-br from-[#7c3aed]/35 to-[#e35d8f]/25 flex items-center justify-center shrink-0">
                    <svg class="w-5 h-5 text-white/45" viewBox="0 0 24 24" fill="currentColor"><path d="M12 12a4.5 4.5 0 100-9 4.5 4.5 0 000 9Zm0 1.8c-3.6 0-7.2 1.8-7.2 4.5V21h14.4v-2.7c0-2.7-3.6-4.5-7.2-4.5Z"/></svg>
                  </div>
                  <div class="min-w-0 flex-1 py-0.5">
                    <div class="flex items-center gap-1">
                      <span class="text-white text-[11px] font-bold">{{ p.name }}</span>
                      <span class="text-gray-500 text-[8px]">{{ p.age }} J.</span>
                    </div>
                    <p class="text-gray-500 text-[9px]">📍 {{ p.city }} · <span class="text-[#e35d8f]">{{ p.cat }}</span></p>
                    <div class="h-1 rounded bg-white/10 w-3/4 mt-1.5"></div>
                  </div>
                </div>
              </div>
            </div>
            <p class="text-gray-400 text-sm leading-relaxed mt-3 text-center">
              So erscheint dein Profil in der Übersicht und wird von Interessenten gefunden.
            </p>
          </div>

          <!-- Kachel 3: Nachrichten -->
          <div class="flex flex-col">
            <h3 class="text-white font-bold text-center mb-3">Nachrichten</h3>
            <div class="relative bg-[#111] border border-white/10 rounded-2xl overflow-hidden shadow-lg hover:border-[#e35d8f]/40 transition">
              <span class="absolute top-8 right-2 z-10 bg-black/70 text-gray-300 text-[9px] px-2 py-0.5 rounded-full">Demo</span>
              <!-- BookLola-Kopfleiste -->
              <div class="bg-[#e35d8f] px-3 py-1.5 flex items-center justify-between">
                <span class="text-white text-[10px] font-black">♥ BookLola.ch</span>
                <span class="text-white/80 text-[8px]">Mein Konto</span>
              </div>
              <!-- Chat-Header -->
              <div class="flex items-center gap-2 px-3 py-2 border-b border-white/8">
                <div class="w-8 h-8 rounded-full bg-gradient-to-br from-[#e35d8f]/50 to-[#7c3aed]/40 flex items-center justify-center shrink-0">
                  <svg class="w-4 h-4 text-white/55" viewBox="0 0 24 24" fill="currentColor"><path d="M12 12a4.5 4.5 0 100-9 4.5 4.5 0 000 9Zm0 1.8c-3.6 0-7.2 1.8-7.2 4.5V21h14.4v-2.7c0-2.7-3.6-4.5-7.2-4.5Z"/></svg>
                </div>
                <div>
                  <p class="text-white text-[11px] font-bold leading-none mb-0.5">Sofia</p>
                  <span class="text-green-400 text-[8px]">● online</span>
                </div>
              </div>
              <!-- Bubbles -->
              <div class="p-3 space-y-2 min-h-[176px]">
                <div class="max-w-[80%] bg-white/8 text-gray-200 text-[10px] px-2.5 py-1.5 rounded-2xl rounded-tl-sm">Hallo, bist du diese Woche verfügbar?</div>
                <div class="max-w-[80%] ml-auto bg-[#e35d8f] text-white text-[10px] px-2.5 py-1.5 rounded-2xl rounded-tr-sm">Ja, gerne 😊 Wann passt es dir?</div>
                <div class="max-w-[80%] bg-white/8 text-gray-200 text-[10px] px-2.5 py-1.5 rounded-2xl rounded-tl-sm">Morgen Abend? 🌙</div>
                <div class="max-w-[80%] ml-auto bg-[#e35d8f] text-white text-[10px] px-2.5 py-1.5 rounded-2xl rounded-tr-sm">Perfekt, ich freue mich!</div>
              </div>
              <!-- Eingabe -->
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
          Beispielhafte Demo-Ansichten – echte Profile zeigen echte Fotos.
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

const overviewDemo = [
  { name: 'Mia',  age: 22, city: 'Bern',  cat: 'Begleitung' },
  { name: 'Nina', age: 27, city: 'Basel', cat: 'Massage' },
];

const steps = [
  { title: 'Kostenlos registrieren', text: 'Lege in einer Minute ein Konto als Inserent:in an.' },
  { title: 'Profil & Fotos', text: 'Fülle dein Profil aus und lade deine Fotos hoch.' },
  { title: 'Gratis aktivieren', text: 'Inserat gratis freischalten – sofort schweizweit sichtbar.' },
];
</script>
