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
          So sieht dein vollständiges Profil für Besucher aus – mit Galerie, Verifizierung, Steckbrief, Services und Kontakt.
        </p>

        <!-- Hero: vollständiges Profil-Mockup -->
        <div class="relative max-w-4xl mx-auto rounded-2xl overflow-hidden border border-white/10 shadow-2xl bg-[#0f0f0f]">
          <span class="absolute top-3 right-3 z-20 bg-black/70 text-gray-300 text-[10px] px-2 py-0.5 rounded-full">Demo</span>

          <!-- Browserleiste -->
          <div class="flex items-center gap-1.5 px-4 py-2.5 bg-[#1a1a1a] border-b border-white/8">
            <span class="w-2.5 h-2.5 rounded-full bg-red-400/70"></span>
            <span class="w-2.5 h-2.5 rounded-full bg-yellow-400/70"></span>
            <span class="w-2.5 h-2.5 rounded-full bg-green-400/70"></span>
            <span class="ml-3 text-[11px] text-gray-500 truncate">booklola.ch/profil/sophie</span>
          </div>

          <div class="p-4 sm:p-6 space-y-5">
            <!-- Galerie -->
            <div class="grid grid-cols-4 gap-2">
              <div v-for="i in 4" :key="i" class="relative aspect-[4/5] rounded-lg overflow-hidden"
                :class="i % 2 ? 'bg-gradient-to-br from-[#e35d8f]/40 to-[#7c3aed]/40' : 'bg-gradient-to-tr from-[#7c3aed]/40 to-[#e35d8f]/35'">
                <svg class="absolute left-1/2 top-[58%] -translate-x-1/2 -translate-y-1/2 w-2/3 h-2/3 text-white/25" viewBox="0 0 24 24" fill="currentColor"><path d="M12 12a4.5 4.5 0 100-9 4.5 4.5 0 000 9Zm0 1.8c-3.6 0-7.2 1.8-7.2 4.5V21h14.4v-2.7c0-2.7-3.6-4.5-7.2-4.5Z"/></svg>
                <span v-if="i === 1" class="absolute top-1.5 left-1.5 w-4 h-4 rounded-full bg-green-500 flex items-center justify-center">
                  <svg class="w-2.5 h-2.5 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                </span>
                <span v-if="i === 4" class="absolute inset-0 bg-black/55 flex flex-col items-center justify-center text-white">
                  <span class="text-2xl font-black">+6</span>
                  <span class="text-[10px] opacity-80">mehr</span>
                </span>
              </div>
            </div>

            <!-- Name + Badges + Pills -->
            <div>
              <div class="flex flex-wrap items-center gap-2.5">
                <h3 class="text-2xl font-black text-white">Sophie</h3>
                <span class="inline-flex items-center gap-1 text-[11px] bg-green-500/15 text-green-300 border border-green-500/40 px-2 py-0.5 rounded-full font-semibold">
                  <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>
                  Vollständig verifiziert
                </span>
                <span class="text-[11px] bg-[#e35d8f]/10 text-[#e35d8f] border border-[#e35d8f]/30 px-2.5 py-0.5 rounded-full font-semibold">Escort</span>
              </div>
              <div class="flex flex-wrap gap-2 mt-2.5">
                <span v-for="pill in ['📍 Zürich', '⏳ 27 Jahre', '👁 1.2k Aufrufe', '★ 4.9']" :key="pill"
                  class="text-[11px] text-gray-400 bg-white/5 border border-white/8 px-2.5 py-1 rounded-full">{{ pill }}</span>
              </div>
            </div>

            <!-- Zwei Spalten: Kontakt + Details -->
            <div class="grid lg:grid-cols-3 gap-4">
              <!-- Kontakt -->
              <div class="space-y-2">
                <div class="flex items-center justify-center gap-2 bg-green-600 text-white text-sm font-bold py-2.5 rounded-lg">
                  <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24"><path d="M.057 24l1.687-6.163a11.867 11.867 0 01-1.587-5.946C.16 5.335 5.495 0 12.05 0a11.82 11.82 0 018.413 3.488 11.82 11.82 0 013.48 8.414c-.003 6.557-5.338 11.892-11.893 11.892a11.9 11.9 0 01-5.688-1.448L.057 24zm6.597-3.807c1.676.995 3.276 1.591 5.392 1.592 5.448 0 9.886-4.434 9.889-9.885.002-5.462-4.415-9.89-9.881-9.892-5.452 0-9.887 4.434-9.889 9.884a9.86 9.86 0 001.51 5.26l-.999 3.648 3.788-.995z"/></svg>
                  WhatsApp
                </div>
                <div class="flex items-center justify-center gap-2 bg-white/5 border border-white/10 text-white text-sm font-semibold py-2.5 rounded-lg">
                  <svg class="w-4 h-4 text-[#e35d8f]" fill="currentColor" viewBox="0 0 20 20"><path d="M2 3a1 1 0 011-1h2.153a1 1 0 01.986.836l.74 4.435a1 1 0 01-.54 1.06l-1.548.773a11.037 11.037 0 006.105 6.105l.774-1.548a1 1 0 011.059-.54l4.435.74a1 1 0 01.836.986V17a1 1 0 01-1 1h-2C7.82 18 2 12.18 2 5V3z"/></svg>
                  Ruf mich an
                </div>
                <div class="flex items-center justify-center gap-2 bg-[#e35d8f] text-white text-sm font-semibold py-2.5 rounded-lg">
                  <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20"><path d="M2.003 5.884L10 9.882l7.997-3.998A2 2 0 0016 4H4a2 2 0 00-1.997 1.884z"/><path d="M18 8.118l-8 4-8-4V14a2 2 0 002 2h12a2 2 0 002-2V8.118z"/></svg>
                  Nachricht senden
                </div>
                <div class="flex items-center justify-center gap-2 bg-white/5 border border-white/10 text-gray-300 text-sm font-semibold py-2.5 rounded-lg">♥ Als Favorit speichern</div>
              </div>

              <!-- Über mich + Steckbrief + Services -->
              <div class="lg:col-span-2 space-y-4">
                <div class="bg-[#1a1a1a] border border-white/8 rounded-xl p-4">
                  <p class="text-[11px] font-bold text-gray-500 uppercase tracking-wider mb-2">Über Sophie</p>
                  <p class="text-sm text-gray-300 leading-relaxed">Herzlich, diskret und mit einem Auge fürs Besondere. Ich nehme mir Zeit für echte Begegnungen und schätze Niveau und gegenseitigen Respekt …</p>
                </div>
                <div class="bg-[#1a1a1a] border border-white/8 rounded-xl p-4">
                  <p class="text-[11px] font-bold text-gray-500 uppercase tracking-wider mb-3">Steckbrief</p>
                  <div class="grid grid-cols-2 sm:grid-cols-3 gap-y-3 gap-x-4">
                    <div v-for="d in [['Herkunft','Europäisch'],['Größe','168 cm'],['Sprachen','DE · EN'],['Statur','Schlank'],['Haare','Braun'],['Raucht','Nein']]" :key="d[0]">
                      <span class="block text-[10px] text-gray-500 uppercase tracking-wide">{{ d[0] }}</span>
                      <span class="block text-sm text-white font-medium">{{ d[1] }}</span>
                    </div>
                  </div>
                </div>
                <div class="bg-[#1a1a1a] border border-white/8 rounded-xl p-4">
                  <p class="text-[11px] font-bold text-gray-500 uppercase tracking-wider mb-3">Angebote &amp; Services</p>
                  <div class="grid grid-cols-2 sm:grid-cols-3 gap-y-2 gap-x-4">
                    <div v-for="s in ['Klassische Massage','Entspannung','Begleitung','Dinner-Date','Wellness','Thai-Massage']" :key="s" class="flex items-center gap-2">
                      <span class="w-4 h-4 rounded-full bg-[#e35d8f]/15 border border-[#e35d8f]/30 flex items-center justify-center shrink-0"><svg class="w-2.5 h-2.5 text-[#e35d8f]" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg></span>
                      <span class="text-xs text-gray-300">{{ s }}</span>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Ergänzend: Übersicht + Nachrichten -->
        <div class="grid md:grid-cols-2 gap-6 mt-8 max-w-4xl mx-auto items-start">

          <!-- Übersicht -->
          <div class="flex flex-col">
            <h3 class="text-white font-bold text-center mb-3">In der Übersicht</h3>
            <div class="relative bg-[#0f0f0f] border border-white/10 rounded-2xl p-3 shadow-lg">
              <span class="absolute top-2 right-2 z-10 bg-black/70 text-gray-300 text-[10px] px-2 py-0.5 rounded-full">Demo</span>
              <div class="grid grid-cols-2 gap-2">
                <div v-for="i in 4" :key="i" class="rounded-lg overflow-hidden border border-white/8 bg-[#1a1a1a]">
                  <div class="relative aspect-[4/5]" :class="i % 2 ? 'bg-gradient-to-br from-[#e35d8f]/40 to-[#7c3aed]/40' : 'bg-gradient-to-tr from-[#7c3aed]/40 to-[#e35d8f]/35'">
                    <svg class="absolute left-1/2 top-[58%] -translate-x-1/2 -translate-y-1/2 w-1/2 h-1/2 text-white/25" viewBox="0 0 24 24" fill="currentColor"><path d="M12 12a4.5 4.5 0 100-9 4.5 4.5 0 000 9Zm0 1.8c-3.6 0-7.2 1.8-7.2 4.5V21h14.4v-2.7c0-2.7-3.6-4.5-7.2-4.5Z"/></svg>
                  </div>
                  <div class="px-2 py-1.5 flex items-center gap-1">
                    <span class="text-[11px] font-semibold text-white truncate">{{ ['Sophie','Lara','Mia','Nina'][i - 1] }}</span>
                    <span class="w-3 h-3 rounded-full bg-green-500 flex items-center justify-center shrink-0"><svg class="w-2 h-2 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg></span>
                  </div>
                </div>
              </div>
            </div>
            <p class="text-gray-400 text-sm leading-relaxed mt-3 text-center">
              So erscheinst du in der Übersicht und wirst von Interessenten gefunden.
            </p>
          </div>

          <!-- Nachrichten (Mockup) -->
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
          Beispielhafte Demo-Ansicht mit Platzhalter-Bildern – echte Profile zeigen echte Fotos.
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
