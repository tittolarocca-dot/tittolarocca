<template>
  <AppLayout>
    <Head :title="profile ? 'Profil bearbeiten' : 'Profil erstellen'" />

    <div class="max-w-3xl mx-auto px-4 py-8">

      <!-- Header -->
      <div class="mb-6">
        <h1 class="text-2xl font-bold text-gray-900">
          {{ profile ? 'Profil bearbeiten' : 'Profil erstellen' }}
        </h1>
        <p class="text-gray-500 text-sm mt-1">
          {{ profile ? 'Ändere deine Angaben – Änderungen sind sofort sichtbar.' : 'Fülle alle Pflichtfelder aus. Du wählst danach ein Paket.' }}
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
          <h2 class="font-semibold text-gray-500 text-xs uppercase tracking-wide">Basisdaten</h2>

          <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <InputField id="display_name" label="Name / Pseudonym"
              v-model="form.display_name" :error="form.errors.display_name"
              required placeholder="z.B. Susi oder Lady X" />
            <InputField id="age" label="Alter" v-model="form.age"
              type="number" min="18" max="99" :error="form.errors.age"
              required placeholder="z.B. 25" />
          </div>

          <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <!-- Stadt -->
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">
                Stadt <span class="text-[#e35d8f]">*</span>
              </label>
              <select v-model="form.city_id"
                :class="['w-full px-3 py-2 border rounded-md text-sm bg-white text-gray-900 focus:outline-none focus:ring-2 focus:ring-[#e35d8f] transition',
                  form.errors.city_id ? 'border-red-500' : 'border-gray-300']">
                <option value="">Stadt wählen…</option>
                <option v-for="city in cities" :key="city.id" :value="city.id">
                  {{ city.name }} ({{ city.canton }})
                </option>
              </select>
              <p v-if="form.errors.city_id" class="mt-1 text-xs text-red-600">{{ form.errors.city_id }}</p>
            </div>

            <!-- Kategorie -->
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">
                Kategorie <span class="text-[#e35d8f]">*</span>
              </label>
              <select v-model="form.category_id"
                :class="['w-full px-3 py-2 border rounded-md text-sm bg-white text-gray-900 focus:outline-none focus:ring-2 focus:ring-[#e35d8f] transition',
                  form.errors.category_id ? 'border-red-500' : 'border-gray-300']">
                <option value="">Kategorie wählen…</option>
                <option v-for="cat in categories" :key="cat.id" :value="cat.id">
                  {{ cat.name }}
                </option>
              </select>
              <p v-if="form.errors.category_id" class="mt-1 text-xs text-red-600">{{ form.errors.category_id }}</p>
            </div>
          </div>

          <!-- Weitere Angaben -->
          <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <InputField id="nationality" label="Nationalität"
              v-model="form.nationality" :error="form.errors.nationality"
              placeholder="z.B. Schweiz, Italien…" />
            <InputField id="height_cm" label="Körpergrösse (cm)"
              v-model="form.height_cm" type="number" min="120" max="230"
              :error="form.errors.height_cm" placeholder="z.B. 168" />
          </div>

          <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <!-- Augenfarbe -->
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Augenfarbe</label>
              <select v-model="form.eye_color"
                class="w-full px-3 py-2 border border-gray-300 rounded-md text-sm bg-white text-gray-900 focus:outline-none focus:ring-2 focus:ring-[#e35d8f] transition">
                <option value="">Keine Angabe</option>
                <option v-for="c in eyeColors" :key="c" :value="c">{{ c }}</option>
              </select>
            </div>

            <!-- Körperbau -->
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Körperbau</label>
              <select v-model="form.body_type"
                class="w-full px-3 py-2 border border-gray-300 rounded-md text-sm bg-white text-gray-900 focus:outline-none focus:ring-2 focus:ring-[#e35d8f] transition">
                <option value="">Keine Angabe</option>
                <option v-for="b in bodyTypes" :key="b" :value="b">{{ b }}</option>
              </select>
            </div>
          </div>

          <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
            <!-- Rauchen -->
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Rauchen</label>
              <select v-model="form.smoking"
                class="w-full px-3 py-2 border border-gray-300 rounded-md text-sm bg-white text-gray-900 focus:outline-none focus:ring-2 focus:ring-[#e35d8f] transition">
                <option :value="null">Keine Angabe</option>
                <option :value="true">Ja</option>
                <option :value="false">Nein</option>
              </select>
            </div>

            <!-- Tattoo -->
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Tattoo</label>
              <select v-model="form.tattoo"
                class="w-full px-3 py-2 border border-gray-300 rounded-md text-sm bg-white text-gray-900 focus:outline-none focus:ring-2 focus:ring-[#e35d8f] transition">
                <option :value="null">Keine Angabe</option>
                <option :value="true">Ja</option>
                <option :value="false">Nein</option>
              </select>
            </div>

            <!-- Intimbereich -->
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Intimbereich</label>
              <select v-model="form.intimate_area"
                class="w-full px-3 py-2 border border-gray-300 rounded-md text-sm bg-white text-gray-900 focus:outline-none focus:ring-2 focus:ring-[#e35d8f] transition">
                <option value="">Keine Angabe</option>
                <option value="Rasiert">Rasiert</option>
                <option value="Nicht rasiert">Nicht rasiert</option>
              </select>
            </div>
          </div>
        </div>

        <!-- Beschreibung -->
        <div class="bg-white rounded-xl border border-gray-200 p-5 space-y-3 shadow-sm">
          <h2 class="font-semibold text-gray-500 text-xs uppercase tracking-wide">Beschreibung</h2>
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">
              Über mich
              <span class="text-gray-400 font-normal ml-1">({{ form.description?.length ?? 0 }}/2000)</span>
            </label>
            <textarea v-model="form.description" rows="5" maxlength="2000"
              placeholder="Beschreibe dich, deine Angebote und was Besucher erwarten können…"
              :class="['w-full px-3 py-2 border rounded-md text-sm bg-white text-gray-900 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-[#e35d8f] transition resize-none',
                form.errors.description ? 'border-red-500' : 'border-gray-300']"
            />
            <p v-if="form.errors.description" class="mt-1 text-xs text-red-600">{{ form.errors.description }}</p>
          </div>
        </div>

        <!-- Tags -->
        <div class="bg-white rounded-xl border border-gray-200 p-5 space-y-4 shadow-sm">
          <h2 class="font-semibold text-gray-500 text-xs uppercase tracking-wide">Angebote / Tags</h2>
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
          <h2 class="font-semibold text-gray-500 text-xs uppercase tracking-wide">Kontakt & Preis</h2>

          <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <InputField id="whatsapp_number" label="Telefon / WhatsApp"
              v-model="form.whatsapp_number" type="tel"
              placeholder="+41 79 123 45 67" :error="form.errors.whatsapp_number"
              hint="Für alle Besucher sichtbar – wird als WhatsApp-Link angezeigt" />

            <InputField id="telegram_username" label="Telegram"
              v-model="form.telegram_username"
              placeholder="@deinusername" :error="form.errors.telegram_username"
              hint="Ohne @ eingeben ist auch OK – z.B. deinusername" />
          </div>

          <InputField id="address" label="Adresse / Standort"
            v-model="form.address"
            placeholder="z.B. Bahnhofstrasse 12, 8001 Zürich" :error="form.errors.address"
            hint="Wird auf Google Maps verlinkt – nur eingeben wenn du Besucherinnen empfängst" />

          <InputField id="website" label="Webseite"
            v-model="form.website"
            placeholder="https://www.deinewebseite.ch" :error="form.errors.website"
            hint="Wird als Button auf deinem Profil angezeigt" />

          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">
              Abo-Preis (CHF/Monat) <span class="text-[#e35d8f]">*</span>
            </label>
            <div class="relative">
              <span class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-sm">CHF</span>
              <input v-model="form.subscription_price_chf" type="number" min="9" max="999" step="1"
                :class="['w-full pl-12 pr-3 py-2 border rounded-md text-sm bg-white text-gray-900 focus:outline-none focus:ring-2 focus:ring-[#e35d8f] transition',
                  form.errors.subscription_price_chf ? 'border-red-500' : 'border-gray-300']" />
            </div>
            <p v-if="form.errors.subscription_price_chf" class="mt-1 text-xs text-red-600">{{ form.errors.subscription_price_chf }}</p>
            <p class="mt-1 text-xs text-gray-500">Abonnenten zahlen diesen Betrag monatlich für Zugriff auf deine privaten Medien.</p>

            <!-- Verdienst-Rechner -->
            <div v-if="form.subscription_price_chf >= 9" class="mt-2 bg-[#e35d8f]/10 border border-[#e35d8f]/20 rounded-lg px-3 py-2 text-xs text-[#e35d8f]">
              Bei 10 Abonnenten: <strong>CHF {{ earnings(10) }}/Mo</strong> ·
              Bei 50: <strong>CHF {{ earnings(50) }}/Mo</strong>
              <span class="text-[#e35d8f]/60 block mt-0.5">(nach 20% Plattform-Provision)</span>
            </div>
          </div>
        </div>

        <!-- Actions -->
        <div class="flex items-center gap-3 justify-end">
          <Link v-if="profile" :href="route('inserat.dashboard')"
            class="px-4 py-2 text-sm text-gray-500 hover:text-gray-900 transition">
            Abbrechen
          </Link>
          <PrimaryButton type="submit" :loading="form.processing">
            {{ profile ? 'Änderungen speichern' : 'Profil erstellen & weiter' }}
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
  const order = ['Allgemein', 'Softcore Service', 'Hardcore Service'];
  return [...groups.entries()]
    .sort((a, b) => {
      const ia = order.indexOf(a[0]);
      const ib = order.indexOf(b[0]);
      return (ia === -1 ? 99 : ia) - (ib === -1 ? 99 : ib);
    })
    .map(([label, items]) => ({ label, items }));
});

const steps = ['Profil erstellen', 'Paket wählen', 'Zahlung', 'Live!'];

const eyeColors = ['Braun', 'Blau', 'Grün', 'Grau', 'Bernstein', 'Schwarz'];
const bodyTypes = ['Schlank', 'Sportlich', 'Rundlich'];

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
