<template>
  <AppLayout>
    <Head :title="profile.display_name" />

    <!-- Success banner after subscribing -->
    <div v-if="subscribed" class="bg-green-500 text-white text-center py-3 text-sm font-semibold">
      🎉 Abonnement erfolgreich! Du hast jetzt Zugang zu allen privaten Inhalten.
    </div>

    <div class="max-w-5xl mx-auto px-4 py-8">

      <!-- Header -->
      <div class="bg-white rounded-xl border border-gray-200 p-6 mb-6">
        <div class="flex flex-col sm:flex-row gap-6">

          <!-- Avatar / First public photo -->
          <div class="shrink-0">
            <div class="w-32 h-32 rounded-xl overflow-hidden bg-pink-100 flex items-center justify-center">
              <img v-if="publicMedia[0]" :src="publicMedia[0].url" alt="Profilfoto" class="w-full h-full object-cover" />
              <span v-else class="text-5xl">👤</span>
            </div>
          </div>

          <!-- Info -->
          <div class="flex-1">
            <div class="flex flex-wrap items-center gap-3 mb-2">
              <h1 class="text-2xl font-bold text-gray-900">{{ profile.display_name }}</h1>
              <span v-if="profile.age" class="bg-pink-100 text-pink-700 text-sm font-semibold px-2.5 py-0.5 rounded-full">
                {{ profile.age }} J.
              </span>
              <span v-if="profile.city" class="bg-gray-100 text-gray-600 text-sm px-2.5 py-0.5 rounded-full">
                📍 {{ profile.city }}
              </span>
            </div>

            <div class="flex flex-wrap gap-2 mb-3">
              <span v-for="tag in profile.tags" :key="tag"
                class="text-xs bg-pink-50 text-pink-600 border border-pink-200 px-2 py-0.5 rounded-full">
                {{ tag }}
              </span>
            </div>

            <p class="text-gray-600 text-sm leading-relaxed line-clamp-3">{{ profile.description }}</p>

            <div class="flex gap-6 mt-4 text-sm text-gray-500">
              <span>👁 {{ profile.total_views }} Aufrufe</span>
              <span>❤️ {{ profile.total_subscribers }} Abonnenten</span>
              <span v-if="reviews.length">⭐ {{ avgRating.toFixed(1) }} ({{ reviews.length }} Bewertungen)</span>
            </div>
          </div>

          <!-- Actions -->
          <div class="shrink-0 flex flex-col gap-3 min-w-[160px]">
            <!-- Owner -->
            <template v-if="isOwner">
              <Link :href="route('inserat.profile.edit')"
                class="w-full text-center bg-gray-100 text-gray-700 text-sm font-semibold px-4 py-2.5 rounded-lg hover:bg-gray-200 transition">
                ✏️ Bearbeiten
              </Link>
              <Link :href="route('inserat.media.index')"
                class="w-full text-center border border-pink-300 text-pink-600 text-sm font-semibold px-4 py-2.5 rounded-lg hover:bg-pink-50 transition">
                🖼️ Medien
              </Link>
            </template>

            <!-- Subscribed -->
            <template v-else-if="isSubscribed">
              <div class="text-center bg-green-50 border border-green-200 rounded-lg px-4 py-2.5">
                <p class="text-green-700 text-sm font-semibold">✅ Abonniert</p>
                <p class="text-xs text-green-600 mt-0.5">Privater Zugang aktiv</p>
              </div>
              <div v-if="profile.whatsapp_number" class="text-center bg-green-600 text-white rounded-lg px-4 py-2.5">
                <a :href="`https://wa.me/${profile.whatsapp_number.replace(/\D/g,'')}`" target="_blank"
                  class="text-sm font-semibold">💬 WhatsApp</a>
              </div>
              <form @submit.prevent="cancelSub">
                <button type="submit" class="w-full text-xs text-gray-400 hover:text-red-600 transition mt-1">
                  Abonnement kündigen
                </button>
              </form>
            </template>

            <!-- Subscribe CTA -->
            <template v-else-if="hasSubscriptionOffer">
              <div class="bg-pink-50 border border-pink-200 rounded-lg p-3 text-center">
                <p class="text-pink-800 text-xs font-semibold uppercase tracking-wide mb-1">Privater Zugang</p>
                <p class="text-2xl font-bold text-pink-600">CHF {{ profile.subscription_price_chf }}</p>
                <p class="text-xs text-pink-500">/ Monat</p>
              </div>
              <form v-if="$page.props.auth.user" @submit.prevent="subscribe">
                <PrimaryButton type="submit" :loading="subscribing" full-width>
                  Jetzt abonnieren
                </PrimaryButton>
              </form>
              <Link v-else :href="route('register')"
                class="w-full text-center bg-pink-600 text-white text-sm font-bold px-4 py-2.5 rounded-lg hover:bg-pink-700 transition block">
                Registrieren & Abonnieren
              </Link>
            </template>
          </div>
        </div>
      </div>

      <!-- Media Tabs -->
      <div class="bg-white rounded-xl border border-gray-200 mb-6">
        <div class="flex border-b border-gray-100">
          <button v-for="tab in tabs" :key="tab.key"
            @click="activeTab = tab.key"
            class="px-5 py-3 text-sm font-semibold transition border-b-2 -mb-px"
            :class="activeTab === tab.key ? 'border-pink-500 text-pink-600' : 'border-transparent text-gray-500 hover:text-gray-700'">
            {{ tab.label }} ({{ tab.count }})
          </button>
        </div>

        <div class="p-4">
          <!-- Public Media -->
          <div v-if="activeTab === 'public'">
            <div v-if="publicMedia.length === 0" class="text-center py-12 text-gray-400">
              Noch keine öffentlichen Fotos.
            </div>
            <div v-else class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-3">
              <div v-for="item in publicMedia" :key="item.id"
                class="aspect-square rounded-lg overflow-hidden cursor-pointer"
                @click="openLightbox(item)">
                <img :src="item.url" :alt="profile.display_name" class="w-full h-full object-cover hover:scale-105 transition duration-200" />
              </div>
            </div>
          </div>

          <!-- Private Media -->
          <div v-if="activeTab === 'private'">
            <template v-if="isOwner || isSubscribed">
              <div v-if="privateMedia.length === 0" class="text-center py-12 text-gray-400">
                Noch keine privaten Inhalte verfügbar.
              </div>
              <div v-else class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-3">
                <div v-for="item in privateMedia" :key="item.id"
                  class="aspect-square rounded-lg overflow-hidden cursor-pointer"
                  @click="openLightbox(item)">
                  <img v-if="item.type === 'image'" :src="item.url" :alt="profile.display_name" class="w-full h-full object-cover hover:scale-105 transition duration-200" />
                  <div v-else class="w-full h-full bg-gray-800 flex items-center justify-center text-white text-4xl">▶</div>
                </div>
              </div>
            </template>
            <div v-else class="text-center py-16">
              <div class="text-5xl mb-4">🔒</div>
              <p class="font-semibold text-gray-700 mb-2">Private Inhalte</p>
              <p class="text-sm text-gray-500 mb-6">Abonniere für CHF {{ profile.subscription_price_chf }}/Monat um alle privaten Fotos & Videos zu sehen.</p>
            </div>
          </div>
        </div>
      </div>

      <!-- Reviews -->
      <div v-if="reviews.length" class="bg-white rounded-xl border border-gray-200 p-6">
        <h2 class="font-semibold text-gray-900 mb-4">Bewertungen ({{ reviews.length }})</h2>
        <div class="space-y-4">
          <div v-for="r in reviews" :key="r.id" class="border-b border-gray-100 pb-4 last:border-0">
            <div class="flex items-center gap-2 mb-1">
              <span class="text-yellow-400">{{ '★'.repeat(r.rating) }}{{ '☆'.repeat(5 - r.rating) }}</span>
              <span class="text-sm font-semibold text-gray-700">{{ r.author }}</span>
              <span class="text-xs text-gray-400 ml-auto">{{ r.created_at }}</span>
            </div>
            <p class="text-sm text-gray-600">{{ r.body }}</p>
          </div>
        </div>
      </div>
    </div>

    <!-- Lightbox -->
    <div v-if="lightboxItem" class="fixed inset-0 bg-black/90 flex items-center justify-center z-50"
      @click.self="lightboxItem = null">
      <button class="absolute top-4 right-4 text-white text-3xl hover:text-gray-300" @click="lightboxItem = null">✕</button>
      <img v-if="lightboxItem.type === 'image'" :src="lightboxItem.url" class="max-h-screen max-w-screen object-contain" />
    </div>
  </AppLayout>
</template>

<script setup>
import { ref, computed } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';

const props = defineProps({
  profile:             { type: Object, required: true },
  publicMedia:         { type: Array,  default: () => [] },
  privateMedia:        { type: Array,  default: () => [] },
  reviews:             { type: Array,  default: () => [] },
  isOwner:             { type: Boolean, default: false },
  isSubscribed:        { type: Boolean, default: false },
  hasSubscriptionOffer:{ type: Boolean, default: false },
  subscribed:          { type: Boolean, default: false },
});

const activeTab     = ref('public');
const lightboxItem  = ref(null);
const subscribing   = ref(false);

const tabs = computed(() => [
  { key: 'public',  label: 'Öffentlich', count: props.publicMedia.length },
  { key: 'private', label: 'Privat 🔒',  count: props.privateMedia.length },
]);

const avgRating = computed(() => {
  if (!props.reviews.length) return 0;
  return props.reviews.reduce((s, r) => s + r.rating, 0) / props.reviews.length;
});

function openLightbox(item) {
  lightboxItem.value = item;
}

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
</script>
