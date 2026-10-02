<template>
  <div class="bg-[#1a1a1a] border border-white/8 rounded-2xl overflow-hidden">
    <!-- Kopf: Avatar + Name + Zeit -->
    <div class="flex items-center gap-2.5 px-4 pt-4">
      <span class="shrink-0 w-9 h-9 rounded-full overflow-hidden bg-gradient-to-br from-[#e35d8f] to-[#7c3aed] flex items-center justify-center text-white text-xs font-bold">
        <img v-if="avatarUrl" :src="avatarUrl" :alt="authorName" class="w-full h-full object-cover" loading="lazy" />
        <template v-else>{{ initials }}</template>
      </span>
      <div class="min-w-0">
        <p class="text-sm font-semibold text-white truncate">{{ authorName }}</p>
        <p class="text-[11px] text-gray-500">{{ post.time }}</p>
      </div>
      <span v-if="post.visibility && post.visibility !== 'public'"
        class="ml-auto text-[10px] text-gray-400 bg-white/5 border border-white/10 px-2 py-0.5 rounded-full">
        {{ post.visibility === 'followers' ? t('profile.feed_vis_followers') : t('profile.feed_vis_private') }}
      </span>
    </div>

    <!-- Text -->
    <p v-if="post.text" class="px-4 pt-3 text-[15px] text-white leading-relaxed whitespace-pre-line">{{ post.text }}</p>

    <!-- Medien -->
    <div v-if="post.media && post.media.length" class="px-4 pt-3">
      <div class="grid gap-1.5 rounded-xl overflow-hidden"
        :class="post.media.length === 1 ? 'grid-cols-1' : 'grid-cols-2'">
        <div v-for="m in post.media" :key="m.id" class="relative bg-black/30 rounded-lg overflow-hidden"
          :class="post.media.length === 1 ? '' : 'aspect-square'">
          <img v-if="m.type === 'image'" :src="m.src" :alt="authorName"
            class="w-full h-auto" :class="post.media.length === 1 ? 'max-h-[70vh] object-contain mx-auto' : 'absolute inset-0 h-full object-cover'"
            loading="lazy" />
          <video v-else :src="m.src" controls preload="metadata" playsinline
            class="w-full" :class="post.media.length === 1 ? 'max-h-[70vh]' : 'absolute inset-0 h-full object-cover'"></video>
        </div>
      </div>
    </div>

    <!-- Like -->
    <div class="px-4 py-3 flex items-center gap-2">
      <button type="button" @click="toggleLike" :disabled="busy"
        class="inline-flex items-center gap-1.5 text-sm font-semibold transition disabled:opacity-60"
        :class="liked ? 'text-[#e35d8f]' : 'text-gray-400 hover:text-[#e35d8f]'">
        <svg class="w-5 h-5" :fill="liked ? 'currentColor' : 'none'" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
          <path stroke-linecap="round" stroke-linejoin="round" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/>
        </svg>
        {{ likes }}
      </button>
    </div>
  </div>
</template>

<script setup>
import { ref, computed } from 'vue';
import { router } from '@inertiajs/vue3';
import { useI18n } from '@/composables/useI18n';

const { t } = useI18n();

const props = defineProps({
  post:       { type: Object, required: true },
  authorName: { type: String, default: '' },
  avatarUrl:  { type: String, default: null },
  isAuthed:   { type: Boolean, default: false },
});

const emit = defineEmits(['login']);

const liked = ref(!!props.post.liked);
const likes = ref(props.post.likes ?? 0);
const busy  = ref(false);

const initials = computed(() => (props.authorName || '?').trim().charAt(0).toUpperCase());

function toggleLike() {
  if (!props.isAuthed) { emit('login'); return; }
  if (busy.value) return;
  busy.value = true;
  // optimistisch
  liked.value = !liked.value;
  likes.value = Math.max(0, likes.value + (liked.value ? 1 : -1));
  router.post(route('konto.posts.like', props.post.id), {}, {
    preserveScroll: true,
    preserveState: true,
    onFinish: () => { busy.value = false; },
  });
}
</script>
