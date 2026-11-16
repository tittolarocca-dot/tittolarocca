<template>
  <AppLayout>
    <Head :title="seoTitle || t('home.title')" />

    <!-- =================================================================== -->
    <!-- V2 – neues Design (hinter BOOKLOLA_UI_V2). Nutzt EXAKT dieselben     -->
    <!-- Props, filters-State und applyFilters() wie V1 – nur Darstellung.   -->
    <!-- =================================================================== -->
    <template v-if="uiV2">

      <!-- ── Hero (fixiert – bleibt beim Scrollen oben; Kreisel + Filter scrollen mit) ── -->
      <section class="lg:sticky lg:top-[60px] lg:z-30" style="background:var(--bl-bg);">
        <div class="max-w-7xl mx-auto px-4 pt-3 pb-4 sm:pt-4 flex flex-col xl:flex-row xl:items-center xl:justify-between gap-5 xl:gap-4">

          <!-- Titel + Subline -->
          <div class="shrink-0">
            <!-- Startseite: gestylter Marketing-Titel mit Pink-Akzent (Desktop: eine Zeile) -->
            <h1 v-if="!seoHeading" class="text-[34px] md:text-[40px] xl:whitespace-nowrap" style="color:var(--bl-text);font-weight:700;line-height:1.02;letter-spacing:-1.7px;">
              <template v-if="heroTitleParts.accent">{{ heroTitleParts.pre }}<span style="color:var(--bl-pink);">{{ heroTitleParts.accent }}</span>{{ heroTitleParts.post }}</template>
              <template v-else>{{ heroTitleParts.pre }}</template>
            </h1>
            <!-- Landingpage (Stadt/Kategorie): SEO-Heading bleibt H1 (darf umbrechen) -->
            <h1 v-else class="text-[34px] md:text-[40px]" style="color:var(--bl-text);font-weight:700;line-height:1.02;letter-spacing:-1.7px;">
              {{ seoHeading }}
            </h1>
            <p class="mt-3 text-[20px] md:text-[25px]" style="color:#f0f0f3;font-weight:500;">
              {{ t('home.hero_subline') }}
            </p>
          </div>

          <!-- Trust-Punkte: rechts, mit Unterzeile + Trenner -->
          <div class="flex flex-col sm:flex-row sm:items-center gap-4 sm:gap-0 shrink-0">
            <div v-for="(trust, i) in trustItems" :key="trust.title"
              class="flex items-center gap-2 sm:px-[11px] first:sm:pl-0 last:sm:pr-0"
              :class="i > 0 ? 'sm:border-l' : ''"
              :style="i > 0 ? 'border-color:var(--bl-border);' : ''">
              <span class="inline-flex items-center justify-center w-7 h-7 rounded-lg shrink-0" style="background:var(--bl-grad-soft);color:var(--bl-pink);">
                <span v-html="trust.icon" class="w-[15px] h-[15px] inline-flex"></span>
              </span>
              <div class="leading-tight">
                <div class="text-[12px] font-bold whitespace-nowrap" style="color:var(--bl-text);">{{ trust.title }}</div>
                <div class="text-[10px] mt-0.5 whitespace-nowrap" style="color:var(--bl-text-muted);">{{ trust.sub }}</div>
              </div>
            </div>
          </div>
        </div>
      </section>

      <!-- ── Kreis-Navigation ───────────────────────────────────────────── -->
      <section v-if="profiles.data.length" style="background:var(--bl-bg);">
        <div class="max-w-7xl mx-auto px-4">
          <div class="relative">

          <!-- Pfeil links -->
          <button v-show="canScrollLeft" @click="scrollCircles(-1)" type="button" aria-label="Zurück"
            class="hidden sm:flex absolute left-0 top-[34px] -translate-y-1/2 z-20 items-center justify-center w-10 h-10 rounded-full transition hover:scale-105"
            style="background:var(--bl-grad);color:#fff;box-shadow:var(--bl-glow);">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2.6" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 6l-6 6 6 6"/></svg>
          </button>
          <!-- weicher Verlauf links -->
          <div v-show="canScrollLeft" class="hidden sm:block pointer-events-none absolute left-0 top-0 bottom-0 w-14 z-10" style="background:linear-gradient(to right, var(--bl-bg), transparent);"></div>

          <div ref="circleScroll" @scroll="updateCircleArrows"
            class="flex gap-3 sm:gap-4 overflow-x-auto pt-1 pb-3 scrollbar-hide">
            <a v-for="nav in circleNav" :key="nav.label" :href="nav.href"
              class="flex flex-col items-center shrink-0 w-16 sm:w-[76px] cursor-pointer group">
              <div class="w-14 h-14 sm:w-[68px] sm:h-[68px] rounded-full flex items-center justify-center transition ring-2 ring-[#e35d8f] ring-offset-2 ring-offset-[#1b1b1b] group-hover:ring-[#f08ab0]"
                style="background:linear-gradient(145deg,#e35d8f 0%,#c44a7a 55%,#8b3ad6 100%);box-shadow:var(--bl-glow);">
                <span v-html="nav.icon" class="inline-flex items-center justify-center" style="color:#fff;width:28px;height:28px;"></span>
              </div>
              <span class="text-[11px] font-bold mt-1.5 text-center w-full truncate" style="color:var(--bl-pink);">{{ nav.label }}</span>
            </a>

            <a v-for="profile in profiles.data.slice(0,12)" :key="'c'+profile.id"
              :href="route('profile.show', profile.slug)"
              class="flex flex-col items-center shrink-0 w-16 sm:w-[76px] cursor-pointer group">
              <div class="w-14 h-14 sm:w-[68px] sm:h-[68px] rounded-full p-[2px] transition" style="background:var(--bl-grad);">
                <span class="block w-full h-full rounded-full overflow-hidden" style="background:var(--bl-surface);">
                  <img v-if="profile.public_media?.[0]"
                    class="lazyload w-full h-full object-cover object-top"
                    :data-src="mediaSrc(profile.public_media[0], 'thumbnail')"
                    src="data:image/gif;base64,R0lGODlhAQABAAAAACH5BAEKAAEALAAAAAABAAEAAAICTAEAOw=="
                    :alt="profile.display_name" width="68" height="68" />
                  <span v-else class="w-full h-full flex items-center justify-center text-xl">👤</span>
                </span>
              </div>
              <span class="text-[11px] mt-1.5 text-center truncate w-full transition" style="color:var(--bl-text-muted);">{{ profile.display_name }}</span>
            </a>
          </div>

          <!-- weicher Verlauf rechts -->
          <div v-show="canScrollRight" class="hidden sm:block pointer-events-none absolute right-0 top-0 bottom-0 w-16 z-10" style="background:linear-gradient(to left, var(--bl-bg) 35%, transparent);"></div>
          <!-- Pfeil rechts -->
          <button v-show="canScrollRight" @click="scrollCircles(1)" type="button" aria-label="Weiter"
            class="hidden sm:flex absolute right-0 top-[34px] -translate-y-1/2 z-20 items-center justify-center w-10 h-10 rounded-full transition hover:scale-105"
            style="background:var(--bl-grad);color:#fff;box-shadow:var(--bl-glow);">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2.6" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 6l6 6-6 6"/></svg>
          </button>
          </div>
        </div>
      </section>

      <!-- ── Filterleiste ───────────────────────────────────────────────── -->
      <section style="background:var(--bl-bg);">
        <div class="max-w-7xl mx-auto px-4 pt-1 pb-4">
          <div class="rounded-2xl p-2.5 sm:p-3" style="background:var(--bl-surface);border:1px solid var(--bl-border);box-shadow:var(--bl-shadow);">
            <div class="grid grid-cols-2 sm:flex sm:flex-wrap gap-2">

              <!-- Region -->
              <div class="relative">
                <select v-model="filters.city" :class="v2Control" style="background:var(--bl-surface-2);border:1px solid var(--bl-border);color:var(--bl-text);">
                  <option value="">{{ t('home.region') }}</option>
                  <option v-for="c in cities" :key="c.id" :value="c.slug">{{ c.name }}</option>
                </select>
                <svg class="pointer-events-none absolute right-2.5 top-3 w-4 h-4" style="color:var(--bl-text-muted);" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
              </div>

              <!-- Alter -->
              <div class="relative">
                <button type="button" @click="ageMenuOpen = !ageMenuOpen" :class="v2Trigger"
                  :style="filters.ages.length ? v2TriggerActive : v2TriggerIdle">
                  <span class="truncate">{{ filters.ages.length ? t('home.age') + ' (' + filters.ages.length + ')' : t('home.age') }}</span>
                  <svg class="shrink-0 w-4 h-4 transition-transform" :class="ageMenuOpen ? 'rotate-180' : ''" style="color:var(--bl-text-muted);" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                </button>
                <div v-if="ageMenuOpen" class="fixed inset-0 z-30" @click="ageMenuOpen = false"></div>
                <div v-if="ageMenuOpen" :class="v2Panel" class="w-56 max-w-[80vw]" style="background:var(--bl-surface-2);border:1px solid var(--bl-border);">
                  <label v-for="a in ageOptions" :key="a" :class="v2Option">
                    <input type="checkbox" :value="a" v-model="filters.ages" class="w-4 h-4 rounded cursor-pointer" style="accent-color:var(--bl-pink);" />
                    <span class="text-sm" style="color:var(--bl-text);">{{ a }}</span>
                  </label>
                  <div v-if="filters.ages.length" class="border-t mt-1 pt-1 px-1" style="border-color:var(--bl-border);">
                    <button type="button" @click="filters.ages = []" class="w-full text-left text-xs px-2 py-1.5 transition" style="color:var(--bl-text-muted);">✕ {{ t('home.reset') }}</button>
                  </div>
                </div>
              </div>

              <!-- Rubrik -->
              <div class="relative">
                <button type="button" @click="categoryMenuOpen = !categoryMenuOpen" :class="v2Trigger"
                  :style="filters.categories.length ? v2TriggerActive : v2TriggerIdle">
                  <span class="truncate">{{ filters.categories.length ? t('home.category') + ' (' + filters.categories.length + ')' : t('home.category') }}</span>
                  <svg class="shrink-0 w-4 h-4 transition-transform" :class="categoryMenuOpen ? 'rotate-180' : ''" style="color:var(--bl-text-muted);" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                </button>
                <div v-if="categoryMenuOpen" class="fixed inset-0 z-30" @click="categoryMenuOpen = false"></div>
                <div v-if="categoryMenuOpen" :class="v2Panel" class="w-64 max-w-[80vw]" style="background:var(--bl-surface-2);border:1px solid var(--bl-border);">
                  <label v-for="c in categories" :key="c.id" :class="v2Option">
                    <input type="checkbox" :value="c.slug" v-model="filters.categories" class="w-4 h-4 rounded cursor-pointer" style="accent-color:var(--bl-pink);" />
                    <span class="text-sm truncate" style="color:var(--bl-text);">{{ c.name }}</span>
                  </label>
                  <div v-if="filters.categories.length" class="border-t mt-1 pt-1 px-1" style="border-color:var(--bl-border);">
                    <button type="button" @click="filters.categories = []" class="w-full text-left text-xs px-2 py-1.5 transition" style="color:var(--bl-text-muted);">✕ {{ t('home.reset') }}</button>
                  </div>
                </div>
              </div>

              <!-- Service -->
              <div class="relative">
                <button type="button" @click="serviceMenuOpen = !serviceMenuOpen" :class="v2Trigger"
                  :style="filters.services.length ? v2TriggerActive : v2TriggerIdle">
                  <span class="truncate">{{ filters.services.length ? t('home.service') + ' (' + filters.services.length + ')' : t('home.service') }}</span>
                  <svg class="shrink-0 w-4 h-4 transition-transform" :class="serviceMenuOpen ? 'rotate-180' : ''" style="color:var(--bl-text-muted);" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                </button>
                <div v-if="serviceMenuOpen" class="fixed inset-0 z-30" @click="serviceMenuOpen = false"></div>
                <div v-if="serviceMenuOpen" :class="v2Panel" class="w-64 max-w-[80vw]" style="background:var(--bl-surface-2);border:1px solid var(--bl-border);">
                  <label v-for="s in services" :key="s.id" :class="v2Option">
                    <input type="checkbox" :value="s.slug" v-model="filters.services" class="w-4 h-4 rounded cursor-pointer" style="accent-color:var(--bl-pink);" />
                    <span class="text-sm truncate" style="color:var(--bl-text);">{{ s.name }}</span>
                  </label>
                  <div v-if="filters.services.length" class="border-t mt-1 pt-1 px-1" style="border-color:var(--bl-border);">
                    <button type="button" @click="filters.services = []" class="w-full text-left text-xs px-2 py-1.5 transition" style="color:var(--bl-text-muted);">✕ {{ t('home.reset') }}</button>
                  </div>
                </div>
              </div>

              <!-- Verifiziert -->
              <div class="relative">
                <select v-model="filters.verified" @change="applyFilters" :class="v2Control" style="background:var(--bl-surface-2);border:1px solid var(--bl-border);color:var(--bl-text);">
                  <option value="">{{ t('home.verified') }}</option>
                  <option value="ja">{{ t('home.yes') }}</option>
                  <option value="nein">{{ t('home.no') }}</option>
                </select>
                <svg class="pointer-events-none absolute right-2.5 top-3 w-4 h-4" style="color:var(--bl-text-muted);" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
              </div>

              <!-- Suche (Text) -->
              <div class="relative col-span-2 sm:col-span-1 sm:flex-1 sm:min-w-[180px]">
                <input v-model="filters.search" type="text" :placeholder="t('home.search')" @keyup.enter="applyFilters"
                  class="w-full text-sm rounded-xl px-4 py-2.5 pr-10 focus:outline-none"
                  style="background:var(--bl-surface-2);border:1px solid var(--bl-border);color:var(--bl-text);" />
                <svg class="absolute right-3 top-3 w-4 h-4" style="color:var(--bl-text-muted);" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
              </div>

              <!-- Suchen-Button -->
              <button @click="applyFilters"
                class="col-span-2 sm:col-span-1 inline-flex items-center justify-center gap-1.5 text-sm font-bold px-5 py-2.5 rounded-xl text-white transition"
                style="background:var(--bl-grad);box-shadow:var(--bl-glow);">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.2"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                {{ t('home.search_btn') }}
              </button>
            </div>
          </div>

          <!-- Aktive Filter + Anzahl -->
          <div class="flex items-center justify-between text-xs mt-3" style="color:var(--bl-text-muted);">
            <div class="flex flex-wrap gap-1.5">
              <span v-if="activeCity" class="px-2.5 py-1 rounded-full" style="background:var(--bl-grad-soft);color:var(--bl-pink);">{{ activeCity.name }}</span>
              <span v-for="name in activeCategoryNames" :key="name" class="px-2.5 py-1 rounded-full" style="background:var(--bl-grad-soft);color:var(--bl-pink);">{{ name }}</span>
              <span v-for="name in activeServiceNames" :key="name" class="px-2.5 py-1 rounded-full" style="background:var(--bl-grad-soft);color:var(--bl-pink);">{{ name }}</span>
              <span v-for="a in (activeAges || [])" :key="a" class="px-2.5 py-1 rounded-full" style="background:var(--bl-grad-soft);color:var(--bl-pink);">{{ t('home.age') }} {{ a }}</span>
              <Link v-if="activeCity || activeCategoryNames.length || activeServiceNames.length || (activeAges && activeAges.length)" :href="route('home')" class="px-2 py-1 transition" style="color:var(--bl-text-muted);">✕ {{ t('home.reset') }}</Link>
            </div>
            <span class="shrink-0 ml-2">{{ profiles.total }} {{ t('home.listings') }}</span>
          </div>
        </div>
      </section>

      <!-- ── SEO-Landing (Breadcrumb + Text + interne Links) ─────────────── -->
      <div v-if="isLanding" class="max-w-7xl mx-auto px-4 pt-1 pb-2">
        <nav v-if="crumbs.length" class="flex flex-wrap items-center gap-1.5 text-xs mb-3" style="color:var(--bl-text-muted);" aria-label="Breadcrumb">
          <template v-for="(c, i) in crumbs" :key="i">
            <Link v-if="c.url" :href="c.url" class="transition" style="color:var(--bl-text-muted);">{{ c.name }}</Link>
            <span v-else style="color:var(--bl-text);">{{ c.name }}</span>
            <span v-if="i < crumbs.length - 1" style="opacity:.4;">›</span>
          </template>
        </nav>
        <p v-if="cityIntro || seoDescription" class="text-sm leading-relaxed max-w-3xl" style="color:var(--bl-text-muted);">{{ cityIntro || seoDescription }}</p>
        <div v-if="cityCategoryLinks.length" class="mt-3 flex flex-wrap gap-2">
          <Link v-for="l in cityCategoryLinks" :key="l.url" :href="l.url"
            class="text-xs rounded-full px-3 py-1 transition" style="background:var(--bl-surface);border:1px solid var(--bl-border);color:var(--bl-text-muted);">{{ l.name }}</Link>
        </div>
      </div>

      <!-- ── Listings ───────────────────────────────────────────────────── -->
      <div class="max-w-7xl mx-auto px-4 pb-14 pt-4">
        <div v-if="profiles.data.length === 0" class="text-center py-20" style="color:var(--bl-text-muted);">
          <div class="text-5xl mb-4">🔍</div>
          <p class="text-lg">{{ t('home.no_results') }}</p>
        </div>

        <div v-else class="space-y-4">
          <SpotlightCardV2 v-if="profiles.data[0]" :profile="profiles.data[0]" />
          <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
            <ProfileCardV2 v-for="profile in profiles.data.slice(1)" :key="profile.id" :profile="profile" />
          </div>
        </div>

        <!-- Pagination -->
        <div v-if="profiles.last_page > 1" class="flex justify-center flex-wrap gap-2 mt-8">
          <Link v-for="link in profiles.links" :key="link.label" :href="link.url || '#'" v-html="link.label"
            class="px-3.5 py-2 text-sm rounded-xl transition"
            :style="link.active
              ? 'background:var(--bl-grad);color:#fff;'
              : 'background:var(--bl-surface);border:1px solid var(--bl-border);color:var(--bl-text-muted);'" />
        </div>
      </div>
    </template>

    <!-- =================================================================== -->
    <!-- V1 – bestehendes Design (unverändert, Fallback bei uiV2 = false)    -->
    <!-- =================================================================== -->
    <template v-else>
    <!-- Hero + Filters (ab Tablet/Desktop fixiert, damit nur die Inserate scrollen) -->
    <div class="bg-[#111] border-b border-white/5 pt-5 pb-4 px-4 md:sticky md:top-13 md:z-30">
      <div class="max-w-7xl mx-auto">
        <h1 class="text-2xl sm:text-3xl md:text-4xl font-black text-[#e35d8f] mb-4">
          {{ seoHeading || t('home.hero') }}
        </h1>

        <!-- Featured Profiles Carousel -->
        <div v-if="profiles.data.length" class="relative mb-4">
          <div class="flex gap-3 overflow-x-auto pt-1 pb-2 scrollbar-hide">
            <!-- Neue Bilder -->
            <a :href="route('neue-bilder')"
              class="flex flex-col items-center shrink-0 w-16 sm:w-20 cursor-pointer group">
              <div class="w-14 h-14 sm:w-[72px] sm:h-[72px] rounded-full ring-2 ring-[#e35d8f] ring-offset-2 ring-offset-[#111] group-hover:ring-[#f08ab0] transition bg-gradient-to-br from-[#e35d8f] via-[#c44a7a] to-[#7c3aed] flex items-center justify-center shadow-lg shadow-[#e35d8f]/40">
                <svg class="w-6 h-6 sm:w-7 sm:h-7 text-white drop-shadow" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
              </div>
              <span class="text-xs text-[#e35d8f] font-bold mt-1 text-center w-full">{{ t('home.new_images') }}</span>
            </a>

            <!-- Feed (Community) -->
            <a :href="route('feed')"
              class="flex flex-col items-center shrink-0 w-16 sm:w-20 cursor-pointer group">
              <div class="w-14 h-14 sm:w-[72px] sm:h-[72px] rounded-full ring-2 ring-[#e35d8f] ring-offset-2 ring-offset-[#111] group-hover:ring-[#f08ab0] transition bg-gradient-to-br from-[#e35d8f] via-[#7c3aed] to-[#c44a7a] flex items-center justify-center shadow-lg shadow-[#e35d8f]/40">
                <svg class="w-6 h-6 sm:w-7 sm:h-7 text-white drop-shadow" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 7.5h1.5m-1.5 3h1.5m-7.5 3h7.5m-7.5 3h7.5m3-9h3.375c.621 0 1.125.504 1.125 1.125V18a2.25 2.25 0 01-2.25 2.25M16.5 7.5V18a2.25 2.25 0 002.25 2.25M16.5 7.5V4.875c0-.621-.504-1.125-1.125-1.125H4.125C3.504 3.75 3 4.254 3 4.875V18a2.25 2.25 0 002.25 2.25h13.5M6 7.5h3v3H6v-3z"/></svg>
              </div>
              <span class="text-xs text-[#e35d8f] font-bold mt-1 text-center w-full">Feed</span>
            </a>

            <!-- Suchen -->
            <a :href="route('search')"
              class="flex flex-col items-center shrink-0 w-16 sm:w-20 cursor-pointer group">
              <div class="w-14 h-14 sm:w-[72px] sm:h-[72px] rounded-full ring-2 ring-[#e35d8f] ring-offset-2 ring-offset-[#111] group-hover:ring-[#f08ab0] transition bg-gradient-to-br from-[#7c3aed] via-[#c44a7a] to-[#e35d8f] flex items-center justify-center shadow-lg shadow-[#e35d8f]/40">
                <svg class="w-6 h-6 sm:w-7 sm:h-7 text-white drop-shadow" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
              </div>
              <span class="text-xs text-[#e35d8f] font-bold mt-1 text-center w-full">{{ t('home.search_btn') }}</span>
            </a>

            <!-- Clubs -->
            <a :href="route('clubs.index')"
              class="flex flex-col items-center shrink-0 w-16 sm:w-20 cursor-pointer group">
              <div class="w-14 h-14 sm:w-[72px] sm:h-[72px] rounded-full ring-2 ring-[#e35d8f] ring-offset-2 ring-offset-[#111] group-hover:ring-[#f08ab0] transition bg-gradient-to-br from-[#c44a7a] via-[#e35d8f] to-[#7c3aed] flex items-center justify-center shadow-lg shadow-[#e35d8f]/40">
                <svg class="w-6 h-6 sm:w-7 sm:h-7 text-white drop-shadow" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M3 21h18M4 21V7l8-4 8 4v14M9 21v-5a1 1 0 011-1h4a1 1 0 011 1v5M9 9h.01M15 9h.01M9 12h.01M15 12h.01"/></svg>
              </div>
              <span class="text-xs text-[#e35d8f] font-bold mt-1 text-center w-full">{{ t('home.clubs') }}</span>
            </a>

            <a v-for="profile in profiles.data.slice(0,12)" :key="profile.id"
              :href="route('profile.show', profile.slug)"
              class="flex flex-col items-center shrink-0 w-16 sm:w-20 cursor-pointer group">
              <div class="w-14 h-14 sm:w-[72px] sm:h-[72px] rounded-full overflow-hidden ring-2 ring-[#e35d8f] ring-offset-2 ring-offset-[#111] group-hover:ring-[#f08ab0] transition">
                <img v-if="profile.public_media?.[0]"
                  class="lazyload w-full h-full object-cover object-top"
                  :data-src="mediaSrc(profile.public_media[0], 'thumbnail')"
                  src="data:image/gif;base64,R0lGODlhAQABAAAAACH5BAEKAAEALAAAAAABAAEAAAICTAEAOw=="
                  :alt="profile.display_name"
                  width="72" height="72" />
                <div v-else class="w-full h-full bg-white/5 flex items-center justify-center text-xl">👤</div>
              </div>
              <span class="text-xs text-gray-400 mt-1 text-center truncate w-full group-hover:text-[#e35d8f] transition">{{ profile.display_name }}</span>
            </a>
          </div>
        </div>

        <!-- Filter Bar -->
        <div class="grid grid-cols-2 sm:flex sm:flex-wrap gap-2 sm:gap-3 mb-3">
          <div class="relative">
            <select v-model="filters.city"
              class="w-full bg-[#1a1a1a] border border-white/10 text-gray-200 text-sm rounded px-3 py-2.5 pr-8 appearance-none cursor-pointer hover:border-[#e35d8f] transition sm:min-w-[130px] focus:outline-none focus:border-[#e35d8f]">
              <option value="">{{ t('home.region') }}</option>
              <option v-for="c in cities" :key="c.id" :value="c.slug">{{ c.name }}</option>
            </select>
            <svg class="pointer-events-none absolute right-2 top-3 w-4 h-4 text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
          </div>
          <!-- Alter: Mehrfachauswahl (Checkboxen) -->
          <div class="relative">
            <button type="button" @click="ageMenuOpen = !ageMenuOpen"
              class="w-full flex items-center justify-between gap-2 bg-[#1a1a1a] border text-sm rounded px-3 py-2.5 cursor-pointer transition sm:min-w-[130px] focus:outline-none"
              :class="[filters.ages.length ? 'border-[#e35d8f] text-white' : 'border-white/10 text-gray-200', 'hover:border-[#e35d8f]']">
              <span class="truncate">
                {{ filters.ages.length ? t('home.age') + ' (' + filters.ages.length + ')' : t('home.age') }}
              </span>
              <svg class="shrink-0 w-4 h-4 text-gray-500 transition-transform" :class="ageMenuOpen ? 'rotate-180' : ''" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
            </button>
            <div v-if="ageMenuOpen" class="fixed inset-0 z-30" @click="ageMenuOpen = false"></div>
            <div v-if="ageMenuOpen"
              class="absolute z-40 mt-1 left-0 w-56 max-w-[80vw] max-h-72 overflow-y-auto bg-[#1a1a1a] border border-white/15 rounded-lg shadow-xl shadow-black/50 p-1">
              <label v-for="a in ageOptions" :key="a"
                class="flex items-center gap-2 px-2.5 py-2 rounded cursor-pointer hover:bg-white/5 transition">
                <input type="checkbox" :value="a" v-model="filters.ages"
                  class="w-4 h-4 rounded accent-[#e35d8f] cursor-pointer" />
                <span class="text-sm text-gray-200">{{ a === '55+' ? '55+' : a }}</span>
              </label>
              <div v-if="filters.ages.length" class="border-t border-white/10 mt-1 pt-1 px-1">
                <button type="button" @click="filters.ages = []"
                  class="w-full text-left text-xs text-gray-400 hover:text-[#e35d8f] px-2 py-1.5 transition">
                  ✕ {{ t('home.reset') }}
                </button>
              </div>
            </div>
          </div>
          <!-- Rubrik: Mehrfachauswahl (Checkboxen) -->
          <div class="relative">
            <button type="button" @click="categoryMenuOpen = !categoryMenuOpen"
              class="w-full flex items-center justify-between gap-2 bg-[#1a1a1a] border text-sm rounded px-3 py-2.5 cursor-pointer transition sm:min-w-[130px] focus:outline-none"
              :class="[filters.categories.length ? 'border-[#e35d8f] text-white' : 'border-white/10 text-gray-200', 'hover:border-[#e35d8f]']">
              <span class="truncate">
                {{ filters.categories.length ? t('home.category') + ' (' + filters.categories.length + ')' : t('home.category') }}
              </span>
              <svg class="shrink-0 w-4 h-4 text-gray-500 transition-transform" :class="categoryMenuOpen ? 'rotate-180' : ''" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
            </button>
            <div v-if="categoryMenuOpen" class="fixed inset-0 z-30" @click="categoryMenuOpen = false"></div>
            <div v-if="categoryMenuOpen"
              class="absolute z-40 mt-1 left-0 w-64 max-w-[80vw] max-h-72 overflow-y-auto bg-[#1a1a1a] border border-white/15 rounded-lg shadow-xl shadow-black/50 p-1">
              <label v-for="c in categories" :key="c.id"
                class="flex items-center gap-2 px-2.5 py-2 rounded cursor-pointer hover:bg-white/5 transition">
                <input type="checkbox" :value="c.slug" v-model="filters.categories"
                  class="w-4 h-4 rounded accent-[#e35d8f] cursor-pointer" />
                <span class="text-sm text-gray-200 truncate">{{ c.name }}</span>
              </label>
              <div v-if="filters.categories.length" class="border-t border-white/10 mt-1 pt-1 px-1">
                <button type="button" @click="filters.categories = []"
                  class="w-full text-left text-xs text-gray-400 hover:text-[#e35d8f] px-2 py-1.5 transition">
                  ✕ {{ t('home.reset') }}
                </button>
              </div>
            </div>
          </div>
          <!-- Service: Mehrfachauswahl (Checkboxen) -->
          <div class="relative">
            <button type="button" @click="serviceMenuOpen = !serviceMenuOpen"
              class="w-full flex items-center justify-between gap-2 bg-[#1a1a1a] border text-sm rounded px-3 py-2.5 cursor-pointer transition sm:min-w-[130px] focus:outline-none"
              :class="[filters.services.length ? 'border-[#e35d8f] text-white' : 'border-white/10 text-gray-200', 'hover:border-[#e35d8f]']">
              <span class="truncate">
                {{ filters.services.length ? t('home.service') + ' (' + filters.services.length + ')' : t('home.service') }}
              </span>
              <svg class="shrink-0 w-4 h-4 text-gray-500 transition-transform" :class="serviceMenuOpen ? 'rotate-180' : ''" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
            </button>

            <!-- Backdrop zum Schließen -->
            <div v-if="serviceMenuOpen" class="fixed inset-0 z-30" @click="serviceMenuOpen = false"></div>

            <!-- Dropdown-Panel -->
            <div v-if="serviceMenuOpen"
              class="absolute z-40 mt-1 left-0 w-64 max-w-[80vw] max-h-72 overflow-y-auto bg-[#1a1a1a] border border-white/15 rounded-lg shadow-xl shadow-black/50 p-1">
              <label v-for="s in services" :key="s.id"
                class="flex items-center gap-2 px-2.5 py-2 rounded cursor-pointer hover:bg-white/5 transition">
                <input type="checkbox" :value="s.slug" v-model="filters.services"
                  class="w-4 h-4 rounded accent-[#e35d8f] cursor-pointer" />
                <span class="text-sm text-gray-200 truncate">{{ s.name }}</span>
              </label>
              <div v-if="filters.services.length" class="border-t border-white/10 mt-1 pt-1 px-1">
                <button type="button" @click="filters.services = []"
                  class="w-full text-left text-xs text-gray-400 hover:text-[#e35d8f] px-2 py-1.5 transition">
                  ✕ {{ t('home.reset') }}
                </button>
              </div>
            </div>
          </div>
          <div class="relative">
            <select v-model="filters.verified" @change="applyFilters"
              class="w-full bg-[#1a1a1a] border border-white/10 text-gray-200 text-sm rounded px-3 py-2.5 pr-8 appearance-none cursor-pointer hover:border-[#e35d8f] transition sm:min-w-[130px] focus:outline-none focus:border-[#e35d8f]">
              <option value="">{{ t('home.verified') }}</option>
              <option value="ja">{{ t('home.yes') }}</option>
              <option value="nein">{{ t('home.no') }}</option>
            </select>
            <svg class="pointer-events-none absolute right-2 top-3 w-4 h-4 text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
          </div>
          <button @click="applyFilters" class="bg-[#e35d8f] hover:bg-[#c44a7a] text-white text-sm font-bold px-4 py-2.5 rounded transition sm:hidden">
            {{ t('home.search_btn') }}
          </button>
          <div class="relative col-span-2 sm:col-span-1 sm:flex-1">
            <input v-model="filters.search" type="text" :placeholder="t('home.search')"
              @keyup.enter="applyFilters"
              class="w-full bg-[#1a1a1a] border border-white/10 text-gray-200 text-sm rounded px-4 py-2.5 pr-10 focus:outline-none focus:border-[#e35d8f] placeholder-gray-600" />
            <svg class="absolute right-3 top-3 w-4 h-4 text-gray-600" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
          </div>
          <button @click="applyFilters" class="hidden sm:block bg-[#e35d8f] hover:bg-[#c44a7a] text-white text-sm font-bold px-5 py-2.5 rounded transition">
            {{ t('home.search_btn') }}
          </button>
        </div>

        <div class="flex items-center justify-between text-xs text-gray-500 mb-2">
          <div class="flex flex-wrap gap-2">
            <span v-if="activeCity" class="text-[#e35d8f]">📍 {{ activeCity.name }}</span>
            <span v-for="name in activeCategoryNames" :key="name" class="text-[#e35d8f]">🏷 {{ name }}</span>
            <span v-for="name in activeServiceNames" :key="name" class="text-[#e35d8f]">✨ {{ name }}</span>
            <span v-for="a in (activeAges || [])" :key="a" class="text-[#e35d8f]">🎂 {{ t('home.age') }} {{ a }}</span>
            <Link v-if="activeCity || activeCategoryNames.length || activeServiceNames.length || (activeAges && activeAges.length)" :href="route('home')" class="text-gray-600 hover:text-gray-300 transition">✕ {{ t('home.reset') }}</Link>
          </div>
          <span class="shrink-0 ml-2 text-gray-500">{{ profiles.total }} {{ t('home.listings') }}</span>
        </div>
      </div>
    </div>

    <!-- SEO-Bereich auf lokalen Landingpages: Breadcrumb + einzigartiger Text + interne Links -->
    <div v-if="isLanding" class="max-w-7xl mx-auto px-3 sm:px-4 pt-4">
      <!-- Sichtbare, crawlbare Breadcrumbs -->
      <nav v-if="crumbs.length" class="flex flex-wrap items-center gap-1.5 text-xs text-gray-500 mb-3" aria-label="Breadcrumb">
        <template v-for="(c, i) in crumbs" :key="i">
          <Link v-if="c.url" :href="c.url" class="hover:text-[#e35d8f] transition">{{ c.name }}</Link>
          <span v-else class="text-gray-300">{{ c.name }}</span>
          <span v-if="i < crumbs.length - 1" class="text-gray-700">›</span>
        </template>
      </nav>

      <!-- Einzigartiger Stadttext (Fallback: Meta-Description) -->
      <p v-if="cityIntro || seoDescription" class="text-sm text-gray-500 leading-relaxed max-w-3xl">
        {{ cityIntro || seoDescription }}
      </p>

      <!-- Crawlbare Stadt×Kategorie-Links -->
      <div v-if="cityCategoryLinks.length" class="mt-3 flex flex-wrap gap-2">
        <Link v-for="l in cityCategoryLinks" :key="l.url" :href="l.url"
          class="text-xs bg-[#1a1a1a] border border-white/10 text-gray-300 hover:border-[#e35d8f] hover:text-[#e35d8f] rounded-full px-3 py-1 transition">
          {{ l.name }}
        </Link>
      </div>
    </div>

    <!-- Listings -->
    <div class="max-w-7xl mx-auto px-3 sm:px-4 pb-12 pt-4 sm:pt-6">
      <div v-if="profiles.data.length === 0" class="text-center py-20 text-gray-600">
        <div class="text-5xl mb-4">🔍</div>
        <p class="text-lg">{{ t('home.no_results') }}</p>
      </div>

      <div v-else class="space-y-4">

        <!-- ── TOP AD ──────────────────────────────────────────────────────── -->
        <a v-if="profiles.data[0]" :href="route('profile.show', profiles.data[0].slug)"
          class="block rounded-2xl overflow-hidden bg-[#1a1a1a] border border-white/8 hover:border-[#e35d8f]/50 hover:shadow-xl hover:shadow-[#e35d8f]/10 transition-all duration-300 group">
          <div class="flex h-[200px] sm:h-[260px] md:h-[300px]">

            <!-- Photo -->
            <div class="relative w-[160px] sm:w-[220px] md:w-[300px] shrink-0 overflow-hidden">
              <CardGallery :media="profiles.data[0].public_media" :alt="profiles.data[0].display_name" eager-first />
              <span class="absolute top-2 left-2 z-10 bg-[#e35d8f] text-white text-[10px] font-black px-2 py-0.5 rounded-full tracking-wider">{{ t('home.top_ad') }}</span>
            </div>

            <!-- Info -->
            <div class="p-4 sm:p-6 flex flex-col justify-between flex-1 min-w-0">
              <div>
                <!-- Name + Verified -->
                <div class="flex items-center gap-2 flex-wrap mb-1.5">
                  <h2 class="text-white font-black text-lg sm:text-2xl group-hover:text-[#e35d8f] transition truncate">
                    {{ profiles.data[0].display_name }}
                  </h2>
                  <span v-if="profiles.data[0].identity_verification_status === 'approved' || profiles.data[0].verification_status === 'approved'"
                    class="inline-flex items-center justify-center w-5 h-5 rounded-full shrink-0"
                    :class="profiles.data[0].identity_verification_status === 'approved' ? 'bg-green-500' : 'bg-sky-500'"
                    :title="profiles.data[0].identity_verification_status === 'approved' ? t('home.verified') : t('home.photo_verified')">
                    <svg class="w-3 h-3 text-white" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>
                  </span>
                </div>

                <!-- Meta -->
                <div class="flex flex-wrap items-center gap-x-3 gap-y-1 text-xs text-gray-400 mb-3">
                  <span v-if="profiles.data[0].age" class="text-white/70">{{ profiles.data[0].age }} J.</span>
                  <span v-if="profiles.data[0].city">📍 {{ profiles.data[0].city?.name }}</span>
                  <span v-if="profiles.data[0].category" class="text-[#e35d8f] font-medium">{{ profiles.data[0].category?.name }}</span>
                </div>

                <!-- Teaser -->
                <p v-if="profiles.data[0].description" class="text-gray-300 text-sm line-clamp-2 leading-relaxed">
                  {{ profiles.data[0].description }}
                </p>
              </div>

              <!-- Badges -->
              <div class="flex flex-wrap gap-1.5 mt-3">
                <span v-if="isNew(profiles.data[0].created_at)"
                  class="bg-blue-500/15 text-blue-300 text-[10px] font-semibold px-2.5 py-1 rounded-full">{{ t('home.badge_new') }}</span>
                <span v-if="profiles.data[0].private_media_count > 0"
                  class="bg-white/5 text-gray-300 text-[10px] font-semibold px-2.5 py-1 rounded-full">{{ t('home.private_gallery') }}</span>
              </div>
            </div>
          </div>
        </a>

        <!-- ── GRID: 2 Spalten Desktop, 1 Spalte Mobile ───────────────────── -->
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
          <a v-for="profile in profiles.data.slice(1)" :key="profile.id"
            :href="route('profile.show', profile.slug)"
            class="flex h-[300px] rounded-2xl overflow-hidden bg-[#1a1a1a] border border-white/8 hover:border-[#e35d8f]/50 hover:shadow-xl hover:shadow-[#e35d8f]/10 transition-all duration-300 group">

            <!-- Photo -->
            <div class="relative w-[50%] shrink-0 overflow-hidden">
              <CardGallery :media="profile.public_media" :alt="profile.display_name" />

              <!-- Badges top-left -->
              <div class="absolute top-2 left-2 z-10 flex flex-col gap-1">
                <span v-if="profile.listing_orders?.[0]?.amount_chf > 0"
                  class="bg-[#e35d8f] text-white text-[10px] font-black px-2 py-0.5 rounded-full tracking-wider">TOP AD</span>
                <span v-if="profile.identity_verification_status === 'approved'"
                  class="flex items-center gap-1 bg-green-500 text-white text-[10px] font-bold px-2 py-0.5 rounded-full">
                  <svg class="w-2.5 h-2.5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>
                  {{ t('home.verified') }}
                </span>
                <span v-else-if="profile.verification_status === 'approved'"
                  class="flex items-center gap-1 bg-sky-500 text-white text-[10px] font-bold px-2 py-0.5 rounded-full">
                  <svg class="w-2.5 h-2.5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>
                  {{ t('home.photo_verified') }}
                </span>
                <span v-if="isNew(profile.created_at)"
                  class="bg-blue-500 text-white text-[10px] font-bold px-2 py-0.5 rounded-full">{{ t('home.badge_new') }}</span>
              </div>
            </div>

            <!-- Text rechts -->
            <div class="p-3 flex-1 min-w-0 flex flex-col justify-between">
              <div>
                <!-- Name + Alter -->
                <div class="flex items-center gap-2 mb-1 flex-wrap">
                  <h3 class="text-white font-bold text-sm sm:text-base group-hover:text-[#e35d8f] transition truncate">
                    {{ profile.display_name }}
                  </h3>
                  <span v-if="profile.identity_verification_status === 'approved' || profile.verification_status === 'approved'"
                    class="inline-flex items-center justify-center w-4 h-4 rounded-full shrink-0"
                    :class="profile.identity_verification_status === 'approved' ? 'bg-green-500' : 'bg-sky-500'"
                    :title="profile.identity_verification_status === 'approved' ? t('home.verified') : t('home.photo_verified')">
                    <svg class="w-2.5 h-2.5 text-white" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>
                  </span>
                  <span v-if="profile.age" class="shrink-0 text-gray-400 text-xs">{{ profile.age }} J.</span>
                </div>

                <!-- Stadt + Kategorie -->
                <div class="flex items-center gap-2 text-xs mb-2 flex-wrap">
                  <span v-if="profile.city" class="text-gray-400">📍 {{ profile.city?.name }}</span>
                  <span v-if="profile.category" class="text-[#e35d8f] font-medium">{{ profile.category?.name }}</span>
                </div>

                <!-- Teaser -->
                <p v-if="profile.description" class="text-gray-300 text-sm line-clamp-2 leading-relaxed">
                  {{ profile.description }}
                </p>
              </div>

              <!-- Badges unten -->
              <div class="flex flex-wrap gap-1 mt-2">
                <span v-if="profile.private_media_count > 0"
                  class="bg-white/5 text-gray-400 text-[10px] px-2 py-0.5 rounded-full">{{ t('home.private_badge') }}</span>
              </div>
            </div>

          </a>
        </div>
      </div>

      <!-- Pagination -->
      <div v-if="profiles.last_page > 1" class="flex justify-center flex-wrap gap-2 mt-8">
        <Link v-for="link in profiles.links" :key="link.label"
          :href="link.url || '#'"
          v-html="link.label"
          class="px-3 py-1.5 text-sm rounded border transition-colors"
          :class="link.active
            ? 'bg-[#e35d8f] text-white border-[#e35d8f]'
            : 'bg-[#1a1a1a] text-gray-400 border-white/10 hover:border-[#e35d8f]/50 hover:text-[#e35d8f]'" />
      </div>
    </div>
    </template>
  </AppLayout>
</template>

<script setup>
import { ref, computed, onMounted, onBeforeUnmount, nextTick } from 'vue';
import { Head, Link, router, usePage } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import CardGallery from '@/Components/CardGallery.vue';
import ProfileCardV2 from '@/Components/V2/ProfileCardV2.vue';
import SpotlightCardV2 from '@/Components/V2/SpotlightCardV2.vue';
import { useI18n } from '@/composables/useI18n';

const { t } = useI18n();
const page = usePage();

// Design-Flag (V2 hinter BOOKLOLA_UI_V2)
const uiV2 = computed(() => !!page.props.features?.uiV2);

// ── V2-Deko: Trust-Argumente + Kreis-Navigation + Control-Styles ──────────
// Hero-Titel in drei Teile splitten, damit das Akzentwort pink gerendert wird.
const heroTitleParts = computed(() => {
  const title  = t('home.hero_title');
  const accent = t('home.hero_accent');
  const i = accent ? title.indexOf(accent) : -1;
  if (i === -1) return { pre: title, accent: '', post: '' };
  return { pre: title.slice(0, i), accent, post: title.slice(i + accent.length) };
});

const trustItems = computed(() => [
  { title: t('home.trust_profiles'), sub: t('home.trust_profiles_sub'),
    icon: '<svg fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24" style="width:100%;height:100%"><circle cx="9" cy="8" r="3.2"/><path stroke-linecap="round" stroke-linejoin="round" d="M3.5 20a5.5 5.5 0 0111 0M16 5.2a3 3 0 010 5.6M18 20a5.5 5.5 0 00-3.5-5.1"/></svg>' },
  { title: t('home.trust_discreet'), sub: t('home.trust_discreet_sub'),
    icon: '<svg fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24" style="width:100%;height:100%"><path stroke-linecap="round" stroke-linejoin="round" d="M12 3l7 3v5c0 4.4-3 8.4-7 10-4-1.6-7-5.6-7-10V6l7-3z"/><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4"/></svg>' },
  { title: t('home.trust_swiss'), sub: t('home.trust_swiss_sub'),
    icon: '<svg fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24" style="width:100%;height:100%"><path stroke-linecap="round" stroke-linejoin="round" d="M13 2L4.5 13.5H11l-1 8L19.5 10H13l0-8z"/></svg>' },
]);

const circleNav = computed(() => [
  { label: t('home.new_images'), href: route('neue-bilder'), icon: '<svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" style="width:100%;height:100%"><path stroke-linecap="round" stroke-linejoin="round" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"/><circle cx="12" cy="13" r="3"/></svg>' },
  { label: 'Feed', href: route('feed'), icon: '<svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" style="width:100%;height:100%"><path stroke-linecap="round" stroke-linejoin="round" d="M4 5h16v11H7l-3 3z"/><path stroke-linecap="round" d="M8 9h8M8 12h5"/></svg>' },
  { label: t('home.search_btn'), href: route('search'), icon: '<svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" style="width:100%;height:100%"><circle cx="11" cy="11" r="7"/><path stroke-linecap="round" d="M21 21l-4-4"/></svg>' },
  { label: t('home.clubs'), href: route('clubs.index'), icon: '<svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" style="width:100%;height:100%"><path stroke-linecap="round" stroke-linejoin="round" d="M3 21h18M4 21V7l8-4 8 4v14M9 21v-5h6v5"/></svg>' },
]);

const v2Control = 'w-full text-sm rounded-xl px-3 py-2.5 pr-8 appearance-none cursor-pointer focus:outline-none sm:min-w-[130px]';
const v2Trigger = 'w-full flex items-center justify-between gap-2 text-sm rounded-xl px-3 py-2.5 cursor-pointer transition sm:min-w-[130px] focus:outline-none';
const v2TriggerIdle   = 'background:var(--bl-surface-2);border:1px solid var(--bl-border);color:var(--bl-text);';
const v2TriggerActive = 'background:var(--bl-surface-2);border:1px solid var(--bl-pink);color:var(--bl-text);';
const v2Panel = 'absolute z-40 mt-1 left-0 max-h-72 overflow-y-auto rounded-xl p-1 shadow-xl';
const v2Option = 'flex items-center gap-2 px-2.5 py-2 rounded-lg cursor-pointer transition hover:bg-white/5';

// ── Kreisel-Navigation: horizontal scrollbar mit Pfeil-Buttons ────────────
const circleScroll = ref(null);
const canScrollLeft  = ref(false);
const canScrollRight = ref(true);   // Standard sichtbar; updateCircleArrows korrigiert

function updateCircleArrows() {
  const el = circleScroll.value;
  if (!el) return;
  canScrollLeft.value  = el.scrollLeft > 4;
  canScrollRight.value = el.scrollLeft + el.clientWidth < el.scrollWidth - 4;
}
function scrollCircles(dir) {
  const el = circleScroll.value;
  if (!el) return;
  el.scrollBy({ left: dir * Math.max(el.clientWidth * 0.8, 240), behavior: 'smooth' });
}
onMounted(() => {
  // Mehrfach prüfen: Layout/Fonts/lazyload können die Breite verzögert ändern.
  nextTick(updateCircleArrows);
  requestAnimationFrame(updateCircleArrows);
  [150, 450, 900].forEach((ms) => setTimeout(updateCircleArrows, ms));
  window.addEventListener('resize', updateCircleArrows);
});
onBeforeUnmount(() => window.removeEventListener('resize', updateCircleArrows));

// Bild-Variante wählen (Fallback auf Original-Stream bei Alt-Medien)
function mediaSrc(m, size) {
  return m?.src?.[size] ?? m?.url;
}

const props = defineProps({
  profiles:       Object,
  cities:         Array,
  categories:     Array,
  services:       Array,
  activeCity:        Object,
  activeCategories:  { type: Array, default: () => [] },
  activeServices:    { type: Array, default: () => [] },
  activeSearch:      String,
  activeAges:        { type: Array, default: () => [] },
  activeVerified:    String,
  seoTitle:          { type: String, default: '' },
  seoDescription:    { type: String, default: '' },
  seoHeading:        { type: String, default: '' },
  crumbs:            { type: Array,  default: () => [] },
  cityIntro:         { type: String, default: '' },
  cityCategoryLinks: { type: Array,  default: () => [] },
});

// Lokale Landingpage (Stadt/Kategorie/Service) → eigener Intro-Text
const isLanding = computed(() =>
  !!props.activeCity
  || (props.activeCategories?.length ?? 0) > 0
  || (props.activeServices?.length ?? 0) > 0
);

const serviceMenuOpen  = ref(false);
const ageMenuOpen       = ref(false);
const categoryMenuOpen  = ref(false);

const ageOptions = ['18-25', '25-35', '35-45', '45-55', '55+'];

const filters = ref({
  city:       props.activeCity?.slug ?? '',
  categories: [...(props.activeCategories ?? [])],
  services:   [...(props.activeServices ?? [])],
  ages:       [...(props.activeAges ?? [])],
  search:     props.activeSearch ?? '',
  verified:   props.activeVerified ?? '',
});

// Namen der aktiven Services für die Chip-Anzeige (Slug → übersetzter Name)
const activeServiceNames = computed(() =>
  (props.activeServices ?? [])
    .map((slug) => props.services?.find((s) => s.slug === slug)?.name)
    .filter(Boolean)
);

// Namen der aktiven Rubriken für die Chip-Anzeige (Slug → übersetzter Name)
const activeCategoryNames = computed(() =>
  (props.activeCategories ?? [])
    .map((slug) => props.categories?.find((c) => c.slug === slug)?.name)
    .filter(Boolean)
);

function isNew(iso) {
  if (!iso) return false;
  return (Date.now() - new Date(iso).getTime()) < 14 * 24 * 60 * 60 * 1000;
}

function applyFilters() {
  serviceMenuOpen.value = false;
  ageMenuOpen.value = false;
  categoryMenuOpen.value = false;
  const query = {};
  if (filters.value.search)            query.search     = filters.value.search;
  if (filters.value.ages.length)       query.age        = filters.value.ages.join(',');
  if (filters.value.verified)          query.verified   = filters.value.verified;
  if (filters.value.services.length)   query.services   = filters.value.services.join(',');
  if (filters.value.categories.length) query.categories = filters.value.categories.join(',');

  if (filters.value.city) {
    router.get(route('city', filters.value.city), query);
  } else {
    router.get(route('home'), query);
  }
}
</script>

<style scoped>
.scrollbar-hide::-webkit-scrollbar { display: none; }
.scrollbar-hide { -ms-overflow-style: none; scrollbar-width: none; }
</style>
