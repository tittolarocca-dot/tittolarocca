<template>
  <!-- Ein Beitrag im globalen Community-Feed. Design identisch zum bestehenden
       Booklola-Dark/Pink-System. Wiederverwendung der Follow-Route. -->
  <article class="bg-[#1a1a1a] border border-white/8 rounded-2xl overflow-hidden">
    <!-- Kopf: Avatar + Name (+ Verified) · Ort · Zeit -->
    <div class="flex items-center gap-2.5 px-4 pt-4">
      <Link :href="route('profile.show', author.slug)" class="shrink-0" :title="author.name">
        <span class="block w-10 h-10 rounded-full overflow-hidden bg-gradient-to-br from-[#e35d8f] to-[#7c3aed] flex items-center justify-center text-white text-sm font-bold">
          <img v-if="author.avatar_url" :src="author.avatar_url" :alt="author.name" class="w-full h-full object-cover" loading="lazy" width="40" height="40" />
          <template v-else>{{ initials }}</template>
        </span>
      </Link>
      <div class="min-w-0 flex-1">
        <Link :href="route('profile.show', author.slug)" class="inline-flex items-center gap-1 group">
          <span class="text-sm font-semibold text-white truncate group-hover:text-[#e35d8f] transition">{{ author.name }}</span>
          <span v-if="author.verified" class="inline-flex items-center justify-center w-4 h-4 rounded-full bg-green-500 shrink-0" :title="t('feed.verified')">
            <svg class="w-2.5 h-2.5 text-white" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>
          </span>
        </Link>
        <p class="text-[11px] text-gray-500 truncate">
          <span v-if="author.city">{{ author.city }} · </span>{{ post.time }}
        </p>
      </div>
    </div>

    <!-- Text -->
    <p v-if="post.text" class="px-4 pt-3 text-[15px] text-white leading-relaxed whitespace-pre-line">{{ post.text }}</p>

    <!-- Medien (responsiv, lazy, WebP-Varianten; Klick öffnet Originalgrösse) -->
    <div v-if="post.media && post.media.length" class="px-4 pt-3">
      <div class="grid gap-1.5 rounded-xl overflow-hidden" :class="post.media.length === 1 ? 'grid-cols-1' : 'grid-cols-2'">
        <div v-for="m in post.media" :key="m.id" class="relative bg-black/30 rounded-lg overflow-hidden"
          :class="post.media.length === 1 ? '' : 'aspect-square'">
          <img v-if="m.type === 'image'" :src="m.src" :alt="author.name" loading="lazy"
            @click="$emit('image', m.full || m.src)"
            class="w-full h-auto cursor-zoom-in"
            :class="post.media.length === 1 ? 'max-h-[70vh] object-contain mx-auto' : 'absolute inset-0 h-full object-cover'" />
          <video v-else :src="m.src" controls preload="metadata" playsinline
            class="w-full" :class="post.media.length === 1 ? 'max-h-[70vh]' : 'absolute inset-0 h-full object-cover'"></video>
        </div>
      </div>
    </div>

    <!-- Aktionen: Profil ansehen · Folgen -->
    <div class="px-4 py-3 mt-1 flex items-center gap-2">
      <Link :href="route('profile.show', author.slug)"
        class="flex-1 text-center text-sm font-semibold text-gray-200 bg-white/5 hover:bg-white/10 border border-white/10 rounded-lg px-3 py-2 transition">
        {{ t('feed.view_profile') }}
      </Link>
      <button type="button" @click="toggleFollow" :disabled="busy"
        class="flex-1 text-sm font-bold rounded-lg px-3 py-2 transition disabled:opacity-60 inline-flex items-center justify-center gap-1.5"
        :class="following ? 'bg-white/5 border border-[#e35d8f]/50 text-[#e35d8f]' : 'bg-[#e35d8f] hover:bg-[#c44a7a] text-white'">
        <svg v-if="!following" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
        {{ following ? t('feed.following') : t('feed.follow') }}
      </button>
    </div>
  </article>
</template>

<script setup>
import { ref, computed } from 'vue';
import { Link, router } from '@inertiajs/vue3';
import { useI18n } from '@/composables/useI18n';

const { t } = useI18n();

const props = defineProps({
  post:     { type: Object,  required: true },
  isAuthed: { type: Boolean, default: false },
});

const emit = defineEmits(['login', 'image']);

const author    = computed(() => props.post.author ?? {});
const initials  = computed(() => (author.value.name || '?').trim().charAt(0).toUpperCase());
const following = ref(!!author.value.is_following);
const busy      = ref(false);

// Bestehende Follow-Funktion wiederverwenden (keine zweite Logik).
function toggleFollow() {
  if (!props.isAuthed) { emit('login'); return; }
  if (busy.value || !author.value.slug) return;
  busy.value = true;
  following.value = !following.value; // optimistisch
  router.post(route('konto.follow.toggle', author.value.slug), {}, {
    preserveScroll: true,
    preserveState: true,
    onFinish: () => { busy.value = false; },
  });
}
</script>
