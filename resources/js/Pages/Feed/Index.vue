<template>
  <AppLayout>
    <Head :title="t('feed.title')" />

    <div class="max-w-5xl mx-auto px-4 py-5 sm:py-6">
      <div class="flex gap-8 justify-center">

        <!-- ── Feed-Spalte ──────────────────────────────────────────────── -->
        <div class="w-full max-w-xl lg:max-w-2xl min-w-0">
          <header class="mb-5">
            <h1 class="text-2xl sm:text-3xl font-black" style="color:var(--bl-text);">{{ t('feed.title') }}</h1>
            <p class="text-sm mt-1" style="color:var(--bl-text-muted);">{{ t('feed.subtitle') }}</p>
          </header>

          <div v-if="items.length" class="space-y-4">
            <FeedCard v-for="p in items" :key="p.id" :post="p" :is-authed="isAuthed"
              @login="goLogin" @image="lightboxImage = $event" />
            <div ref="sentinel" class="h-10"></div>
            <div v-if="loading" class="text-center text-sm py-2" style="color:var(--bl-text-muted);">{{ t('feed.loading') }}</div>
            <div v-else-if="!nextUrl" class="text-center text-xs py-2" style="color:var(--bl-text-muted);opacity:.7;">{{ t('feed.end') }}</div>
          </div>

          <div v-else class="rounded-2xl p-10 text-center" style="background:var(--bl-surface);border:1px solid var(--bl-border);">
            <div class="text-4xl mb-3">📭</div>
            <p class="text-sm" style="color:var(--bl-text-muted);">{{ t('feed.empty_line1') }}<br>{{ t('feed.empty_line2') }}</p>
          </div>
        </div>

        <!-- ── Sidebar (nur Desktop) ────────────────────────────────────── -->
        <aside class="hidden lg:block w-80 shrink-0">
          <div class="sticky space-y-4" style="top:76px;">

            <!-- Neue Profile -->
            <div v-if="newProfiles.length" class="rounded-2xl p-4" style="background:var(--bl-surface);border:1px solid var(--bl-border);">
              <h3 class="flex items-center gap-2 text-[15px] font-bold mb-3" style="color:var(--bl-text);">
                <svg class="w-[18px] h-[18px]" style="color:var(--bl-pink);" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
                {{ t('feed.new_profiles') }}
              </h3>
              <div class="space-y-1">
                <Link v-for="p in newProfiles" :key="p.slug" :href="route('profile.show', p.slug)" class="flex items-center gap-3 p-2 -mx-1 rounded-xl transition hover:bg-white/5">
                  <span class="w-10 h-10 rounded-full overflow-hidden shrink-0 flex items-center justify-center" style="background:var(--bl-surface-2);">
                    <img v-if="p.avatar" :src="p.avatar" :alt="p.display_name" class="w-full h-full object-cover object-top" loading="lazy" />
                    <span v-else class="text-lg">👤</span>
                  </span>
                  <span class="min-w-0">
                    <span class="flex items-center gap-1.5">
                      <span class="text-sm font-semibold truncate" style="color:var(--bl-text);">{{ p.display_name }}</span>
                      <svg v-if="p.verified" class="w-3.5 h-3.5 shrink-0" fill="currentColor" viewBox="0 0 20 20" style="color:var(--bl-verify);"><path fill-rule="evenodd" d="M16.7 5.3a1 1 0 010 1.4l-8 8a1 1 0 01-1.4 0l-4-4a1 1 0 011.4-1.4L8 12.6l7.3-7.3a1 1 0 011.4 0z" clip-rule="evenodd"/></svg>
                    </span>
                    <span v-if="p.city" class="block text-xs truncate" style="color:var(--bl-text-muted);">📍 {{ p.city }}</span>
                  </span>
                </Link>
              </div>
            </div>

            <!-- Jetzt online -->
            <div v-if="onlineProfiles.length" class="rounded-2xl p-4" style="background:var(--bl-surface);border:1px solid var(--bl-border);">
              <h3 class="flex items-center gap-2 text-[15px] font-bold mb-3" style="color:var(--bl-text);">
                <span class="w-2.5 h-2.5 rounded-full" style="background:var(--bl-success);box-shadow:0 0 0 3px rgba(47,210,122,.2);"></span>
                {{ t('feed.online_now') }}
              </h3>
              <div class="space-y-1">
                <Link v-for="p in onlineProfiles" :key="'on'+p.slug" :href="route('profile.show', p.slug)" class="flex items-center gap-3 p-2 -mx-1 rounded-xl transition hover:bg-white/5">
                  <span class="relative w-10 h-10 shrink-0">
                    <span class="w-10 h-10 rounded-full overflow-hidden flex items-center justify-center" style="background:var(--bl-surface-2);">
                      <img v-if="p.avatar" :src="p.avatar" :alt="p.display_name" class="w-full h-full object-cover object-top" loading="lazy" />
                      <span v-else class="text-lg">👤</span>
                    </span>
                    <span class="absolute -bottom-0.5 -right-0.5 w-3.5 h-3.5 rounded-full border-2" style="background:var(--bl-success);border-color:var(--bl-surface);"></span>
                  </span>
                  <span class="min-w-0">
                    <span class="flex items-center gap-1.5">
                      <span class="text-sm font-semibold truncate" style="color:var(--bl-text);">{{ p.display_name }}</span>
                      <svg v-if="p.verified" class="w-3.5 h-3.5 shrink-0" fill="currentColor" viewBox="0 0 20 20" style="color:var(--bl-verify);"><path fill-rule="evenodd" d="M16.7 5.3a1 1 0 010 1.4l-8 8a1 1 0 01-1.4 0l-4-4a1 1 0 011.4-1.4L8 12.6l7.3-7.3a1 1 0 011.4 0z" clip-rule="evenodd"/></svg>
                    </span>
                    <span v-if="p.city" class="block text-xs truncate" style="color:var(--bl-text-muted);">📍 {{ p.city }}</span>
                  </span>
                </Link>
              </div>
            </div>

            <!-- Städte entdecken -->
            <div v-if="cities.length" class="rounded-2xl p-4" style="background:var(--bl-surface);border:1px solid var(--bl-border);">
              <h3 class="flex items-center gap-2 text-[15px] font-bold mb-3" style="color:var(--bl-text);">
                <svg class="w-[18px] h-[18px]" style="color:var(--bl-pink);" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M5.05 4.05a7 7 0 119.9 9.9L10 18.9l-4.95-4.95a7 7 0 010-9.9zM10 11a2 2 0 100-4 2 2 0 000 4z" clip-rule="evenodd"/></svg>
                {{ t('nav.cities') }}
              </h3>
              <div class="flex flex-wrap gap-2">
                <Link v-for="city in cities" :key="city.id" :href="route('city', { city: city.slug })"
                  class="text-xs rounded-full px-3 py-1.5 transition hover:border-[#e35d8f] hover:text-[#e35d8f]" style="background:var(--bl-surface-2);border:1px solid var(--bl-border);color:var(--bl-text-muted);">{{ city.name }}</Link>
              </div>
            </div>
          </div>
        </aside>
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
import { Head, Link, router, usePage } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import FeedCard from '@/Components/FeedCard.vue';
import { useI18n } from '@/composables/useI18n';

const { t } = useI18n();

const props = defineProps({
  posts:       { type: Array,  default: () => [] },
  nextPageUrl: { type: String, default: null },
  isAuthed:    { type: Boolean, default: false },
  newProfiles:    { type: Array, default: () => [] },
  onlineProfiles: { type: Array, default: () => [] },
});

const page = usePage();
const isAuthed = computed(() => props.isAuthed || !!page.props.auth?.user);
const cities   = computed(() => (page.props.cities ?? []).filter((c) => c?.slug));

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
