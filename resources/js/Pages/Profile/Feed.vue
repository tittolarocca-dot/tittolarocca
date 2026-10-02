<template>
  <AppLayout>
    <Head :title="profile.display_name + ' – Feed'" />

    <div class="max-w-2xl mx-auto px-4 py-6">
      <div class="mb-5">
        <Link :href="route('profile.show', profile.slug)" class="text-xs text-gray-400 hover:text-[#e35d8f] transition">← {{ profile.display_name }}</Link>
        <h1 class="text-2xl font-black text-white mt-2">{{ t('profile.feed_title', { name: profile.display_name }) }}</h1>
      </div>

      <div v-if="posts.length" class="space-y-4">
        <FeedPost v-for="p in posts" :key="p.id" :post="p" :author-name="profile.display_name"
          :is-authed="isAuthed" @login="goLogin" />
        <Link v-if="nextPageUrl" :href="nextPageUrl" preserve-scroll
          class="block text-center text-sm text-[#e35d8f] hover:underline py-3">{{ t('profile.feed_older') }} →</Link>
      </div>

      <div v-else class="bg-[#1a1a1a] border border-white/8 rounded-2xl p-8 text-center">
        <p class="text-gray-300 text-sm">{{ t('profile.feed_empty', { name: profile.display_name }) }}</p>
      </div>
    </div>
  </AppLayout>
</template>

<script setup>
import { computed } from 'vue';
import { Head, Link, router, usePage } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import FeedPost from '@/Components/FeedPost.vue';
import { useI18n } from '@/composables/useI18n';

const { t } = useI18n();
const page = usePage();

defineProps({
  profile:     { type: Object, required: true },
  posts:       { type: Array,  default: () => [] },
  nextPageUrl: { type: String, default: null },
  isFollowing: { type: Boolean, default: false },
});

const isAuthed = computed(() => !!page.props.auth?.user);

function goLogin() {
  router.visit(route('login'));
}
</script>
