<template>
  <div class="rounded-2xl overflow-hidden"
    style="background:linear-gradient(100deg,#141016 0%,#1b141d 55%,#1b121c 100%);border:1px solid rgba(227,93,143,.42);box-shadow:var(--bl-shadow);">
    <div class="flex flex-col lg:flex-row">

      <!-- Foto -->
      <Link :href="route('profile.show', profile.slug)" class="relative block lg:w-[44%] shrink-0" style="min-height:300px;">
        <CardGallery :media="profile.public_media" :alt="profile.display_name" eager-first :swipe="false" />
        <span class="absolute top-3.5 left-3.5 z-10 inline-flex items-center gap-1.5 text-[13px] font-extrabold px-3.5 py-2 rounded-full"
          style="background:var(--bl-grad);color:#fff;box-shadow:0 6px 18px -4px rgba(227,93,143,.5);">
          <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 20 20"><path d="M10 1.5l2.6 5.27 5.82.85-4.21 4.1.99 5.78L10 14.77l-5.2 2.73.99-5.78L1.58 7.62l5.82-.85L10 1.5z"/></svg>
          Spotlight
        </span>
      </Link>

      <!-- Info + Aktionen -->
      <div class="flex-1 flex flex-col xl:flex-row min-w-0">

        <!-- Info -->
        <div class="p-5 lg:p-7 flex-1 min-w-0 flex flex-col justify-center">
          <div class="flex items-center gap-2 flex-wrap">
            <h2 class="font-black text-2xl lg:text-[30px] leading-none" style="color:var(--bl-text);">{{ profile.display_name }}</h2>
            <span v-if="identityVerified || photoVerified" class="inline-flex items-center justify-center w-6 h-6 rounded-full shrink-0" style="background:var(--bl-verify);">
              <svg class="w-3.5 h-3.5 text-white" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.7 5.3a1 1 0 010 1.4l-8 8a1 1 0 01-1.4 0l-4-4a1 1 0 011.4-1.4L8 12.6l7.3-7.3a1 1 0 011.4 0z" clip-rule="evenodd"/></svg>
            </span>
          </div>

          <div class="flex flex-wrap items-center gap-x-3 gap-y-1 text-sm mt-3" style="color:var(--bl-text-muted);">
            <span v-if="profile.age">{{ profile.age }} J.</span>
            <span v-if="profile.city" class="inline-flex items-center gap-1">
              <svg class="w-4 h-4" style="color:var(--bl-pink);" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M5.05 4.05a7 7 0 119.9 9.9L10 18.9l-4.95-4.95a7 7 0 010-9.9zM10 11a2 2 0 100-4 2 2 0 000 4z" clip-rule="evenodd"/></svg>
              {{ profile.city?.name }}
            </span>
            <span v-if="profile.category" class="font-bold" style="color:var(--bl-pink);">{{ profile.category?.name }}</span>
          </div>

          <template v-if="profile.description">
            <p class="text-sm mt-3 leading-relaxed" style="color:var(--bl-text-muted);" :class="(!descExpanded && descIsLong) ? 'line-clamp-2' : ''">{{ profile.description }}</p>
            <button v-if="descIsLong" type="button" @click.stop="descExpanded = !descExpanded" class="inline-flex items-center gap-1 text-sm font-bold mt-1 self-start" style="color:var(--bl-pink);">
              {{ descExpanded ? 'Weniger' : 'Mehr anzeigen' }}
              <svg class="w-3.5 h-3.5 transition-transform" :class="descExpanded ? 'rotate-180' : ''" fill="none" stroke="currentColor" stroke-width="2.4" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 9l6 6 6-6"/></svg>
            </button>
          </template>

          <!-- Chips -->
          <div class="flex flex-wrap gap-2 mt-4">
            <span v-if="profile.activity && profile.activity.online" class="inline-flex items-center gap-1.5 text-[13px] font-semibold px-3 py-1.5 rounded-full" style="background:rgba(47,210,122,.14);color:var(--bl-success);">
              <span class="w-2 h-2 rounded-full" style="background:var(--bl-success);"></span>Online
            </span>
            <span v-else-if="profile.activity" class="inline-flex items-center gap-1.5 text-[13px] font-semibold px-3 py-1.5 rounded-full" style="background:rgba(255,255,255,.05);color:var(--bl-text-muted);">
              {{ profile.activity.last_seen }}
            </span>
            <span v-if="photoVerified" class="inline-flex items-center gap-1.5 text-[13px] font-semibold px-3 py-1.5 rounded-full" style="background:rgba(255,255,255,.05);color:var(--bl-text);">
              <svg class="w-3.5 h-3.5" style="color:var(--bl-verify);" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24"><rect x="3" y="7" width="18" height="12" rx="2"/><circle cx="12" cy="13" r="3"/></svg>Foto verifiziert
            </span>
            <span v-if="identityVerified" class="inline-flex items-center gap-1.5 text-[13px] font-semibold px-3 py-1.5 rounded-full" style="background:rgba(255,255,255,.05);color:var(--bl-text);">
              <svg class="w-3.5 h-3.5" style="color:var(--bl-verify);" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 3l7 3v5c0 4.4-3 8.4-7 10-4-1.6-7-5.6-7-10V6l7-3z"/><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4"/></svg>18+ bestätigt
            </span>
          </div>
        </div>

        <!-- Aktionen -->
        <div class="p-5 lg:p-6 xl:w-[216px] shrink-0 flex flex-col justify-center gap-2.5 xl:border-l" style="border-color:var(--bl-border);">
          <Link :href="route('profile.show', profile.slug)"
            class="inline-flex items-center justify-center gap-2 px-4 py-3 rounded-xl text-[15px] font-bold text-white transition" style="background:var(--bl-grad);box-shadow:var(--bl-glow);">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" d="M2.5 12S5.5 5.5 12 5.5 21.5 12 21.5 12 18.5 18.5 12 18.5 2.5 12 2.5 12z"/></svg>
            {{ t('profile.view_profile') }}
          </Link>
          <button type="button" @click="toggleFollow" :disabled="followBusy"
            class="inline-flex items-center justify-center gap-2 px-4 py-3 rounded-xl text-[15px] font-bold transition disabled:opacity-60"
            :style="following ? 'background:rgba(227,93,143,.15);color:var(--bl-pink);border:1px solid rgba(227,93,143,.5);' : 'background:transparent;color:var(--bl-text);border:1px solid rgba(255,255,255,.28);'">
            <svg class="w-4 h-4" :fill="following ? 'currentColor' : 'none'" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/></svg>
            {{ following ? t('profile.following') : t('profile.follow') }}
          </button>
          <Link :href="messageHref"
            class="inline-flex items-center justify-center gap-2 px-4 py-3 rounded-xl text-[15px] font-bold transition" style="background:transparent;color:var(--bl-text);border:1px solid rgba(255,255,255,.28);">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.87 9.87 0 01-4-.8L3 20l.8-3.6A7.98 7.98 0 013 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/></svg>
            {{ t('profile.send_message') }}
          </Link>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed } from 'vue';
import { Link, router, usePage } from '@inertiajs/vue3';
import CardGallery from '@/Components/CardGallery.vue';
import { useI18n } from '@/composables/useI18n';

const props = defineProps({ profile: { type: Object, required: true } });
const { t } = useI18n();
const page = usePage();

const identityVerified = computed(() => props.profile.identity_verification_status === 'approved');
const photoVerified    = computed(() => props.profile.verification_status === 'approved');
const descIsLong = computed(() => (props.profile.description || '').length > 140);
const descExpanded = ref(false);

const following  = ref(!!props.profile.is_following);
const followBusy = ref(false);
function toggleFollow() {
  if (!page.props.auth?.user) { router.visit(route('login')); return; }
  if (followBusy.value) return;
  followBusy.value = true;
  following.value = !following.value; // optimistisch
  router.post(route('konto.follow.toggle', props.profile.slug), {}, {
    preserveScroll: true, preserveState: true,
    onError: () => { following.value = !following.value; },
    onFinish: () => { followBusy.value = false; },
  });
}

const messageHref = computed(() =>
  page.props.auth?.user
    ? route('konto.messages', { to: props.profile.slug })
    : route('login')
);
</script>
