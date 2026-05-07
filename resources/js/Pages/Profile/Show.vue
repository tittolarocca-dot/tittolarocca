<template>
  <AppLayout>
    <Head :title="profile.display_name" />

    <!-- Success banner -->
    <div v-if="subscribed" class="bg-[#e35d8f] text-white text-center py-3 text-sm font-semibold">
      🎉 Abonnement erfolgreich! Du hast jetzt Zugang zu allen privaten Inhalten.
    </div>

    <!-- ── PROFILE HEADER ───────────────────────────────────────────────────── -->
    <div class="bg-[#111] border-b border-white/5">
      <div class="max-w-5xl mx-auto px-6 py-5">
        <!-- Breadcrumb -->
        <nav class="flex items-center gap-1.5 text-xs text-gray-500 mb-4 flex-wrap">
          <Link :href="route('home')" class="hover:text-[#e35d8f] transition">Startseite</Link>
          <span>/</span>
          <Link v-if="profile.city" :href="route('city', profile.city_slug)" class="hover:text-[#e35d8f] transition">{{ profile.city }}</Link>
          <span v-if="profile.city">/</span>
          <Link v-if="profile.category" :href="route('category', profile.category_slug)" class="hover:text-[#e35d8f] transition">{{ profile.category }}</Link>
          <span v-if="profile.category">/</span>
          <span class="text-gray-400">{{ profile.display_name }}</span>
        </nav>

        <!-- Name + badges -->
        <div class="flex flex-wrap items-center gap-3">
          <h1 class="text-3xl font-black text-white">{{ profile.display_name }}</h1>
          <span v-if="profile.verification_status === 'approved'"
            class="inline-flex items-center gap-1.5 text-xs bg-green-900/30 text-green-400 border border-green-700/40 px-3 py-1 rounded-full font-semibold">
            <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
            Verifiziert
          </span>
          <span v-if="profile.category"
            class="text-xs bg-[#e35d8f]/10 text-[#e35d8f] border border-[#e35d8f]/30 px-3 py-1 rounded-full font-semibold">
            {{ profile.category }}
          </span>
          <!-- Rating pills -->
          <div v-if="reviews.length" class="flex items-center gap-1 text-yellow-400 text-sm">
            <span>★</span>
            <span class="text-white font-bold text-sm">{{ avgRating.toFixed(1) }}</span>
            <span class="text-gray-500 text-xs">({{ reviews.length }})</span>
          </div>
        </div>

        <!-- Quick info pills -->
        <div class="flex flex-wrap gap-2 mt-3">
          <span v-if="profile.city" class="inline-flex items-center gap-1 text-xs text-gray-400 bg-white/5 border border-white/8 px-3 py-1.5 rounded-full">
            <svg class="w-3.5 h-3.5 text-[#e35d8f]" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M5.05 4.05a7 7 0 119.9 9.9L10 18.9l-4.95-4.95a7 7 0 010-9.9zM10 11a2 2 0 100-4 2 2 0 000 4z" clip-rule="evenodd"/></svg>
            {{ profile.city }}
          </span>
          <span v-if="profile.age" class="inline-flex items-center gap-1 text-xs text-gray-400 bg-white/5 border border-white/8 px-3 py-1.5 rounded-full">
            <svg class="w-3.5 h-3.5 text-[#e35d8f]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            {{ profile.age }} Jahre
          </span>
          <span class="inline-flex items-center gap-1 text-xs text-gray-400 bg-white/5 border border-white/8 px-3 py-1.5 rounded-full">
            <svg class="w-3.5 h-3.5 text-[#e35d8f]" fill="currentColor" viewBox="0 0 20 20"><path d="M10 12a2 2 0 100-4 2 2 0 000 4z"/><path fill-rule="evenodd" d="M.458 10C1.732 5.943 5.522 3 10 3s8.268 2.943 9.542 7c-1.274 4.057-5.064 7-9.542 7S1.732 14.057.458 10zM14 10a4 4 0 11-8 0 4 4 0 018 0z" clip-rule="evenodd"/></svg>
            {{ profile.total_views }} Aufrufe
          </span>
          <span class="inline-flex items-center gap-1 text-xs text-gray-400 bg-white/5 border border-white/8 px-3 py-1.5 rounded-full">
            <svg class="w-3.5 h-3.5 text-[#e35d8f]" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M3.172 5.172a4 4 0 015.656 0L10 6.343l1.172-1.171a4 4 0 115.656 5.656L10 17.657l-6.828-6.829a4 4 0 010-5.656z" clip-rule="evenodd"/></svg>
            {{ profile.total_subscribers }} Abonnenten
          </span>
        </div>
      </div>
    </div>

    <!-- ── HERO GALLERY ─────────────────────────────────────────────────────── -->
    <div class="bg-[#0f0f0f] pb-4">
      <div class="max-w-5xl mx-auto px-6">
        <!-- Hauptfoto -->
        <div v-if="mainMedia"
          class="rounded-2xl overflow-hidden bg-black cursor-pointer flex justify-center"
          @click="openLightbox(mainMedia)">
          <img
            :src="mainMedia.url"
            :alt="profile.display_name"
            class="block max-h-[560px] w-auto max-w-full h-auto"
            loading="eager"
            fetchpriority="high" />
        </div>
        <!-- Fallback: kein Hauptfoto gesetzt -->
        <div v-else-if="publicMedia.length"
          class="flex gap-1 h-[300px] sm:h-[380px] md:h-[440px] overflow-hidden rounded-2xl">
          <div class="relative flex-[2] min-w-0 cursor-pointer group" @click="openLightbox(publicMedia[0])">
            <img
              :src="publicMedia[0].url"
              :alt="profile.display_name"
              class="w-full h-full object-cover object-top transition duration-300 group-hover:scale-105"
              loading="eager" fetchpriority="high" width="800" height="440" />
          </div>
          <div class="flex flex-col gap-1 flex-1 min-w-0">
            <div v-for="(item, i) in publicMedia.slice(1, 4)" :key="item.id"
              class="relative flex-1 cursor-pointer group overflow-hidden"
              @click="openLightbox(item)">
              <img
                class="lazyload w-full h-full object-cover object-top transition duration-300 group-hover:scale-105"
                :data-src="item.url"
                src="data:image/gif;base64,R0lGODlhAQABAAAAACH5BAEKAAEALAAAAAABAAEAAAICTAEAOw=="
                :alt="profile.display_name" width="300" height="200" />
              <div v-if="i === 2 && publicMedia.length > 4"
                class="absolute inset-0 bg-black/55 flex flex-col items-center justify-center text-white font-bold">
                <span class="text-2xl">+{{ publicMedia.length - 4 }}</span>
                <span class="text-xs mt-1 font-normal opacity-80">weitere</span>
              </div>
            </div>
          </div>
        </div>
        <div v-else class="h-48 bg-white/5 flex items-center justify-center text-gray-500 text-6xl rounded-2xl">👤</div>
      </div>
    </div>

    <!-- ── MAIN CONTENT ─────────────────────────────────────────────────────── -->
    <div class="max-w-5xl mx-auto px-6 py-6 space-y-5">
      <div class="flex flex-col lg:flex-row gap-5 items-start">

        <!-- ── LEFT SIDEBAR ──────────────────────────────────────────────── -->
        <div class="w-full lg:w-72 shrink-0 space-y-4">

          <!-- Contact Buttons (FIRST) -->
          <div v-if="profile.whatsapp_number || profile.phone_number || profile.telegram_username" class="bg-[#1a1a1a] border border-white/8 rounded-2xl p-5 space-y-3">
            <p class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-1">Kontakt</p>
            <a v-if="profile.whatsapp_number"
              :href="`https://wa.me/${profile.whatsapp_number.replace(/\D/g,'')}`"
              target="_blank"
              class="flex items-center justify-center gap-2.5 w-full bg-[#25D366] hover:bg-[#1fb855] text-white text-sm font-bold px-4 py-3.5 rounded-xl transition shadow-lg shadow-[#25D366]/10">
              <svg class="w-5 h-5 shrink-0" fill="currentColor" viewBox="0 0 24 24"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/></svg>
              WhatsApp schreiben
            </a>
            <a v-if="profile.phone_number"
              :href="`tel:${profile.phone_number.replace(/\s/g,'')}`"
              class="flex items-center justify-center gap-2.5 w-full bg-[#6c757d] hover:bg-[#5a6268] text-white text-sm font-bold px-4 py-3.5 rounded-xl transition shadow-lg shadow-black/10">
              <svg class="w-5 h-5 shrink-0" fill="currentColor" viewBox="0 0 24 24"><path d="M6.62 10.79c1.44 2.83 3.76 5.14 6.59 6.59l2.2-2.2c.27-.27.67-.36 1.02-.24 1.12.37 2.33.57 3.57.57.55 0 1 .45 1 1V20c0 .55-.45 1-1 1-9.39 0-17-7.61-17-17 0-.55.45-1 1-1h3.5c.55 0 1 .45 1 1 0 1.25.2 2.45.57 3.57.11.35.03.74-.25 1.02l-2.2 2.2z"/></svg>
              Telefon anrufen
            </a>
            <a v-if="profile.telegram_username"
              :href="`https://t.me/${profile.telegram_username.replace('@','')}`"
              target="_blank"
              class="flex items-center justify-center gap-2.5 w-full bg-[#0088cc] hover:bg-[#0077b5] text-white text-sm font-bold px-4 py-3.5 rounded-xl transition shadow-lg shadow-[#0088cc]/10">
              <svg class="w-5 h-5 shrink-0" fill="currentColor" viewBox="0 0 24 24"><path d="M11.944 0A12 12 0 0 0 0 12a12 12 0 0 0 12 12 12 12 0 0 0 12-12A12 12 0 0 0 12 0a12 12 0 0 0-.056 0zm4.962 7.224c.1-.002.321.023.465.14a.506.506 0 0 1 .171.325c.016.093.036.306.02.472-.18 1.898-.96 6.502-1.36 8.627-.168.9-.499 1.201-.82 1.23-.696.065-1.225-.46-1.9-.902-1.056-.693-1.653-1.124-2.678-1.8-1.185-.78-.417-1.21.258-1.91.177-.184 3.247-2.977 3.307-3.23.007-.032.014-.15-.056-.212s-.174-.041-.249-.024c-.106.024-1.793 1.14-5.061 3.345-.48.33-.913.49-1.302.48-.428-.008-1.252-.241-1.865-.44-.752-.245-1.349-.374-1.297-.789.027-.216.325-.437.893-.663 3.498-1.524 5.83-2.529 6.998-3.014 3.332-1.386 4.025-1.627 4.476-1.635z"/></svg>
              Telegram schreiben
            </a>
          </div>

          <!-- Subscribe / Owner CTA -->
          <div class="bg-[#1a1a1a] border border-white/8 rounded-2xl p-5">

            <!-- Owner -->
            <template v-if="isOwner">
              <p class="text-xs text-gray-400 uppercase tracking-wide font-semibold mb-3">Dein Profil</p>
              <Link :href="route('inserat.profile.edit')"
                class="block w-full text-center bg-white/5 text-gray-300 text-sm font-semibold px-4 py-3 rounded-xl hover:bg-white/10 transition mb-2.5">
                ✏️ Profil bearbeiten
              </Link>
              <Link :href="route('inserat.media.index')"
                class="block w-full text-center border border-[#e35d8f] text-[#e35d8f] text-sm font-semibold px-4 py-3 rounded-xl hover:bg-[#e35d8f]/10 transition">
                🖼️ Medien verwalten
              </Link>
            </template>

            <!-- Active Trial -->
            <template v-else-if="isTrialing">
              <div class="text-center mb-4">
                <span class="inline-block bg-purple-900/30 text-purple-300 text-xs font-bold px-3 py-1 rounded-full mb-3">
                  🎁 Gratis-Test aktiv
                </span>
                <p class="text-3xl font-black text-white">{{ trialDaysLeft }}</p>
                <p class="text-sm text-gray-400">Tage verbleibend</p>
                <p class="text-xs text-gray-500 mt-0.5">bis {{ trialEndsAt }}</p>
              </div>
              <p class="text-xs text-gray-400 text-center mb-4">Danach CHF {{ profile.subscription_price_chf }}/Monat</p>
              <form @submit.prevent="subscribe" class="mb-2">
                <button type="submit" :disabled="subscribing"
                  class="w-full bg-[#e35d8f] hover:bg-[#c44a7a] disabled:opacity-50 text-white font-bold py-3 rounded-xl transition text-sm">
                  {{ subscribing ? 'Weiterleitung…' : 'Jetzt abonnieren' }}
                </button>
              </form>
              <button @click="cancelSub" class="w-full text-xs text-gray-500 hover:text-red-400 transition py-1">
                Test beenden
              </button>
            </template>

            <!-- Active paid subscription -->
            <template v-else-if="isSubscribed">
              <div class="text-center mb-4">
                <div class="w-12 h-12 rounded-full bg-green-900/30 border border-green-700/40 flex items-center justify-center mx-auto mb-3">
                  <svg class="w-6 h-6 text-green-400" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
                </div>
                <p class="text-green-400 font-bold">Aktiv abonniert</p>
                <p class="text-xs text-gray-400 mt-1">Vollzugang zu privaten Inhalten</p>
              </div>
              <button @click="cancelSub" class="w-full text-xs text-gray-500 hover:text-red-400 transition py-1">
                Abonnement kündigen
              </button>
            </template>

            <!-- Not logged in -->
            <template v-else-if="!$page.props.auth.user">
              <p class="text-xs text-gray-400 uppercase tracking-wide font-semibold mb-3 text-center">Privater Zugang</p>
              <div class="bg-purple-900/20 border border-purple-700/30 rounded-xl p-4 mb-4 text-center">
                <p class="text-purple-300 font-bold">🎁 3 Tage gratis testen</p>
                <p class="text-2xl font-black text-white mt-1">CHF {{ profile.subscription_price_chf }}</p>
                <p class="text-xs text-purple-400">pro Monat danach</p>
              </div>
              <Link :href="route('register')"
                class="block w-full text-center bg-purple-600 hover:bg-purple-700 text-white font-bold py-3 rounded-xl transition text-sm mb-2.5">
                Kostenlos registrieren
              </Link>
              <Link :href="route('login')"
                class="block w-full text-center border border-white/10 text-gray-400 text-sm py-2.5 rounded-xl hover:border-white/20 transition">
                Bereits Mitglied? Anmelden
              </Link>
            </template>

            <!-- Logged in, trial available -->
            <template v-else-if="hasSubscriptionOffer && !hasTrialed">
              <p class="text-xs text-gray-400 uppercase tracking-wide font-semibold mb-3 text-center">Privater Zugang</p>
              <div class="bg-purple-900/20 border border-purple-700/30 rounded-xl p-4 mb-4 text-center">
                <p class="text-purple-300 font-bold">🎁 3 Tage gratis testen</p>
                <p class="text-2xl font-black text-white mt-1">CHF {{ profile.subscription_price_chf }}</p>
                <p class="text-xs text-purple-400">pro Monat danach</p>
              </div>
              <form @submit.prevent="startTrial" class="mb-2.5">
                <button type="submit" :disabled="trialing"
                  class="w-full bg-purple-600 hover:bg-purple-700 disabled:opacity-50 text-white font-bold py-3 rounded-xl transition text-sm">
                  {{ trialing ? 'Wird aktiviert…' : '3 Tage gratis testen' }}
                </button>
              </form>
              <form @submit.prevent="subscribe">
                <button type="submit" :disabled="subscribing"
                  class="w-full border border-[#e35d8f] text-[#e35d8f] font-semibold py-2.5 rounded-xl hover:bg-[#e35d8f]/10 transition text-sm">
                  {{ subscribing ? 'Weiterleitung…' : `Abonnieren · CHF ${profile.subscription_price_chf}/Mo` }}
                </button>
              </form>
            </template>

            <!-- Logged in, already trialed -->
            <template v-else-if="hasSubscriptionOffer && hasTrialed">
              <p class="text-xs text-gray-400 uppercase tracking-wide font-semibold mb-3 text-center">Privater Zugang</p>
              <div class="text-center mb-4">
                <p class="text-3xl font-black text-white">CHF {{ profile.subscription_price_chf }}</p>
                <p class="text-sm text-gray-400">/ Monat</p>
              </div>
              <form @submit.prevent="subscribe">
                <button type="submit" :disabled="subscribing"
                  class="w-full bg-[#e35d8f] hover:bg-[#c44a7a] disabled:opacity-50 text-white font-bold py-3 rounded-xl transition">
                  {{ subscribing ? 'Weiterleitung…' : 'Jetzt abonnieren' }}
                </button>
              </form>
            </template>
          </div>

          <!-- Address -->
          <div v-if="profile.address" class="bg-[#1a1a1a] border border-white/8 rounded-2xl p-5">
            <p class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-3">Standort</p>
            <div class="flex items-start gap-2 mb-4">
              <svg class="w-4 h-4 text-[#e35d8f] shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M5.05 4.05a7 7 0 119.9 9.9L10 18.9l-4.95-4.95a7 7 0 010-9.9zM10 11a2 2 0 100-4 2 2 0 000 4z" clip-rule="evenodd"/>
              </svg>
              <span class="text-sm text-gray-300 leading-snug">{{ profile.address }}</span>
            </div>
            <a :href="`https://www.google.com/maps/search/?api=1&query=${encodeURIComponent(profile.address)}`"
              target="_blank" rel="noopener"
              class="flex items-center justify-center gap-2 w-full border border-white/10 text-gray-400 text-sm font-semibold px-4 py-2.5 rounded-xl hover:border-[#e35d8f] hover:text-[#e35d8f] transition">
              <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/>
              </svg>
              Auf Google Maps öffnen
            </a>
          </div>

          <!-- Stats -->
          <div class="bg-[#1a1a1a] border border-white/8 rounded-2xl p-5">
            <p class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-3">Statistiken</p>
            <div class="space-y-2.5 text-sm">
              <div class="flex justify-between">
                <span class="text-gray-500">Inseriert seit</span>
                <span class="text-gray-300">{{ profile.created_at }}</span>
              </div>
              <div class="flex justify-between">
                <span class="text-gray-500">Aufrufe</span>
                <span class="text-white font-semibold">{{ profile.total_views }}</span>
              </div>
              <div class="flex justify-between">
                <span class="text-gray-500">Abonnenten</span>
                <span class="text-white font-semibold">{{ profile.total_subscribers }}</span>
              </div>
              <div v-if="reviews.length" class="flex justify-between">
                <span class="text-gray-500">Bewertung</span>
                <span class="text-white font-semibold">{{ avgRating.toFixed(1) }} ★ ({{ reviews.length }})</span>
              </div>
            </div>
          </div>
        </div>

        <!-- ── RIGHT COLUMN (Medien + Bewertungen) ───────────────────────── -->
        <div class="flex-1 min-w-0 space-y-5">

          <!-- Media Tabs -->
          <div class="bg-[#1a1a1a] border border-white/8 rounded-2xl overflow-hidden">
            <div class="flex border-b border-white/8 px-2 pt-2">
              <button v-for="tab in tabs" :key="tab.key" @click="activeTab = tab.key"
                class="px-5 py-3 text-sm font-semibold transition rounded-t-lg border-b-2 -mb-px"
                :class="activeTab === tab.key ? 'border-[#e35d8f] text-[#e35d8f] bg-white/3' : 'border-transparent text-gray-500 hover:text-gray-300'">
                {{ tab.label }} ({{ tab.count }})
              </button>
            </div>
            <div class="p-4">
              <!-- Public Media -->
              <div v-if="activeTab === 'public'">
                <div v-if="publicMedia.length === 0" class="text-center py-10 text-gray-500">Noch keine Fotos.</div>
                <div v-else class="grid grid-cols-2 sm:grid-cols-3 gap-2">
                  <div v-for="item in publicMedia" :key="item.id"
                    class="aspect-square rounded-xl overflow-hidden cursor-pointer group" @click="openLightbox(item)">
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
                <template v-if="isOwner || isSubscribed || isTrialing">
                  <div v-if="privateMedia.length === 0" class="text-center py-10 text-gray-500">Noch keine privaten Inhalte.</div>
                  <div v-else class="grid grid-cols-2 sm:grid-cols-3 gap-2">
                    <div v-for="item in privateMedia" :key="item.id"
                      class="rounded-xl overflow-hidden cursor-pointer group"
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

                <!-- Non-subscriber: blurred preview -->
                <template v-else-if="privateMediaCount > 0">
                  <div class="relative">
                    <div class="grid grid-cols-2 sm:grid-cols-3 gap-2">
                      <div v-for="i in Math.min(privateMediaCount, 6)" :key="i"
                        class="aspect-square rounded-xl overflow-hidden relative select-none">
                        <div class="absolute inset-0 scale-110"
                          :style="`background: ${blurGradients[(i - 1) % blurGradients.length]}; filter: blur(10px) brightness(0.65);`" />
                        <div class="absolute inset-0 flex items-center justify-center">
                          <svg class="w-7 h-7 text-white/80 drop-shadow" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M5 9V7a5 5 0 0110 0v2a2 2 0 012 2v5a2 2 0 01-2 2H5a2 2 0 01-2-2v-5a2 2 0 012-2zm8-2v2H7V7a3 3 0 016 0z" clip-rule="evenodd"/>
                          </svg>
                        </div>
                      </div>
                    </div>
                    <div class="absolute inset-x-0 bottom-0 pt-24 bg-gradient-to-t from-[#1a1a1a] via-[#1a1a1a]/95 to-transparent flex flex-col items-center pb-4 pointer-events-none">
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
                          class="pointer-events-auto bg-[#e35d8f] hover:bg-[#c44a7a] disabled:opacity-50 text-white text-sm font-bold px-6 py-2.5 rounded-lg transition">
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

          <!-- Über mich / Beschreibung -->
          <div v-if="profile.description" class="bg-[#1a1a1a] border border-white/8 rounded-2xl p-5">
            <h2 class="text-sm font-bold text-gray-400 uppercase tracking-wider mb-3">Über {{ profile.display_name }}</h2>
            <p class="text-gray-300 text-sm leading-relaxed whitespace-pre-line">{{ profile.description }}</p>
          </div>

          <!-- Angebote & Services -->
          <div v-if="profile.tags && profile.tags.length" class="bg-[#1a1a1a] border border-white/8 rounded-2xl p-5">
            <h2 class="text-sm font-bold text-gray-400 uppercase tracking-wider mb-4">Angebote & Services</h2>
            <div class="grid grid-cols-2 gap-x-4 gap-y-2.5">
              <div v-for="tag in profile.tags" :key="tag" class="flex items-center gap-2.5">
                <span class="flex-shrink-0 w-5 h-5 rounded-full bg-[#e35d8f]/15 border border-[#e35d8f]/30 flex items-center justify-center">
                  <svg class="w-3 h-3 text-[#e35d8f]" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>
                </span>
                <span class="text-sm text-gray-300">{{ tag }}</span>
              </div>
            </div>
          </div>

          <!-- Review Form -->
          <div v-if="isSubscribed && !hasReviewed" class="bg-[#1a1a1a] border border-white/8 rounded-2xl p-5">
            <h2 class="font-semibold text-white mb-4">Bewertung abgeben</h2>
            <form @submit.prevent="submitReview" class="space-y-4">
              <div class="flex gap-1">
                <button v-for="n in 5" :key="n" type="button" @click="reviewForm.stars = n"
                  class="text-3xl transition" :class="n <= reviewForm.stars ? 'text-yellow-400' : 'text-gray-600'">★</button>
              </div>
              <textarea v-model="reviewForm.comment" rows="3" maxlength="1000" placeholder="Deine Erfahrung…"
                class="w-full bg-[#111] border border-white/10 text-gray-200 text-sm rounded-lg px-3 py-2 focus:outline-none focus:border-[#e35d8f] resize-none placeholder-gray-600" />
              <button type="submit" :disabled="!reviewForm.stars || submittingReview"
                class="bg-[#e35d8f] hover:bg-[#c44a7a] disabled:opacity-50 text-white text-sm font-bold px-5 py-2.5 rounded-lg transition">
                {{ submittingReview ? 'Einreichen…' : 'Bewertung einreichen' }}
              </button>
            </form>
          </div>

          <!-- Reviews -->
          <div class="bg-[#1a1a1a] border border-white/8 rounded-2xl p-5">
            <h2 class="font-semibold text-white mb-4">
              Bewertungen
              <span class="text-gray-500 font-normal text-sm">({{ reviews.length }})</span>
            </h2>
            <div v-if="!reviews.length" class="text-center py-6 text-gray-500 text-sm">Noch keine Bewertungen.</div>
            <div v-else class="space-y-4">
              <div v-for="r in reviews" :key="r.id" class="border-b border-white/5 pb-4 last:border-0 last:pb-0">
                <div class="flex items-center gap-2 mb-1">
                  <span class="text-yellow-400 text-sm">{{ '★'.repeat(r.stars) }}{{ '☆'.repeat(5 - r.stars) }}</span>
                  <span class="text-sm font-semibold text-gray-300">{{ r.author }}</span>
                  <span class="text-xs text-gray-500 ml-auto">{{ r.created_at }}</span>
                </div>
                <p class="text-sm text-gray-400">{{ r.comment }}</p>
                <div v-if="r.reply" class="mt-2 ml-4 pl-3 border-l-2 border-[#e35d8f]/40 text-sm text-gray-500 italic">
                  <span class="font-semibold text-[#e35d8f]">Antwort: </span>{{ r.reply }}
                </div>
                <div v-if="isOwner && !r.reply" class="mt-2">
                  <button @click="replyTarget = replyTarget === r.id ? null : r.id" class="text-xs text-[#e35d8f] hover:underline">Antworten</button>
                  <div v-if="replyTarget === r.id" class="mt-2 flex gap-2">
                    <input v-model="replyText" type="text" placeholder="Deine Antwort…" maxlength="500"
                      class="flex-1 bg-[#111] border border-white/10 text-gray-200 rounded px-2 py-1 text-xs focus:outline-none focus:border-[#e35d8f]" />
                    <button @click="submitReply(r.id)" class="bg-[#e35d8f] text-white text-xs px-3 py-1 rounded hover:bg-[#c44a7a]">Senden</button>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>

      </div>
    </div>

    <!-- ── LIGHTBOX ──────────────────────────────────────────────────────────── -->
    <div v-if="lightboxItem" class="fixed inset-0 bg-black/95 flex items-center justify-center z-50 p-4" @click.self="lightboxItem = null">
      <button class="absolute top-4 right-4 text-white text-3xl hover:text-gray-300 w-10 h-10 flex items-center justify-center z-10" @click="lightboxItem = null">✕</button>
      <img v-if="lightboxItem.type === 'image'"
        :src="lightboxItem.url"
        class="max-h-[90vh] max-w-[90vw] object-contain rounded-lg" />
      <video v-else
        :src="lightboxItem.url"
        class="max-h-[90vh] max-w-[90vw] rounded-lg"
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
  mainMedia:           { type: Object, default: null },
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
  'linear-gradient(135deg, #fd7043, #e35d8f)',
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
