<template>
  <AppLayout>
    <Head title="Nachrichten" />

    <!-- PPV success toast -->
    <div v-if="ppvSuccessToast"
      class="fixed top-4 right-4 z-50 bg-green-500 text-white px-4 py-3 rounded-xl shadow-lg text-sm font-medium flex items-center gap-2">
      ✅ Inhalt freigeschaltet! Öffne die Konversation um ihn anzuschauen.
      <button @click="ppvSuccessToast = false" class="ml-2 opacity-70 hover:opacity-100">✕</button>
    </div>

    <div class="max-w-4xl mx-auto px-4 py-8 flex gap-4 h-[calc(100vh-120px)]">

      <!-- Sidebar -->
      <div class="w-72 shrink-0 bg-white rounded-xl border border-gray-200 flex flex-col overflow-hidden shadow-sm">
        <div class="px-4 py-3 border-b border-gray-200">
          <h2 class="font-semibold text-gray-900">Nachrichten</h2>
        </div>
        <div class="flex-1 overflow-y-auto">

          <template v-if="conversations.length">
            <button v-for="conv in conversations" :key="'conv-' + conv.user_id"
              @click="openConversation(conv)"
              class="w-full text-left px-4 py-3 border-b border-gray-100 hover:bg-gray-50 transition"
              :class="activeConv?.user_id === conv.user_id && !activeConv?.isNew ? 'bg-gray-50' : ''"
            >
              <div class="flex items-center justify-between mb-0.5">
                <span class="font-semibold text-sm text-gray-900">{{ conv.profile?.display_name ?? conv.name }}</span>
                <span class="text-xs text-gray-400">{{ conv.last_at }}</span>
              </div>
              <p class="text-xs text-gray-500 truncate">{{ conv.last_message }}</p>
              <span v-if="conv.unread" class="inline-block mt-1 bg-[#e35d8f]/10 text-[#e35d8f] text-xs px-1.5 rounded">
                {{ conv.unread }} neu
              </span>
            </button>
          </template>

          <template v-if="newSubscriptions.length">
            <div v-if="conversations.length" class="px-4 py-2 text-xs font-semibold text-gray-400 uppercase tracking-wide border-b border-gray-100">
              Abonnements
            </div>
            <button v-for="sub in newSubscriptions" :key="'sub-' + sub.creator_user_id"
              @click="openNewConversation(sub)"
              class="w-full text-left px-4 py-3 border-b border-gray-100 hover:bg-gray-50 transition"
              :class="activeConv?.user_id === sub.creator_user_id && activeConv?.isNew ? 'bg-gray-50' : ''"
            >
              <div class="flex items-center gap-2">
                <span class="w-2 h-2 rounded-full bg-[#e35d8f] shrink-0"></span>
                <span class="font-semibold text-sm text-gray-900">{{ sub.display_name }}</span>
              </div>
              <p class="text-xs text-gray-400 mt-0.5 pl-4">Neue Konversation starten</p>
            </button>
          </template>

          <div v-if="!conversations.length && !newSubscriptions.length"
            class="text-center py-10 text-gray-400 text-sm px-4">
            Noch keine Nachrichten.<br>Abonniere ein Profil und sende eine Nachricht.
          </div>
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

          <!-- Messages -->
          <div ref="chatBox" class="flex-1 overflow-y-auto p-4 space-y-3">
            <div v-if="chatLoading" class="text-center text-gray-400 py-8">Lädt…</div>
            <template v-else>
              <div v-if="chatMessages.length === 0" class="text-center py-12 text-gray-400 text-sm">
                Noch keine Nachrichten. Schreib als Erstes!
              </div>
              <div v-for="msg in chatMessages" :key="msg.id" class="flex"
                :class="msg.from_me ? 'justify-end' : 'justify-start'">

                <!-- Regular free chat image (no price) -->
                <template v-if="msg.ppv_media_type && !msg.is_ppv">
                  <div class="max-w-xs rounded-xl overflow-hidden border border-gray-200 shadow-sm">
                    <img :src="msg.ppv_media_url" class="w-full object-cover max-h-64 rounded-t-xl" />
                    <div v-if="msg.body" class="px-3 py-1.5 text-xs text-gray-600">{{ msg.body }}</div>
                    <div class="px-3 pb-2 text-xs text-gray-400">{{ msg.created_at }}</div>
                  </div>
                </template>

                <!-- PPV locked content -->
                <template v-else-if="msg.ppv_media_type && msg.is_ppv && !msg.from_me">
                  <div v-if="msg.ppv_purchased" class="max-w-xs rounded-xl overflow-hidden border border-gray-200 shadow-sm">
                    <img v-if="msg.ppv_media_type === 'image'" :src="msg.ppv_media_url" class="w-full object-cover" />
                    <video v-else :src="msg.ppv_media_url" controls class="w-full max-h-64" />
                    <div v-if="msg.body" class="px-3 py-2 text-xs text-gray-600">{{ msg.body }}</div>
                    <div class="px-3 pb-2 text-xs text-gray-400">{{ msg.created_at }}</div>
                  </div>
                  <div v-else class="max-w-xs rounded-xl overflow-hidden border border-[#e35d8f]/30 shadow-sm">
                    <div class="relative bg-gray-100 h-40 flex flex-col items-center justify-center gap-2">
                      <div class="absolute inset-0 bg-gradient-to-br from-[#e35d8f]/20 to-purple-200/40"></div>
                      <div class="relative z-10 text-center">
                        <div class="text-4xl mb-1">{{ msg.ppv_media_type === 'video' ? '🎬' : '📷' }}</div>
                        <div class="text-xs text-gray-600 font-medium">
                          {{ msg.ppv_media_type === 'video' ? 'Privates Video' : 'Privates Foto' }}
                        </div>
                        <div class="text-lg font-bold text-[#e35d8f] mt-1">CHF {{ Number(msg.ppv_price_chf).toFixed(2) }}</div>
                      </div>
                    </div>
                    <div v-if="msg.body" class="px-3 pt-2 text-xs text-gray-600">{{ msg.body }}</div>
                    <div class="px-3 py-2 flex items-center justify-between">
                      <span class="text-xs text-gray-400">{{ msg.created_at }}</span>
                      <button @click="buyPpv(msg)" :disabled="buyingId === msg.id"
                        class="bg-[#e35d8f] text-white text-xs font-bold px-3 py-1.5 rounded-lg hover:bg-[#c44a7a] disabled:opacity-60 transition">
                        {{ buyingId === msg.id ? '…' : `🔓 CHF ${Number(msg.ppv_price_chf).toFixed(2)} freischalten` }}
                      </button>
                    </div>
                  </div>
                </template>

                <!-- Regular text message -->
                <template v-else>
                  <div class="max-w-xs px-3 py-2 rounded-xl text-sm"
                    :class="msg.from_me ? 'bg-[#e35d8f] text-white' : 'bg-gray-100 text-gray-800'">
                    <span class="break-words whitespace-pre-wrap">{{ msg.body }}</span>
                    <div class="text-xs mt-1 opacity-60">{{ msg.created_at }}</div>
                  </div>
                </template>

              </div>
            </template>
          </div>

          <!-- Input Area -->
          <div class="border-t border-gray-200">

            <!-- Send error -->
            <div v-if="sendError" class="px-4 pt-3 pb-0">
              <p class="text-xs text-red-500 bg-red-50 border border-red-200 rounded-lg px-3 py-2">
                {{ sendError }}
                <button @click="sendError = ''" class="ml-2 font-bold hover:text-red-700">✕</button>
              </p>
            </div>

            <!-- Emoji Picker -->
            <div v-if="showEmoji" class="px-4 pt-3 pb-1">
              <div class="bg-white border border-gray-200 rounded-xl p-2 shadow-md">
                <div class="flex flex-wrap gap-1">
                  <button v-for="e in emojis" :key="e" @click="insertEmoji(e)"
                    class="text-xl hover:bg-gray-100 rounded px-1 py-0.5 transition">{{ e }}</button>
                </div>
              </div>
            </div>

            <!-- Image preview (pending upload) -->
            <div v-if="pendingImage" class="px-4 pt-2">
              <div class="relative inline-block">
                <img :src="pendingImagePreview" class="h-20 rounded-lg object-cover border border-gray-200" />
                <button @click="clearImage"
                  class="absolute -top-1.5 -right-1.5 bg-gray-700 text-white rounded-full w-5 h-5 flex items-center justify-center text-xs hover:bg-gray-900">✕</button>
              </div>
            </div>

            <div class="px-4 py-3 flex items-end gap-2">
              <!-- Image upload button -->
              <label class="shrink-0 cursor-pointer text-gray-400 hover:text-[#e35d8f] transition p-1">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                </svg>
                <input type="file" accept="image/*" class="hidden" @change="onImageSelect" ref="imageInput" />
              </label>

              <!-- Emoji button -->
              <button type="button" @click="showEmoji = !showEmoji"
                class="shrink-0 text-gray-400 hover:text-[#e35d8f] transition p-1 text-xl leading-none"
                :class="showEmoji ? 'text-[#e35d8f]' : ''">😊</button>

              <!-- Text input -->
              <textarea v-model="replyText" rows="1" ref="textInput"
                placeholder="Nachricht schreiben…"
                @keydown.enter.exact.prevent="sendOrUpload"
                @input="autoResize"
                class="flex-1 border border-gray-300 bg-white text-gray-900 rounded-xl px-3 py-2 text-sm placeholder-gray-400 focus:outline-none focus:border-[#e35d8f] resize-none overflow-hidden leading-5 max-h-32"
                :disabled="sending" />

              <!-- Send button -->
              <button @click="sendOrUpload"
                :disabled="(!replyText.trim() && !pendingImage) || sending"
                class="shrink-0 bg-[#e35d8f] text-white px-4 py-2 rounded-xl text-sm font-bold hover:bg-[#c44a7a] disabled:opacity-40 transition">
                {{ sending ? '…' : 'Senden' }}
              </button>
            </div>
          </div>
        </template>
      </div>
    </div>
  </AppLayout>
</template>

<script setup>
import { ref, computed, nextTick, onMounted } from 'vue';
import { Head, router } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';

const props = defineProps({
  conversations: { type: Array, default: () => [] },
  subscriptions: { type: Array, default: () => [] },
  ppvSuccess:    { type: Boolean, default: false },
});

const newSubscriptions = computed(() =>
  props.subscriptions.filter(sub =>
    !props.conversations.some(c => c.user_id === sub.creator_user_id)
  )
);

const activeConv         = ref(null);
const chatMessages       = ref([]);
const chatLoading        = ref(false);
const replyText          = ref('');
const sending            = ref(false);
const sendError          = ref('');
const chatBox            = ref(null);
const textInput          = ref(null);
const imageInput         = ref(null);
const buyingId           = ref(null);
const ppvSuccessToast    = ref(false);
const showEmoji          = ref(false);
const pendingImage       = ref(null);
const pendingImagePreview= ref(null);

const emojis = [
  '😀','😘','😍','🥰','😏','😈','🔥','❤️','💋','💦',
  '🥵','😋','🤤','👅','💎','🌹','🍑','💄','🫦','✨',
  '🙈','😜','🤩','💪','👄','🎉','💌','😻','🦋','🌸',
  '❤️‍🔥','🥂','🍾','🌙','⭐','💰','🎁','📸','🎬','👑',
];

onMounted(() => {
  if (props.ppvSuccess) {
    ppvSuccessToast.value = true;
    setTimeout(() => { ppvSuccessToast.value = false; }, 5000);
  }
});

async function openConversation(conv) {
  activeConv.value   = { ...conv, isNew: false };
  chatLoading.value  = true;
  chatMessages.value = [];
  showEmoji.value    = false;
  sendError.value    = '';
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

function openNewConversation(sub) {
  activeConv.value   = {
    user_id: sub.creator_user_id,
    name:    sub.display_name,
    profile: { display_name: sub.display_name, slug: sub.slug },
    isNew:   true,
  };
  chatMessages.value = [];
  chatLoading.value  = false;
  showEmoji.value    = false;
  sendError.value    = '';
}

function autoResize(e) {
  const el = e.target;
  el.style.height = 'auto';
  el.style.height = Math.min(el.scrollHeight, 128) + 'px';
}

function insertEmoji(emoji) {
  replyText.value += emoji;
  showEmoji.value  = false;
  nextTick(() => textInput.value?.focus());
}

function onImageSelect(e) {
  const file = e.target.files[0];
  if (!file) return;
  pendingImage.value        = file;
  pendingImagePreview.value = URL.createObjectURL(file);
}

function clearImage() {
  pendingImage.value        = null;
  pendingImagePreview.value = null;
  if (imageInput.value) imageInput.value.value = '';
}

async function sendOrUpload() {
  if (sending.value) return;
  if (!activeConv.value?.profile?.slug) {
    sendError.value = 'Konversation nicht gefunden. Bitte Seite neu laden.';
    return;
  }

  sendError.value = '';
  if (pendingImage.value) {
    await uploadImage();
  } else if (replyText.value.trim()) {
    await sendText();
  }
}

async function sendText() {
  sending.value = true;
  try {
    const res = await fetch(route('konto.messages.send', activeConv.value.profile.slug), {
      method:  'POST',
      headers: {
        'Content-Type': 'application/json',
        'X-Requested-With': 'XMLHttpRequest',
        'X-XSRF-TOKEN': getCsrf(),
      },
      body: JSON.stringify({ body: replyText.value }),
    });
    const data = await res.json().catch(() => ({}));
    if (res.ok && data.ok) {
      replyText.value = '';
      if (textInput.value) { textInput.value.style.height = 'auto'; }
      await refreshConversation();
    } else {
      sendError.value = data.error ?? 'Nachricht konnte nicht gesendet werden.';
    }
  } catch (e) {
    sendError.value = 'Netzwerkfehler. Bitte versuche es erneut.';
  } finally {
    sending.value = false;
  }
}

async function uploadImage() {
  sending.value = true;
  try {
    const fd = new FormData();
    fd.append('media', pendingImage.value);
    if (replyText.value.trim()) fd.append('body', replyText.value);

    const res = await fetch(route('konto.messages.send.media', activeConv.value.profile.slug), {
      method:  'POST',
      headers: {
        'X-Requested-With': 'XMLHttpRequest',
        'X-XSRF-TOKEN': getCsrf(),
      },
      body: fd,
    });
    const data = await res.json().catch(() => ({}));
    if (res.ok && data.ok) {
      replyText.value = '';
      clearImage();
      await refreshConversation();
    } else {
      sendError.value = data.error ?? 'Bild konnte nicht gesendet werden.';
    }
  } catch (e) {
    sendError.value = 'Netzwerkfehler. Bitte versuche es erneut.';
  } finally {
    sending.value = false;
  }
}

async function refreshConversation() {
  if (!activeConv.value) return;
  try {
    const res = await fetch(route('konto.messages.conversation', activeConv.value.user_id), {
      headers: { 'X-Requested-With': 'XMLHttpRequest' },
    });
    const data = await res.json();
    if (Array.isArray(data.messages)) {
      chatMessages.value = data.messages;
      await nextTick();
      if (chatBox.value) chatBox.value.scrollTop = chatBox.value.scrollHeight;
    }
  } catch (e) {
    // Silent fail — messages stay as-is
  }
}

function buyPpv(msg) {
  if (buyingId.value) return;
  buyingId.value = msg.id;
  router.post(route('konto.messages.ppv.checkout', msg.id), {}, {
    onFinish: () => { buyingId.value = null; },
  });
}

function getCsrf() {
  return decodeURIComponent(document.cookie.split('; ')
    .find(r => r.startsWith('XSRF-TOKEN='))?.split('=')[1] ?? '');
}
</script>
