<template>
  <AppLayout>
    <Head title="Nachrichten" />
    <div class="max-w-4xl mx-auto px-4 py-8 flex gap-4 h-[calc(100vh-120px)]">

      <!-- Conversation List -->
      <div class="w-72 shrink-0 bg-[#1a1a1a] rounded-xl border border-[#2a2a2a] flex flex-col overflow-hidden">
        <div class="px-4 py-3 border-b border-[#2a2a2a] flex items-center justify-between">
          <h2 class="font-semibold text-white">Nachrichten</h2>
          <span v-if="unreadCount" class="bg-[#e91e8c] text-white text-xs font-bold w-5 h-5 flex items-center justify-center rounded-full">
            {{ unreadCount }}
          </span>
        </div>
        <div class="flex-1 overflow-y-auto">
          <div v-if="conversations.length === 0" class="text-center py-10 text-gray-500 text-sm px-4">
            Noch keine Nachrichten.
          </div>
          <button
            v-for="conv in conversations" :key="conv.user_id"
            @click="openConversation(conv)"
            class="w-full text-left px-4 py-3 border-b border-[#222] hover:bg-[#222] transition"
            :class="activeConv?.user_id === conv.user_id ? 'bg-[#222]' : ''"
          >
            <div class="flex items-center justify-between mb-0.5">
              <span class="font-semibold text-sm text-white">{{ conv.name }}</span>
              <span class="text-xs text-gray-500">{{ conv.last_at }}</span>
            </div>
            <p class="text-xs text-gray-500 truncate">{{ conv.last_message }}</p>
            <span v-if="conv.unread" class="inline-block mt-1 bg-[#e91e8c]/20 text-[#e91e8c] text-xs px-1.5 rounded">
              {{ conv.unread }} neu
            </span>
          </button>
        </div>
      </div>

      <!-- Chat Panel -->
      <div class="flex-1 bg-[#1a1a1a] rounded-xl border border-[#2a2a2a] flex flex-col overflow-hidden">
        <div v-if="!activeConv" class="flex-1 flex items-center justify-center text-gray-500">
          <div class="text-center">
            <div class="text-5xl mb-3">💬</div>
            <p>Wähle eine Konversation aus.</p>
          </div>
        </div>
        <template v-else>
          <div class="px-4 py-3 border-b border-[#2a2a2a] font-semibold text-white">
            {{ activeConv.name }}
          </div>
          <div ref="chatBox" class="flex-1 overflow-y-auto p-4 space-y-3">
            <div v-if="chatLoading" class="text-center text-gray-500 py-8">Lädt…</div>
            <template v-else>
              <div v-for="msg in chatMessages" :key="msg.id" class="flex"
                :class="msg.from_me ? 'justify-end' : 'justify-start'">
                <div class="max-w-xs px-3 py-2 rounded-xl text-sm"
                  :class="msg.from_me ? 'bg-[#e91e8c] text-white' : 'bg-[#2a2a2a] text-gray-200'">
                  {{ msg.body }}
                  <div class="text-xs mt-1 opacity-60">{{ msg.created_at }}</div>
                </div>
              </div>
            </template>
          </div>
          <div class="px-4 py-3 border-t border-[#2a2a2a]">
            <form @submit.prevent="sendReply" class="flex gap-2">
              <input v-model="replyText" type="text" placeholder="Nachricht schreiben…"
                class="flex-1 border border-[#2a2a2a] bg-[#111111] text-white rounded-lg px-3 py-2 text-sm placeholder-gray-600 focus:outline-none focus:border-[#e91e8c]"
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
