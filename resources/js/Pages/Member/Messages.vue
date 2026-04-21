<template>
  <AppLayout>
    <Head title="Nachrichten" />
    <div class="max-w-4xl mx-auto px-4 py-8 flex gap-4 h-[calc(100vh-120px)]">

      <!-- Conversation List -->
      <div class="w-72 shrink-0 bg-white rounded-xl border border-gray-200 flex flex-col overflow-hidden shadow-sm">
        <div class="px-4 py-3 border-b border-gray-200">
          <h2 class="font-semibold text-gray-900">Nachrichten</h2>
        </div>
        <div class="flex-1 overflow-y-auto">
          <div v-if="conversations.length === 0" class="text-center py-10 text-gray-400 text-sm px-4">
            Noch keine Nachrichten.<br>
            Abonniere ein Profil und sende eine Nachricht.
          </div>
          <button v-for="conv in conversations" :key="conv.user_id"
            @click="openConversation(conv)"
            class="w-full text-left px-4 py-3 border-b border-gray-100 hover:bg-gray-50 transition"
            :class="activeConv?.user_id === conv.user_id ? 'bg-gray-50' : ''"
          >
            <div class="flex items-center justify-between mb-0.5">
              <span class="font-semibold text-sm text-gray-900">
                {{ conv.profile?.display_name ?? conv.name }}
              </span>
              <span class="text-xs text-gray-400">{{ conv.last_at }}</span>
            </div>
            <p class="text-xs text-gray-500 truncate">{{ conv.last_message }}</p>
            <span v-if="conv.unread" class="inline-block mt-1 bg-[#e91e8c]/10 text-[#e91e8c] text-xs px-1.5 rounded">
              {{ conv.unread }} neu
            </span>
          </button>
        </div>
      </div>

      <!-- Chat Panel -->
      <div class="flex-1 bg-white rounded-xl border border-gray-200 flex flex-col overflow-hidden shadow-sm">
        <div v-if="!activeConv" class="flex-1 flex items-center justify-center text-gray-400">
          <div class="text-center">
            <div class="text-5xl mb-3">💬</div>
            <p>Wähle eine Konversation aus.</p>
          </div>
        </div>
        <template v-else>
          <div class="px-4 py-3 border-b border-gray-200 font-semibold text-gray-900">
            {{ activeConv.profile?.display_name ?? activeConv.name }}
          </div>
          <div ref="chatBox" class="flex-1 overflow-y-auto p-4 space-y-3">
            <div v-if="chatLoading" class="text-center text-gray-400 py-8">Lädt…</div>
            <template v-else>
              <div v-for="msg in chatMessages" :key="msg.id" class="flex"
                :class="msg.from_me ? 'justify-end' : 'justify-start'">
                <div class="max-w-xs px-3 py-2 rounded-xl text-sm"
                  :class="msg.from_me ? 'bg-[#e91e8c] text-white' : 'bg-gray-100 text-gray-800'">
                  {{ msg.body }}
                  <div class="text-xs mt-1 opacity-60">{{ msg.created_at }}</div>
                </div>
              </div>
            </template>
          </div>
          <div class="px-4 py-3 border-t border-gray-200">
            <form @submit.prevent="sendMessage" class="flex gap-2">
              <input v-model="replyText" type="text" placeholder="Nachricht schreiben…"
                class="flex-1 border border-gray-300 bg-white text-gray-900 rounded-lg px-3 py-2 text-sm placeholder-gray-400 focus:outline-none focus:border-[#e91e8c]"
                :disabled="sending" />
              <button type="submit" :disabled="!replyText.trim() || sending"
                class="bg-[#e91e8c] text-white px-4 py-2 rounded-lg text-sm font-bold hover:bg-[#c91478] disabled:opacity-40 transition">
                Senden
              </button>
            </form>
          </div>
        </template>
      </div>
    </div>
  </AppLayout>
</template>

<script setup>
import { ref, nextTick } from 'vue';
import { Head, router } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';

const props = defineProps({
  conversations: { type: Array, default: () => [] },
});

const activeConv   = ref(null);
const chatMessages = ref([]);
const chatLoading  = ref(false);
const replyText    = ref('');
const sending      = ref(false);
const chatBox      = ref(null);

async function openConversation(conv) {
  activeConv.value   = conv;
  chatLoading.value  = true;
  chatMessages.value = [];
  try {
    const res = await fetch(route('konto.messages.conversation', conv.user_id), {
      headers: { 'X-Requested-With': 'XMLHttpRequest' },
    });
    const data = await res.json();
    chatMessages.value = data.messages;
    await nextTick();
    if (chatBox.value) chatBox.value.scrollTop = chatBox.value.scrollHeight;
  } finally {
    chatLoading.value = false;
  }
}

function sendMessage() {
  if (!replyText.value.trim() || !activeConv.value.profile) return;
  sending.value = true;
  router.post(route('konto.messages.send', activeConv.value.profile.slug), {
    body: replyText.value,
  }, {
    preserveScroll: true,
    onSuccess: () => {
      replyText.value = '';
      openConversation(activeConv.value);
    },
    onFinish: () => { sending.value = false; },
  });
}
</script>
