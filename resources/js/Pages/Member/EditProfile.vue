<template>
  <AppLayout>
    <Head title="Mein Profil" />

    <div class="max-w-3xl mx-auto px-4 py-8 space-y-6">
      <div>
        <h1 class="text-2xl font-bold text-white">Mein Profil</h1>
        <p class="text-gray-400 text-sm mt-1">Erzähle etwas über dich – so sehen dich andere.</p>
      </div>

      <!-- Profilfoto -->
      <div class="bg-[#1a1a1a] rounded-2xl border border-white/8 p-5">
        <h2 class="text-xs font-bold text-gray-500 uppercase tracking-wide mb-4">Profilfoto</h2>

        <div class="flex items-center gap-5">
          <div class="w-24 h-24 rounded-full overflow-hidden shrink-0 flex items-center justify-center border border-white/10"
            :style="member.avatar_url ? '' : 'background: linear-gradient(135deg,#e35d8f,#7c3aed)'">
            <img v-if="member.avatar_url" :src="member.avatar_url" alt="Profilfoto" class="w-full h-full object-cover" />
            <span v-else class="text-3xl font-bold text-white">{{ (member.name || '?').charAt(0).toUpperCase() }}</span>
          </div>

          <div class="flex flex-col gap-2">
            <input ref="fileInput" type="file" accept="image/jpeg,image/png,image/webp" class="hidden" @change="uploadAvatar" />
            <button type="button" @click="fileInput?.click()" :disabled="uploadingAvatar"
              class="inline-flex items-center gap-2 bg-[#e35d8f] hover:bg-[#c44a7a] disabled:opacity-50 text-white text-sm font-bold px-4 py-2 rounded-lg transition">
              <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"/><path stroke-linecap="round" stroke-linejoin="round" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
              {{ uploadingAvatar ? 'Wird hochgeladen …' : (member.avatar_url ? 'Foto ändern' : 'Foto hinzufügen') }}
            </button>
            <button v-if="member.avatar_url" type="button" @click="removeAvatar"
              class="text-xs text-gray-500 hover:text-red-400 transition text-left">Entfernen</button>
          </div>
        </div>

        <!-- Bilderregeln -->
        <div class="mt-4 bg-[#111] border border-white/8 rounded-xl p-4 text-xs text-gray-400 space-y-1.5">
          <p class="text-red-400 font-bold">⚠ Keine Genitalien-Fotos erlaubt.</p>
          <p>• Keine Fake-Fotos (Urheberrecht)</p>
          <p>• Nur Fotos in guter Qualität</p>
          <p>• Keine Smileys, Rahmen oder Bildeffekte</p>
          <p>• Verstösse können gemeldet und das Foto entfernt werden.</p>
        </div>
      </div>

      <form @submit.prevent="submit" class="space-y-6">
        <!-- Aussehen -->
        <div class="bg-[#1a1a1a] rounded-2xl border border-white/8 p-5 space-y-5">
          <h2 class="text-xs font-bold text-gray-500 uppercase tracking-wide">Aussehen</h2>

          <div>
            <label class="block text-sm font-medium text-gray-300 mb-2">Geschlecht</label>
            <div class="flex flex-wrap gap-2">
              <button v-for="[val,lbl] in genderOptions" :key="val" type="button" @click="form.gender = val" :class="pill(form.gender === val)">{{ lbl }}</button>
            </div>
          </div>

          <div class="grid grid-cols-2 sm:grid-cols-3 gap-4">
            <div>
              <label class="block text-sm font-medium text-gray-300 mb-1">Alter</label>
              <input v-model="form.age" type="number" min="18" max="99" placeholder="z.B. 30"
                class="w-full bg-[#111] border border-white/10 text-gray-200 text-sm rounded-lg px-3 py-2 focus:outline-none focus:border-[#e35d8f]" />
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-300 mb-1">Grösse (cm)</label>
              <input v-model="form.height_cm" type="number" min="120" max="230" placeholder="z.B. 178"
                class="w-full bg-[#111] border border-white/10 text-gray-200 text-sm rounded-lg px-3 py-2 focus:outline-none focus:border-[#e35d8f]" />
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-300 mb-1">Gewicht (kg)</label>
              <input v-model="form.weight_kg" type="number" min="30" max="200" placeholder="z.B. 72"
                class="w-full bg-[#111] border border-white/10 text-gray-200 text-sm rounded-lg px-3 py-2 focus:outline-none focus:border-[#e35d8f]" />
            </div>
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-300 mb-1">Standort</label>
            <select v-model="form.city_id"
              class="w-full bg-[#111] border border-white/10 text-gray-200 text-sm rounded-lg px-3 py-2 focus:outline-none focus:border-[#e35d8f]">
              <option :value="null">Keine Angabe</option>
              <option v-for="c in cities" :key="c.id" :value="c.id">{{ c.name }} ({{ c.canton }})</option>
            </select>
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-300 mb-2">Sprachen</label>
            <div class="flex flex-wrap gap-2">
              <button v-for="lang in languageOptions" :key="lang" type="button" @click="toggleLanguage(lang)" :class="pill(form.languages.includes(lang))">{{ lang }}</button>
            </div>
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-300 mb-2">Raucher</label>
            <div class="grid grid-cols-3 gap-1 bg-white/5 border border-white/10 rounded-full p-1 max-w-md">
              <button v-for="[val,lbl] in smokingOptions" :key="val" type="button" @click="form.smoking = val" :class="seg(form.smoking === val)">{{ lbl }}</button>
            </div>
          </div>
        </div>

        <!-- Über mich -->
        <div class="bg-[#1a1a1a] rounded-2xl border border-white/8 p-5 space-y-4">
          <h2 class="text-xs font-bold text-gray-500 uppercase tracking-wide">Über mich</h2>
          <div>
            <label class="block text-sm font-medium text-gray-300 mb-1">
              Beschreibung <span class="text-gray-500 font-normal">({{ form.bio?.length ?? 0 }}/2500)</span>
            </label>
            <textarea v-model="form.bio" rows="4" maxlength="2500" placeholder="Verrate mehr über dich …"
              class="w-full bg-[#111] border border-white/10 text-gray-200 text-sm rounded-lg px-3 py-2 focus:outline-none focus:border-[#e35d8f] resize-none" />
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-300 mb-1">
              Vorlieben <span class="text-gray-500 font-normal">({{ form.preferences?.length ?? 0 }}/2500)</span>
            </label>
            <textarea v-model="form.preferences" rows="3" maxlength="2500" placeholder="Angebote und Wünsche – das bestimmst nur du!"
              class="w-full bg-[#111] border border-white/10 text-gray-200 text-sm rounded-lg px-3 py-2 focus:outline-none focus:border-[#e35d8f] resize-none" />
          </div>
        </div>

        <div class="flex justify-end">
          <button type="submit" :disabled="form.processing"
            class="bg-[#e35d8f] hover:bg-[#c44a7a] disabled:opacity-50 text-white text-sm font-bold px-6 py-3 rounded-xl transition">
            {{ form.processing ? 'Speichern …' : 'Speichern' }}
          </button>
        </div>
      </form>

      <!-- Konto verwalten -->
      <div class="bg-[#1a1a1a] rounded-2xl border border-red-500/20 p-5 space-y-4">
        <h2 class="text-xs font-bold text-red-400 uppercase tracking-wide">Konto verwalten</h2>

        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
          <div>
            <p class="text-sm font-semibold text-white">Konto deaktivieren</p>
            <p class="text-xs text-gray-500">Dein Profil wird ausgeblendet. Beim nächsten Login wird es reaktiviert.</p>
          </div>
          <button @click="deactivate" class="shrink-0 border border-white/15 text-gray-300 text-sm font-semibold px-4 py-2 rounded-lg hover:border-white/30 transition">
            Deaktivieren
          </button>
        </div>

        <div class="border-t border-white/5 pt-4 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
          <div>
            <p class="text-sm font-semibold text-white">Konto endgültig löschen</p>
            <p class="text-xs text-gray-500">Alle deine Daten, Bewertungen und Abos werden entfernt. Nicht umkehrbar.</p>
          </div>
          <button @click="destroy" class="shrink-0 bg-red-500/90 hover:bg-red-500 text-white text-sm font-bold px-4 py-2 rounded-lg transition">
            Löschen
          </button>
        </div>
      </div>
    </div>
  </AppLayout>
</template>

<script setup>
import { ref } from 'vue';
import { Head, useForm, router } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';

const props = defineProps({
  member: { type: Object, required: true },
  cities: { type: Array,  default: () => [] },
});

const fileInput = ref(null);
const uploadingAvatar = ref(false);

function uploadAvatar(e) {
  const file = e.target.files?.[0];
  if (!file) return;
  uploadingAvatar.value = true;
  router.post(route('konto.account.avatar.upload'), { photo: file }, {
    forceFormData: true,
    preserveScroll: true,
    onFinish: () => {
      uploadingAvatar.value = false;
      if (fileInput.value) fileInput.value.value = '';
    },
  });
}

function removeAvatar() {
  if (!confirm('Profilfoto wirklich entfernen?')) return;
  router.delete(route('konto.account.avatar.delete'), { preserveScroll: true });
}

const genderOptions = [['frau', 'Frau'], ['mann', 'Mann'], ['paar', 'Paar'], ['trans', 'Trans*'], ['divers', 'Divers']];
const smokingOptions = [['', 'Keine Angabe'], ['0', 'Nichtraucher*in'], ['1', 'Raucher*in']];
const languageOptions = [
  'Deutsch', 'Englisch', 'Französisch', 'Italienisch', 'Spanisch', 'Portugiesisch',
  'Russisch', 'Türkisch', 'Arabisch', 'Ungarisch', 'Rumänisch', 'Polnisch', 'Albanisch',
];

const form = useForm({
  gender:      props.member.gender ?? '',
  age:         props.member.age ?? '',
  height_cm:   props.member.height_cm ?? '',
  weight_kg:   props.member.weight_kg ?? '',
  city_id:     props.member.city_id ?? null,
  languages:   Array.isArray(props.member.languages) ? [...props.member.languages] : [],
  smoking:     props.member.smoking === true ? '1' : props.member.smoking === false ? '0' : '',
  bio:         props.member.bio ?? '',
  preferences: props.member.preferences ?? '',
});

function toggleLanguage(lang) {
  const i = form.languages.indexOf(lang);
  if (i === -1) form.languages.push(lang);
  else form.languages.splice(i, 1);
}

function pill(active) {
  return [
    'px-3 py-1.5 rounded-full text-sm font-medium border transition-all',
    active ? 'bg-[#e35d8f] text-white border-[#e35d8f]' : 'bg-white/5 text-gray-300 border-white/10 hover:border-[#e35d8f]/50',
  ];
}
function seg(active) {
  return [
    'py-2 rounded-full text-sm font-medium transition text-center',
    active ? 'bg-[#e35d8f] text-white shadow' : 'text-gray-400 hover:text-white',
  ];
}

function submit() {
  form.put(route('konto.account.update'), { preserveScroll: true });
}

function deactivate() {
  if (!confirm('Konto wirklich deaktivieren? Du wirst abgemeldet und beim nächsten Login wieder aktiviert.')) return;
  router.post(route('konto.account.deactivate'));
}

function destroy() {
  if (!confirm('Konto wirklich UNWIDERRUFLICH löschen? Alle deine Daten werden entfernt.')) return;
  router.delete(route('konto.account.destroy'));
}
</script>
