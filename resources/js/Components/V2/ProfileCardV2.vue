<template>
  <Link :href="route('profile.show', profile.slug)"
    class="group block rounded-2xl overflow-hidden transition-all duration-300"
    style="background:var(--bl-surface);border:1px solid var(--bl-border);"
    @mouseenter="hover = true" @mouseleave="hover = false"
    :style="hover ? 'background:var(--bl-surface);border:1px solid var(--bl-border);box-shadow:var(--bl-glow);transform:translateY(-2px)' : 'background:var(--bl-surface);border:1px solid var(--bl-border)'">

    <!-- Foto -->
    <div class="relative overflow-hidden" style="aspect-ratio:4/3;">
      <CardGallery :media="profile.public_media" :alt="profile.display_name" />
      <span v-if="photoVerified || identityVerified"
        class="absolute bottom-2 left-2 z-10 inline-flex items-center gap-1 text-[11px] font-bold px-2.5 py-1 rounded-full"
        style="background:var(--bl-verify);color:#fff;">
        <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.7 5.3a1 1 0 010 1.4l-8 8a1 1 0 01-1.4 0l-4-4a1 1 0 011.4-1.4L8 12.6l7.3-7.3a1 1 0 011.4 0z" clip-rule="evenodd"/></svg>
        {{ identityVerified ? 'Verifiziert' : 'Foto verifiziert' }}
      </span>
      <span v-if="isNew"
        class="absolute top-2 left-2 z-10 text-[10px] font-extrabold px-2.5 py-1 rounded-full"
        style="background:var(--bl-pink);color:#fff;letter-spacing:.04em;">NEU</span>
    </div>

    <!-- Info -->
    <div class="p-3.5">
      <div class="flex items-center gap-1.5 min-w-0">
        <h3 class="font-bold text-base truncate transition" :style="hover ? 'color:var(--bl-pink)' : 'color:var(--bl-text)'">{{ profile.display_name }}</h3>
        <svg v-if="identityVerified || photoVerified" class="w-4 h-4 shrink-0" fill="currentColor" viewBox="0 0 20 20" style="color:var(--bl-verify);"><path fill-rule="evenodd" d="M16.7 5.3a1 1 0 010 1.4l-8 8a1 1 0 01-1.4 0l-4-4a1 1 0 011.4-1.4L8 12.6l7.3-7.3a1 1 0 011.4 0z" clip-rule="evenodd"/></svg>
      </div>

      <div class="flex items-center gap-1.5 text-sm mt-1" style="color:var(--bl-text-muted);">
        <span v-if="profile.age">{{ profile.age }} J.</span>
        <template v-if="profile.city">
          <span v-if="profile.age" style="opacity:.5">·</span>
          <svg class="w-3.5 h-3.5 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 21s-7-5.2-7-11a7 7 0 1114 0c0 5.8-7 11-7 11z"/><circle cx="12" cy="10" r="2.5"/></svg>
          <span class="truncate">{{ profile.city?.name }}</span>
        </template>
      </div>

      <div v-if="profile.category" class="text-sm font-semibold mt-1" style="color:var(--bl-pink);">{{ profile.category?.name }}</div>
      <p v-if="profile.description" class="text-sm mt-1.5 line-clamp-1" style="color:var(--bl-text-muted);">{{ profile.description }}</p>
    </div>
  </Link>
</template>

<script setup>
import { ref, computed } from 'vue';
import { Link } from '@inertiajs/vue3';
import CardGallery from '@/Components/CardGallery.vue';

const props = defineProps({ profile: { type: Object, required: true } });
const hover = ref(false);

const identityVerified = computed(() => props.profile.identity_verification_status === 'approved');
const photoVerified    = computed(() => props.profile.verification_status === 'approved');
const isNew = computed(() => {
  const iso = props.profile.created_at;
  return iso ? (Date.now() - new Date(iso).getTime()) < 14 * 24 * 60 * 60 * 1000 : false;
});
</script>
