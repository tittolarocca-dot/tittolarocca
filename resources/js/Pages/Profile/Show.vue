<template>
  <AppLayout>
    <Head :title="profile.display_name" />

    <!-- Success banner -->
    <div v-if="subscribed" class="bg-[#e91e8c] text-white text-center py-3 text-sm font-semibold">
      🎉 Abonnement erfolgreich! Du hast jetzt Zugang zu allen privaten Inhalten.
    </div>

    <!-- Photo Grid -->
    <div class="bg-[#0f0f0f] border-b border-white/5">
      <div class="max-w-7xl mx-auto">
        <div v-if="publicMedia.length" class="flex overflow-x-auto h-52 sm:h-64 md:h-80">
          <div v-for="(item, i) in publicMedia.slice(0,4)" :key="item.id"
            class="shrink-0 cursor-pointer relative"
            :class="i === 0 ? 'w-1/2' : 'w-1/4'"
            @click="openLightbox(item)">
            <!-- i===0: first/biggest image → eager priority; others → lazyload -->
            <img v-if="i === 0"
              :src="item.url"
              :alt="profile.display_name"
              class="w-full h-full object-cover object-top border-r border-white/5"
              loading="eager"
              fetchpriority="high"
              width="640" height="320" />
            <img v-else
              class="lazyload w-full h-full object-cover object-top border-r border-white/5"
              :data-src="item.url"
              src="data:image/gif;base64,R0lGODlhAQABAAAAACH5BAEKAAEALAAAAAABAAEAAAICTAEAOw=="
              :alt="profile.display_name"
              width="320" height="320" />
            <div v-if="i === 3 && publicMedia.length > 4"
              class="absolute inset-0 bg-black/60 flex items-center justify-center text-white font-bold text-xl">
              +{{ publicMedia.length - 4 }}
            </div>
          </div>
        </div>
        <div v-else class="h-40 sm:h-48 bg-white/5 flex items-center justify-center text-gray-500 text-5xl">👤</div>
      </div>
    </div>

    <div class="max-w-7xl mx-auto px-3 sm:px-4 py-4 sm:py-6">
      <div class="flex flex-col md:flex-row gap-4 md:gap-6">

        <!-- LEFT: Contact Sidebar -->
        <div class="md:w-72 shrink-0 space-y-3 md:space-y-4">

          <!-- Kontakt (WhatsApp + Telegram) -->
          <div v-if="profile.whatsapp_number || profile.telegram_username" class="bg-[#1a1a1a] border border-white/8 rounded-xl p-4 space-y-2">
            <p class="text-xs text-gray-400 uppercase tracking-wide mb-2">Kontakt</p>

            <!-- WhatsApp -->
            <a v-if="profile.whatsapp_number"
              :href="`https://wa.me/${profile.whatsapp_number.replace(/\D/g,'')}`"
              target="_blank"
              class="flex items-center justify-center gap-2 w-full bg-[#25D366] hover:bg-[#1fb855] text-white text-sm font-bold px-4 py-3 rounded-lg transition">
              <svg class="w-5 h-5 shrink-0" fill="currentColor" viewBox="0 0 24 24"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/></svg>
              WhatsApp schreiben
            </a>

            <!-- Telegram -->
            <a v-if="profile.telegram_username"
              :href="`https://t.me/${profile.telegram_username.replace('@','')}`"
              target="_blank"
              class="flex items-center justify-center gap-2 w-full bg-[#0088cc] hover:bg-[#0077b5] text-white text-sm font-bold px-4 py-3 rounded-lg transition">
              <svg class="w-5 h-5 shrink-0" fill="currentColor" viewBox="0 0 24 24"><path d="M11.944 0A12 12 0 0 0 0 12a12 12 0 0 0 12 12 12 12 0 0 0 12-12A12 12 0 0 0 12 0a12 12 0 0 0-.056 0zm4.962 7.224c.1-.002.321.023.465.14a.506.506 0 0 1 .171.325c.016.093.036.306.02.472-.18 1.898-.96 6.502-1.36 8.627-.168.9-.499 1.201-.82 1.23-.696.065-1.225-.46-1.9-.902-1.056-.693-1.653-1.124-2.678-1.8-1.185-.78-.417-1.21.258-1.91.177-.184 3.247-2.977 3.307-3.23.007-.032.014-.15-.056-.212s-.174-.041-.249-.024c-.106.024-1.793 1.14-5.061 3.345-.48.33-.913.49-1.302.48-.428-.008-1.252-.241-1.865-.44-.752-.245-1.349-.374-1.297-.789.027-.216.325-.437.893-.663 3.498-1.524 5.83-2.529 6.998-3.014 3.332-1.386 4.025-1.627 4.476-1.635z"/></svg>
              Telegram schreiben
            </a>
          </div>

          <!-- Adresse mit Google Maps -->
          <div v-if="profile.address" class="bg-[#1a1a1a] border border-white/8 rounded-xl p-4">
            <p class="text-xs text-gray-400 uppercase tracking-wide mb-2">Standort</p>
            <div class="flex items-start gap-2 mb-3">
              <svg class="w-4 h-4 text-[#e91e8c] shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M5.05 4.05a7 7 0 119.9 9.9L10 18.9l-4.95-4.95a7 7 0 010-9.9zM10 11a2 2 0 100-4 2 2 0 000 4z" clip-rule="evenodd"/>
              </svg>
              <span class="text-sm text-gray-300 leading-snug">{{ profile.address }}</span>
            </div>
            <a :href="`https://www.google.com/maps/search/?api=1&query=${encodeURIComponent(profile.address)}`"
              target="_blank" rel="noopener"
              class="flex items-center justify-center gap-2 w-full border border-white/10 text-gray-400 text-sm font-semibold px-4 py-2.5 rounded-lg hover:border-[#e91e8c] hover:text-[#e91e8c] transition">
              <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/>
              </svg>
              Auf Google Maps öffnen
            </a>
          </div>

          <!-- Subscribe / Owner CTA -->
          <div class="bg-[#1a1a1a] border border-white/8 rounded-xl p-4">

            <!-- Owner -->
            <template v-if="isOwner">
              <Link :href="route('inserat.profile.edit')"
                class="block w-full text-center bg-white/5 text-gray-300 text-sm font-semibold px-4 py-2.5 rounded-lg hover:bg-white/10 transition mb-2">
                ✏️ Profil bearbeiten
              </Link>
              <Link :href="route('inserat.media.index')"
                class="block w-full text-center border border-[#e91e8c] text-[#e91e8c] text-sm font-semibold px-4 py-2.5 rounded-lg hover:bg-[#e91e8c]/10 transition">
                🖼️ Medien verwalten
              </Link>
            </template>

            <!-- Aktiver Trial -->
            <template v-else-if="isTrialing">
              <div class="text-center mb-4">
                <span class="inline-block bg-purple-900/30 text-purple-300 text-xs font-bold px-3 py-1 rounded-full mb-2">
                  🎁 Gratis-Test aktiv
                </span>
                <p class="text-2xl font-black text-white">{{ trialDaysLeft }} Tage</p>
                <p class="text-xs text-gray-400 mt-0.5">noch bis {{ trialEndsAt }}</p>
              </div>
              <div class="bg-purple-900/20 rounded-lg p-3 mb-3 text-xs text-purple-300 text-center">
                Privater Zugang bis {{ trialEndsAt }} kostenlos
              </div>
              <p class="text-xs text-gray-400 text-center mb-3">Danach für CHF {{ profile.subscription_price_chf }}/Monat weiter</p>
              <form @submit.prevent="subscribe" class="mb-2">
                <button type="submit" :disabled="subscribing"
                  class="w-full bg-[#e91e8c] hover:bg-[#c91478] disabled:opacity-50 text-white font-bold py-2.5 rounded-lg transition text-sm">
                  {{ subscribing ? 'Weiterleitung…' : 'Jetzt abonnieren' }}
                </button>
              </form>
              <button @click="cancelSub" class="w-full text-xs text-gray-500 hover:text-red-400 transition">
                Test beenden
              </button>
            </template>

            <!-- Aktives bezahltes Abo -->
            <template v-else-if="isSubscribed">
              <div class="text-center mb-3">
                <p class="text-green-400 text-sm font-semibold">✅ Abonniert</p>
                <p class="text-xs text-gray-400 mt-0.5">Privater Zugang aktiv</p>
              </div>
              <button @click="cancelSub" class="w-full text-xs text-gray-500 hover:text-red-400 transition">
                Abonnement kündigen
              </button>
            </template>

            <!-- Nicht eingeloggt -->
            <template v-else-if="!$page.props.auth.user">
              <div class="text-center mb-4">
                <p class="text-gray-400 text-xs uppercase tracking-wide mb-2">Privater Zugang</p>
                <div class="bg-purple-900/20 border border-purple-700/30 rounded-lg p-3 mb-3">
                  <p class="text-purple-300 font-bold text-sm">🎁 3 Tage gratis testen</p>
                  <p class="text-xs text-purple-400 mt-0.5">Danach CHF {{ profile.subscription_price_chf }}/Monat</p>
                </div>
              </div>
              <Link :href="route('register')"
                class="block w-full text-center bg-purple-600 hover:bg-purple-700 text-white font-bold py-3 rounded-lg transition text-sm mb-2">
                Kostenlos registrieren & testen
              </Link>
              <Link :href="route('login')"
                class="block w-full text-center border border-white/10 text-gray-400 text-sm font-semibold py-2.5 rounded-lg hover:border-white/20 transition">
                Bereits registriert? Anmelden
              </Link>
            </template>

            <!-- Eingeloggt, Trial verfügbar -->
            <template v-else-if="hasSubscriptionOffer && !hasTrialed">
              <div class="text-center mb-4">
                <p class="text-gray-400 text-xs uppercase tracking-wide mb-2">Privater Zugang</p>
                <div class="bg-purple-900/20 border border-purple-700/30 rounded-lg p-3 mb-3">
                  <p class="text-purple-300 font-bold text-sm">🎁 3 Tage gratis testen</p>
                  <p class="text-xs text-purple-400 mt-0.5">Danach CHF {{ profile.subscription_price_chf }}/Monat</p>
                </div>
              </div>
              <form @submit.prevent="startTrial" class="mb-2">
                <button type="submit" :disabled="trialing"
                  class="w-full bg-purple-600 hover:bg-purple-700 disabled:opacity-50 text-white font-bold py-3 rounded-lg transition text-sm">
                  {{ trialing ? 'Wird aktiviert…' : '3 Tage gratis testen' }}
                </button>
              </form>
              <form @submit.prevent="subscribe">
                <button type="submit" :disabled="subscribing"
                  class="w-full border border-[#e91e8c] text-[#e91e8c] font-semibold py-2.5 rounded-lg hover:bg-[#e91e8c]/10 transition text-sm">
                  {{ subscribing ? 'Weiterleitung…' : `Direkt abonnieren · CHF ${profile.subscription_price_chf}/Mo` }}
                </button>
              </form>
            </template>

            <!-- Eingeloggt, Trial bereits genutzt -->
            <template v-else-if="hasSubscriptionOffer && hasTrialed">
              <div class="text-center mb-3">
                <p class="text-gray-400 text-xs uppercase tracking-wide mb-1">Privater Zugang</p>
                <p class="text-3xl font-black text-white">CHF {{ profile.subscription_price_chf }}</p>
                <p class="text-xs text-gray-400">/ Monat</p>
              </div>
              <form @submit.prevent="subscribe">
                <button type="submit" :disabled="subscribing"
                  class="w-full bg-[#e91e8c] hover:bg-[#c91478] disabled:opacity-50 text-white font-bold py-3 rounded-lg transition text-sm">
                  {{ subscribing ? 'Weiterleitung…' : 'Jetzt abonnieren' }}
                </button>
              </form>
            </template>

          </div>

          <!-- Stats -->
          <div class="bg-[#1a1a1a] border border-white/8 rounded-xl p-4 space-y-2 text-sm">
            <div class="flex justify-between text-gray-400">
              <span>👁 Aufrufe</span><span class="text-white font-semibold">{{ profile.total_views }}</span>
            </div>
            <div class="flex justify-between text-gray-400">
              <span>❤️ Abonnenten</span><span class="text-white font-semibold">{{ profile.total_subscribers }}</span>
            </div>
            <div v-if="reviews.length" class="flex justify-between text-gray-400">
              <span>⭐ Bewertung</span>
              <span class="text-white font-semibold">{{ avgRating.toFixed(1) }} ({{ reviews.length }})</span>
            </div>
            <div v-if="profile.city" class="flex justify-between text-gray-400">
              <span>📍 Stadt</span><span class="text-gray-300">{{ profile.city }}</span>
            </div>
            <div v-if="profile.age" class="flex justify-between text-gray-400">
              <span>🎂 Alter</span><span class="text-gray-300">{{ profile.age }} Jahre</span>
            </div>
            <div class="flex justify-between text-gray-400">
              <span>📅 Inseriert</span><span class="text-gray-300">{{ profile.created_at }}</span>
            </div>
          </div>
        </div>

        <!-- RIGHT: Content -->
        <div class="flex-1 min-w-0 space-y-5">

          <!-- Header -->
          <div>
            <div class="flex flex-wrap items-center gap-3 mb-2">
              <h1 class="text-2xl font-black text-white">{{ profile.display_name }}</h1>
              <span v-if="profile.category" class="text-xs bg-[#e91e8c]/10 text-[#e91e8c] border border-[#e91e8c]/30 px-2.5 py-0.5 rounded-full">
                {{ profile.category }}
              </span>
            </div>
            <div class="flex flex-wrap gap-2">
              <span v-for="tag in profile.tags" :key="tag"
                class="text-xs bg-white/5 text-gray-400 border border-white/10 px-2.5 py-1 rounded-full">
                {{ tag }}
              </span>
            </div>
          </div>

          <!-- Description -->
          <div class="bg-[#1a1a1a] border border-white/8 rounded-xl p-5">
            <p class="text-gray-300 text-sm leading-relaxed whitespace-pre-line">{{ profile.description }}</p>
          </div>

          <!-- Media Tabs -->
          <div class="bg-[#1a1a1a] border border-white/8 rounded-xl overflow-hidden">
            <div class="flex border-b border-white/8">
              <button v-for="tab in tabs" :key="tab.key" @click="activeTab = tab.key"
                class="px-5 py-3 text-sm font-semibold transition border-b-2 -mb-px"
                :class="activeTab === tab.key ? 'border-[#e91e8c] text-[#e91e8c]' : 'border-transparent text-gray-500 hover:text-gray-300'">
                {{ tab.label }} ({{ tab.count }})
              </button>
            </div>
            <div class="p-4">
              <!-- Public Media -->
              <div v-if="activeTab === 'public'">
                <div v-if="publicMedia.length === 0" class="text-center py-10 text-gray-500">Noch keine Fotos.</div>
                <div v-else class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 gap-2">
                  <div v-for="item in publicMedia" :key="item.id"
                    class="aspect-square rounded-lg overflow-hidden cursor-pointer group" @click="openLightbox(item)">
                    <img
                      class="lazyload w-full h-full object-cover object-top group-hover:scale-105 transition duration-200"
                      :data-src="item.url"
                      src="data:image/gif;base64,R0lGODlhAQABAAAAACH5BAEKAAEALAAAAAABAAEAAAICTAEAOw=="
                      :alt="profile.display_name"
                      width="200" height="200" />
                  </div>
                </div>
              </div>
              <!-- Private Media -->
              <div v-if="activeTab === 'private'">
                <!-- Subscriber / Owner / Trialing: full access -->
                <template v-if="isOwner || isSubscribed || isTrialing">
                  <div v-if="privateMedia.length === 0" class="text-center py-10 text-gray-500">Noch keine privaten Inhalte.</div>
                  <div v-else class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 gap-2">
                    <div v-for="item in privateMedia" :key="item.id"
                      class="rounded-lg overflow-hidden cursor-pointer group"
                      :class="item.type === 'image' ? 'aspect-square' : ''"
                      @click="openLightbox(item)">
                      <img v-if="item.type === 'image'"
                        class="lazyload w-full h-full object-cover object-top group-hover:scale-105 transition duration-200"
                        :data-src="item.url"
                        src="data:image/gif;base64,R0lGODlhAQABAAAAACH5BAEKAAEALAAAAAABAAEAAAICTAEAOw=="
                        :alt="profile.display_name"
                        width="200" height="200" />
                      <div v-else class="relative bg-black aspect-video flex items-center justify-center">
                        <video :src="item.url" class="w-full h-full object-contain" preload="metadata" />
                        <div class="absolute inset-0 flex items-center justify-center bg-black/30 group-hover:bg-black/10 transition">
                          <div class="w-12 h-12 rounded-full bg-white/90 flex items-center justify-center shadow">
                            <svg class="w-5 h-5 text-gray-800 ml-0.5" fill="currentColor" viewBox="0 0 24 24"><path d="M8 5v14l11-7z"/></svg>
                          </div>
                        </div>
                      </div>
                    </div>
                  </div>
                </template>

                <!-- Non-subscriber: blurred preview grid -->
                <template v-else-if="privateMediaCount > 0">
                  <div class="relative">
                    <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 gap-2">
                      <div v-for="i in Math.min(privateMediaCount, 10)" :key="i"
                        class="aspect-square rounded-lg overflow-hidden relative select-none">
                        <!-- Gradient background that mimics a blurred photo -->
                        <div class="absolute inset-0 scale-110"
                          :style="`background: ${blurGradients[(i - 1) % blurGradients.length]}; filter: blur(10px) brightness(0.65);`" />
                        <!-- Lock icon overlay -->
                        <div class="absolute inset-0 flex items-center justify-center">
                          <svg class="w-7 h-7 text-white/80 drop-shadow" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M5 9V7a5 5 0 0110 0v2a2 2 0 012 2v5a2 2 0 01-2 2H5a2 2 0 01-2-2v-5a2 2 0 012-2zm8-2v2H7V7a3 3 0 016 0z" clip-rule="evenodd"/>
                          </svg>
                        </div>
                      </div>
                    </div>

                    <!-- Fade + subscribe CTA overlay at bottom -->
                    <div class="absolute inset-x-0 bottom-0 pt-24 bg-gradient-to-t from-[#1a1a1a] via-[#1a1a1a]/95 to-transparent flex flex-col items-center pb-4 pointer-events-none">
                      <svg class="w-10 h-10 text-gray-500 mb-2" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M5 9V7a5 5 0 0110 0v2a2 2 0 012 2v5a2 2 0 01-2 2H5a2 2 0 01-2-2v-5a2 2 0 012-2zm8-2v2H7V7a3 3 0 016 0z" clip-rule="evenodd"/>
                      </svg>
                      <p class="font-bold text-white text-sm mb-0.5">{{ privateMediaCount }} private Inhalte</p>
                      <p class="text-xs text-gray-400 mb-3">Freischalten für CHF {{ profile.subscription_price_chf }}/Monat</p>
                      <template v-if="!$page.props.auth.user">
                        <Link :href="route('register')"
                          class="pointer-events-auto bg-purple-600 hover:bg-purple-700 text-white text-sm font-bold px-6 py-2.5 rounded-lg transition">
                          Kostenlos testen – 3 Tage gratis
                        </Link>
                      </template>
                      <template v-else-if="!hasTrialed">
                        <button @click="startTrial" :disabled="trialing"
                          class="pointer-events-auto bg-purple-600 hover:bg-purple-700 disabled:opacity-50 text-white text-sm font-bold px-6 py-2.5 rounded-lg transition">
                          {{ trialing ? 'Wird aktiviert…' : '3 Tage gratis testen' }}
                        </button>
                      </template>
                      <template v-else>
                        <button @click="subscribe" :disabled="subscribing"
                          class="pointer-events-auto bg-[#e91e8c] hover:bg-[#c91478] disabled:opacity-50 text-white text-sm font-bold px-6 py-2.5 rounded-lg transition">
                          {{ subscribing ? 'Weiterleitung…' : `Jetzt abonnieren · CHF ${profile.subscription_price_chf}/Mo` }}
                        </button>
                      </template>
                    </div>
                  </div>
                </template>

                <div v-else class="text-center py-10 text-gray-500">Noch keine privaten Inhalte.</div>
              </div>
            </div>
          </div>

          <!-- Review Form -->
          <div v-if="isSubscribed && !hasReviewed" class="bg-[#1a1a1a] border border-white/8 rounded-xl p-5">
            <h2 class="font-semibold text-white mb-4">Bewertung abgeben</h2>
            <form @submit.prevent="submitReview" class="space-y-4">
              <div class="flex gap-1">
                <button v-for="n in 5" :key="n" type="button" @click="reviewForm.stars = n"
                  class="text-3xl transition" :class="n <= reviewForm.stars ? 'text-yellow-400' : 'text-gray-600'">★</button>
              </div>
              <textarea v-model="reviewForm.comment" rows="3" maxlength="1000" placeholder="Deine Erfahrung…"
                class="w-full bg-[#111] border border-white/10 text-gray-200 text-sm rounded-lg px-3 py-2 focus:outline-none focus:border-[#e91e8c] resize-none placeholder-gray-600" />
              <button type="submit" :disabled="!reviewForm.stars || submittingReview"
                class="bg-[#e91e8c] hover:bg-[#c91478] disabled:opacity-50 text-white text-sm font-bold px-5 py-2.5 rounded-lg transition">
                {{ submittingReview ? 'Einreichen…' : 'Bewertung einreichen' }}
              </button>
            </form>
          </div>

          <!-- Reviews -->
          <div class="bg-[#1a1a1a] border border-white/8 rounded-xl p-5">
            <h2 class="font-semibold text-white mb-4">Bewertungen ({{ reviews.length }})</h2>
            <div v-if="!reviews.length" class="text-center py-6 text-gray-500 text-sm">Noch keine Bewertungen.</div>
            <div v-else class="space-y-4">
              <div v-for="r in reviews" :key="r.id" class="border-b border-white/5 pb-4 last:border-0">
                <div class="flex items-center gap-2 mb-1">
                  <span class="text-yellow-400 text-sm">{{ '★'.repeat(r.stars) }}{{ '☆'.repeat(5 - r.stars) }}</span>
                  <span class="text-sm font-semibold text-gray-300">{{ r.author }}</span>
                  <span class="text-xs text-gray-500 ml-auto">{{ r.created_at }}</span>
                </div>
                <p class="text-sm text-gray-400">{{ r.comment }}</p>
                <div v-if="r.reply" class="mt-2 ml-4 pl-3 border-l-2 border-[#e91e8c]/40 text-sm text-gray-500 italic">
                  <span class="font-semibold text-[#e91e8c]">Antwort: </span>{{ r.reply }}
                </div>
                <div v-if="isOwner && !r.reply" class="mt-2">
                  <button @click="replyTarget = replyTarget === r.id ? null : r.id" class="text-xs text-[#e91e8c] hover:underline">Antworten</button>
                  <div v-if="replyTarget === r.id" class="mt-2 flex gap-2">
                    <input v-model="replyText" type="text" placeholder="Deine Antwort…" maxlength="500"
                      class="flex-1 bg-[#111] border border-white/10 text-gray-200 rounded px-2 py-1 text-xs focus:outline-none focus:border-[#e91e8c]" />
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
    <div v-if="lightboxItem" class="fixed inset-0 bg-black/95 flex items-center justify-center z-50 p-4" @click.self="lightboxItem = null">
      <button class="absolute top-4 right-4 text-white text-3xl hover:text-gray-300 w-10 h-10 flex items-center justify-center z-10" @click="lightboxItem = null">✕</button>
      <img v-if="lightboxItem.type === 'image'"
        :src="lightboxItem.url"
        class="max-h-[90vh] max-w-[90vw] object-contain rounded" />
      <video v-else
        :src="lightboxItem.url"
        class="max-h-[90vh] max-w-[90vw] rounded"
        controls
        autoplay
        playsinline
        controlsList="nodownload"
        @click.stop />
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
  isTrialing:          { type: Boolean, default: false },
  trialEndsAt:         { type: String,  default: null },
  trialDaysLeft:       { type: Number,  default: 0 },
  hasTrialed:          { type: Boolean, default: false },
  hasSubscriptionOffer:{ type: Boolean, default: false },
  hasReviewed:         { type: Boolean, default: false },
  subscribed:          { type: Boolean, default: false },
  privateMediaCount:   { type: Number,  default: 0 },
});

const activeTab        = ref('public');
const lightboxItem     = ref(null);
const subscribing      = ref(false);
const trialing         = ref(false);
const submittingReview = ref(false);
const reviewForm       = ref({ stars: 0, comment: '' });
const replyTarget      = ref(null);
const replyText        = ref('');

const blurGradients = [
  'linear-gradient(135deg, #f093fb, #f5576c)',
  'linear-gradient(135deg, #4facfe, #00f2fe)',
  'linear-gradient(135deg, #f7971e, #ffd200)',
  'linear-gradient(135deg, #a18cd1, #fbc2eb)',
  'linear-gradient(135deg, #84fab0, #8fd3f4)',
  'linear-gradient(135deg, #fd7043, #e91e8c)',
  'linear-gradient(135deg, #30cfd0, #330867)',
  'linear-gradient(135deg, #f6d365, #fda085)',
];

const tabs = computed(() => [
  { key: 'public',  label: 'Öffentlich', count: props.publicMedia.length },
  { key: 'private', label: 'Privat 🔒',  count: props.privateMediaCount || props.privateMedia.length },
]);

const avgRating = computed(() => {
  if (!props.reviews.length) return 0;
  return props.reviews.reduce((s, r) => s + r.stars, 0) / props.reviews.length;
});

function openLightbox(item) { lightboxItem.value = item; }

function startTrial() {
  trialing.value = true;
  router.post(route('konto.trial', props.profile.slug), {}, {
    onFinish: () => { trialing.value = false; },
  });
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
