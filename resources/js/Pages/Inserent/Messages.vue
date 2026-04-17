<template>
  <AppLayout>
    <Head title="Nachrichten" />
    <div class="max-w-4xl mx-auto px-4 py-8 flex gap-6 h-[calc(100vh-120px)]">

      <!-- Conversation List -->
      <div class="w-72 shrink-0 bg-white rounded-xl border border-gray-200 flex flex-col overflow-hidden">
        <div class="px-4 py-3 border-b border-gray-100 flex items-center justify-between">
          <h2 class="font-semibold text-gray-800">Nachrichten</h2>
          <span v-if="unreadCount" class="bg-pink-600 text-white text-xs font-bold w-5 h-5 flex items-center justify-center rounded-full">
            {{ unreadCount }}
          </span>
        </div>
        <div class="flex-1 overflow-y-auto">
          <div v-if="conversations.length === 0" class="text-center py-10 text-gray-400 text-sm px-4">
            Noch keine Nachrichten.
          </div>
          <button
            v-for="conv in conversations"
            :key="conv.user_id"
            @click="openConversation(conv)"
            class="w-full text-left px-4 py-3 border-b border-gray-50 hover:bg-pink-50 transition"
            :class="activeConv?.user_id === conv.user_id ? 'bg-pink-50' : ''"
          >
            <div class="flex items-center justify-between mb-0.5">
              <span class="font-semibold text-sm text-gray-800">{{ conv.name }}</span>
              <span class="text-xs text-gray-400">{{ conv.last_at }}</span>
            </div>
            <p class="text-xs text-gray-500 truncate">{{ conv.last_message }}</p>
            <span v-if="conv.unread" class="inline-block mt-1 bg-pink-100 text-pink-700 text-xs px-1.5 rounded">
              {{ conv.unread }} neu
            </span>
          </button>
        </div>
      </div>

      <!-- Chat Panel -->
      <div class="flex-1 bg-white rounded-xl border border-gray-200 flex flex-col overflow-hidden">
        <div v-if="!activeConv" class="flex-1 flex items-center justify-center text-gray-400">
          <div class="text-center">
            <div class="text-5xl mb-3">💬</div>
            <p>Wähle eine Konversation aus.</p>
          </div>
        </div>
        <template v-else>
          <div class="px-4 py-3 border-b border-gray-100 font-semibold text-gray-800">
            {{ activeConv.name }}
          </div>
          <div ref="chatBox" class="flex-1 overflow-y-auto p-4 space-y-3">
            <div v-if="chatLoading" class="text-center text-gray-400 py-8">Lädt…</div>
            <template v-else>
              <div v-for="msg in chatMessages" :key="msg.id"
                class="flex"
                :class="msg.from_me ? 'justify-end' : 'justify-start'">
                <div class="max-w-xs px-3 py-2 rounded-xl text-sm"
                  :class="msg.from_me ? 'bg-pink-600 text-white' : 'bg-gray-100 text-gray-800'">
                  {{ msg.body }}
                  <div class="text-xs mt-1 opacity-60">{{ msg.created_at }}</div>
                </div>
              </div>
            </template>
          </div>
          <div class="px-4 py-3 border-t border-gray-100">
            <form @submit.prevent="sendReply" class="flex gap-2">
              <input
                v-model="replyText"
                type="text"
                placeholder="Nachricht schreiben…"
                class="flex-1 border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:border-pink-400"
                :disabled="sending"
              />
              <button type="submit"
                :disabled="!replyText.trim() || sending"
                class="bg-pink-600 text-white px-4 py-2 rounded-lg text-sm font-semibold hover:bg-pink-700 disabled:opacity-50 transition">
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
  unreadCount:   { type: Number, default: 0 },
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
    const res = await fetch(route('inserat.messages.conversation', conv.user_id), {
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

function sendReply() {
  if (!replyText.value.trim() || sending.value) return;
  sending.value = true;
  router.post(route('inserat.messages.reply', activeConv.value.user_id), {
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
