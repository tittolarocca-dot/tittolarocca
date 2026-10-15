<template>
  <AppLayout>
    <Head title="Aktuell auf Booklola" />

    <div class="max-w-xl mx-auto px-4 py-5 sm:py-6">
      <!-- Kopf -->
      <header class="mb-5">
        <h1 class="text-2xl sm:text-3xl font-black text-white">Aktuell auf Booklola</h1>
        <p class="text-sm text-gray-400 mt-1">Neue Beiträge, Bilder und Updates aus der Community</p>
      </header>

      <!-- Beiträge -->
      <div v-if="items.length" class="space-y-4">
        <FeedCard v-for="p in items" :key="p.id" :post="p" :is-authed="isAuthed"
          @login="goLogin" @image="lightboxImage = $event" />

        <!-- Sentinel für Infinite Scroll -->
        <div ref="sentinel" class="h-10"></div>
        <div v-if="loading" class="text-center text-gray-500 text-sm py-2">Lädt …</div>
        <div v-else-if="!nextUrl" class="text-center text-gray-600 text-xs py-2">Du bist auf dem neuesten Stand.</div>
      </div>

      <!-- Empty State -->
      <div v-else class="bg-[#1a1a1a] border border-white/8 rounded-2xl p-10 text-center">
        <div class="text-4xl mb-3">📭</div>
        <p class="text-gray-300 text-sm">Noch keine Beiträge vorhanden.<br>Schau bald wieder vorbei.</p>
      </div>
    </div>

    <!-- Foto-Lightbox (Originalgrösse) -->
    <div v-if="lightboxImage" class="fixed inset-0 z-[60] bg-black/90 flex items-center justify-center p-4" @click="lightboxImage = null">
      <img :src="lightboxImage" class="max-h-[92vh] max-w-[92vw] object-contain rounded-lg select-none" />
      <button class="absolute top-4 right-4 text-white text-3xl w-10 h-10 flex items-center justify-center hover:text-gray-300" @click.stop="lightboxImage = null">✕</button>
    </div>
  </AppLayout>
</template>

<script setup>
import { ref, computed, onMounted, onBeforeUnmount } from 'vue';
import { Head, router, usePage } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import FeedCard from '@/Components/FeedCard.vue';

const props = defineProps({
  posts:       { type: Array,  default: () => [] },
  nextPageUrl: { type: String, default: null },
  isAuthed:    { type: Boolean, default: false },
});

const page = usePage();
const isAuthed = computed(() => props.isAuthed || !!page.props.auth?.user);

const items   = ref([...props.posts]);
const nextUrl = ref(props.nextPageUrl);
const loading = ref(false);
const sentinel = ref(null);
const lightboxImage = ref(null);

let observer = null;

async function loadMore() {
  if (loading.value || !nextUrl.value) return;
  loading.value = true;
  try {
    const sep = nextUrl.value.includes('?') ? '&' : '?';
    const res = await fetch(`${nextUrl.value}${sep}json=1`, {
      headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' },
    });
    const data = await res.json();
    // Doppelte vermeiden (falls zwischenzeitlich neue Beiträge oben dazukamen)
    const seen = new Set(items.value.map(i => i.id));
    for (const p of (data.posts ?? [])) if (!seen.has(p.id)) items.value.push(p);
    nextUrl.value = data.nextPageUrl;
  } catch (e) {
    // still: beim nächsten Scroll erneut versuchen
  } finally {
    loading.value = false;
  }
}

onMounted(() => {
  observer = new IntersectionObserver((entries) => {
    if (entries.some(e => e.isIntersecting)) loadMore();
  }, { rootMargin: '600px 0px' });
  if (sentinel.value) observer.observe(sentinel.value);
});

onBeforeUnmount(() => { if (observer) observer.disconnect(); });

function goLogin() {
  router.visit(route('login'));
}
</script>
