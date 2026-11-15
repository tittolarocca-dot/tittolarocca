<template>
  <Link :href="route('profile.show', profile.slug)"
    class="group block rounded-2xl overflow-hidden transition-all duration-300"
    style="background:var(--bl-surface);border:1px solid var(--bl-border);"
    @mouseenter="hover = true" @mouseleave="hover = false"
    :style="hover
      ? 'background:var(--bl-surface);border:1px solid rgba(227,93,143,.45);box-shadow:var(--bl-glow)'
      : 'background:var(--bl-surface);border:1px solid var(--bl-border)'">
    <div class="flex h-[210px] sm:h-[270px] md:h-[300px]">

      <!-- Foto -->
      <div class="relative w-[150px] sm:w-[230px] md:w-[300px] shrink-0 overflow-hidden">
        <CardGallery :media="profile.public_media" :alt="profile.display_name" eager-first />
        <span class="absolute top-2.5 left-2.5 z-10 inline-flex items-center gap-1 text-[11px] font-extrabold px-2.5 py-1 rounded-full"
          style="background:var(--bl-grad);color:#fff;letter-spacing:.03em;box-shadow:0 4px 14px -4px rgba(227,93,143,.6);">
          <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20"><path d="M10 1.5l2.6 5.27 5.82.85-4.21 4.1.99 5.78L10 14.77l-5.2 2.73.99-5.78L1.58 7.62l5.82-.85L10 1.5z"/></svg>
          Spotlight
        </span>
      </div>

      <!-- Info -->
      <div class="p-4 sm:p-6 flex flex-col justify-between flex-1 min-w-0">
        <div class="min-w-0">
          <div class="flex items-center gap-2 flex-wrap mb-2">
            <h2 class="font-black text-lg sm:text-2xl truncate transition" :style="hover ? 'color:var(--bl-pink)' : 'color:var(--bl-text)'">
              {{ profile.display_name }}
            </h2>
            <span v-if="identityVerified || photoVerified"
              class="inline-flex items-center justify-center w-5 h-5 rounded-full shrink-0"
              style="background:var(--bl-verify);"
              :title="identityVerified ? 'Verifiziert' : 'Foto verifiziert'">
              <svg class="w-3 h-3 text-white" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.7 5.3a1 1 0 010 1.4l-8 8a1 1 0 01-1.4 0l-4-4a1 1 0 011.4-1.4L8 12.6l7.3-7.3a1 1 0 011.4 0z" clip-rule="evenodd"/></svg>
            </span>
          </div>

          <div class="flex flex-wrap items-center gap-x-3 gap-y-1 text-sm mb-3" style="color:var(--bl-text-muted);">
            <span v-if="profile.age">{{ profile.age }} J.</span>
            <span v-if="profile.city" class="inline-flex items-center gap-1">
              <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 21s-7-5.2-7-11a7 7 0 1114 0c0 5.8-7 11-7 11z"/><circle cx="12" cy="10" r="2.5"/></svg>
              {{ profile.city?.name }}
            </span>
            <span v-if="profile.category" class="font-semibold" style="color:var(--bl-pink);">{{ profile.category?.name }}</span>
          </div>

          <p v-if="profile.description" class="text-sm line-clamp-2 leading-relaxed" style="color:var(--bl-text-muted);">
            {{ profile.description }}
          </p>
        </div>

        <div class="flex flex-wrap items-center gap-2 mt-3">
          <span v-if="isNew"
            class="text-[11px] font-bold px-2.5 py-1 rounded-full"
            style="background:rgba(227,93,143,.16);color:var(--bl-pink);">Neu</span>
          <span v-if="profile.private_media_count > 0"
            class="inline-flex items-center gap-1 text-[11px] font-semibold px-2.5 py-1 rounded-full"
            style="background:rgba(255,255,255,.06);color:var(--bl-text-muted);">
            <svg class="w-3 h-3" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><rect x="4" y="10" width="16" height="11" rx="2"/><path d="M8 10V7a4 4 0 118 0v3"/></svg>
            Private Galerie
          </span>
          <span class="ml-auto inline-flex items-center gap-1.5 text-sm font-bold px-3.5 py-1.5 rounded-full transition"
            :style="hover
              ? 'background:var(--bl-grad);color:#fff;box-shadow:var(--bl-glow)'
              : 'background:rgba(227,93,143,.14);color:var(--bl-pink)'">
            Profil ansehen
            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 6l6 6-6 6"/></svg>
          </span>
        </div>
      </div>
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
