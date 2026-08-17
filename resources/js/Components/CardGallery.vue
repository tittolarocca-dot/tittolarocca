<template>
  <!-- Wischbare Foto-Galerie für Inserate-Karten.
       Nutzt natives CSS-Scroll-Snap: auf Mobile per Finger nach links/rechts
       wischen, auf Desktop per Trackpad/Drag. Ein Tap auf das Bild folgt dem
       umschliessenden Karten-Link (öffnet das Profil). -->
  <div v-if="slides.length" class="absolute inset-0">
    <div ref="track"
      class="cg-track flex h-full w-full overflow-x-auto snap-x snap-mandatory scrollbar-hide overscroll-x-contain"
      @scroll.passive="onScroll">
      <div v-for="(m, i) in slides" :key="m.id ?? i"
        class="snap-center shrink-0 w-full h-full">
        <!-- Erstes Bild optional eager (LCP), Rest lazy via lazysizes -->
        <img v-if="eagerFirst && i === 0"
          class="w-full h-full object-cover object-top select-none"
          :src="src(m, 'card')"
          :srcset="srcset(m)"
          sizes="auto"
          loading="eager"
          fetchpriority="high"
          draggable="false"
          :alt="alt" />
        <img v-else
          class="lazyload w-full h-full object-cover object-top select-none"
          :data-src="src(m, 'card')"
          :data-srcset="srcset(m)"
          data-sizes="auto"
          src="data:image/gif;base64,R0lGODlhAQABAAAAACH5BAEKAAEALAAAAAABAAEAAAICTAEAOw=="
          draggable="false"
          :alt="alt" />
      </div>
    </div>

    <!-- Punkt-Indikator (nur bei mehreren Fotos) -->
    <div v-if="slides.length > 1"
      class="absolute bottom-2 left-1/2 -translate-x-1/2 flex gap-1 pointer-events-none z-10">
      <span v-for="(m, i) in slides" :key="'dot-' + (m.id ?? i)"
        class="h-1.5 w-1.5 rounded-full transition-all duration-200 shadow-sm"
        :class="i === active ? 'bg-white scale-110' : 'bg-white/45'"></span>
    </div>
  </div>

  <!-- Kein Foto vorhanden -->
  <div v-else class="w-full h-full bg-white/5 flex items-center justify-center text-3xl">👤</div>
</template>

<script setup>
import { ref, computed } from 'vue';

const props = defineProps({
  media:      { type: Array,  default: () => [] },
  alt:        { type: String, default: '' },
  // Höchstzahl an Fotos in der Karten-Galerie (Performance/UX)
  max:        { type: Number, default: 8 },
  // Erstes Bild sofort laden (für die grosse Top-Ad-Karte / LCP)
  eagerFirst: { type: Boolean, default: false },
});

// Nur Bilder, sortiert kommt bereits vom Backend, auf max begrenzen
const slides = computed(() =>
  (props.media ?? [])
    .filter((m) => m && m.type === 'image')
    .slice(0, props.max)
);

const track  = ref(null);
const active = ref(0);

function onScroll() {
  const el = track.value;
  if (!el) return;
  const w = el.clientWidth || 1;
  active.value = Math.round(el.scrollLeft / w);
}

// Bild-Variante wählen (Fallback auf Original-Stream bei Alt-Medien)
function src(m, size) {
  return m?.src?.[size] ?? m?.url;
}
function srcset(m) {
  return m?.src ? `${m.src.thumbnail} 320w, ${m.src.card} 720w` : undefined;
}
</script>

<style scoped>
.scrollbar-hide::-webkit-scrollbar { display: none; }
.scrollbar-hide { -ms-overflow-style: none; scrollbar-width: none; }
/* Horizontales Wischen erlauben, vertikales Scrollen der Seite nicht blockieren */
.cg-track { touch-action: pan-x pan-y; }
</style>
