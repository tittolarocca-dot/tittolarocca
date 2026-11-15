<template>
  <Link :href="route('profile.show', profile.slug)"
    class="group block rounded-[18px] overflow-hidden transition-all duration-300"
    :style="hover
      ? 'background:var(--bl-surface);border:1px solid rgba(227,93,143,.4);box-shadow:var(--bl-glow);'
      : 'background:var(--bl-surface);border:1px solid var(--bl-border);'"
    @mouseenter="hover = true" @mouseleave="hover = false">
    <div class="grid" style="grid-template-columns:44% 1fr;">

      <!-- Foto -->
      <div class="relative overflow-hidden" style="min-height:210px;background:#1a1420;">
        <CardGallery :media="profile.public_media" :alt="profile.display_name" />

        <!-- Favorit (Herz) -->
        <button type="button" @click.stop.prevent="toggleFav" :aria-label="fav ? 'Gemerkt' : 'Merken'"
          class="absolute right-2.5 top-2.5 z-20 w-9 h-9 rounded-full flex items-center justify-center transition"
          style="background:rgba(0,0,0,.35);backdrop-filter:blur(4px);">
          <svg class="w-5 h-5 transition" :fill="fav ? 'var(--bl-pink)' : 'none'" :stroke="fav ? 'var(--bl-pink)' : '#fff'" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4.318 6.318a4.5 4.5 0 016.364 0L12 7.636l1.318-1.318a4.5 4.5 0 116.364 6.364L12 20.364l-7.682-7.682a4.5 4.5 0 010-6.364z"/></svg>
        </button>

        <!-- Verifiziert-Badge -->
        <span v-if="photoVerified || identityVerified"
          class="absolute left-2.5 bottom-2.5 z-10 inline-flex items-center gap-1 text-[11px] font-bold px-2.5 py-1 rounded-full"
          style="background:var(--bl-verify);color:#fff;">
          <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.7 5.3a1 1 0 010 1.4l-8 8a1 1 0 01-1.4 0l-4-4a1 1 0 011.4-1.4L8 12.6l7.3-7.3a1 1 0 011.4 0z" clip-rule="evenodd"/></svg>
          {{ identityVerified ? 'Verifiziert' : 'Foto verifiziert' }}
        </span>
      </div>

      <!-- Inhalt -->
      <div class="p-3.5 sm:p-4 min-w-0 flex flex-col">
        <div class="flex items-center gap-1.5 min-w-0">
          <h3 class="font-bold text-[17px] truncate transition" :style="hover ? 'color:var(--bl-pink)' : 'color:var(--bl-text)'">{{ profile.display_name }}</h3>
          <svg v-if="identityVerified || photoVerified" class="w-4 h-4 shrink-0" fill="currentColor" viewBox="0 0 20 20" style="color:var(--bl-verify);"><path fill-rule="evenodd" d="M16.7 5.3a1 1 0 010 1.4l-8 8a1 1 0 01-1.4 0l-4-4a1 1 0 011.4-1.4L8 12.6l7.3-7.3a1 1 0 011.4 0z" clip-rule="evenodd"/></svg>
        </div>

        <div class="flex items-center gap-1.5 text-[13px] mt-1.5" style="color:var(--bl-text-muted);">
          <span v-if="profile.age">{{ profile.age }} J.</span>
          <template v-if="profile.city">
            <span v-if="profile.age" style="opacity:.5">·</span>
            <svg class="w-3.5 h-3.5 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 21s-7-5.2-7-11a7 7 0 1114 0c0 5.8-7 11-7 11z"/><circle cx="12" cy="10" r="2.5"/></svg>
            <span class="truncate">{{ profile.city?.name }}</span>
          </template>
        </div>

        <div v-if="profile.category" class="text-[13px] font-bold mt-1.5" style="color:var(--bl-pink);">{{ profile.category?.name }}</div>

        <p v-if="profile.description" class="text-[13px] mt-2 leading-snug line-clamp-2" style="color:var(--bl-text-muted);">{{ profile.description }}</p>

        <!-- Status / NEU (unten) -->
        <div class="mt-auto pt-2.5 flex items-center gap-2">
          <span v-if="profile.activity && profile.activity.online" class="inline-flex items-center gap-1.5 text-[13px] font-semibold" style="color:var(--bl-success);">
            <span class="w-2 h-2 rounded-full" style="background:var(--bl-success);"></span>Online
          </span>
          <span v-else-if="profile.activity" class="inline-flex items-center gap-1.5 text-[13px]" style="color:var(--bl-text-muted);">
            <span class="w-2 h-2 rounded-full" style="background:#6b6670;"></span>{{ profile.activity.last_seen }}
          </span>
          <span v-if="isNew" class="text-[10px] font-extrabold px-2 py-0.5 rounded-full" style="background:rgba(227,93,143,.16);color:var(--bl-pink);letter-spacing:.04em;">NEU</span>
        </div>
      </div>
    </div>
  </Link>
</template>

<script setup>
import { ref, computed } from 'vue';
import { Link, router, usePage } from '@inertiajs/vue3';
import CardGallery from '@/Components/CardGallery.vue';

const props = defineProps({ profile: { type: Object, required: true } });
const page = usePage();
const hover = ref(false);
const fav = ref(!!props.profile.is_favorited);

const identityVerified = computed(() => props.profile.identity_verification_status === 'approved');
const photoVerified    = computed(() => props.profile.verification_status === 'approved');
const isNew = computed(() => {
  const iso = props.profile.created_at;
  return iso ? (Date.now() - new Date(iso).getTime()) < 14 * 24 * 60 * 60 * 1000 : false;
});

function toggleFav() {
  if (!page.props.auth?.user) { router.visit(route('login')); return; }
  fav.value = !fav.value; // optimistisch
  router.post(route('konto.favorites.toggle', props.profile.slug), {}, {
    preserveScroll: true, preserveState: true,
    onError: () => { fav.value = !fav.value; },
  });
}
</script>
