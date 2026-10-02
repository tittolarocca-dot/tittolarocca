<template>
  <AppLayout>
    <Head :title="t('dashboard.posts_title')" />
    <div class="max-w-2xl mx-auto px-4 py-8">

      <div class="mb-6">
        <Link :href="route('inserat.dashboard')" class="text-xs text-gray-400 hover:text-[#e35d8f] transition">← Dashboard</Link>
        <h1 class="text-2xl font-bold text-white mt-2">{{ t('dashboard.posts_title') }}</h1>
        <p class="text-gray-400 text-sm mt-1">{{ t('dashboard.posts_intro') }}</p>
      </div>

      <!-- Composer -->
      <div class="bg-[#1a1a1a] border border-white/8 rounded-2xl p-4 mb-6">
        <textarea v-model="text" rows="3" :placeholder="t('dashboard.posts_text_ph')" maxlength="2000"
          class="w-full bg-[#111] border border-white/10 text-gray-100 rounded-xl px-3 py-2.5 text-sm placeholder-gray-500 focus:outline-none focus:border-[#e35d8f] resize-none"></textarea>

        <!-- Medien-Vorschau -->
        <div v-if="previews.length" class="grid grid-cols-3 gap-2 mt-3">
          <div v-for="(p, i) in previews" :key="i" class="relative aspect-square rounded-lg overflow-hidden bg-black/30">
            <img v-if="p.type === 'image'" :src="p.url" class="w-full h-full object-cover" />
            <video v-else :src="p.url" class="w-full h-full object-cover"></video>
            <button @click="removeFile(i)" class="absolute top-1 right-1 bg-black/60 text-white rounded-full w-6 h-6 flex items-center justify-center text-xs">✕</button>
          </div>
        </div>

        <div class="flex flex-wrap items-center gap-2 mt-3">
          <label class="inline-flex items-center gap-1.5 text-sm text-gray-300 border border-white/10 rounded-lg px-3 py-1.5 cursor-pointer hover:border-[#e35d8f] transition">
            <span>📎</span> {{ t('dashboard.posts_add_media') }}
            <input type="file" accept="image/jpeg,image/png,image/webp,video/mp4,video/quicktime" multiple class="hidden" @change="onFiles" />
          </label>

          <select v-model="visibility" class="bg-[#111] border border-white/10 text-gray-200 text-sm rounded-lg px-2.5 py-1.5 focus:outline-none focus:border-[#e35d8f]">
            <option value="public">{{ t('profile.feed_vis_public') }}</option>
            <option value="followers">{{ t('profile.feed_vis_followers') }}</option>
            <option value="private">{{ t('profile.feed_vis_private') }}</option>
          </select>

          <button @click="publish" :disabled="!canPublish || sending"
            class="ml-auto bg-[#e35d8f] text-white text-sm font-bold px-5 py-2 rounded-lg hover:bg-[#c44a7a] disabled:opacity-40 transition">
            {{ sending ? '…' : t('dashboard.posts_publish') }}
          </button>
        </div>
      </div>

      <!-- Bestehende Beiträge -->
      <div v-if="posts.length" class="space-y-4">
        <div v-for="p in posts" :key="p.id" class="bg-[#1a1a1a] border border-white/8 rounded-2xl p-4">
          <div class="flex items-center justify-between mb-2">
            <span class="text-[11px] text-gray-500">{{ p.time }} · {{ visLabel(p.visibility) }} · ♡ {{ p.likes }}</span>
            <div class="flex items-center gap-2">
              <button @click="startEdit(p)" class="text-xs text-gray-400 hover:text-[#e35d8f]">{{ t('dashboard.posts_edit') }}</button>
              <button @click="remove(p)" class="text-xs text-gray-400 hover:text-red-400">{{ t('dashboard.posts_delete') }}</button>
            </div>
          </div>

          <template v-if="editing === p.id">
            <textarea v-model="editText" rows="3" maxlength="2000"
              class="w-full bg-[#111] border border-white/10 text-gray-100 rounded-xl px-3 py-2.5 text-sm focus:outline-none focus:border-[#e35d8f] resize-none"></textarea>
            <div class="flex items-center gap-2 mt-2">
              <select v-model="editVisibility" class="bg-[#111] border border-white/10 text-gray-200 text-sm rounded-lg px-2.5 py-1.5 focus:outline-none focus:border-[#e35d8f]">
                <option value="public">{{ t('profile.feed_vis_public') }}</option>
                <option value="followers">{{ t('profile.feed_vis_followers') }}</option>
                <option value="private">{{ t('profile.feed_vis_private') }}</option>
              </select>
              <button @click="saveEdit(p)" class="ml-auto bg-[#e35d8f] text-white text-xs font-bold px-4 py-1.5 rounded-lg hover:bg-[#c44a7a] transition">{{ t('dashboard.posts_save') }}</button>
              <button @click="editing = null" class="text-xs text-gray-400">{{ t('dashboard.posts_cancel') }}</button>
            </div>
          </template>

          <template v-else>
            <p v-if="p.text" class="text-sm text-white whitespace-pre-line mb-2">{{ p.text }}</p>
            <div v-if="p.media.length" class="grid gap-1.5" :class="p.media.length === 1 ? 'grid-cols-1' : 'grid-cols-3'">
              <div v-for="m in p.media" :key="m.id" class="rounded-lg overflow-hidden bg-black/30" :class="p.media.length === 1 ? 'max-h-60' : 'aspect-square'">
                <img v-if="m.type === 'image'" :src="m.src" class="w-full h-full object-cover" loading="lazy" />
                <video v-else :src="m.src" class="w-full h-full object-cover" preload="metadata"></video>
              </div>
            </div>
          </template>
        </div>
      </div>

      <div v-else class="bg-[#1a1a1a] border border-white/8 rounded-2xl p-10 text-center text-gray-500">
        <div class="text-3xl mb-2">📝</div>
        <p class="text-sm">{{ t('dashboard.posts_empty') }}</p>
      </div>

    </div>
  </AppLayout>
</template>

<script setup>
import { ref, computed } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import { useI18n } from '@/composables/useI18n';

const { t } = useI18n();

const props = defineProps({
  posts:   { type: Array,  default: () => [] },
  profile: { type: Object, default: () => ({}) },
});

const text       = ref('');
const visibility = ref('public');
const files      = ref([]);
const previews   = ref([]);
const sending    = ref(false);

const editing        = ref(null);
const editText       = ref('');
const editVisibility = ref('public');

const canPublish = computed(() => text.value.trim() !== '' || files.value.length > 0);

function visLabel(v) {
  return { public: t('profile.feed_vis_public'), followers: t('profile.feed_vis_followers'), private: t('profile.feed_vis_private') }[v] ?? v;
}

function onFiles(e) {
  for (const f of Array.from(e.target.files)) {
    if (files.value.length >= 10) break;
    files.value.push(f);
    previews.value.push({ url: URL.createObjectURL(f), type: f.type.startsWith('video/') ? 'video' : 'image' });
  }
  e.target.value = '';
}

function removeFile(i) {
  files.value.splice(i, 1);
  previews.value.splice(i, 1);
}

function publish() {
  if (!canPublish.value || sending.value) return;
  sending.value = true;
  const form = new FormData();
  if (text.value.trim()) form.append('text', text.value);
  form.append('visibility', visibility.value);
  files.value.forEach(f => form.append('media[]', f));
  router.post(route('inserat.posts.store'), form, {
    forceFormData: true,
    preserveScroll: true,
    onSuccess: () => { text.value = ''; files.value = []; previews.value = []; visibility.value = 'public'; },
    onFinish: () => { sending.value = false; },
  });
}

function startEdit(p) {
  editing.value = p.id;
  editText.value = p.text ?? '';
  editVisibility.value = p.visibility;
}

function saveEdit(p) {
  router.put(route('inserat.posts.update', p.id), { text: editText.value, visibility: editVisibility.value }, {
    preserveScroll: true,
    onSuccess: () => { editing.value = null; },
  });
}

function remove(p) {
  if (!confirm(t('dashboard.posts_delete_confirm'))) return;
  router.delete(route('inserat.posts.destroy', p.id), { preserveScroll: true });
}
</script>
