<template>
  <AppLayout>
    <Head :title="profile.display_name" />

    <!-- Success banner -->
    <div v-if="subscribed" class="bg-[#e91e8c] text-white text-center py-3 text-sm font-semibold">
      🎉 Abonnement erfolgreich! Du hast jetzt Zugang zu allen privaten Inhalten.
    </div>

    <!-- Photo Grid -->
    <div class="bg-gray-50 border-b border-gray-200">
      <div class="max-w-7xl mx-auto">
        <div v-if="publicMedia.length" class="flex overflow-x-auto h-64 md:h-80">
          <div v-for="(item, i) in publicMedia.slice(0,4)" :key="item.id"
            class="shrink-0 cursor-pointer relative"
            :class="i === 0 ? 'w-1/2' : 'w-1/4'"
            @click="openLightbox(item)">
            <img :src="item.url" :alt="profile.display_name" class="w-full h-full object-cover border-r border-gray-100" />
            <div v-if="i === 3 && publicMedia.length > 4"
              class="absolute inset-0 bg-black/60 flex items-center justify-center text-white font-bold text-xl">
              +{{ publicMedia.length - 4 }}
            </div>
          </div>
        </div>
        <div v-else class="h-48 bg-gray-100 flex items-center justify-center text-gray-400 text-5xl">👤</div>
      </div>
    </div>

    <div class="max-w-7xl mx-auto px-4 py-6">
      <div class="flex flex-col md:flex-row gap-6">

        <!-- LEFT: Contact Sidebar -->
        <div class="md:w-72 shrink-0 space-y-4">

          <!-- Subscribe / Owner CTA -->
          <div class="bg-white border border-gray-200 rounded-xl p-4 shadow-sm">
            <template v-if="isOwner">
              <Link :href="route('inserat.profile.edit')"
                class="block w-full text-center bg-gray-100 text-gray-700 text-sm font-semibold px-4 py-2.5 rounded-lg hover:bg-gray-200 transition mb-2">
                ✏️ Profil bearbeiten
              </Link>
              <Link :href="route('inserat.media.index')"
                class="block w-full text-center border border-[#e91e8c] text-[#e91e8c] text-sm font-semibold px-4 py-2.5 rounded-lg hover:bg-[#e91e8c]/10 transition">
                🖼️ Medien verwalten
              </Link>
            </template>
            <template v-else-if="isSubscribed">
              <div class="text-center mb-3">
                <p class="text-green-700 text-sm font-semibold">✅ Abonniert</p>
                <p class="text-xs text-gray-500 mt-0.5">Privater Zugang aktiv</p>
              </div>
              <a v-if="profile.whatsapp_number"
                :href="`https://wa.me/${profile.whatsapp_number.replace(/\D/g,'')}`"
                target="_blank"
                class="flex items-center justify-between w-full bg-[#25D366]/10 border border-[#25D366]/30 text-[#25D366] text-sm font-semibold px-4 py-3 rounded-lg hover:bg-[#25D366]/20 transition mb-2">
                <div class="flex items-center gap-2">
                  <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/></svg>
                  WhatsApp
                </div>
                <svg class="w-4 h-4 text-green-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
              </a>
              <button @click="cancelSub" class="w-full text-xs text-gray-400 hover:text-red-500 transition">
                Abonnement kündigen
              </button>
            </template>
            <template v-else-if="hasSubscriptionOffer">
              <div class="text-center mb-3">
                <p class="text-gray-500 text-xs uppercase tracking-wide mb-1">Privater Zugang</p>
                <p class="text-3xl font-black text-gray-900">CHF {{ profile.subscription_price_chf }}</p>
                <p class="text-xs text-gray-500">/ Monat</p>
              </div>
              <form v-if="$page.props.auth.user" @submit.prevent="subscribe">
                <button type="submit" :disabled="subscribing"
                  class="w-full bg-[#e91e8c] hover:bg-[#c91478] disabled:opacity-50 text-white font-bold py-3 rounded-lg transition text-sm">
                  {{ subscribing ? 'Weiterleitung…' : 'Jetzt abonnieren' }}
                </button>
              </form>
              <Link v-else :href="route('register')"
                class="block w-full text-center bg-[#e91e8c] hover:bg-[#c91478] text-white font-bold py-3 rounded-lg transition text-sm">
                Registrieren & Abonnieren
              </Link>
            </template>
          </div>

          <!-- Stats -->
          <div class="bg-white border border-gray-200 rounded-xl p-4 space-y-2 text-sm shadow-sm">
            <div class="flex justify-between text-gray-600">
              <span>👁 Aufrufe</span><span class="text-gray-900 font-semibold">{{ profile.total_views }}</span>
            </div>
            <div class="flex justify-between text-gray-600">
              <span>❤️ Abonnenten</span><span class="text-gray-900 font-semibold">{{ profile.total_subscribers }}</span>
            </div>
            <div v-if="reviews.length" class="flex justify-between text-gray-600">
              <span>⭐ Bewertung</span>
              <span class="text-gray-900 font-semibold">{{ avgRating.toFixed(1) }} ({{ reviews.length }})</span>
            </div>
            <div v-if="profile.city" class="flex justify-between text-gray-600">
              <span>📍 Stadt</span><span class="text-gray-900">{{ profile.city }}</span>
            </div>
            <div v-if="profile.age" class="flex justify-between text-gray-600">
              <span>🎂 Alter</span><span class="text-gray-900">{{ profile.age }} Jahre</span>
            </div>
          </div>
        </div>

        <!-- RIGHT: Content -->
        <div class="flex-1 min-w-0 space-y-5">

          <!-- Header -->
          <div>
            <div class="flex flex-wrap items-center gap-3 mb-2">
              <h1 class="text-2xl font-black text-gray-900">{{ profile.display_name }}</h1>
              <span v-if="profile.category" class="text-xs bg-[#e91e8c]/10 text-[#e91e8c] border border-[#e91e8c]/30 px-2.5 py-0.5 rounded-full">
                {{ profile.category }}
              </span>
            </div>
            <div class="flex flex-wrap gap-2">
              <span v-for="tag in profile.tags" :key="tag"
                class="text-xs bg-gray-100 text-gray-600 border border-gray-200 px-2.5 py-1 rounded-full">
                {{ tag }}
              </span>
            </div>
          </div>

          <!-- Description -->
          <div class="bg-white border border-gray-200 rounded-xl p-5 shadow-sm">
            <p class="text-gray-700 text-sm leading-relaxed whitespace-pre-line">{{ profile.description }}</p>
          </div>

          <!-- Media Tabs -->
          <div class="bg-white border border-gray-200 rounded-xl overflow-hidden shadow-sm">
            <div class="flex border-b border-gray-200">
              <button v-for="tab in tabs" :key="tab.key" @click="activeTab = tab.key"
                class="px-5 py-3 text-sm font-semibold transition border-b-2 -mb-px"
                :class="activeTab === tab.key ? 'border-[#e91e8c] text-[#e91e8c]' : 'border-transparent text-gray-500 hover:text-gray-700'">
                {{ tab.label }} ({{ tab.count }})
              </button>
            </div>
            <div class="p-4">
              <!-- Public Media -->
              <div v-if="activeTab === 'public'">
                <div v-if="publicMedia.length === 0" class="text-center py-10 text-gray-400">Noch keine Fotos.</div>
                <div v-else class="grid grid-cols-3 sm:grid-cols-4 md:grid-cols-5 gap-2">
                  <div v-for="item in publicMedia" :key="item.id"
                    class="aspect-square rounded-lg overflow-hidden cursor-pointer group" @click="openLightbox(item)">
                    <img :src="item.url" :alt="profile.display_name" class="w-full h-full object-cover group-hover:scale-105 transition duration-200" />
                  </div>
                </div>
              </div>
              <!-- Private Media -->
              <div v-if="activeTab === 'private'">
                <template v-if="isOwner || isSubscribed">
                  <div v-if="privateMedia.length === 0" class="text-center py-10 text-gray-400">Noch keine privaten Inhalte.</div>
                  <div v-else class="grid grid-cols-3 sm:grid-cols-4 md:grid-cols-5 gap-2">
                    <div v-for="item in privateMedia" :key="item.id"
                      class="aspect-square rounded-lg overflow-hidden cursor-pointer group" @click="openLightbox(item)">
                      <img v-if="item.type === 'image'" :src="item.url" :alt="profile.display_name" class="w-full h-full object-cover group-hover:scale-105 transition duration-200" />
                      <div v-else class="w-full h-full bg-gray-100 flex items-center justify-center text-gray-700 text-4xl">▶</div>
                    </div>
                  </div>
                </template>
                <div v-else class="text-center py-16">
                  <div class="text-5xl mb-4">🔒</div>
                  <p class="text-gray-700 font-semibold mb-2">Private Inhalte</p>
                  <p class="text-sm text-gray-500">Abonniere für CHF {{ profile.subscription_price_chf }}/Monat</p>
                </div>
              </div>
            </div>
          </div>

          <!-- Review Form -->
          <div v-if="isSubscribed && !hasReviewed" class="bg-white border border-gray-200 rounded-xl p-5 shadow-sm">
            <h2 class="font-semibold text-gray-900 mb-4">Bewertung abgeben</h2>
            <form @submit.prevent="submitReview" class="space-y-4">
              <div class="flex gap-1">
                <button v-for="n in 5" :key="n" type="button" @click="reviewForm.stars = n"
                  class="text-3xl transition" :class="n <= reviewForm.stars ? 'text-yellow-400' : 'text-gray-300'">★</button>
              </div>
              <textarea v-model="reviewForm.comment" rows="3" maxlength="1000" placeholder="Deine Erfahrung…"
                class="w-full bg-white border border-gray-300 text-gray-700 text-sm rounded-lg px-3 py-2 focus:outline-none focus:border-[#e91e8c] resize-none placeholder-gray-400" />
              <button type="submit" :disabled="!reviewForm.stars || submittingReview"
                class="bg-[#e91e8c] hover:bg-[#c91478] disabled:opacity-50 text-white text-sm font-bold px-5 py-2.5 rounded-lg transition">
                {{ submittingReview ? 'Einreichen…' : 'Bewertung einreichen' }}
              </button>
            </form>
          </div>

          <!-- Reviews -->
          <div class="bg-white border border-gray-200 rounded-xl p-5 shadow-sm">
            <h2 class="font-semibold text-gray-900 mb-4">Bewertungen ({{ reviews.length }})</h2>
            <div v-if="!reviews.length" class="text-center py-6 text-gray-400 text-sm">Noch keine Bewertungen.</div>
            <div v-else class="space-y-4">
              <div v-for="r in reviews" :key="r.id" class="border-b border-gray-100 pb-4 last:border-0">
                <div class="flex items-center gap-2 mb-1">
                  <span class="text-yellow-400 text-sm">{{ '★'.repeat(r.stars) }}{{ '☆'.repeat(5 - r.stars) }}</span>
                  <span class="text-sm font-semibold text-gray-700">{{ r.author }}</span>
                  <span class="text-xs text-gray-400 ml-auto">{{ r.created_at }}</span>
                </div>
                <p class="text-sm text-gray-600">{{ r.comment }}</p>
                <div v-if="r.reply" class="mt-2 ml-4 pl-3 border-l-2 border-[#e91e8c]/40 text-sm text-gray-500 italic">
                  <span class="font-semibold text-[#e91e8c]">Antwort: </span>{{ r.reply }}
                </div>
                <div v-if="isOwner && !r.reply" class="mt-2">
                  <button @click="replyTarget = replyTarget === r.id ? null : r.id" class="text-xs text-[#e91e8c] hover:underline">Antworten</button>
                  <div v-if="replyTarget === r.id" class="mt-2 flex gap-2">
                    <input v-model="replyText" type="text" placeholder="Deine Antwort…" maxlength="500"
                      class="flex-1 bg-white border border-gray-300 text-gray-700 rounded px-2 py-1 text-xs focus:outline-none focus:border-[#e91e8c]" />
                    <button @click="submitReply(r.id)" class="bg-[#e91e8c] text-white text-xs px-3 py-1 rounded hover:bg-[#c91478]">Senden</button>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Lightbox -->
    <div v-if="lightboxItem" class="fixed inset-0 bg-black/95 flex items-center justify-center z-50" @click.self="lightboxItem = null">
      <button class="absolute top-4 right-4 text-white text-3xl hover:text-gray-300 w-10 h-10 flex items-center justify-center" @click="lightboxItem = null">✕</button>
      <img v-if="lightboxItem.type === 'image'" :src="lightboxItem.url" class="max-h-[90vh] max-w-[90vw] object-contain rounded" />
    </div>
  </AppLayout>
</template>

<script setup>
import { ref, computed } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';

const props = defineProps({
  profile:             { type: Object, required: true },
  publicMedia:         { type: Array,  default: () => [] },
  privateMedia:        { type: Array,  default: () => [] },
  reviews:             { type: Array,  default: () => [] },
  isOwner:             { type: Boolean, default: false },
  isSubscribed:        { type: Boolean, default: false },
  hasSubscriptionOffer:{ type: Boolean, default: false },
  hasReviewed:         { type: Boolean, default: false },
  subscribed:          { type: Boolean, default: false },
});

const activeTab        = ref('public');
const lightboxItem     = ref(null);
const subscribing      = ref(false);
const submittingReview = ref(false);
const reviewForm       = ref({ stars: 0, comment: '' });
const replyTarget      = ref(null);
const replyText        = ref('');

const tabs = computed(() => [
  { key: 'public',  label: 'Öffentlich', count: props.publicMedia.length },
  { key: 'private', label: 'Privat 🔒',  count: props.privateMedia.length },
]);

const avgRating = computed(() => {
  if (!props.reviews.length) return 0;
  return props.reviews.reduce((s, r) => s + r.stars, 0) / props.reviews.length;
});

function openLightbox(item) { lightboxItem.value = item; }

function subscribe() {
  subscribing.value = true;
  router.post(route('konto.subscribe', props.profile.slug), {}, {
    onFinish: () => { subscribing.value = false; },
  });
}

function cancelSub() {
  if (!confirm('Abonnement wirklich kündigen?')) return;
  router.post(route('konto.cancel', props.profile.slug));
}

function submitReview() {
  if (!reviewForm.value.stars) return;
  submittingReview.value = true;
  router.post(route('konto.review.store', props.profile.slug), reviewForm.value, {
    preserveScroll: true,
    onFinish: () => { submittingReview.value = false; },
    onSuccess: () => { reviewForm.value = { stars: 0, comment: '' }; },
  });
}

function submitReply(reviewId) {
  if (!replyText.value.trim()) return;
  router.post(route('inserat.review.reply', reviewId), { reply: replyText.value }, {
    preserveScroll: true,
    onSuccess: () => { replyTarget.value = null; replyText.value = ''; },
  });
}
</script>
