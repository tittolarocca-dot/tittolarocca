<template>
  <AppLayout>
    <Head title="Nachrichten" />
    <div class="max-w-7xl mx-auto px-4 py-4 sm:py-6 flex gap-3 h-[calc(100vh-110px)]">

      <!-- Sidebar -->
      <div class="w-full sm:w-80 shrink-0 bg-[#1a1a1a] rounded-2xl border border-white/8 flex-col overflow-hidden"
        :class="activeConv ? 'hidden sm:flex' : 'flex'">
        <div class="px-4 pt-3 pb-2 border-b border-white/8">
          <div class="flex items-center justify-between mb-2">
            <h2 class="font-bold text-white text-lg">Chats</h2>
            <span v-if="unreadCount" class="bg-[#e35d8f] text-white text-xs font-bold min-w-[20px] h-5 px-1 flex items-center justify-center rounded-full">{{ unreadCount }}</span>
          </div>
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
          <div v-if="filteredConversations.length === 0" class="text-center py-10 text-gray-500 text-sm px-4">Noch keine Nachrichten.</div>
          <button v-for="conv in filteredConversations" :key="conv.user_id"
            @click="openConversation(conv)"
            class="w-full text-left px-3 py-2.5 flex items-center gap-3 hover:bg-white/5 transition"
            :class="activeConv?.user_id === conv.user_id ? 'bg-white/5' : ''">
            <span class="shrink-0 w-11 h-11 rounded-full bg-gradient-to-br from-[#e35d8f] to-[#7c3aed] flex items-center justify-center text-white text-sm font-bold">{{ initials(conv.name) }}</span>
            <span class="flex-1 min-w-0">
              <span class="flex items-center justify-between gap-2">
                <span class="font-semibold text-sm text-white truncate">{{ conv.name }}</span>
                <span class="text-[11px] text-gray-500 shrink-0">{{ conv.last_at }}</span>
              </span>
              <span class="flex items-center justify-between gap-2 mt-0.5">
                <span class="text-xs text-gray-500 truncate">{{ conv.last_message }}</span>
                <span v-if="conv.unread" class="shrink-0 min-w-[18px] h-[18px] px-1 bg-[#e35d8f] text-white text-[10px] font-bold rounded-full flex items-center justify-center">{{ conv.unread }}</span>
              </span>
            </span>
          </button>
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
            <Link :href="route('mitglied.show', activeConv.user_id)"
              class="flex items-center gap-3 min-w-0 flex-1 group" title="Mitglieder-Profil ansehen">
              <span class="shrink-0 w-9 h-9 rounded-full bg-gradient-to-br from-[#e35d8f] to-[#7c3aed] flex items-center justify-center text-white text-xs font-bold">{{ initials(activeConv.name) }}</span>
              <span class="font-semibold text-white text-sm truncate group-hover:text-[#e35d8f] transition">{{ activeConv.name }}</span>
            </Link>
            <button v-if="!activeConv.blocked" @click="blockUser"
              class="shrink-0 text-xs font-semibold text-gray-400 hover:text-red-400 transition inline-flex items-center gap-1">
              <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"/></svg>
              Blockieren
            </button>
            <button v-else @click="unblockUser"
              class="shrink-0 text-xs font-semibold text-red-400 hover:text-gray-300 transition inline-flex items-center gap-1">
              <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"/></svg>
              Blockiert · aufheben
            </button>
          </div>
          <div v-if="activeConv.blocked" class="px-4 py-2 bg-red-500/10 border-b border-red-500/20 text-xs text-red-400">
            Dieser Nutzer ist blockiert und kann dir nicht schreiben.
          </div>

          <!-- Nachrichten -->
          <div ref="chatBox" class="flex-1 overflow-y-auto p-4 space-y-1.5 chat-bg">
            <div v-if="chatLoading" class="text-center text-gray-500 py-8 text-sm">Lädt…</div>
            <template v-else>
              <div v-if="chatMessages.length === 0" class="text-center py-12 text-gray-500 text-sm">Noch keine Nachrichten.</div>

              <template v-for="(msg, i) in chatMessages" :key="msg.id">
                <div v-if="i === 0 || msg.date !== chatMessages[i-1].date" class="flex justify-center my-3">
                  <span class="text-[11px] text-gray-300 bg-[#1a1a1a] border border-white/8 px-3 py-1 rounded-full">{{ dayLabel(msg.date) }}</span>
                </div>

                <div class="flex" :class="msg.from_me ? 'justify-end' : 'justify-start'">
                  <!-- Medien (von der Inserentin gesendet) -->
                  <template v-if="msg.ppv_media_type">
                    <div class="max-w-[78%] rounded-2xl overflow-hidden bg-[#1a1a1a] border"
                      :class="msg.ppv_media_mode === 'free' ? 'border-white/8' : 'border-[#e35d8f]/30'">
                      <div class="px-3 py-2 flex items-center gap-2"
                        :class="msg.ppv_media_mode === 'free' ? 'bg-white/5' : 'bg-[#e35d8f]/10'">
                        <span class="text-lg">{{ msg.ppv_media_type === 'video' ? '🎬' : '📷' }}</span>
                        <div class="min-w-0">
                          <p class="text-xs font-semibold" :class="msg.ppv_media_mode === 'free' ? 'text-gray-300' : 'text-[#e35d8f]'">{{ mediaModeLabel(msg.ppv_media_mode) }}</p>
                          <p v-if="msg.ppv_media_mode === 'paid'" class="text-xs text-gray-400">CHF {{ Number(msg.ppv_price_chf).toFixed(2) }}</p>
                        </div>
                        <span v-if="msg.ppv_media_mode === 'paid'" class="ml-auto text-xs text-green-400 font-medium shrink-0">{{ msg.ppv_purchase_count ?? 0 }}× gekauft</span>
                        <span v-else-if="msg.ppv_media_mode === 'manual' && msg.ppv_released" class="ml-auto text-xs text-green-400 font-medium shrink-0">✓ Freigegeben</span>
                        <button v-else-if="msg.ppv_media_mode === 'manual'" @click="releaseMedia(msg)" :disabled="releasingId === msg.id"
                          class="ml-auto bg-[#e35d8f] text-white text-xs font-bold px-2.5 py-1 rounded-lg hover:bg-[#c44a7a] disabled:opacity-50 transition shrink-0">
                          {{ releasingId === msg.id ? '…' : 'Freigeben' }}
                        </button>
                      </div>
                      <div class="p-2">
                        <img v-if="msg.ppv_media_type === 'image'" :src="msg.ppv_media_url" class="w-full rounded object-cover max-h-48" />
                        <video v-else :src="msg.ppv_media_url" controls class="w-full rounded max-h-48" />
                      </div>
                      <div v-if="msg.body" class="px-3 pb-1 text-xs text-gray-300">{{ msg.body }}</div>
                      <div class="px-3 pb-2 text-[10px] text-gray-500 text-right">{{ msg.time }}<span v-if="msg.from_me" class="ml-1" :class="msg.read ? 'text-sky-400' : 'text-gray-500'">{{ msg.read ? '✓✓' : '✓' }}</span></div>
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
          <div class="border-t border-white/8 bg-[#1a1a1a] px-3 py-2.5">
            <!-- Medien-Sendeformular (Foto/Video mit Freigabe-Modus) -->
            <div v-if="ppvMode" class="bg-[#e35d8f]/5 border border-[#e35d8f]/20 rounded-xl p-3 space-y-2.5 mb-1">
              <div class="flex items-center justify-between">
                <span class="text-xs font-semibold text-[#e35d8f]">📎 Foto / Video senden</span>
                <button @click="cancelPpv" class="text-gray-400 hover:text-white text-xs">✕ Abbrechen</button>
              </div>
              <div v-if="ppvPreview" class="relative">
                <img v-if="ppvFileType === 'image'" :src="ppvPreview" class="w-full rounded-lg object-cover max-h-40" />
                <video v-else :src="ppvPreview" class="w-full rounded-lg max-h-40" />
                <button @click="clearPpvFile" class="absolute top-1 right-1 bg-black/60 text-white rounded-full w-6 h-6 flex items-center justify-center text-xs">✕</button>
              </div>
              <div v-else>
                <label class="block w-full border-2 border-dashed border-[#e35d8f]/30 rounded-lg p-4 text-center cursor-pointer hover:border-[#e35d8f]/60 transition">
                  <span class="text-gray-400 text-sm">Bild oder Video auswählen…</span>
                  <input type="file" accept="image/*,video/mp4,video/quicktime,video/webm" class="hidden" @change="onPpvFileChange" ref="ppvFileInput" />
                </label>
              </div>

              <!-- Freigabe-Modus -->
              <div>
                <p class="text-[11px] text-gray-400 mb-1.5">Freigabe</p>
                <div class="grid grid-cols-3 gap-1.5">
                  <button v-for="opt in mediaModes" :key="opt.k" type="button" @click="mediaMode = opt.k"
                    class="px-2 py-1.5 rounded-lg text-xs font-semibold border transition"
                    :class="mediaMode === opt.k ? 'bg-[#e35d8f] text-white border-[#e35d8f]' : 'bg-[#111] text-gray-300 border-white/10 hover:border-[#e35d8f]/50'">
                    {{ opt.label }}
                  </button>
                </div>
                <p class="text-[10px] text-gray-500 mt-1.5 leading-snug">{{ mediaModeHint }}</p>
              </div>

              <!-- Preis nur bei Online-Zahlung -->
              <div v-if="mediaMode === 'paid'" class="relative w-32">
                <span class="absolute left-2 top-1/2 -translate-y-1/2 text-gray-500 text-xs">CHF</span>
                <input v-model="ppvPrice" type="number" min="1" max="999" step="1" placeholder="9"
                  class="w-full pl-8 pr-2 py-1.5 bg-[#111] border border-white/10 text-gray-100 rounded-lg text-sm focus:outline-none focus:border-[#e35d8f]" />
              </div>

              <input v-model="ppvText" type="text" placeholder="Optionaler Text…"
                class="w-full bg-[#111] border border-white/10 text-gray-100 rounded-lg px-3 py-1.5 text-sm focus:outline-none focus:border-[#e35d8f]" />

              <button @click="sendPpv" :disabled="!canSendMedia || sending"
                class="w-full bg-[#e35d8f] text-white py-2 rounded-lg text-sm font-bold hover:bg-[#c44a7a] disabled:opacity-40 transition">
                {{ sending ? 'Wird gesendet…' : sendMediaLabel }}
              </button>
            </div>

            <!-- Normales Antwortformular -->
            <form v-else @submit.prevent="sendReply" class="flex items-end gap-2">
              <button type="button" @click="ppvMode = true" title="Foto/Video senden"
                class="shrink-0 text-gray-400 hover:text-[#e35d8f] p-1.5 text-lg leading-none transition">📎</button>
              <textarea v-model="replyText" rows="1" placeholder="Nachricht schreiben…"
                @keydown.enter.exact.prevent="sendReply"
                class="flex-1 border border-white/10 bg-[#111] text-gray-100 rounded-2xl px-3.5 py-2 text-sm placeholder-gray-500 focus:outline-none focus:border-[#e35d8f] resize-none overflow-hidden leading-5 max-h-32"
                :disabled="sending" />
              <button type="submit" :disabled="!replyText.trim() || sending"
                class="shrink-0 w-10 h-10 flex items-center justify-center bg-[#e35d8f] text-white rounded-full hover:bg-[#c44a7a] disabled:opacity-40 transition">
                <svg v-if="!sending" class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24"><path d="M2.01 21L23 12 2.01 3 2 10l15 2-15 2z"/></svg>
                <span v-else class="text-xs">…</span>
              </button>
            </form>
          </div>
        </template>
      </div>
    </div>
  </AppLayout>
</template>

<script setup>
import { ref, computed, nextTick } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';

const props = defineProps({
  conversations: { type: Array, default: () => [] },
  unreadCount:   { type: Number, default: 0 },
});

const search     = ref('');
const chatFilter = ref('all');
const filteredConversations = computed(() => {
  let list = props.conversations;
  if (chatFilter.value === 'unread') list = list.filter(c => c.unread);
  const q = search.value.trim().toLowerCase();
  if (q) list = list.filter(c => (c.name ?? '').toLowerCase().includes(q));
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

const activeConv   = ref(null);
const chatMessages = ref([]);
const chatLoading  = ref(false);
const replyText    = ref('');
const sending      = ref(false);
const chatBox      = ref(null);

// Medien-/Freigabe-State
const ppvMode      = ref(false);
const ppvFile      = ref(null);
const ppvPreview   = ref(null);
const ppvFileType  = ref('image');
const ppvPrice     = ref('');
const ppvText      = ref('');
const ppvFileInput = ref(null);
const mediaMode    = ref('free');   // free | paid | manual
const releasingId  = ref(null);

const mediaModes = [
  { k: 'free',   label: 'Gratis' },
  { k: 'paid',   label: 'Online-Zahlung' },
  { k: 'manual', label: 'Manuell' },
];

const mediaModeHint = computed(() => ({
  free:   'Das Mitglied sieht Foto/Video sofort – kostenlos.',
  paid:   'Gesperrt. Das Mitglied schaltet gegen Online-Zahlung (CHF) frei.',
  manual: 'Gesperrt. Du gibst es später manuell frei – z. B. nach TWINT-Zahlung.',
}[mediaMode.value]));

const sendMediaLabel = computed(() => ({
  free:   'Gratis senden',
  paid:   'Kostenpflichtig senden',
  manual: 'Gesperrt senden',
}[mediaMode.value]));

const canSendMedia = computed(() =>
  !!ppvFile.value && (mediaMode.value !== 'paid' || !!ppvPrice.value)
);

function mediaModeLabel(mode) {
  return { free: 'Gratis geteilt', paid: 'Online-Zahlung', manual: 'Manuelle Freigabe' }[mode] ?? 'Inhalt';
}

function releaseMedia(msg) {
  if (releasingId.value) return;
  releasingId.value = msg.id;
  router.post(route('inserat.messages.release', msg.id), {}, {
    preserveScroll: true,
    onSuccess: () => { openConversation(activeConv.value); },
    onFinish: () => { releasingId.value = null; },
  });
}

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

function closeConversation() {
  activeConv.value = null;
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
  ppvMode.value   = false;
  ppvPrice.value  = '';
  ppvText.value   = '';
  mediaMode.value = 'free';
  clearPpvFile();
}

function sendPpv() {
  if (!canSendMedia.value || sending.value) return;
  sending.value = true;

  const formData = new FormData();
  formData.append('media', ppvFile.value);
  formData.append('mode', mediaMode.value);
  if (mediaMode.value === 'paid') formData.append('price', ppvPrice.value);
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

<style scoped>
/* Dezentes dunkles Chat-Muster (ähnlich WhatsApp) */
.chat-bg {
  background-color: #0b0b0b;
  background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='70' height='70'%3E%3Cg fill='%23ffffff' fill-opacity='0.02'%3E%3Ccircle cx='12' cy='14' r='2'/%3E%3Ccircle cx='48' cy='34' r='2'/%3E%3Cpath d='M30 54h3v3h-3z'/%3E%3Cpath d='M58 8h3v3h-3z'/%3E%3C/g%3E%3C/svg%3E");
}
</style>
