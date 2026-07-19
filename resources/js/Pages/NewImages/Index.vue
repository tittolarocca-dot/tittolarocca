<template>
  <AppLayout>
    <Head :title="t('home.new_images')" />

    <div class="max-w-7xl mx-auto px-3 sm:px-4 py-6">

      <!-- Header -->
      <div class="flex items-center gap-3 mb-6">
        <div class="w-12 h-12 rounded-full bg-gradient-to-br from-[#e35d8f] via-[#c44a7a] to-[#7c3aed] flex items-center justify-center shadow-lg shadow-[#e35d8f]/30">
          <svg class="w-6 h-6 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
        </div>
        <div>
          <h1 class="text-2xl font-black text-white">{{ t('home.new_images') }}</h1>
          <p class="text-xs text-gray-500">{{ t('home.new_images_desc') }}</p>
        </div>
      </div>

      <!-- Grid -->
      <div v-if="images.data.length" class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 gap-2">
        <a
          v-for="image in images.data"
          :key="image.id"
          :href="route('profile.show', image.profile.slug)"
          class="relative block overflow-hidden rounded-xl group aspect-[3/4]"
        >
          <!-- Bild: privat = serverseitig weichgezeichnete Vorschau, öffentlich = card-Variante -->
          <img
            :src="image.private ? image.preview_url : (image.src?.card ?? image.src?.full ?? image.src?.thumbnail)"
            :srcset="!image.private && image.src ? `${image.src.thumbnail} 320w, ${image.src.card} 720w` : undefined"
            sizes="(max-width: 640px) 50vw, (max-width: 1024px) 33vw, 20vw"
            :alt="image.private ? 'Locked' : image.profile.display_name"
            :class="['w-full h-full object-cover object-top transition duration-300', image.private ? 'blur-md scale-110' : '']"
            loading="lazy"
          />

          <!-- Locked-Content-Overlay -->
          <div v-if="image.private" class="absolute inset-0 flex flex-col items-center justify-center bg-black/40">
            <div class="w-11 h-11 rounded-full bg-black/50 backdrop-blur-sm flex items-center justify-center mb-2">
              <svg class="w-5 h-5 text-white" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M5 9V7a5 5 0 0110 0v2a2 2 0 012 2v5a2 2 0 01-2 2H5a2 2 0 01-2-2v-5a2 2 0 012-2zm8-2v2H7V7a3 3 0 016 0z" clip-rule="evenodd"/></svg>
            </div>
            <span class="text-[11px] text-white font-bold drop-shadow text-center px-2">{{ t('home.members_only') }}</span>
          </div>

          <!-- Hover overlay (nur öffentliche Bilder) -->
          <div v-else class="absolute inset-0 bg-gradient-to-t from-black/80 via-transparent to-transparent opacity-0 group-hover:opacity-100 transition duration-300 flex items-end p-3">
            <span class="text-white text-xs font-bold truncate">{{ image.profile.display_name }}</span>
          </div>
        </a>
      </div>

      <div v-else class="text-center py-20 text-gray-600">
        <div class="text-5xl mb-4">📷</div>
        <p class="text-lg">{{ t('home.no_images') }}</p>
      </div>

      <!-- Pagination -->
      <div v-if="images.last_page > 1" class="flex justify-center gap-1 mt-8 flex-wrap">
        <Link
          v-for="link in images.links"
          :key="link.label"
          :href="link.url || '#'"
          v-html="link.label"
          class="px-3 py-1.5 text-sm rounded border transition-colors"
          :class="link.active
            ? 'bg-[#e35d8f] text-white border-[#e35d8f]'
            : 'bg-[#1a1a1a] text-gray-400 border-white/10 hover:border-[#e35d8f]/50 hover:text-[#e35d8f]'" />
      </div>

    </div>
  </AppLayout>
</template>

<script setup>
import { Head, Link } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import { useI18n } from '@/composables/useI18n';

const { t } = useI18n();

defineProps({
  images: Object,
});
</script>
