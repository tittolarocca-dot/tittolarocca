<template>
  <AppLayout>
    <Head title="Nachrichten" />
    <div class="max-w-4xl mx-auto px-4 py-8 flex gap-4 h-[calc(100vh-120px)]">

      <!-- Conversation List -->
      <div class="w-72 shrink-0 bg-white rounded-xl border border-gray-200 flex flex-col overflow-hidden shadow-sm">
        <div class="px-4 py-3 border-b border-gray-200 flex items-center justify-between">
          <h2 class="font-semibold text-gray-900">Nachrichten</h2>
          <span v-if="unreadCount" class="bg-[#e35d8f] text-white text-xs font-bold w-5 h-5 flex items-center justify-center rounded-full">
            {{ unreadCount }}
          </span>
        </div>
        <div class="flex-1 overflow-y-auto">
          <div v-if="conversations.length === 0" class="text-center py-10 text-gray-400 text-sm px-4">
            Noch keine Nachrichten.
          </div>
          <button
            v-for="conv in conversations" :key="conv.user_id"
            @click="openConversation(conv)"
            class="w-full text-left px-4 py-3 border-b border-gray-100 hover:bg-gray-50 transition"
            :class="activeConv?.user_id === conv.user_id ? 'bg-gray-50' : ''"
          >
            <div class="flex items-center justify-between mb-0.5">
              <span class="font-semibold text-sm text-gray-900">{{ conv.name }}</span>
              <span class="text-xs text-gray-400">{{ conv.last_at }}</span>
            </div>
            <p class="text-xs text-gray-500 truncate">{{ conv.last_message }}</p>
            <span v-if="conv.unread" class="inline-block mt-1 bg-[#e35d8f]/10 text-[#e35d8f] text-xs px-1.5 rounded">
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
          <div class="px-4 py-3 border-b border-gray-200 flex items-center justify-between gap-2">
            <span class="font-semibold text-gray-900">{{ activeConv.name }}</span>
            <button v-if="!activeConv.blocked" @click="blockUser"
              class="shrink-0 text-xs font-semibold text-gray-400 hover:text-red-500 transition inline-flex items-center gap-1">
              <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"/></svg>
              Blockieren
            </button>
            <button v-else @click="unblockUser"
              class="shrink-0 text-xs font-semibold text-red-500 hover:text-gray-600 transition inline-flex items-center gap-1">
              <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"/></svg>
              Blockiert · aufheben
            </button>
          </div>
          <div v-if="activeConv.blocked" class="px-4 py-2 bg-red-50 border-b border-red-100 text-xs text-red-600">
            Dieser Nutzer ist blockiert und kann dir nicht schreiben.
          </div>

          <!-- Messages -->
          <div ref="chatBox" class="flex-1 overflow-y-auto p-4 space-y-3">
            <div v-if="chatLoading" class="text-center text-gray-400 py-8">Lädt…</div>
            <template v-else>
              <div v-for="msg in chatMessages" :key="msg.id" class="flex"
                :class="msg.from_me ? 'justify-end' : 'justify-start'">

                <!-- PPV message (sent by inserent) -->
                <template v-if="msg.ppv_media_type">
                  <div class="max-w-xs rounded-xl overflow-hidden border border-[#e35d8f]/30 shadow-sm">
                    <div class="bg-[#e35d8f]/10 px-3 py-2 flex items-center gap-2">
                      <span class="text-lg">{{ msg.ppv_media_type === 'video' ? '🎬' : '📷' }}</span>
                      <div>
                        <p class="text-xs font-semibold text-[#e35d8f]">Bezahlter Inhalt</p>
                        <p class="text-xs text-gray-500">CHF {{ Number(msg.ppv_price_chf).toFixed(2) }}</p>
                      </div>
                      <span class="ml-auto text-xs text-green-600 font-medium">
                        {{ msg.ppv_purchase_count ?? 0 }}× gekauft
                      </span>
                    </div>
                    <div class="p-2">
                      <!-- Inline preview for inserent -->
                      <img v-if="msg.ppv_media_type === 'image'" :src="msg.ppv_media_url"
                        class="w-full rounded object-cover max-h-48" />
                      <video v-else :src="msg.ppv_media_url" controls class="w-full rounded max-h-48" />
                    </div>
                    <div v-if="msg.body" class="px-3 pb-2 text-xs text-gray-600">{{ msg.body }}</div>
                    <div class="px-3 pb-2 text-xs text-gray-400">{{ msg.created_at }}</div>
                  </div>
                </template>

                <!-- Regular text message -->
                <template v-else>
                  <div class="max-w-xs px-3 py-2 rounded-xl text-sm"
                    :class="msg.from_me ? 'bg-[#e35d8f] text-white' : 'bg-gray-100 text-gray-800'">
                    {{ msg.body }}
                    <div class="text-xs mt-1 opacity-60">{{ msg.created_at }}</div>
                  </div>
                </template>

              </div>
            </template>
          </div>

          <!-- Input Area -->
          <div class="px-4 py-3 border-t border-gray-200 space-y-2">

            <!-- PPV send form (shown when ppvMode is active) -->
            <div v-if="ppvMode" class="bg-[#e35d8f]/5 border border-[#e35d8f]/20 rounded-xl p-3 space-y-2">
              <div class="flex items-center justify-between">
                <span class="text-xs font-semibold text-[#e35d8f]">🔒 Bezahlter Inhalt senden</span>
                <button @click="cancelPpv" class="text-gray-400 hover:text-gray-600 text-xs">✕ Abbrechen</button>
              </div>

              <div v-if="ppvPreview" class="relative">
                <img v-if="ppvFileType === 'image'" :src="ppvPreview" class="w-full rounded-lg object-cover max-h-40" />
                <video v-else :src="ppvPreview" class="w-full rounded-lg max-h-40" />
                <button @click="clearPpvFile" class="absolute top-1 right-1 bg-black/50 text-white rounded-full w-6 h-6 flex items-center justify-center text-xs hover:bg-black/70">✕</button>
              </div>

              <div v-if="!ppvPreview">
                <label class="block w-full border-2 border-dashed border-[#e35d8f]/30 rounded-lg p-4 text-center cursor-pointer hover:border-[#e35d8f]/60 transition">
                  <span class="text-gray-400 text-sm">Bild oder Video auswählen…</span>
                  <input type="file" accept="image/*,video/mp4,video/quicktime,video/webm"
                    class="hidden" @change="onPpvFileChange" ref="ppvFileInput" />
                </label>
              </div>

              <div class="flex items-center gap-2">
                <div class="relative flex-shrink-0 w-28">
                  <span class="absolute left-2 top-1/2 -translate-y-1/2 text-gray-400 text-xs">CHF</span>
                  <input v-model="ppvPrice" type="number" min="1" max="999" step="1" placeholder="9"
                    class="w-full pl-8 pr-2 py-1.5 border border-gray-200 rounded-lg text-sm focus:outline-none focus:border-[#e35d8f]" />
                </div>
                <input v-model="ppvText" type="text" placeholder="Optionaler Text…"
                  class="flex-1 border border-gray-200 rounded-lg px-3 py-1.5 text-sm focus:outline-none focus:border-[#e35d8f]" />
              </div>

              <button @click="sendPpv"
                :disabled="!ppvFile || !ppvPrice || sending"
                class="w-full bg-[#e35d8f] text-white py-2 rounded-lg text-sm font-bold hover:bg-[#c44a7a] disabled:opacity-40 transition">
                {{ sending ? 'Wird gesendet…' : 'PPV senden' }}
              </button>
            </div>

            <!-- Normal reply form -->
            <form v-else @submit.prevent="sendReply" class="flex gap-2">
              <input v-model="replyText" type="text" placeholder="Nachricht schreiben…"
                class="flex-1 border border-gray-300 bg-white text-gray-900 rounded-lg px-3 py-2 text-sm placeholder-gray-400 focus:outline-none focus:border-[#e35d8f]"
                :disabled="sending" />
              <button type="button" @click="ppvMode = true" title="Bezahlten Inhalt senden"
                class="border border-gray-200 text-gray-500 hover:text-[#e35d8f] hover:border-[#e35d8f]/40 px-3 py-2 rounded-lg text-sm transition">
                🔒
              </button>
              <button type="submit" :disabled="!replyText.trim() || sending"
                class="bg-[#e35d8f] text-white px-4 py-2 rounded-lg text-sm font-bold hover:bg-[#c44a7a] disabled:opacity-40 transition">
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

// PPV state
const ppvMode      = ref(false);
const ppvFile      = ref(null);
const ppvPreview   = ref(null);
const ppvFileType  = ref('image');
const ppvPrice     = ref('');
const ppvText      = ref('');
const ppvFileInput = ref(null);

async function openConversation(conv) {
  activeConv.value   = conv;
  chatLoading.value  = true;
  chatMessages.value = [];
  cancelPpv();
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

function blockUser() {
  if (!activeConv.value) return;
  router.post(route('inserat.messages.block', activeConv.value.user_id), {}, {
    preserveScroll: true,
    onSuccess: () => { activeConv.value.blocked = true; },
  });
}

function unblockUser() {
  if (!activeConv.value) return;
  router.post(route('inserat.messages.unblock', activeConv.value.user_id), {}, {
    preserveScroll: true,
    onSuccess: () => { activeConv.value.blocked = false; },
  });
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

function onPpvFileChange(e) {
  const file = e.target.files[0];
  if (!file) return;
  ppvFile.value = file;
  ppvFileType.value = file.type.startsWith('video/') ? 'video' : 'image';
  ppvPreview.value = URL.createObjectURL(file);
}

function clearPpvFile() {
  ppvFile.value    = null;
  ppvPreview.value = null;
  if (ppvFileInput.value) ppvFileInput.value.value = '';
}

function cancelPpv() {
  ppvMode.value  = false;
  ppvPrice.value = '';
  ppvText.value  = '';
  clearPpvFile();
}

function sendPpv() {
  if (!ppvFile.value || !ppvPrice.value || sending.value) return;
  sending.value = true;

  const formData = new FormData();
  formData.append('media', ppvFile.value);
  formData.append('price', ppvPrice.value);
  if (ppvText.value) formData.append('body', ppvText.value);
  formData.append('_method', 'POST');

  router.post(route('inserat.messages.ppv', activeConv.value.user_id), formData, {
    forceFormData: true,
    preserveScroll: true,
    onSuccess: () => {
      cancelPpv();
      openConversation(activeConv.value);
    },
    onFinish: () => { sending.value = false; },
  });
}
</script>
