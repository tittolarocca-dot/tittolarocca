<template>
  <a :href="route('profile.show', profile.slug)"
    class="flex h-[300px] rounded-2xl overflow-hidden bg-[#1a1a1a] border border-white/8 hover:border-[#e35d8f]/50 hover:shadow-xl hover:shadow-[#e35d8f]/10 transition-all duration-300 group">

    <!-- Photo -->
    <div class="relative w-[50%] shrink-0 overflow-hidden">
      <img v-if="profile.public_media?.[0]"
        class="lazyload w-full h-full object-cover object-center group-hover:scale-105 transition duration-500"
        :data-src="profile.public_media[0].url"
        src="data:image/gif;base64,R0lGODlhAQABAAAAACH5BAEKAAEALAAAAAABAAEAAAICTAEAOw=="
        :alt="profile.display_name" />
      <div v-else class="w-full h-full bg-white/5 flex items-center justify-center text-3xl">👤</div>

      <!-- Badges top-left -->
      <div class="absolute top-2 left-2 flex flex-col gap-1">
        <span v-if="profile.listing_orders?.[0]?.amount_chf > 0"
          class="bg-[#e35d8f] text-white text-[10px] font-black px-2 py-0.5 rounded-full tracking-wider">TOP AD</span>
        <span v-if="profile.verification_status === 'approved'"
          class="flex items-center gap-1 bg-green-500 text-white text-[10px] font-bold px-2 py-0.5 rounded-full">
          <svg class="w-2.5 h-2.5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>
          {{ t('home.verified') }}
        </span>
        <span v-if="isNew(profile.created_at)"
          class="bg-blue-500 text-white text-[10px] font-bold px-2 py-0.5 rounded-full">{{ t('home.badge_new') }}</span>
      </div>
    </div>

    <!-- Text rechts -->
    <div class="p-3 flex-1 min-w-0 flex flex-col justify-between">
      <div>
        <!-- Name + Alter -->
        <div class="flex items-center gap-2 mb-1 flex-wrap">
          <h3 class="text-white font-bold text-sm sm:text-base group-hover:text-[#e35d8f] transition truncate">
            {{ profile.display_name }}
          </h3>
          <span v-if="profile.verification_status === 'approved'"
            class="inline-flex items-center justify-center w-4 h-4 rounded-full bg-green-500 shrink-0"
            :title="t('home.verified')">
            <svg class="w-2.5 h-2.5 text-white" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>
          </span>
          <span v-if="profile.age" class="shrink-0 text-gray-400 text-xs">{{ profile.age }} J.</span>
        </div>

        <!-- Stadt + Kategorie -->
        <div class="flex items-center gap-2 text-xs mb-2 flex-wrap">
          <span v-if="profile.city" class="text-gray-400">📍 {{ profile.city?.name }}</span>
          <span v-if="profile.category" class="text-[#e35d8f] font-medium">{{ profile.category?.name }}</span>
        </div>

        <!-- Teaser -->
        <p v-if="profile.description" class="text-gray-300 text-sm line-clamp-2 leading-relaxed">
          {{ profile.description }}
        </p>
      </div>

      <!-- Badges unten -->
      <div class="flex flex-wrap gap-1 mt-2">
        <span v-if="profile.private_media_count > 0"
          class="bg-white/5 text-gray-400 text-[10px] px-2 py-0.5 rounded-full">{{ t('home.private_badge') }}</span>
      </div>
    </div>
  </a>
</template>

<script setup>
import { useI18n } from '@/composables/useI18n';

const { t } = useI18n();

defineProps({
  profile: { type: Object, required: true },
});

function isNew(iso) {
  if (!iso) return false;
  return (Date.now() - new Date(iso).getTime()) < 14 * 24 * 60 * 60 * 1000;
}
</script>
