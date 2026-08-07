<template>
  <AppLayout>
    <Head title="Nachrichten" />

    <!-- PPV success toast -->
    <div v-if="ppvSuccessToast"
      class="fixed top-4 right-4 z-50 bg-green-600 text-white px-4 py-3 rounded-xl shadow-lg text-sm font-medium flex items-center gap-2">
      ✅ Inhalt freigeschaltet! Öffne die Konversation um ihn anzuschauen.
      <button @click="ppvSuccessToast = false" class="ml-2 opacity-70 hover:opacity-100">✕</button>
    </div>

    <div class="max-w-5xl mx-auto px-2 sm:px-4 py-4 sm:py-6 flex gap-3 h-[calc(100vh-110px)]">

      <!-- Sidebar -->
      <div class="w-full sm:w-80 shrink-0 bg-[#1a1a1a] rounded-2xl border border-white/8 flex-col overflow-hidden"
        :class="activeConv ? 'hidden sm:flex' : 'flex'">
        <!-- Header + Suche -->
        <div class="px-4 pt-3 pb-2 border-b border-white/8">
          <h2 class="font-bold text-white text-lg mb-2">Chats</h2>
          <div class="relative">
            <svg class="absolute left-3 top-2.5 w-4 h-4 text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
            <input v-model="search" type="text" placeholder="Suchen…"
              class="w-full bg-[#111] border border-white/10 text-gray-200 text-sm rounded-full pl-9 pr-3 py-2 focus:outline-none focus:border-[#e35d8f] transition" />
          </div>
          <div class="flex gap-2 mt-2.5">
            <button v-for="chip in [{k:'all',l:'Alle'},{k:'unread',l:'Ungelesen'}]" :key="chip.k"
              @click="chatFilter = chip.k"
              class="text-xs font-semibold px-3 py-1 rounded-full transition"
              :class="chatFilter === chip.k ? 'bg-[#e35d8f] text-white' : 'bg-white/5 text-gray-300 hover:bg-white/10'">
              {{ chip.l }}
            </button>
          </div>
        </div>

        <div class="flex-1 overflow-y-auto">
          <template v-if="filteredConversations.length">
            <button v-for="conv in filteredConversations" :key="'conv-' + conv.user_id"
              @click="openConversation(conv)"
              class="w-full text-left px-3 py-2.5 flex items-center gap-3 hover:bg-white/5 transition"
              :class="activeConv?.user_id === conv.user_id && !activeConv?.isNew ? 'bg-white/5' : ''">
              <span class="shrink-0 w-11 h-11 rounded-full bg-gradient-to-br from-[#e35d8f] to-[#7c3aed] flex items-center justify-center text-white text-sm font-bold">{{ initials(conv.profile?.display_name ?? conv.name) }}</span>
              <span class="flex-1 min-w-0">
                <span class="flex items-center justify-between gap-2">
                  <span class="font-semibold text-sm text-white truncate">{{ conv.profile?.display_name ?? conv.name }}</span>
                  <span class="text-[11px] text-gray-500 shrink-0">{{ conv.last_at }}</span>
                </span>
                <span class="flex items-center justify-between gap-2 mt-0.5">
                  <span class="text-xs text-gray-500 truncate">{{ conv.last_message }}</span>
                  <span v-if="conv.unread" class="shrink-0 min-w-[18px] h-[18px] px-1 bg-[#e35d8f] text-white text-[10px] font-bold rounded-full flex items-center justify-center">{{ conv.unread }}</span>
                </span>
              </span>
            </button>
          </template>

          <template v-if="newSubscriptions.length && chatFilter === 'all' && !search">
            <div v-if="filteredConversations.length" class="px-4 py-1.5 text-[11px] font-semibold text-gray-500 uppercase tracking-wide">Abonnements</div>
            <button v-for="sub in newSubscriptions" :key="'sub-' + sub.creator_user_id"
              @click="openNewConversation(sub)"
              class="w-full text-left px-3 py-2.5 flex items-center gap-3 hover:bg-white/5 transition"
              :class="activeConv?.user_id === sub.creator_user_id && activeConv?.isNew ? 'bg-white/5' : ''">
              <span class="shrink-0 w-11 h-11 rounded-full bg-white/5 border border-[#e35d8f]/40 flex items-center justify-center text-[#e35d8f] text-sm font-bold">{{ initials(sub.display_name) }}</span>
              <span class="flex-1 min-w-0">
                <span class="font-semibold text-sm text-white truncate block">{{ sub.display_name }}</span>
                <span class="text-xs text-gray-500">Neue Konversation starten</span>
              </span>
            </button>
          </template>

          <div v-if="!filteredConversations.length && !newSubscriptions.length" class="text-center py-10 text-gray-500 text-sm px-4">
            Noch keine Nachrichten.<br>Abonniere ein Profil und sende eine Nachricht.
          </div>
        </div>
      </div>

      <!-- Chat Panel -->
      <div class="flex-1 bg-[#0e0e0e] rounded-2xl border border-white/8 flex-col overflow-hidden"
        :class="activeConv ? 'flex' : 'hidden sm:flex'">
        <div v-if="!activeConv" class="flex-1 flex items-center justify-center text-gray-600">
          <div class="text-center">
            <div class="text-5xl mb-3">💬</div>
            <p class="text-sm">Wähle eine Konversation aus.</p>
          </div>
        </div>
        <template v-else>
          <!-- Kopfzeile -->
          <div class="px-3 py-2.5 border-b border-white/8 bg-[#1a1a1a] flex items-center gap-3">
            <button @click="closeConversation" class="sm:hidden text-gray-400 hover:text-white p-1 -ml-1">
              <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/></svg>
            </button>
            <Link v-if="activeConv.profile?.slug" :href="route('profile.show', activeConv.profile.slug)"
              class="flex items-center gap-3 min-w-0 group" title="Profil ansehen">
              <span class="shrink-0 w-9 h-9 rounded-full bg-gradient-to-br from-[#e35d8f] to-[#7c3aed] flex items-center justify-center text-white text-xs font-bold">{{ initials(activeConv.profile?.display_name ?? activeConv.name) }}</span>
              <span class="font-semibold text-white text-sm truncate group-hover:text-[#e35d8f] transition">{{ activeConv.profile?.display_name ?? activeConv.name }}</span>
            </Link>
            <div v-else class="flex items-center gap-3 min-w-0">
              <span class="shrink-0 w-9 h-9 rounded-full bg-gradient-to-br from-[#e35d8f] to-[#7c3aed] flex items-center justify-center text-white text-xs font-bold">{{ initials(activeConv.name) }}</span>
              <span class="font-semibold text-white text-sm truncate">{{ activeConv.name }}</span>
            </div>
          </div>

          <!-- Nachrichten -->
          <div ref="chatBox" class="flex-1 overflow-y-auto p-4 space-y-1.5 chat-bg">
            <div v-if="chatLoading" class="text-center text-gray-500 py-8 text-sm">Lädt…</div>
            <template v-else>
              <div v-if="chatMessages.length === 0" class="text-center py-12 text-gray-500 text-sm">Noch keine Nachrichten. Schreib als Erstes!</div>

              <template v-for="(msg, i) in chatMessages" :key="msg.id">
                <!-- Datums-Trenner -->
                <div v-if="i === 0 || msg.date !== chatMessages[i-1].date" class="flex justify-center my-3">
                  <span class="text-[11px] text-gray-300 bg-[#1a1a1a] border border-white/8 px-3 py-1 rounded-full">{{ dayLabel(msg.date) }}</span>
                </div>

                <div class="flex" :class="msg.from_me ? 'justify-end' : 'justify-start'">
                  <!-- Freies Chat-Bild -->
                  <template v-if="msg.ppv_media_type && !msg.is_ppv">
                    <div class="max-w-[75%] rounded-2xl overflow-hidden bg-[#1a1a1a] border border-white/8">
                      <img :src="msg.ppv_media_url" class="w-full object-cover max-h-64" />
                      <div v-if="msg.body" class="px-3 py-1.5 text-xs text-gray-300">{{ msg.body }}</div>
                      <div class="px-3 pb-2 text-[10px] text-gray-500 text-right">{{ msg.time }}<span v-if="msg.from_me" class="ml-1" :class="msg.read ? 'text-sky-400' : 'text-gray-500'">{{ msg.read ? '✓✓' : '✓' }}</span></div>
                    </div>
                  </template>

                  <!-- PPV (gesperrt / gekauft) -->
                  <template v-else-if="msg.ppv_media_type && msg.is_ppv && !msg.from_me">
                    <div v-if="msg.ppv_purchased" class="max-w-[75%] rounded-2xl overflow-hidden bg-[#1a1a1a] border border-white/8">
                      <img v-if="msg.ppv_media_type === 'image'" :src="msg.ppv_media_url" class="w-full object-cover" />
                      <video v-else :src="msg.ppv_media_url" controls class="w-full max-h-64" />
                      <div v-if="msg.body" class="px-3 py-2 text-xs text-gray-300">{{ msg.body }}</div>
                      <div class="px-3 pb-2 text-[10px] text-gray-500">{{ msg.time }}</div>
                    </div>
                    <div v-else class="max-w-[75%] rounded-2xl overflow-hidden bg-[#1a1a1a] border border-[#e35d8f]/30">
                      <div class="relative h-40 flex items-center justify-center bg-gradient-to-br from-[#e35d8f]/20 to-[#7c3aed]/20">
                        <div class="text-center">
                          <div class="text-4xl mb-1">{{ msg.ppv_media_type === 'video' ? '🎬' : '📷' }}</div>
                          <div class="text-xs text-gray-300 font-medium">{{ msg.ppv_media_type === 'video' ? 'Privates Video' : 'Privates Foto' }}</div>
                          <div class="text-lg font-bold text-[#e35d8f] mt-1">CHF {{ Number(msg.ppv_price_chf).toFixed(2) }}</div>
                        </div>
                      </div>
                      <div v-if="msg.body" class="px-3 pt-2 text-xs text-gray-300">{{ msg.body }}</div>
                      <div class="px-3 py-2 flex items-center justify-between">
                        <span class="text-[10px] text-gray-500">{{ msg.time }}</span>
                        <button @click="buyPpv(msg)" :disabled="buyingId === msg.id"
                          class="bg-[#e35d8f] text-white text-xs font-bold px-3 py-1.5 rounded-lg hover:bg-[#c44a7a] disabled:opacity-60 transition">
                          {{ buyingId === msg.id ? '…' : `🔓 CHF ${Number(msg.ppv_price_chf).toFixed(2)} freischalten` }}
                        </button>
                      </div>
                    </div>
                  </template>

                  <!-- Textnachricht -->
                  <template v-else>
                    <div class="max-w-[78%] px-3 py-1.5 rounded-2xl text-sm"
                      :class="msg.from_me ? 'bg-[#e35d8f] text-white rounded-br-md' : 'bg-[#232323] text-gray-100 rounded-bl-md'">
                      <span class="break-words whitespace-pre-wrap">{{ msg.body }}</span>
                      <span class="inline-flex items-center gap-1 float-right ml-2 mt-1.5 text-[10px] leading-none" :class="msg.from_me ? 'text-white/70' : 'text-gray-400'">
                        {{ msg.time }}
                        <span v-if="msg.from_me" :class="msg.read ? 'text-sky-300' : 'text-white/60'">{{ msg.read ? '✓✓' : '✓' }}</span>
                      </span>
                    </div>
                  </template>
                </div>
              </template>
            </template>
          </div>

          <!-- Eingabe -->
          <div class="border-t border-white/8 bg-[#1a1a1a]">
            <div v-if="showEmoji" class="px-3 pt-3">
              <div class="bg-[#111] border border-white/10 rounded-xl p-2">
                <div class="flex flex-wrap gap-1">
                  <button v-for="e in emojis" :key="e" @click="insertEmoji(e)" class="text-xl hover:bg-white/10 rounded px-1 py-0.5 transition">{{ e }}</button>
                </div>
              </div>
            </div>

            <div v-if="pendingImage" class="px-3 pt-2">
              <div class="relative inline-block">
                <img :src="pendingImagePreview" class="h-20 rounded-lg object-cover border border-white/10" />
                <button @click="clearImage" class="absolute -top-1.5 -right-1.5 bg-black/70 text-white rounded-full w-5 h-5 flex items-center justify-center text-xs">✕</button>
              </div>
            </div>

            <div class="px-3 py-2.5 flex items-end gap-2">
              <button type="button" @click="showEmoji = !showEmoji" class="shrink-0 p-1.5 text-xl leading-none transition" :class="showEmoji ? 'text-[#e35d8f]' : 'text-gray-400 hover:text-[#e35d8f]'">😊</button>
              <label class="shrink-0 cursor-pointer text-gray-400 hover:text-[#e35d8f] transition p-1.5">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"/></svg>
                <input type="file" accept="image/*" class="hidden" @change="onImageSelect" ref="imageInput" />
              </label>
              <textarea v-model="replyText" rows="1" ref="textInput" placeholder="Nachricht schreiben…"
                @keydown.enter.exact.prevent="sendOrUpload" @input="autoResize"
                class="flex-1 border border-white/10 bg-[#111] text-gray-100 rounded-2xl px-3.5 py-2 text-sm placeholder-gray-500 focus:outline-none focus:border-[#e35d8f] resize-none overflow-hidden leading-5 max-h-32"
                :disabled="sending" />
              <button @click="sendOrUpload" :disabled="(!replyText.trim() && !pendingImage) || sending"
                class="shrink-0 w-10 h-10 flex items-center justify-center bg-[#e35d8f] text-white rounded-full hover:bg-[#c44a7a] disabled:opacity-40 transition">
                <svg v-if="!sending" class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24"><path d="M2.01 21L23 12 2.01 3 2 10l15 2-15 2z"/></svg>
                <span v-else class="text-xs">…</span>
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
import { Head, Link, router } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';

const props = defineProps({
  conversations: { type: Array, default: () => [] },
  subscriptions: { type: Array, default: () => [] },
  startWith:     { type: Object, default: null },
  ppvSuccess:    { type: Boolean, default: false },
});

const newSubscriptions = computed(() =>
  props.subscriptions.filter(sub =>
    !props.conversations.some(c => c.user_id === sub.creator_user_id)
  )
);

const search      = ref('');
const chatFilter  = ref('all');
const filteredConversations = computed(() => {
  let list = props.conversations;
  if (chatFilter.value === 'unread') list = list.filter(c => c.unread);
  const q = search.value.trim().toLowerCase();
  if (q) list = list.filter(c => (c.profile?.display_name ?? c.name ?? '').toLowerCase().includes(q));
  return list;
});

function initials(name) {
  return (name || '?').trim().split(/\s+/).map(w => w[0]).slice(0, 2).join('').toUpperCase();
}
function dayLabel(date) {
  const today = new Date();
  const pad = (n) => String(n).padStart(2, '0');
  const t = `${pad(today.getDate())}.${pad(today.getMonth() + 1)}.${today.getFullYear()}`;
  const y = new Date(today); y.setDate(today.getDate() - 1);
  const yStr = `${pad(y.getDate())}.${pad(y.getMonth() + 1)}.${y.getFullYear()}`;
  if (date === t) return 'Heute';
  if (date === yStr) return 'Gestern';
  return date;
}

const activeConv         = ref(null);
const chatMessages       = ref([]);
const chatLoading        = ref(false);
const replyText          = ref('');
const sending            = ref(false);
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
  if (props.startWith) {
    const existing = props.conversations.find(c => c.user_id === props.startWith.user_id);
    if (existing) {
      openConversation(existing);
    } else {
      openNewConversation({
        creator_user_id: props.startWith.user_id,
        display_name:    props.startWith.name,
        slug:            props.startWith.profile.slug,
      });
    }
  }
});

async function openConversation(conv) {
  activeConv.value   = { ...conv, isNew: false };
  chatLoading.value  = true;
  chatMessages.value = [];
  showEmoji.value    = false;
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
}

function closeConversation() {
  activeConv.value = null;
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
  if (sending.value || !activeConv.value?.profile?.slug) return;
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
    if (res.ok) {
      replyText.value = '';
      if (textInput.value) { textInput.value.style.height = 'auto'; }
      await refreshConversation();
    }
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
    if (res.ok) {
      replyText.value = '';
      clearImage();
      await refreshConversation();
    }
  } finally {
    sending.value = false;
  }
}

async function refreshConversation() {
  if (!activeConv.value) return;
  const res = await fetch(route('konto.messages.conversation', activeConv.value.user_id), {
    headers: { 'X-Requested-With': 'XMLHttpRequest' },
  });
  const data = await res.json();
  chatMessages.value = data.messages;
  await nextTick();
  if (chatBox.value) chatBox.value.scrollTop = chatBox.value.scrollHeight;
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

<style scoped>
/* Dezentes dunkles Chat-Muster (ähnlich WhatsApp) */
.chat-bg {
  background-color: #0b0b0b;
  background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='70' height='70'%3E%3Cg fill='%23ffffff' fill-opacity='0.02'%3E%3Ccircle cx='12' cy='14' r='2'/%3E%3Ccircle cx='48' cy='34' r='2'/%3E%3Cpath d='M30 54h3v3h-3z'/%3E%3Cpath d='M58 8h3v3h-3z'/%3E%3C/g%3E%3C/svg%3E");
}
</style>
