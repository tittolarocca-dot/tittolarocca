<template>
  <AppLayout>
    <Head :title="profile.display_name" />

    <!-- Success banner -->
    <div v-if="subscribed" class="bg-[#e35d8f] text-white text-center py-3 text-sm font-semibold">
      {{ t('profile.subscribed_success') }}
    </div>

    <!-- ── MEDIA TABS ──────────────────────────────────────────────────────────── -->
    <div class="bg-[#0f0f0f] border-b border-white/5">
      <div class="max-w-7xl mx-auto px-4 py-4">
        <div class="bg-[#1a1a1a] border border-white/8 rounded-2xl overflow-hidden">
          <div class="flex border-b border-white/8 px-2 pt-2">
            <button v-for="tab in tabs" :key="tab.key" @click="activeTab = tab.key"
              class="px-5 py-3 text-[15px] font-black transition rounded-t-lg border-b-2 -mb-px"
              :class="activeTab === tab.key ? 'border-[#e35d8f] text-white bg-white/3' : 'border-transparent text-white/40 hover:text-white'">
              {{ tab.label }} ({{ tab.count }})
            </button>
          </div>
          <div class="p-4">
            <!-- Public Media -->
            <div v-if="activeTab === 'public'">
              <div v-if="publicMedia.length === 0" class="text-center py-10 text-gray-500">{{ t('profile.no_photos') }}</div>
              <template v-else>
                <!-- 1 item -->
                <div v-if="publicMedia.length === 1"
                  class="relative h-[60vh] max-h-[80vh] rounded-xl overflow-hidden cursor-pointer group bg-[#0d0d0d]"
                  @click="openLightbox(publicMedia[0])">
                  <MediaThumb :item="publicMedia[0]" :alt="profile.display_name" :eager="true" />
                </div>
                <!-- 2 items -->
                <div v-else-if="publicMedia.length === 2"
                  class="grid grid-cols-2 gap-1 h-[250px] rounded-xl overflow-hidden">
                  <div v-for="(item, i) in publicMedia.slice(0,2)" :key="item.id"
                    class="relative overflow-hidden cursor-pointer group" @click="openLightbox(item)">
                    <MediaThumb :item="item" :alt="profile.display_name" :eager="i===0" />
                  </div>
                </div>
                <!-- 3–4 items -->
                <div v-else-if="publicMedia.length <= 4"
                  class="flex gap-1 h-[250px] rounded-xl overflow-hidden">
                  <div class="relative flex-[2] overflow-hidden cursor-pointer group" @click="openLightbox(publicMedia[0])">
                    <MediaThumb :item="publicMedia[0]" :alt="profile.display_name" :eager="true" />
                  </div>
                  <div class="flex flex-col gap-1 flex-1">
                    <div v-for="item in publicMedia.slice(1)" :key="item.id"
                      class="relative flex-1 overflow-hidden cursor-pointer group" @click="openLightbox(item)">
                      <MediaThumb :item="item" :alt="profile.display_name" :eager="false" />
                    </div>
                  </div>
                </div>
                <!-- 5+ items -->
                <div v-else class="flex gap-1 h-[250px] rounded-xl overflow-hidden">
                  <div class="relative overflow-hidden cursor-pointer group" style="flex:3" @click="openLightbox(publicMedia[0])">
                    <MediaThumb :item="publicMedia[0]" :alt="profile.display_name" :eager="true" />
                  </div>
                  <div class="grid grid-cols-2 gap-1" style="flex:2">
                    <div v-for="(item, i) in publicMedia.slice(1,5)" :key="item.id"
                      class="relative overflow-hidden cursor-pointer group" @click="openLightbox(item)">
                      <MediaThumb :item="item" :alt="profile.display_name" :eager="false" />
                      <div v-if="i === 3 && publicMedia.length > 5"
                        class="absolute inset-0 bg-black/60 flex flex-col items-center justify-center text-white font-bold pointer-events-none">
                        <span class="text-3xl font-black">+{{ publicMedia.length - 5 }}</span>
                        <span class="text-xs mt-1 opacity-80">weitere</span>
                      </div>
                    </div>
                  </div>
                </div>
              </template>
            </div>

            <!-- Private Media -->
            <div v-if="activeTab === 'private'">
              <template v-if="isOwner || isSubscribed || isTrialing || isLaunchUnlocked">
                <div v-if="privateMedia.length === 0" class="text-center py-10 text-gray-500">{{ t('profile.no_private') }}</div>
                <template v-else>
                  <!-- 1 item -->
                  <div v-if="privateMedia.length === 1"
                    class="relative h-[60vh] max-h-[80vh] rounded-xl overflow-hidden cursor-pointer group bg-[#0d0d0d]"
                    @click="openLightbox(privateMedia[0])">
                    <MediaThumb :item="privateMedia[0]" :alt="profile.display_name" :eager="true" />
                  </div>
                  <!-- 2 items -->
                  <div v-else-if="privateMedia.length === 2"
                    class="grid grid-cols-2 gap-1 h-[250px] rounded-xl overflow-hidden">
                    <div v-for="(item, i) in privateMedia.slice(0,2)" :key="item.id"
                      class="relative overflow-hidden cursor-pointer group" @click="openLightbox(item)">
                      <MediaThumb :item="item" :alt="profile.display_name" :eager="i===0" />
                    </div>
                  </div>
                  <!-- 3–4 items -->
                  <div v-else-if="privateMedia.length <= 4"
                    class="flex gap-1 h-[250px] rounded-xl overflow-hidden">
                    <div class="relative flex-[2] overflow-hidden cursor-pointer group" @click="openLightbox(privateMedia[0])">
                      <MediaThumb :item="privateMedia[0]" :alt="profile.display_name" :eager="true" />
                    </div>
                    <div class="flex flex-col gap-1 flex-1">
                      <div v-for="item in privateMedia.slice(1)" :key="item.id"
                        class="relative flex-1 overflow-hidden cursor-pointer group" @click="openLightbox(item)">
                        <MediaThumb :item="item" :alt="profile.display_name" :eager="false" />
                      </div>
                    </div>
                  </div>
                  <!-- 5+ items -->
                  <div v-else class="flex gap-1 h-[250px] rounded-xl overflow-hidden">
                    <div class="relative overflow-hidden cursor-pointer group" style="flex:3" @click="openLightbox(privateMedia[0])">
                      <MediaThumb :item="privateMedia[0]" :alt="profile.display_name" :eager="true" />
                    </div>
                    <div class="grid grid-cols-2 gap-1" style="flex:2">
                      <div v-for="(item, i) in privateMedia.slice(1,5)" :key="item.id"
                        class="relative overflow-hidden cursor-pointer group" @click="openLightbox(item)">
                        <MediaThumb :item="item" :alt="profile.display_name" :eager="false" />
                        <div v-if="i === 3 && privateMedia.length > 5"
                          class="absolute inset-0 bg-black/60 flex flex-col items-center justify-center text-white font-bold pointer-events-none">
                          <span class="text-3xl font-black">+{{ privateMedia.length - 5 }}</span>
                          <span class="text-xs mt-1 opacity-80">{{ t('profile.more') }}</span>
                        </div>
                      </div>
                    </div>
                  </div>
                </template>
              </template>
              <template v-else-if="privateMediaCount > 0">
                <div class="relative h-[250px] rounded-xl overflow-hidden">
                  <div class="flex gap-1 h-full">
                    <div class="relative overflow-hidden" style="flex:3">
                      <img :src="privateMedia[0]?.preview_url" alt="Locked"
                        class="absolute inset-0 w-full h-full object-cover blur-lg scale-110 brightness-75" />
                      <div class="absolute inset-0 flex items-center justify-center">
                        <svg class="w-10 h-10 text-white/80 drop-shadow" fill="currentColor" viewBox="0 0 20 20">
                          <path fill-rule="evenodd" d="M5 9V7a5 5 0 0110 0v2a2 2 0 012 2v5a2 2 0 01-2 2H5a2 2 0 01-2-2v-5a2 2 0 012-2zm8-2v2H7V7a3 3 0 016 0z" clip-rule="evenodd"/>
                        </svg>
                      </div>
                    </div>
                    <div class="grid grid-cols-2 gap-1" style="flex:2">
                      <div v-for="item in privateMedia.slice(1, 5)" :key="item.id" class="relative overflow-hidden">
                        <img :src="item.preview_url" alt="Locked"
                          class="absolute inset-0 w-full h-full object-cover blur-lg scale-110 brightness-75" />
                        <div class="absolute inset-0 flex items-center justify-center">
                          <svg class="w-7 h-7 text-white/60" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M5 9V7a5 5 0 0110 0v2a2 2 0 012 2v5a2 2 0 01-2 2H5a2 2 0 01-2-2v-5a2 2 0 012-2zm8-2v2H7V7a3 3 0 016 0z" clip-rule="evenodd"/>
                          </svg>
                        </div>
                      </div>
                    </div>
                  </div>
                  <div class="absolute inset-0 flex flex-col items-center justify-center bg-black/40 px-4 text-center">
                    <div class="w-12 h-12 rounded-full bg-black/50 backdrop-blur-sm flex items-center justify-center mb-2">
                      <svg class="w-6 h-6 text-white" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M5 9V7a5 5 0 0110 0v2a2 2 0 012 2v5a2 2 0 01-2 2H5a2 2 0 01-2-2v-5a2 2 0 012-2zm8-2v2H7V7a3 3 0 016 0z" clip-rule="evenodd"/></svg>
                    </div>

                    <!-- ── LAUNCH-MODUS: keine Preise, keine Zahlung ── -->
                    <template v-if="launchMode">
                      <p class="font-bold text-white text-base mb-1">{{ t('profile.private_gallery') }}</p>
                      <p class="text-xs text-gray-300 mb-1">{{ t('profile.private_gallery_intro') }}</p>
                      <template v-if="launchGalleryFree">
                        <p class="text-xs text-gray-300 mb-4">{{ t('profile.launch_gallery_free') }}</p>
                        <Link v-if="!$page.props.auth.user" :href="route('register')"
                          class="bg-[#e35d8f] hover:bg-[#c44a7a] text-white text-sm font-bold px-6 py-2.5 rounded-lg transition">
                          {{ t('profile.launch_register_view') }}
                        </Link>
                      </template>
                      <template v-else>
                        <p class="text-xs text-gray-300 mb-4">{{ t('profile.launch_gallery_locked') }}</p>
                        <button type="button" @click="onFavoriteClick"
                          class="border border-white/20 text-white/90 text-sm font-semibold px-5 py-2.5 rounded-lg hover:border-[#e35d8f] hover:text-[#e35d8f] transition">
                          {{ favorited ? t('profile.saved_favorite') : t('profile.launch_notify_me') }}
                        </button>
                      </template>
                    </template>

                    <!-- ── NORMALBETRIEB (mit Preisen/Abo) ── -->
                    <template v-else>
                      <p class="text-xs text-gray-200 mb-1">{{ t('home.members_only') }}</p>
                      <p class="font-bold text-white text-base mb-1">{{ t('profile.private_count', { count: privateMediaCount }) }}</p>
                      <p class="text-xs text-gray-300 mb-4">{{ t('profile.unlock_for', { price: profile.subscription_price_chf }) }}</p>
                      <template v-if="!$page.props.auth.user">
                        <Link :href="route('register')" class="bg-purple-600 hover:bg-purple-700 text-white text-sm font-bold px-6 py-2.5 rounded-lg transition">{{ t('profile.free_trial') }}</Link>
                      </template>
                      <template v-else-if="!hasTrialed">
                        <button @click="startTrial" :disabled="trialing" class="bg-purple-600 hover:bg-purple-700 disabled:opacity-50 text-white text-sm font-bold px-6 py-2.5 rounded-lg transition">{{ trialing ? t('profile.activating') : t('profile.free_trial') }}</button>
                      </template>
                      <template v-else>
                        <button @click="subscribe" :disabled="subscribing" class="bg-[#e35d8f] hover:bg-[#c44a7a] disabled:opacity-50 text-white text-sm font-bold px-6 py-2.5 rounded-lg transition">{{ subscribing ? t('profile.redirecting') : t('profile.direct_subscribe', { price: profile.subscription_price_chf }) }}</button>
                      </template>
                    </template>
                  </div>
                </div>
              </template>
              <div v-else class="text-center py-10 text-gray-500">
                <p v-if="launchMode" class="text-sm">{{ t('profile.no_private_launch') }}</p>
                <p v-else class="text-sm">{{ t('profile.no_private') }}</p>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- ── PROFILE HEADER ───────────────────────────────────────────────────── -->
    <div class="bg-[#111] border-b border-white/5">
      <div class="max-w-7xl mx-auto px-4 py-5">
        <!-- Breadcrumb -->
        <nav class="flex items-center gap-1.5 text-xs text-gray-500 mb-4 flex-wrap">
          <Link :href="route('home')" class="hover:text-[#e35d8f] transition">{{ t('nav.home') }}</Link>
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
          <!-- Verifizierungs-Badges (Foto / Identität getrennt) -->
          <span v-if="photoVerified && idVerified"
            class="inline-flex items-center gap-1 text-xs bg-green-500/15 text-green-300 border border-green-500/40 px-2.5 py-1 rounded-full font-semibold">
            <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>
            {{ t('home.fully_verified') }}
          </span>
          <template v-else>
            <span v-if="idVerified"
              class="inline-flex items-center gap-1 text-xs bg-green-500/15 text-green-300 border border-green-500/40 px-2.5 py-1 rounded-full font-semibold">
              <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>
              {{ t('home.id_verified') }}
            </span>
            <span v-if="photoVerified"
              class="inline-flex items-center gap-1 text-xs bg-sky-500/15 text-sky-300 border border-sky-500/40 px-2.5 py-1 rounded-full font-semibold">
              <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>
              {{ t('home.photo_verified') }}
            </span>
          </template>
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
            {{ t('profile.age_years', { age: profile.age }) }}
          </span>
          <span class="inline-flex items-center gap-1 text-xs text-gray-400 bg-white/5 border border-white/8 px-3 py-1.5 rounded-full">
            <svg class="w-3.5 h-3.5 text-[#e35d8f]" fill="currentColor" viewBox="0 0 20 20"><path d="M10 12a2 2 0 100-4 2 2 0 000 4z"/><path fill-rule="evenodd" d="M.458 10C1.732 5.943 5.522 3 10 3s8.268 2.943 9.542 7c-1.274 4.057-5.064 7-9.542 7S1.732 14.057.458 10zM14 10a4 4 0 11-8 0 4 4 0 018 0z" clip-rule="evenodd"/></svg>
            {{ profile.total_views }} {{ t('profile.views_count') }}
          </span>
          <span class="inline-flex items-center gap-1 text-xs text-gray-400 bg-white/5 border border-white/8 px-3 py-1.5 rounded-full">
            <svg class="w-3.5 h-3.5 text-yellow-400" fill="currentColor" viewBox="0 0 20 20"><path d="M2 10.5a1.5 1.5 0 113 0v6a1.5 1.5 0 01-3 0v-6zM6 10.333v5.43a2 2 0 001.106 1.79l.05.025A4 4 0 008.943 18h5.416a2 2 0 001.962-1.608l1.2-6A2 2 0 0015.56 8H12V4a2 2 0 00-2-2 1 1 0 00-1 1v.667a4 4 0 01-.8 2.4L6.8 7.933a4 4 0 00-.8 2.4z"/></svg>
            {{ likesCount }} {{ t('profile.likes_count') }}
          </span>
          <span class="inline-flex items-center gap-1 text-xs text-gray-400 bg-white/5 border border-white/8 px-3 py-1.5 rounded-full">
            <svg class="w-3.5 h-3.5 text-[#e35d8f]" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M3.172 5.172a4 4 0 015.656 0L10 6.343l1.172-1.171a4 4 0 115.656 5.656L10 17.657l-6.828-6.829a4 4 0 010-5.656z" clip-rule="evenodd"/></svg>
            {{ followersCount }} {{ t('profile.followers_count') }}
          </span>
        </div>
      </div>
    </div>

    <!-- ── MAIN CONTENT ─────────────────────────────────────────────────────── -->
    <div class="max-w-7xl mx-auto px-4 py-6 space-y-5">
      <div class="flex flex-col lg:flex-row gap-5">

        <!-- ── MAIN CONTENT ──────────────────────────────────────────────── -->
        <div class="flex-1 min-w-0 space-y-5 lg:order-2">

          <!-- Über mich / Beschreibung -->
          <div v-if="profile.description" class="bg-[#1a1a1a] border border-white/8 rounded-2xl p-5">
            <h2 class="text-sm font-bold text-gray-400 uppercase tracking-wider mb-3">{{ t('profile.about_section', { name: profile.display_name }) }}</h2>
            <p class="text-white text-[16px] leading-relaxed whitespace-pre-line">{{ profile.description }}</p>
          </div>

          <!-- Steckbrief / Details -->
          <div v-if="details.length" class="bg-[#1a1a1a] border border-white/8 rounded-2xl p-5">
            <h2 class="text-sm font-bold text-gray-400 uppercase tracking-wider mb-4">{{ t('profile.details_section') }}</h2>
            <div class="grid grid-cols-2 sm:grid-cols-3 gap-x-4 gap-y-4">
              <div v-for="item in details" :key="item.label" class="flex items-center gap-2.5">
                <span class="flex-shrink-0 w-8 h-8 rounded-full bg-[#e35d8f]/15 border border-[#e35d8f]/30 flex items-center justify-center">
                  <svg class="w-4 h-4 text-[#e35d8f]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.6">
                    <path stroke-linecap="round" stroke-linejoin="round" :d="item.icon" />
                  </svg>
                </span>
                <span class="min-w-0">
                  <span class="block text-[11px] text-gray-500 uppercase tracking-wide leading-tight">{{ item.label }}</span>
                  <span class="block text-sm text-white font-medium leading-tight truncate">{{ item.value }}</span>
                </span>
              </div>
            </div>
          </div>

          <!-- Angebote & Services (Tags as checklist) -->
          <div v-if="profile.tags && profile.tags.length" class="bg-[#1a1a1a] border border-white/8 rounded-2xl p-5">
            <h2 class="text-sm font-bold text-gray-400 uppercase tracking-wider mb-4">{{ t('profile.services_section') }}</h2>
            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-x-4 gap-y-2.5">
              <div v-for="tag in profile.tags" :key="tag"
                class="flex items-center gap-2.5">
                <span class="flex-shrink-0 w-5 h-5 rounded-full bg-[#e35d8f]/15 border border-[#e35d8f]/30 flex items-center justify-center">
                  <svg class="w-3 h-3 text-[#e35d8f]" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>
                </span>
                <span class="text-sm text-gray-300">{{ tag }}</span>
              </div>
            </div>
          </div>

          <!-- Gesprochene Sprachen -->
          <div v-if="languages.length" class="bg-[#1a1a1a] border border-white/8 rounded-2xl p-5">
            <h2 class="text-sm font-bold text-gray-400 uppercase tracking-wider mb-4">{{ t('profile.languages_section') }}</h2>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-x-8 gap-y-3">
              <div v-for="l in languages" :key="l.code" class="flex items-center justify-between gap-3">
                <span class="flex items-center gap-2 text-sm text-gray-300">
                  <span class="text-base leading-none">{{ langFlag(l.code) }}</span>{{ t('languages.' + l.code) }}
                </span>
                <span class="text-sm tracking-tight whitespace-nowrap">
                  <span class="text-yellow-400">{{ '★'.repeat(l.level) }}</span><span class="text-gray-700">{{ '★'.repeat(5 - l.level) }}</span>
                </span>
              </div>
            </div>
          </div>

          <!-- Review Form / eigene Bewertung -->
          <div v-if="isSubscribed && !isOwner" class="bg-[#1a1a1a] border border-white/8 rounded-2xl p-5">
            <h2 class="font-semibold text-white mb-4">{{ myReview ? t('profile.edit_review') : t('profile.review_submit_title') }}</h2>

            <!-- Status der eigenen Bewertung -->
            <div v-if="myReview && myReview.status === 'pending'"
              class="mb-3 text-xs bg-yellow-500/10 border border-yellow-500/30 text-yellow-300 rounded-lg px-3 py-2">
              {{ t('profile.review_status_pending') }}
            </div>
            <div v-else-if="myReview && myReview.status === 'rejected'"
              class="mb-3 text-xs bg-red-500/10 border border-red-500/30 text-red-300 rounded-lg px-3 py-2">
              {{ t('profile.review_status_rejected') }}
            </div>

            <form @submit.prevent="submitReview" class="space-y-4">
              <div class="flex gap-1">
                <button v-for="n in 5" :key="n" type="button" @click="reviewForm.stars = n"
                  class="text-3xl transition" :class="n <= reviewForm.stars ? 'text-yellow-400' : 'text-gray-600'">★</button>
              </div>
              <textarea v-model="reviewForm.comment" rows="3" maxlength="1000" :placeholder="t('profile.your_experience')"
                class="w-full bg-[#111] border border-white/10 text-gray-200 text-sm rounded-lg px-3 py-2 focus:outline-none focus:border-[#e35d8f] resize-none placeholder-gray-600" />
              <p class="text-[11px] text-gray-600">{{ t('profile.review_policy_hint') }}</p>
              <button type="submit" :disabled="!reviewForm.stars || submittingReview"
                class="bg-[#e35d8f] hover:bg-[#c44a7a] disabled:opacity-50 text-white text-sm font-bold px-5 py-2.5 rounded-lg transition">
                {{ submittingReview ? t('profile.submitting') : (myReview ? t('profile.update_review') : t('profile.submit_review')) }}
              </button>
            </form>
          </div>

          <!-- Reviews -->
          <div class="bg-[#1a1a1a] border border-white/8 rounded-2xl p-5">
            <h2 class="font-semibold text-white mb-4">
              {{ t('profile.reviews') }}
              <span class="text-gray-500 font-normal text-sm">({{ reviews.length }})</span>
            </h2>
            <div v-if="!reviews.length" class="text-center py-6 text-gray-500 text-sm">{{ t('profile.no_reviews') }}</div>
            <div v-else class="space-y-4">
              <div v-for="r in reviews" :key="r.id" class="border-b border-white/5 pb-4 last:border-0 last:pb-0">
                <div class="flex items-center gap-2 mb-1">
                  <span class="text-yellow-400 text-sm">{{ '★'.repeat(r.stars) }}{{ '☆'.repeat(5 - r.stars) }}</span>
                  <span class="text-sm font-semibold text-gray-300">{{ r.author }}</span>
                  <span class="text-xs text-gray-500 ml-auto">{{ r.created_at }}</span>
                </div>
                <p class="text-sm text-gray-400">{{ r.comment }}</p>
                <div v-if="r.reply" class="mt-2 ml-4 pl-3 border-l-2 border-[#e35d8f]/40 text-sm text-gray-500 italic">
                  <span class="font-semibold text-[#e35d8f]">{{ t('profile.reply_answer') }}</span>{{ r.reply }}
                </div>

                <!-- Antwort-Status (nur Owner) -->
                <div v-if="isOwner && r.reply_status === 'pending'" class="mt-2 text-xs text-yellow-400">{{ t('profile.reply_status_pending') }}</div>
                <div v-else-if="isOwner && r.reply_status === 'rejected'" class="mt-2 text-xs text-red-400">{{ t('profile.reply_status_rejected') }}</div>

                <!-- Antwort verfassen (Owner, wenn noch keine sichtbare/anhängige Antwort) -->
                <div v-if="isOwner && !r.reply && r.reply_status !== 'pending'" class="mt-2">
                  <button @click="replyTarget = replyTarget === r.id ? null : r.id" class="text-xs text-[#e35d8f] hover:underline">{{ t('profile.reply') }}</button>
                  <div v-if="replyTarget === r.id" class="mt-2 flex gap-2">
                    <input v-model="replyText" type="text" :placeholder="t('profile.your_experience')" maxlength="500"
                      class="flex-1 bg-[#111] border border-white/10 text-gray-200 rounded px-2 py-1 text-xs focus:outline-none focus:border-[#e35d8f]" />
                    <button @click="submitReply(r.id)" class="bg-[#e35d8f] text-white text-xs px-3 py-1 rounded hover:bg-[#c44a7a]">{{ t('profile.send') }}</button>
                  </div>
                </div>

                <!-- Melden (Owner) -->
                <div v-if="isOwner" class="mt-2">
                  <button @click="reportTarget = reportTarget === r.id ? null : r.id" class="text-xs text-gray-600 hover:text-red-400 transition">⚑ {{ t('profile.report_review') }}</button>
                  <div v-if="reportTarget === r.id" class="mt-2 flex gap-2">
                    <input v-model="reportReason" type="text" :placeholder="t('profile.report_reason_ph')" maxlength="500"
                      class="flex-1 bg-[#111] border border-white/10 text-gray-200 rounded px-2 py-1 text-xs focus:outline-none focus:border-red-400" />
                    <button @click="submitReport(r.id)" class="bg-red-500/80 text-white text-xs px-3 py-1 rounded hover:bg-red-500">{{ t('profile.send') }}</button>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- ── LEFT SIDEBAR ──────────────────────────────────────────────── -->
        <div class="lg:w-72 shrink-0 space-y-4 lg:order-1">

          <!-- Subscribe / Owner CTA (sticky) – im Launch-Modus keine Preise/Abos für Besucher -->
          <div v-if="isOwner || !launchMode" class="bg-[#1a1a1a] border border-white/8 rounded-2xl p-5 lg:sticky lg:top-4">

            <!-- Owner -->
            <template v-if="isOwner">
              <p class="text-xs text-gray-400 uppercase tracking-wide font-semibold mb-3">{{ t('profile.your_profile') }}</p>
              <Link :href="route('inserat.profile.edit')"
                class="block w-full text-center bg-white/5 text-gray-300 text-sm font-semibold px-4 py-3 rounded-xl hover:bg-white/10 transition mb-2.5">
                {{ t('profile.edit_profile') }}
              </Link>
              <Link :href="route('inserat.media.index')"
                class="block w-full text-center border border-[#e35d8f] text-[#e35d8f] text-sm font-semibold px-4 py-3 rounded-xl hover:bg-[#e35d8f]/10 transition">
                {{ t('profile.manage_media') }}
              </Link>
            </template>

            <!-- Active Trial -->
            <template v-else-if="isTrialing">
              <div class="text-center mb-4">
                <span class="inline-block bg-purple-900/30 text-purple-300 text-xs font-bold px-3 py-1 rounded-full mb-3">
                  {{ t('profile.trial_active') }}
                </span>
                <p class="text-3xl font-black text-white">{{ trialDaysLeft }}</p>
                <p class="text-sm text-gray-400">{{ t('profile.trial_days_remaining') }}</p>
                <p class="text-xs text-gray-500 mt-0.5">{{ t('profile.trial_until', { date: trialEndsAt }) }}</p>
              </div>
              <p class="text-xs text-gray-400 text-center mb-4">{{ t('profile.then_per_month', { price: profile.subscription_price_chf }) }}</p>
              <form @submit.prevent="subscribe" class="mb-2">
                <button type="submit" :disabled="subscribing"
                  class="w-full bg-[#e35d8f] hover:bg-[#c44a7a] disabled:opacity-50 text-white font-bold py-3 rounded-xl transition text-sm">
                  {{ subscribing ? t('profile.redirecting') : t('profile.subscribe_now') }}
                </button>
              </form>
              <button @click="cancelSub" class="w-full text-xs text-gray-500 hover:text-red-400 transition py-1">
                {{ t('profile.cancel_trial') }}
              </button>
            </template>

            <!-- Active paid subscription -->
            <template v-else-if="isSubscribed">
              <div class="text-center mb-4">
                <div class="w-12 h-12 rounded-full bg-green-900/30 border border-green-700/40 flex items-center justify-center mx-auto mb-3">
                  <svg class="w-6 h-6 text-green-400" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
                </div>
                <p class="text-green-400 font-bold">{{ t('profile.subscribed_active') }}</p>
                <p class="text-xs text-gray-400 mt-1">{{ t('profile.full_access') }}</p>
              </div>
              <button @click="cancelSub" class="w-full text-xs text-gray-500 hover:text-red-400 transition py-1">
                {{ t('profile.cancel_sub') }}
              </button>
            </template>

            <!-- Not logged in -->
            <template v-else-if="!$page.props.auth.user">
              <p class="text-xs text-gray-400 uppercase tracking-wide font-semibold mb-3 text-center">{{ t('profile.private_access') }}</p>
              <div class="bg-purple-900/20 border border-purple-700/30 rounded-xl p-4 mb-4 text-center">
                <p class="text-purple-300 font-bold">{{ t('profile.free_trial') }}</p>
                <p class="text-2xl font-black text-white mt-1">CHF {{ profile.subscription_price_chf }}</p>
                <p class="text-xs text-purple-400">{{ t('profile.per_month') }}</p>
              </div>
              <Link :href="route('register')"
                class="block w-full text-center bg-purple-600 hover:bg-purple-700 text-white font-bold py-3 rounded-xl transition text-sm mb-2.5">
                {{ t('profile.register_free') }}
              </Link>
              <Link :href="route('login')"
                class="block w-full text-center border border-white/10 text-gray-400 text-sm py-2.5 rounded-xl hover:border-white/20 transition">
                {{ t('profile.already_registered') }}
              </Link>
            </template>

            <!-- Logged in, trial available -->
            <template v-else-if="hasSubscriptionOffer && !hasTrialed">
              <p class="text-xs text-gray-400 uppercase tracking-wide font-semibold mb-3 text-center">{{ t('profile.private_access') }}</p>
              <div class="bg-purple-900/20 border border-purple-700/30 rounded-xl p-4 mb-4 text-center">
                <p class="text-purple-300 font-bold">{{ t('profile.free_trial') }}</p>
                <p class="text-2xl font-black text-white mt-1">CHF {{ profile.subscription_price_chf }}</p>
                <p class="text-xs text-purple-400">{{ t('profile.per_month') }}</p>
              </div>
              <form @submit.prevent="startTrial" class="mb-2.5">
                <button type="submit" :disabled="trialing"
                  class="w-full bg-purple-600 hover:bg-purple-700 disabled:opacity-50 text-white font-bold py-3 rounded-xl transition text-sm">
                  {{ trialing ? t('profile.activating') : t('profile.free_trial') }}
                </button>
              </form>
              <form @submit.prevent="subscribe">
                <button type="submit" :disabled="subscribing"
                  class="w-full border border-[#e35d8f] text-[#e35d8f] font-semibold py-2.5 rounded-xl hover:bg-[#e35d8f]/10 transition text-sm">
                  {{ subscribing ? t('profile.redirecting') : t('profile.direct_subscribe', { price: profile.subscription_price_chf }) }}
                </button>
              </form>
            </template>

            <!-- Logged in, already trialed -->
            <template v-else-if="hasSubscriptionOffer && hasTrialed">
              <p class="text-xs text-gray-400 uppercase tracking-wide font-semibold mb-3 text-center">{{ t('profile.private_access') }}</p>
              <div class="text-center mb-4">
                <p class="text-3xl font-black text-white">CHF {{ profile.subscription_price_chf }}</p>
                <p class="text-sm text-gray-400">{{ t('profile.per_month') }}</p>
              </div>
              <form @submit.prevent="subscribe">
                <button type="submit" :disabled="subscribing"
                  class="w-full bg-[#e35d8f] hover:bg-[#c44a7a] disabled:opacity-50 text-white font-bold py-3 rounded-xl transition">
                  {{ subscribing ? t('profile.redirecting') : t('profile.subscribe_now') }}
                </button>
              </form>
            </template>
          </div>

          <!-- Contact Buttons -->
          <div v-if="profile.whatsapp_number || profile.telegram_username" class="bg-[#1a1a1a] border border-white/8 rounded-2xl p-5 space-y-3">
            <p class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-1">{{ t('profile.contact') }}</p>
            <a v-if="profile.whatsapp_number"
              :href="`https://wa.me/${profile.whatsapp_number.replace(/\D/g,'')}`"
              target="_blank"
              class="flex items-center justify-center gap-2.5 w-full bg-[#25D366] hover:bg-[#1fb855] text-white text-sm font-bold px-4 py-3.5 rounded-xl transition shadow-lg shadow-[#25D366]/10">
              <svg class="w-5 h-5 shrink-0" fill="currentColor" viewBox="0 0 24 24"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/></svg>
              {{ t('profile.whatsapp_write') }}
            </a>
            <!-- Call me -->
            <a v-if="profile.whatsapp_number"
              :href="`tel:${profile.whatsapp_number.replace(/\D/g,'')}`"
              class="flex items-center justify-center gap-2.5 w-full bg-[#1a1a1a] hover:bg-white/5 border border-white/15 hover:border-[#e35d8f]/60 text-white text-sm font-bold px-4 py-3.5 rounded-xl transition">
              <svg class="w-5 h-5 shrink-0 text-[#e35d8f]" fill="currentColor" viewBox="0 0 24 24"><path d="M6.62 10.79c1.44 2.83 3.76 5.14 6.59 6.59l2.2-2.2c.27-.27.67-.36 1.02-.24 1.12.37 2.33.57 3.57.57.55 0 1 .45 1 1V20c0 .55-.45 1-1 1-9.39 0-17-7.61-17-17 0-.55.45-1 1-1h3.5c.55 0 1 .45 1 1 0 1.25.2 2.45.57 3.57.11.35.03.74-.25 1.02l-2.2 2.2z"/></svg>
              {{ t('profile.call_me') }} · {{ profile.whatsapp_number }}
            </a>

            <!-- Website -->
            <a v-if="profile.website"
              :href="profile.website"
              target="_blank" rel="noopener noreferrer"
              class="flex items-center justify-center gap-2.5 w-full bg-[#1a1a1a] hover:bg-white/5 border border-white/15 hover:border-[#e35d8f]/60 text-white text-sm font-bold px-4 py-3.5 rounded-xl transition">
              <svg class="w-5 h-5 shrink-0 text-[#e35d8f]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9a9 9 0 01-9-9m9 9c1.657 0 3-4.03 3-9s-1.343-9-3-9m0 18c-1.657 0-3-4.03-3-9s1.343-9 3-9m-9 9a9 9 0 019-9"/>
              </svg>
              {{ t('profile.website_btn') }}
            </a>
            <a v-if="profile.telegram_username"
              :href="`https://t.me/${profile.telegram_username.replace('@','')}`"
              target="_blank"
              class="flex items-center justify-center gap-2.5 w-full bg-[#0088cc] hover:bg-[#0077b5] text-white text-sm font-bold px-4 py-3.5 rounded-xl transition shadow-lg shadow-[#0088cc]/10">
              <svg class="w-5 h-5 shrink-0" fill="currentColor" viewBox="0 0 24 24"><path d="M11.944 0A12 12 0 0 0 0 12a12 12 0 0 0 12 12 12 12 0 0 0 12-12A12 12 0 0 0 12 0a12 12 0 0 0-.056 0zm4.962 7.224c.1-.002.321.023.465.14a.506.506 0 0 1 .171.325c.016.093.036.306.02.472-.18 1.898-.96 6.502-1.36 8.627-.168.9-.499 1.201-.82 1.23-.696.065-1.225-.46-1.9-.902-1.056-.693-1.653-1.124-2.678-1.8-1.185-.78-.417-1.21.258-1.91.177-.184 3.247-2.977 3.307-3.23.007-.032.014-.15-.056-.212s-.174-.041-.249-.024c-.106.024-1.793 1.14-5.061 3.345-.48.33-.913.49-1.302.48-.428-.008-1.252-.241-1.865-.44-.752-.245-1.349-.374-1.297-.789.027-.216.325-.437.893-.663 3.498-1.524 5.83-2.529 6.998-3.014 3.332-1.386 4.025-1.627 4.476-1.635z"/></svg>
              {{ t('profile.telegram_write') }}
            </a>
          </div>

          <!-- Profil teilen -->
          <div class="bg-[#1a1a1a] border border-white/8 rounded-2xl p-5 space-y-2">
            <button @click="shareProfile"
              class="flex items-center justify-center gap-2.5 w-full bg-white/5 hover:bg-white/10 border border-white/10 hover:border-[#e35d8f]/50 text-white text-sm font-bold px-4 py-3.5 rounded-xl transition">
              <svg class="w-5 h-5 shrink-0 text-[#e35d8f]" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.684 13.342C8.886 12.938 9 12.482 9 12c0-.482-.114-.938-.316-1.342m0 2.684a3 3 0 110-2.684m0 2.684l6.632 3.316m-6.632-6l6.632-3.316m0 0a3 3 0 105.367-2.684 3 3 0 00-5.367 2.684zm0 9.316a3 3 0 105.368 2.684 3 3 0 00-5.368-2.684z"/></svg>
              {{ t('profile.share') }}
            </button>
            <p v-if="shareCopied" class="text-center text-xs text-green-400 mt-2">{{ t('profile.link_copied') }}</p>

            <!-- Like -->
            <button type="button" @click="onLikeClick"
              class="flex items-center justify-center gap-2.5 w-full border text-sm font-bold px-4 py-3.5 rounded-xl transition"
              :class="liked
                ? 'bg-[#e35d8f]/10 border-[#e35d8f]/60 text-[#e35d8f]'
                : 'bg-white/5 border-white/10 hover:border-[#e35d8f]/50 text-white hover:text-[#e35d8f]'">
              <svg class="w-5 h-5 shrink-0 text-yellow-400" fill="currentColor" viewBox="0 0 24 24">
                <path d="M7.493 18.5c-.425 0-.82-.236-.975-.632A7.48 7.48 0 016 15.125c0-1.75.599-3.358 1.602-4.634.151-.192.373-.309.6-.397.473-.183.89-.514 1.212-.924a9.042 9.042 0 012.861-2.4c.723-.384 1.35-.956 1.653-1.715a4.498 4.498 0 00.322-1.672V2.5a.75.75 0 01.75-.75 2.25 2.25 0 012.25 2.25c0 1.152-.26 2.243-.723 3.218-.266.558.107 1.282.725 1.282h3.126c1.026 0 1.945.694 2.054 1.715.045.422.068.85.068 1.285a11.95 11.95 0 01-2.649 7.521c-.388.482-.987.729-1.605.729H14.23c-.483 0-.964-.078-1.423-.23l-3.114-1.04a4.501 4.501 0 00-1.423-.23h-.777zM2.331 10.727a11.969 11.969 0 00-.831 4.398 12 12 0 00.52 3.507c.26.85 1.084 1.368 1.973 1.368H4.9c.445 0 .72-.498.523-.898a8.963 8.963 0 01-.924-3.977c0-1.708.476-3.305 1.302-4.666.245-.403-.028-.959-.5-.959H4.25c-.832 0-1.612.453-1.918 1.227z"/>
              </svg>
              {{ liked ? t('profile.liked') : t('profile.like') }}
              <span class="ml-0.5 opacity-70 font-bold">({{ likesCount }})</span>
            </button>

            <!-- Favorit / Follow -->
            <button type="button" @click="onFavoriteClick"
              class="flex items-center justify-center gap-2.5 w-full border text-sm font-bold px-4 py-3.5 rounded-xl transition"
              :class="favorited
                ? 'bg-[#e35d8f]/10 border-[#e35d8f]/60 text-[#e35d8f]'
                : 'bg-white/5 border-white/10 hover:border-[#e35d8f]/50 text-white hover:text-[#e35d8f]'">
              <svg class="w-5 h-5 shrink-0 text-[#e35d8f]" :fill="favorited ? 'currentColor' : 'none'" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M4.318 6.318a4.5 4.5 0 016.364 0L12 7.636l1.318-1.318a4.5 4.5 0 116.364 6.364L12 20.364l-7.682-7.682a4.5 4.5 0 010-6.364z"/>
              </svg>
              {{ favorited ? t('profile.saved_favorite') : t('profile.save_favorite') }}
              <span class="ml-0.5 opacity-70 font-bold">({{ followersCount }})</span>
            </button>
          </div>

          <!-- Address -->
          <div v-if="profile.address" class="bg-[#1a1a1a] border border-white/8 rounded-2xl p-5">
            <p class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-3">{{ t('profile.location') }}</p>
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
              {{ t('profile.open_maps') }}
            </a>
          </div>

          <!-- Stats -->
          <div class="bg-[#1a1a1a] border border-white/8 rounded-2xl p-5">
            <p class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-3">{{ t('profile.stat_section') }}</p>
            <div class="space-y-2.5 text-sm">
              <div class="flex justify-between">
                <span class="text-gray-500">{{ t('profile.listed_since') }}</span>
                <span class="text-gray-300">{{ profile.created_at }}</span>
              </div>
              <div class="flex justify-between">
                <span class="text-gray-500">{{ t('profile.views_count') }}</span>
                <span class="text-white font-semibold">{{ profile.total_views }}</span>
              </div>
              <div class="flex justify-between">
                <span class="text-gray-500">{{ t('profile.likes_count') }}</span>
                <span class="text-white font-semibold">{{ likesCount }}</span>
              </div>
              <div class="flex justify-between">
                <span class="text-gray-500">{{ t('profile.followers_count') }}</span>
                <span class="text-white font-semibold">{{ followersCount }}</span>
              </div>
              <div v-if="reviews.length" class="flex justify-between">
                <span class="text-gray-500">{{ t('profile.rating_label') }}</span>
                <span class="text-white font-semibold">{{ avgRating.toFixed(1) }} ★ ({{ reviews.length }})</span>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- ── LIGHTBOX ──────────────────────────────────────────────────────────── -->
    <div v-if="lightboxItem" class="fixed inset-0 bg-black/95 flex items-center justify-center z-50 p-4" @click.self="lightboxIndex = null">

      <!-- Close -->
      <button class="absolute top-4 right-4 text-white text-3xl hover:text-gray-300 w-10 h-10 flex items-center justify-center z-10" @click="lightboxIndex = null">✕</button>

      <!-- Counter -->
      <div class="absolute top-4 left-1/2 -translate-x-1/2 text-white/50 text-sm tabular-nums z-10">
        {{ lightboxIndex + 1 }} / {{ allMedia.length }}
      </div>

      <!-- Prev arrow -->
      <button v-if="lightboxIndex > 0"
        class="absolute left-3 sm:left-6 top-1/2 -translate-y-1/2 w-11 h-11 bg-black/60 hover:bg-[#e35d8f] rounded-full flex items-center justify-center text-white transition z-10"
        @click.stop="lightboxPrev">
        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15 19l-7-7 7-7"/></svg>
      </button>

      <!-- Next arrow -->
      <button v-if="lightboxIndex < allMedia.length - 1"
        class="absolute right-3 sm:right-6 top-1/2 -translate-y-1/2 w-11 h-11 bg-black/60 hover:bg-[#e35d8f] rounded-full flex items-center justify-center text-white transition z-10"
        @click.stop="lightboxNext">
        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7"/></svg>
      </button>

      <!-- Media -->
      <img v-if="lightboxItem.type === 'image'"
        :src="lightboxItem.src?.full ?? lightboxItem.url"
        class="max-h-[90vh] max-w-[90vw] object-contain rounded-lg" />
      <video v-else
        :src="lightboxItem.url"
        class="max-h-[90vh] max-w-[90vw] rounded-lg"
        controls autoplay playsinline controlsList="nodownload"
        @click.stop />
    </div>

    <!-- ── MEMBER-GATE (Gäste) ───────────────────────────────────────────────── -->
    <MemberGateModal :open="showMemberGate" @close="showMemberGate = false" />
  </AppLayout>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted } from 'vue';
import { Head, Link, router, usePage } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import MediaThumb from '@/Components/MediaThumb.vue';
import MemberGateModal from '@/Components/MemberGateModal.vue';
import { useI18n } from '@/composables/useI18n';

const page = usePage();

const { t } = useI18n();

const mobileCarousel      = ref(null);
const activeCarouselIndex = ref(0);

function onCarouselScroll(e) {
  const el = e.target;
  activeCarouselIndex.value = Math.round(el.scrollLeft / el.offsetWidth);
}

// ── Lightbox ────────────────────────────────────────────────────────────────
const lightboxIndex = ref(null);

const allMedia = computed(() => {
  const media = [...props.publicMedia];
  if (props.isOwner || props.isSubscribed || props.isTrialing) {
    media.push(...props.privateMedia);
  }
  return media;
});

const lightboxItem = computed(() =>
  lightboxIndex.value !== null ? allMedia.value[lightboxIndex.value] ?? null : null
);

function openLightbox(item) {
  const idx = allMedia.value.findIndex(m => m.id === item.id);
  lightboxIndex.value = idx >= 0 ? idx : 0;
}

function lightboxNext() {
  if (lightboxIndex.value !== null && lightboxIndex.value < allMedia.value.length - 1)
    lightboxIndex.value++;
}

function lightboxPrev() {
  if (lightboxIndex.value !== null && lightboxIndex.value > 0)
    lightboxIndex.value--;
}

function onKeydown(e) {
  if (lightboxIndex.value === null) return;
  if (e.key === 'ArrowRight') lightboxNext();
  if (e.key === 'ArrowLeft')  lightboxPrev();
  if (e.key === 'Escape')     lightboxIndex.value = null;
}

onMounted(()   => window.addEventListener('keydown', onKeydown));
onUnmounted(() => window.removeEventListener('keydown', onKeydown));

const props = defineProps({
  profile:             { type: Object, required: true },
  publicMedia:         { type: Array,  default: () => [] },
  privateMedia:        { type: Array,  default: () => [] },
  reviews:             { type: Array,  default: () => [] },
  languages:           { type: Array,  default: () => [] },
  isOwner:             { type: Boolean, default: false },
  isSubscribed:        { type: Boolean, default: false },
  isTrialing:          { type: Boolean, default: false },
  trialEndsAt:         { type: String,  default: null },
  trialDaysLeft:       { type: Number,  default: 0 },
  hasTrialed:          { type: Boolean, default: false },
  hasSubscriptionOffer:{ type: Boolean, default: false },
  hasReviewed:         { type: Boolean, default: false },
  myReview:            { type: Object,  default: null },
  isFavorited:         { type: Boolean, default: false },
  isLiked:             { type: Boolean, default: false },
  subscribed:          { type: Boolean, default: false },
  privateMediaCount:   { type: Number,  default: 0 },
  launchMode:          { type: Boolean, default: false },
  launchGalleryFree:   { type: Boolean, default: false },
  isLaunchUnlocked:    { type: Boolean, default: false },
});

const activeTab        = ref('public');
const favorited        = ref(props.isFavorited);
const liked            = ref(props.isLiked);
const likesCount       = ref(props.profile.likes_count ?? 0);
const followersCount   = ref(props.profile.followers_count ?? 0);
const showMemberGate   = ref(false);
const subscribing      = ref(false);
const trialing         = ref(false);
const submittingReview = ref(false);
const reviewForm       = ref({ stars: props.myReview?.stars ?? 0, comment: props.myReview?.comment ?? '' });
const replyTarget      = ref(null);
const replyText        = ref('');
const reportTarget     = ref(null);
const reportReason     = ref('');

const tabs = computed(() => [
  { key: 'public',  label: t('profile.tab_public'), count: props.publicMedia.length },
  { key: 'private', label: t('profile.tab_private'), count: props.privateMediaCount || props.privateMedia.length },
]);

const avgRating = computed(() => {
  if (!props.reviews.length) return 0;
  return props.reviews.reduce((s, r) => s + r.stars, 0) / props.reviews.length;
});

const photoVerified = computed(() => props.profile.verification_status === 'approved');
const idVerified    = computed(() => props.profile.identity_verification_status === 'approved');

// Outline-Icons (Heroicons-Stil, 24er viewBox) – bewusst andere Motive als die Vorlage
const detailIcons = {
  // Globus → Nationalität
  nationality: 'M12 21a9 9 0 100-18 9 9 0 000 18zm0 0c2.485 0 4.5-4.03 4.5-9S14.485 3 12 3m0 18c-2.485 0-4.5-4.03-4.5-9S9.515 3 12 3M3.6 9h16.8M3.6 15h16.8',
  // Auf-/Ab-Pfeil → Körpergrösse
  height: 'M12 3.75v16.5m0 0l-3.75-3.75M12 20.25l3.75-3.75M12 3.75L8.25 7.5M12 3.75L15.75 7.5',
  // Auge → Augenfarbe
  eye_color: 'M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178zM15 12a3 3 0 11-6 0 3 3 0 016 0z',
  // Person → Körperbau
  body_type: 'M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.5 20.118a7.5 7.5 0 0115 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.5-1.632z',
  // Funkeln → Intimbereich
  intimate_area: 'M9.813 15.904L9 18.75l-.813-2.846a4.5 4.5 0 00-3.09-3.09L2.25 12l2.846-.813a4.5 4.5 0 003.09-3.09L9 5.25l.813 2.846a4.5 4.5 0 003.09 3.09L15.75 12l-2.846.813a4.5 4.5 0 00-3.09 3.09z',
  // Flamme → Rauchen
  smoking: 'M15.362 5.214A8.252 8.252 0 0112 21 8.25 8.25 0 016.038 7.048 8.287 8.287 0 009 9.6a8.983 8.983 0 013.361-6.867 8.21 8.21 0 003 2.48z',
  // Stern → Tattoo
  tattoo: 'M11.48 3.5a.562.562 0 011.04 0l2.125 5.111a.563.563 0 00.475.345l5.518.442c.499.04.701.663.321.988l-4.204 3.602a.563.563 0 00-.182.557l1.285 5.385a.562.562 0 01-.84.61l-4.725-2.885a.562.562 0 00-.586 0L6.982 20.54a.562.562 0 01-.84-.61l1.285-5.386a.562.562 0 00-.182-.557l-4.204-3.602a.562.562 0 01.321-.988l5.518-.442a.563.563 0 00.475-.345L11.48 3.5z',
  // Globus (Meridiane) → Herkunft
  origin: 'M12 21a9 9 0 100-18 9 9 0 000 18zm0 0a14.98 14.98 0 01-3.5-9A14.98 14.98 0 0112 3a14.98 14.98 0 013.5 9 14.98 14.98 0 01-3.5 9z',
  // Waage → Gewicht
  weight: 'M12 3a2.25 2.25 0 00-2.12 1.5H6a1.5 1.5 0 00-1.44 1.08L2.3 13.2A4.5 4.5 0 006.6 19h.3a4.5 4.5 0 004.3-5.8L9.4 6.5h5.2l-1.8 6.7A4.5 4.5 0 0017.1 19h.3a4.5 4.5 0 004.3-5.8l-2.26-7.62A1.5 1.5 0 0018 4.5h-3.88A2.25 2.25 0 0012 3z',
  // Herz → Oberweite
  cup_size: 'M21 8.25c0-2.485-2.099-4.5-4.688-4.5-1.935 0-3.597 1.126-4.312 2.733-.715-1.607-2.377-2.733-4.313-2.733C5.1 3.75 3 5.765 3 8.25c0 7.22 9 12 9 12s9-4.78 9-12z',
  // Kreise → Brusttyp
  breast_type: 'M8.25 15a3.75 3.75 0 100-7.5 3.75 3.75 0 000 7.5zM15.75 15a3.75 3.75 0 100-7.5 3.75 3.75 0 000 7.5z',
};

const originLabels = {
  europaeisch: 'Europäisch (Weiß)', asiatisch: 'Asiatisch', schwarz: 'Schwarz',
  indisch: 'Indisch', latina: 'Latina (Hispanisch)', gemischt: 'Gemischt',
};
const breastLabels = { natur: 'Natur', implantate: 'Implantate' };

const langFlags = {
  de: '🇩🇪', en: '🇬🇧', fr: '🇫🇷', es: '🇪🇸', it: '🇮🇹',
  hu: '🇭🇺', ro: '🇷🇴', pt: '🇵🇹', ru: '🇷🇺', other: '🌐',
};
const langFlag = (code) => langFlags[code] ?? '🌐';

const details = computed(() => {
  const p = props.profile;
  const yesNo = (v) => (v ? t('profile.yes') : t('profile.no'));
  const items = [
    p.nationality           && { key: 'nationality',   label: t('profile.nationality'),   value: p.nationality },
    p.origin                && { key: 'origin',         label: t('profile.origin'),        value: originLabels[p.origin] ?? p.origin },
    p.height_cm             && { key: 'height',         label: t('profile.height'),        value: `${p.height_cm} cm` },
    p.weight_kg             && { key: 'weight',         label: t('profile.weight'),        value: `${p.weight_kg} kg` },
    p.eye_color             && { key: 'eye_color',      label: t('profile.eye_color'),     value: p.eye_color },
    p.cup_size              && { key: 'cup_size',       label: t('profile.cup_size'),      value: p.cup_size },
    p.breast_type           && { key: 'breast_type',    label: t('profile.breast_type'),   value: breastLabels[p.breast_type] ?? p.breast_type },
    p.body_type             && { key: 'body_type',      label: t('profile.body_type'),     value: p.body_type },
    p.intimate_area         && { key: 'intimate_area',  label: t('profile.intimate_area'), value: p.intimate_area },
    p.smoking !== null && p.smoking !== undefined && { key: 'smoking', label: t('profile.smoking'), value: yesNo(p.smoking) },
    p.tattoo  !== null && p.tattoo  !== undefined && { key: 'tattoo',  label: t('profile.tattoo'),  value: yesNo(p.tattoo) },
  ];
  return items.filter(Boolean).map((i) => ({ ...i, icon: detailIcons[i.key] }));
});

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
  if (!confirm(t('profile.cancel_confirm'))) return;
  router.post(route('konto.cancel', props.profile.slug));
}

function submitReview() {
  if (!reviewForm.value.stars) return;
  submittingReview.value = true;
  const opts = {
    preserveScroll: true,
    onFinish: () => { submittingReview.value = false; },
  };
  if (props.myReview) {
    // Bearbeiten → geht serverseitig erneut auf 'pending'
    router.put(route('konto.review.update', props.myReview.id), reviewForm.value, opts);
  } else {
    router.post(route('konto.review.store', props.profile.slug), reviewForm.value, opts);
  }
}

function submitReply(reviewId) {
  if (!replyText.value.trim()) return;
  router.post(route('inserat.review.reply', reviewId), { reply: replyText.value }, {
    preserveScroll: true,
    onSuccess: () => { replyTarget.value = null; replyText.value = ''; },
  });
}

function submitReport(reviewId) {
  if (!reportReason.value.trim()) return;
  router.post(route('inserat.review.report', reviewId), { reason: reportReason.value }, {
    preserveScroll: true,
    onSuccess: () => { reportTarget.value = null; reportReason.value = ''; },
  });
}

// Like: 1 pro Member (Toggle). Gäste sehen den Member-Gate-Dialog.
function onLikeClick() {
  if (!page.props.auth?.user) { showMemberGate.value = true; return; }
  router.post(route('konto.likes.toggle', props.profile.slug), {}, {
    preserveScroll: true,
    onSuccess: () => {
      liked.value = !liked.value;
      likesCount.value = Math.max(0, likesCount.value + (liked.value ? 1 : -1));
    },
  });
}

// Favorit = Follow. Followers = Anzahl Member, die favorisiert haben.
function onFavoriteClick() {
  if (!page.props.auth?.user) { showMemberGate.value = true; return; }
  router.post(route('konto.favorites.toggle', props.profile.slug), {}, {
    preserveScroll: true,
    onSuccess: () => {
      favorited.value = !favorited.value;
      followersCount.value = Math.max(0, followersCount.value + (favorited.value ? 1 : -1));
    },
  });
}

const shareCopied = ref(false);
async function shareProfile() {
  const url = window.location.href;
  if (navigator.share) {
    try { await navigator.share({ title: props.profile.display_name, url }); } catch {}
  } else {
    try {
      await navigator.clipboard.writeText(url);
      shareCopied.value = true;
      setTimeout(() => { shareCopied.value = false; }, 2000);
    } catch {}
  }
}
</script>
