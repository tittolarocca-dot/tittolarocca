<template>
  <AppLayout>
    <Head :title="profile ? t('inserent.edit_title') : t('inserent.create_title')" />

    <div class="max-w-3xl mx-auto px-4 py-8">

      <!-- Header -->
      <div class="mb-6">
        <h1 class="text-2xl font-bold text-gray-900">
          {{ profile ? t('inserent.edit_title') : t('inserent.create_title') }}
        </h1>
        <p class="text-gray-500 text-sm mt-1">
          {{ profile ? t('inserent.edit_subtitle') : t('inserent.create_subtitle') }}
        </p>
      </div>

      <!-- Stepper (nur beim Erstellen) -->
      <div v-if="!profile" class="flex items-center gap-0 mb-8">
        <div v-for="(step, i) in steps" :key="i" class="flex items-center gap-0 flex-1 last:flex-none">
          <div :class="[
            'w-7 h-7 rounded-full flex items-center justify-center text-xs font-bold shrink-0',
            i === 0 ? 'bg-[#e35d8f] text-white' : 'bg-gray-200 text-gray-500',
          ]">{{ i + 1 }}</div>
          <span class="ml-1.5 text-xs font-medium" :class="i === 0 ? 'text-[#e35d8f]' : 'text-gray-400'">
            {{ step }}
          </span>
          <div v-if="i < steps.length - 1" class="flex-1 h-px bg-gray-200 mx-3"></div>
        </div>
      </div>

      <form @submit.prevent="submit" class="space-y-6">

        <!-- Basisdaten -->
        <div class="bg-white rounded-xl border border-gray-200 p-5 space-y-4 shadow-sm">
          <h2 class="font-semibold text-gray-500 text-xs uppercase tracking-wide">{{ t('inserent.basics') }}</h2>

          <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <InputField id="display_name" :label="t('inserent.name')"
              v-model="form.display_name" :error="form.errors.display_name"
              required :placeholder="t('inserent.name_ph')" />
            <InputField id="age" :label="t('inserent.age')" v-model="form.age"
              type="number" min="18" max="99" :error="form.errors.age"
              required :placeholder="t('inserent.age_ph')" />
          </div>

          <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <!-- Stadt -->
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">
                {{ t('inserent.city') }} <span class="text-[#e35d8f]">*</span>
              </label>
              <select v-model="form.city_id"
                :class="['w-full px-3 py-2 border rounded-md text-sm bg-white text-gray-900 focus:outline-none focus:ring-2 focus:ring-[#e35d8f] transition',
                  form.errors.city_id ? 'border-red-500' : 'border-gray-300']">
                <option value="">{{ t('inserent.city_select') }}</option>
                <option v-for="city in cities" :key="city.id" :value="city.id">
                  {{ city.name }} ({{ city.canton }})
                </option>
              </select>
              <p v-if="form.errors.city_id" class="mt-1 text-xs text-red-600">{{ form.errors.city_id }}</p>
            </div>

            <!-- Kategorie -->
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">
                {{ t('inserent.category') }} <span class="text-[#e35d8f]">*</span>
              </label>
              <select v-model="form.category_id"
                :class="['w-full px-3 py-2 border rounded-md text-sm bg-white text-gray-900 focus:outline-none focus:ring-2 focus:ring-[#e35d8f] transition',
                  form.errors.category_id ? 'border-red-500' : 'border-gray-300']">
                <option value="">{{ t('inserent.category_select') }}</option>
                <option v-for="cat in categories" :key="cat.id" :value="cat.id">
                  {{ cat.name }}
                </option>
              </select>
              <p v-if="form.errors.category_id" class="mt-1 text-xs text-red-600">{{ form.errors.category_id }}</p>
            </div>
          </div>

          <!-- Weitere Angaben -->
          <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <InputField id="nationality" :label="t('inserent.nationality')"
              v-model="form.nationality" :error="form.errors.nationality"
              :placeholder="t('inserent.nationality_ph')" />
            <InputField id="height_cm" :label="t('inserent.height')"
              v-model="form.height_cm" type="number" min="120" max="230"
              :error="form.errors.height_cm" :placeholder="t('inserent.height_ph')" />
          </div>

          <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <!-- Augenfarbe -->
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">{{ t('inserent.eye_color') }}</label>
              <select v-model="form.eye_color"
                class="w-full px-3 py-2 border border-gray-300 rounded-md text-sm bg-white text-gray-900 focus:outline-none focus:ring-2 focus:ring-[#e35d8f] transition">
                <option value="">{{ t('inserent.none') }}</option>
                <option v-for="[val,key] in eyeColors" :key="val" :value="val">{{ t('inserent.'+key) }}</option>
              </select>
            </div>

            <!-- Körperbau -->
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">{{ t('inserent.body_type') }}</label>
              <select v-model="form.body_type"
                class="w-full px-3 py-2 border border-gray-300 rounded-md text-sm bg-white text-gray-900 focus:outline-none focus:ring-2 focus:ring-[#e35d8f] transition">
                <option value="">{{ t('inserent.none') }}</option>
                <option v-for="[val,key] in bodyTypes" :key="val" :value="val">{{ t('inserent.'+key) }}</option>
              </select>
            </div>
          </div>

          <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
            <!-- Rauchen -->
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">{{ t('inserent.smoking') }}</label>
              <select v-model="form.smoking"
                class="w-full px-3 py-2 border border-gray-300 rounded-md text-sm bg-white text-gray-900 focus:outline-none focus:ring-2 focus:ring-[#e35d8f] transition">
                <option :value="null">{{ t('inserent.none') }}</option>
                <option :value="true">{{ t('inserent.yes') }}</option>
                <option :value="false">{{ t('inserent.no') }}</option>
              </select>
            </div>

            <!-- Tattoo -->
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">{{ t('inserent.tattoo') }}</label>
              <select v-model="form.tattoo"
                class="w-full px-3 py-2 border border-gray-300 rounded-md text-sm bg-white text-gray-900 focus:outline-none focus:ring-2 focus:ring-[#e35d8f] transition">
                <option :value="null">{{ t('inserent.none') }}</option>
                <option :value="true">{{ t('inserent.yes') }}</option>
                <option :value="false">{{ t('inserent.no') }}</option>
              </select>
            </div>

            <!-- Intimbereich -->
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">{{ t('inserent.intimate_area') }}</label>
              <select v-model="form.intimate_area"
                class="w-full px-3 py-2 border border-gray-300 rounded-md text-sm bg-white text-gray-900 focus:outline-none focus:ring-2 focus:ring-[#e35d8f] transition">
                <option value="">{{ t('inserent.none') }}</option>
                <option value="Glatt">{{ t('inserent.intim_smooth') }}</option>
                <option value="Teilrasiert">{{ t('inserent.intim_partial') }}</option>
                <option value="Natürlich">{{ t('inserent.intim_natural') }}</option>
              </select>
            </div>
          </div>
        </div>

        <!-- Aussehen -->
        <div class="bg-white rounded-xl border border-gray-200 p-5 space-y-4 shadow-sm">
          <h2 class="font-semibold text-gray-500 text-xs uppercase tracking-wide">{{ t('inserent.appearance') }}</h2>

          <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <!-- Geschlecht -->
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">{{ t('inserent.gender') }}</label>
              <select v-model="form.gender"
                class="w-full px-3 py-2 border border-gray-300 rounded-md text-sm bg-white text-gray-900 focus:outline-none focus:ring-2 focus:ring-[#e35d8f] transition">
                <option value="">{{ t('inserent.none') }}</option>
                <option v-for="[val,key] in genderOptions" :key="val" :value="val">{{ t('inserent.'+key) }}</option>
              </select>
            </div>

            <!-- Herkunft -->
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">{{ t('inserent.origin') }}</label>
              <select v-model="form.origin"
                class="w-full px-3 py-2 border border-gray-300 rounded-md text-sm bg-white text-gray-900 focus:outline-none focus:ring-2 focus:ring-[#e35d8f] transition">
                <option value="">{{ t('inserent.none') }}</option>
                <option v-for="[val,key] in originOptions" :key="val" :value="val">{{ t('inserent.'+key) }}</option>
              </select>
            </div>
          </div>

          <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
            <!-- Gewicht -->
            <InputField id="weight_kg" :label="t('inserent.weight')"
              v-model="form.weight_kg" type="number" min="30" max="200"
              :error="form.errors.weight_kg" :placeholder="t('inserent.weight_ph')" />

            <!-- Oberweite -->
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">{{ t('inserent.cup') }}</label>
              <select v-model="form.cup_size"
                class="w-full px-3 py-2 border border-gray-300 rounded-md text-sm bg-white text-gray-900 focus:outline-none focus:ring-2 focus:ring-[#e35d8f] transition">
                <option value="">{{ t('inserent.none') }}</option>
                <option v-for="c in cupSizes" :key="c" :value="c">{{ c }}</option>
              </select>
            </div>

            <!-- Brusttyp -->
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">{{ t('inserent.breast') }}</label>
              <select v-model="form.breast_type"
                class="w-full px-3 py-2 border border-gray-300 rounded-md text-sm bg-white text-gray-900 focus:outline-none focus:ring-2 focus:ring-[#e35d8f] transition">
                <option value="">{{ t('inserent.none') }}</option>
                <option v-for="[val,key] in breastTypes" :key="val" :value="val">{{ t('inserent.'+key) }}</option>
              </select>
            </div>
          </div>

          <!-- Video -->
          <label class="flex items-center gap-3 cursor-pointer">
            <input type="checkbox" v-model="form.has_video"
              class="w-4 h-4 rounded border-gray-300 text-[#e35d8f] focus:ring-[#e35d8f]" />
            <span class="text-sm text-gray-700">{{ t('inserent.video') }}</span>
          </label>
        </div>

        <!-- Sprachen -->
        <div class="bg-white rounded-xl border border-gray-200 p-5 space-y-3 shadow-sm">
          <h2 class="font-semibold text-gray-500 text-xs uppercase tracking-wide">{{ t('inserent.languages') }}</h2>
          <p class="text-xs text-gray-500">{{ t('inserent.languages_hint') }}</p>
          <div class="space-y-1">
            <div v-for="[code, flag] in languageList" :key="code"
              class="flex items-center justify-between gap-3 py-1.5 border-b border-gray-100 last:border-0">
              <span class="flex items-center gap-2 text-sm text-gray-700">
                <span class="text-base leading-none">{{ flag }}</span>{{ t('languages.' + code) }}
              </span>
              <div class="flex gap-0.5">
                <button v-for="n in 5" :key="n" type="button" @click="setLang(code, n)"
                  class="text-lg leading-none transition"
                  :class="n <= (form.languages[code] || 0) ? 'text-yellow-400' : 'text-gray-300 hover:text-yellow-200'">★</button>
              </div>
            </div>
          </div>
        </div>

        <!-- Beschreibung -->
        <div class="bg-white rounded-xl border border-gray-200 p-5 space-y-3 shadow-sm">
          <h2 class="font-semibold text-gray-500 text-xs uppercase tracking-wide">{{ t('inserent.description') }}</h2>
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">
              {{ t('inserent.about') }}
              <span class="text-gray-400 font-normal ml-1">({{ form.description?.length ?? 0 }}/2000)</span>
            </label>
            <textarea v-model="form.description" rows="5" maxlength="2000"
              :placeholder="t('inserent.about_ph')"
              :class="['w-full px-3 py-2 border rounded-md text-sm bg-white text-gray-900 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-[#e35d8f] transition resize-none',
                form.errors.description ? 'border-red-500' : 'border-gray-300']"
            />
            <p v-if="form.errors.description" class="mt-1 text-xs text-red-600">{{ form.errors.description }}</p>
          </div>
        </div>

        <!-- Tags -->
        <div class="bg-white rounded-xl border border-gray-200 p-5 space-y-4 shadow-sm">
          <h2 class="font-semibold text-gray-500 text-xs uppercase tracking-wide">{{ t('inserent.offers') }}</h2>
          <div v-for="group in tagGroups" :key="group.label" class="space-y-2">
            <h3 class="text-[11px] font-bold text-[#e35d8f] uppercase tracking-wide">{{ group.label }}</h3>
            <div class="flex flex-wrap gap-2">
              <button v-for="tag in group.items" :key="tag.id" type="button" @click="toggleTag(tag.id)"
                :class="[
                  'px-3 py-1.5 rounded-full text-xs font-medium border transition-all',
                  form.tag_ids.includes(tag.id)
                    ? 'bg-[#e35d8f] text-white border-[#e35d8f]'
                    : 'bg-transparent text-gray-600 border-gray-200 hover:border-[#e35d8f]/50',
                ]">
                {{ tag.name }}
              </button>
            </div>
          </div>
        </div>

        <!-- Kontakt & Preise -->
        <div class="bg-white rounded-xl border border-gray-200 p-5 space-y-4 shadow-sm">
          <h2 class="font-semibold text-gray-500 text-xs uppercase tracking-wide">{{ t('inserent.contact') }}</h2>

          <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <InputField id="whatsapp_number" :label="t('inserent.phone')"
              v-model="form.whatsapp_number" type="tel"
              placeholder="+41 79 123 45 67" :error="form.errors.whatsapp_number"
              :hint="t('inserent.phone_hint')" />

            <InputField id="telegram_username" :label="t('inserent.telegram')"
              v-model="form.telegram_username"
              placeholder="@deinusername" :error="form.errors.telegram_username"
              :hint="t('inserent.telegram_hint')" />
          </div>

          <InputField id="address" :label="t('inserent.address')"
            v-model="form.address"
            :placeholder="t('inserent.address_ph')" :error="form.errors.address"
            :hint="t('inserent.address_hint')" />

          <InputField id="website" :label="t('inserent.website')"
            v-model="form.website"
            :placeholder="t('inserent.website_ph')" :error="form.errors.website"
            :hint="t('inserent.website_hint')" />

          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">
              {{ t('inserent.price') }} <span class="text-[#e35d8f]">*</span>
            </label>
            <div class="relative">
              <span class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-sm">CHF</span>
              <input v-model="form.subscription_price_chf" type="number" min="9" max="999" step="1"
                :class="['w-full pl-12 pr-3 py-2 border rounded-md text-sm bg-white text-gray-900 focus:outline-none focus:ring-2 focus:ring-[#e35d8f] transition',
                  form.errors.subscription_price_chf ? 'border-red-500' : 'border-gray-300']" />
            </div>
            <p v-if="form.errors.subscription_price_chf" class="mt-1 text-xs text-red-600">{{ form.errors.subscription_price_chf }}</p>
            <p class="mt-1 text-xs text-gray-500">{{ t('inserent.price_note') }}</p>

            <!-- Verdienst-Rechner -->
            <div v-if="form.subscription_price_chf >= 9" class="mt-2 bg-[#e35d8f]/10 border border-[#e35d8f]/20 rounded-lg px-3 py-2 text-xs text-[#e35d8f]">
              {{ t('inserent.earnings10') }} <strong>CHF {{ earnings(10) }}{{ t('inserent.per_month') }}</strong> ·
              {{ t('inserent.earnings50') }} <strong>CHF {{ earnings(50) }}{{ t('inserent.per_month') }}</strong>
              <span class="text-[#e35d8f]/60 block mt-0.5">{{ t('inserent.earnings_note') }}</span>
            </div>
          </div>
        </div>

        <!-- Actions -->
        <div class="flex items-center gap-3 justify-end">
          <Link v-if="profile" :href="route('inserat.dashboard')"
            class="px-4 py-2 text-sm text-gray-500 hover:text-gray-900 transition">
            {{ t('inserent.cancel') }}
          </Link>
          <PrimaryButton type="submit" :loading="form.processing">
            {{ profile ? t('inserent.save') : t('inserent.create') }}
          </PrimaryButton>
        </div>

      </form>
    </div>
  </AppLayout>
</template>

<script setup>
import { computed } from 'vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import InputField from '@/Components/InputField.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import { useI18n } from '@/composables/useI18n';

const { t } = useI18n();

const props = defineProps({
  profile:    Object,
  cities:     Array,
  categories: Array,
  tags:       Array,
});

// Tags nach Gruppe bündeln – ungruppierte zuerst, dann Softcore / Hardcore
const tagGroups = computed(() => {
  const groups = new Map();
  for (const tag of props.tags ?? []) {
    const key = tag.group || 'Allgemein';
    if (!groups.has(key)) groups.set(key, []);
    groups.get(key).push(tag);
  }
  const order = ['Allgemein', 'Klassisch', 'Spezial', 'BDSM / Fetisch', 'Massage'];
  return [...groups.entries()]
    .sort((a, b) => {
      const ia = order.indexOf(a[0]);
      const ib = order.indexOf(b[0]);
      return (ia === -1 ? 99 : ia) - (ib === -1 ? 99 : ib);
    })
    .map(([label, items]) => ({ label, items }));
});

const steps = computed(() => [
  t('inserent.step_profile'), t('inserent.step_package'), t('inserent.step_payment'), t('inserent.step_live'),
]);

// [gespeicherter Wert, Übersetzungs-Key]
const eyeColors = [['Braun', 'eye_brown'], ['Blau', 'eye_blue'], ['Grün', 'eye_green'], ['Grau', 'eye_gray'], ['Bernstein', 'eye_amber'], ['Schwarz', 'eye_black']];
const bodyTypes = [['Schlank', 'body_slim'], ['Sportlich', 'body_sporty'], ['Rundlich', 'body_curvy']];
const cupSizes  = ['A', 'B', 'C', 'D', 'E', 'F', 'G'];
const genderOptions = [['frau', 'g_woman'], ['trans', 'g_trans'], ['gigolo', 'g_gigolo']];
const originOptions = [
  ['europaeisch', 'o_european'], ['asiatisch', 'o_asian'], ['schwarz', 'o_black'],
  ['indisch', 'o_indian'], ['latina', 'o_latina'], ['gemischt', 'o_mixed'],
];
const breastTypes = [['natur', 'b_natural'], ['implantate', 'b_implants']];
const languageList = [
  ['de', '🇩🇪'], ['en', '🇬🇧'], ['fr', '🇫🇷'], ['es', '🇪🇸'], ['it', '🇮🇹'],
  ['hu', '🇭🇺'], ['ro', '🇷🇴'], ['pt', '🇵🇹'], ['ru', '🇷🇺'], ['other', '🌐'],
];

function setLang(code, n) {
  const cur = form.languages[code] || 0;
  form.languages = { ...form.languages, [code]: cur === n ? 0 : n };
}

const form = useForm({
  display_name:           props.profile?.display_name           ?? '',
  description:            props.profile?.description            ?? '',
  city_id:                props.profile?.city_id                ?? '',
  category_id:            props.profile?.category_id            ?? '',
  age:                    props.profile?.age                    ?? '',
  nationality:            props.profile?.nationality            ?? '',
  height_cm:              props.profile?.height_cm              ?? '',
  eye_color:              props.profile?.eye_color              ?? '',
  smoking:                props.profile?.smoking                ?? null,
  tattoo:                 props.profile?.tattoo                 ?? null,
  intimate_area:          props.profile?.intimate_area          ?? '',
  body_type:              props.profile?.body_type              ?? '',
  gender:                 props.profile?.gender                 ?? '',
  origin:                 props.profile?.origin                 ?? '',
  weight_kg:              props.profile?.weight_kg              ?? '',
  cup_size:               props.profile?.cup_size               ?? '',
  breast_type:            props.profile?.breast_type            ?? '',
  has_video:              props.profile?.has_video              ?? false,
  languages:              props.profile?.languages              ?? {},
  whatsapp_number:        props.profile?.whatsapp_number        ?? '',
  telegram_username:      props.profile?.telegram_username      ?? '',
  address:                props.profile?.address                ?? '',
  website:                props.profile?.website                ?? '',
  subscription_price_chf: props.profile?.subscription_price_chf ?? 10,
  tag_ids:                props.profile?.tag_ids                ?? [],
});

function toggleTag(id) {
  const idx = form.tag_ids.indexOf(id);
  if (idx === -1) form.tag_ids.push(id);
  else            form.tag_ids.splice(idx, 1);
}

function earnings(subs) {
  return (form.subscription_price_chf * subs * 0.8).toFixed(2);
}

function submit() {
  if (props.profile) {
    form.put(route('inserat.profile.update'));
  } else {
    form.post(route('inserat.profile.store'));
  }
}
</script>
