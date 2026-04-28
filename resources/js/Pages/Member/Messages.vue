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

          <!-- Existing conversations -->
          <template v-if="conversations.length">
            <button v-for="conv in conversations" :key="'conv-' + conv.user_id"
              @click="openConversation(conv)"
              class="w-full text-left px-4 py-3 border-b border-gray-100 hover:bg-gray-50 transition"
              :class="activeConv?.user_id === conv.user_id && !activeConv?.isNew ? 'bg-gray-50' : ''"
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
          </template>

          <!-- Subscriptions without a conversation yet -->
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
                <span class="w-2 h-2 rounded-full bg-[#e91e8c] shrink-0"></span>
                <span class="font-semibold text-sm text-gray-900">{{ sub.display_name }}</span>
              </div>
              <p class="text-xs text-gray-400 mt-0.5 pl-4">Neue Konversation starten</p>
            </button>
          </template>

          <!-- No subscriptions at all -->
          <div v-if="!conversations.length && !newSubscriptions.length"
            class="text-center py-10 text-gray-400 text-sm px-4">
            Noch keine Nachrichten.<br>
            Abonniere ein Profil und sende eine Nachricht.
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

                <!-- PPV message from creator (locked) -->
                <template v-if="msg.ppv_media_type && !msg.from_me">
                  <div v-if="msg.ppv_purchased" class="max-w-xs rounded-xl overflow-hidden border border-gray-200 shadow-sm">
                    <img v-if="msg.ppv_media_type === 'image'" :src="msg.ppv_media_url" class="w-full object-cover" />
                    <video v-else :src="msg.ppv_media_url" controls class="w-full max-h-64" />
                    <div v-if="msg.body" class="px-3 py-2 text-xs text-gray-600">{{ msg.body }}</div>
                    <div class="px-3 pb-2 text-xs text-gray-400">{{ msg.created_at }}</div>
                  </div>
                  <div v-else class="max-w-xs rounded-xl overflow-hidden border border-[#e91e8c]/30 shadow-sm">
                    <div class="relative bg-gray-100 h-40 flex flex-col items-center justify-center gap-2">
                      <div class="absolute inset-0 bg-gradient-to-br from-[#e91e8c]/20 to-purple-200/40"></div>
                      <div class="relative z-10 text-center">
                        <div class="text-4xl mb-1">{{ msg.ppv_media_type === 'video' ? '🎬' : '📷' }}</div>
                        <div class="text-xs text-gray-600 font-medium">
                          {{ msg.ppv_media_type === 'video' ? 'Privates Video' : 'Privates Foto' }}
                        </div>
                        <div class="text-lg font-bold text-[#e91e8c] mt-1">CHF {{ Number(msg.ppv_price_chf).toFixed(2) }}</div>
                      </div>
                    </div>
                    <div v-if="msg.body" class="px-3 pt-2 text-xs text-gray-600">{{ msg.body }}</div>
                    <div class="px-3 py-2 flex items-center justify-between">
                      <span class="text-xs text-gray-400">{{ msg.created_at }}</span>
                      <button @click="buyPpv(msg)" :disabled="buyingId === msg.id"
                        class="bg-[#e91e8c] text-white text-xs font-bold px-3 py-1.5 rounded-lg hover:bg-[#c91478] disabled:opacity-60 transition">
                        {{ buyingId === msg.id ? '…' : `🔓 CHF ${Number(msg.ppv_price_chf).toFixed(2)} freischalten` }}
                      </button>
                    </div>
                  </div>
                </template>

                <!-- Regular text message -->
                <template v-else>
                  <div class="max-w-xs px-3 py-2 rounded-xl text-sm"
                    :class="msg.from_me ? 'bg-[#e91e8c] text-white' : 'bg-gray-100 text-gray-800'">
                    {{ msg.body }}
                    <div class="text-xs mt-1 opacity-60">{{ msg.created_at }}</div>
                  </div>
                </template>

              </div>
            </template>
          </div>

          <!-- Input -->
          <div class="px-4 py-3 border-t border-gray-200">
            <form @submit.prevent="sendMessage" class="flex gap-2">
              <input v-model="replyText" type="text" placeholder="Nachricht schreiben…"
                class="flex-1 border border-gray-300 bg-white text-gray-900 rounded-lg px-3 py-2 text-sm placeholder-gray-400 focus:outline-none focus:border-[#e91e8c]"
                :disabled="sending" />
              <button type="submit" :disabled="!replyText.trim() || sending"
                class="bg-[#e91e8c] text-white px-4 py-2 rounded-lg text-sm font-bold hover:bg-[#c91478] disabled:opacity-40 transition">
                {{ sending ? '…' : 'Senden' }}
              </button>
            </form>
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

// Subscriptions that have no existing conversation yet
const newSubscriptions = computed(() =>
  props.subscriptions.filter(sub =>
    !props.conversations.some(c => c.user_id === sub.creator_user_id)
  )
);

const activeConv      = ref(null);
const chatMessages    = ref([]);
const chatLoading     = ref(false);
const replyText       = ref('');
const sending         = ref(false);
const chatBox         = ref(null);
const buyingId        = ref(null);
const ppvSuccessToast = ref(false);

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
}

function sendMessage() {
  if (!replyText.value.trim() || !activeConv.value?.profile?.slug) return;
  sending.value = true;
  router.post(route('konto.messages.send', activeConv.value.profile.slug), {
    body: replyText.value,
  }, {
    preserveScroll: true,
    onSuccess: () => {
      replyText.value = '';
      // After first message, refetch as a real conversation
      openConversation({ ...activeConv.value, isNew: false });
    },
    onFinish: () => { sending.value = false; },
  });
}

function buyPpv(msg) {
  if (buyingId.value) return;
  buyingId.value = msg.id;
  router.post(route('konto.messages.ppv.checkout', msg.id), {}, {
    onFinish: () => { buyingId.value = null; },
  });
}
</script>
