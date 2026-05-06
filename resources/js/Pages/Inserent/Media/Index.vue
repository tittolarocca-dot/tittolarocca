<template>
  <AppLayout>
    <div class="max-w-5xl mx-auto px-4 py-8">
      <div class="flex items-center justify-between mb-6">
        <div>
          <h1 class="text-2xl font-bold text-gray-900">Meine Medien</h1>
          <p class="text-sm text-gray-500 mt-1">Öffentliche und private Fotos/Videos verwalten</p>
        </div>
        <Link :href="route('inserat.dashboard')" class="text-sm text-pink-600 hover:underline">← Dashboard</Link>
      </div>

      <!-- Status Banner -->
      <div v-if="profile.status !== 'active'" class="mb-6 bg-yellow-50 border border-yellow-200 rounded-lg p-4 text-sm text-yellow-800">
        Dein Profil ist noch nicht aktiv. Nach der Freischaltung sind genehmigte Medien öffentlich sichtbar.
      </div>

      <!-- Upload Panels -->
      <div class="grid md:grid-cols-2 gap-6 mb-8">
        <!-- Public Upload -->
        <div class="bg-white rounded-xl border border-gray-200 p-6">
          <div class="flex items-center justify-between mb-3">
            <h2 class="font-semibold text-gray-800">Öffentliche Fotos</h2>
            <span class="text-xs text-gray-500">{{ limits.public_used }}/{{ limits.public_max }}</span>
          </div>
          <p class="text-xs text-gray-500 mb-4">Für alle Besucher sichtbar (nach Genehmigung)</p>
          <DropZone
            visibility="public"
            :disabled="limits.public_used >= limits.public_max"
            @upload="upload($event, 'public')"
          />
        </div>

        <!-- Private Upload -->
        <div class="bg-white rounded-xl border border-gray-200 p-6">
          <div class="flex items-center justify-between mb-3">
            <h2 class="font-semibold text-gray-800">Private Fotos / Videos</h2>
            <span class="text-xs text-gray-500">{{ limits.private_used }}/{{ limits.private_max }}</span>
          </div>
          <p class="text-xs text-gray-500 mb-4">Nur für zahlende Abonnenten sichtbar</p>
          <DropZone
            visibility="private"
            :disabled="limits.private_used >= limits.private_max"
            @upload="upload($event, 'private')"
          />
        </div>
      </div>

      <!-- Media Grid -->
      <div v-if="media.length === 0" class="text-center py-16 text-gray-400">
        <div class="text-5xl mb-3">📷</div>
        <p>Noch keine Medien hochgeladen.</p>
      </div>

      <template v-else>
        <!-- Hauptfoto -->
        <div class="bg-white rounded-xl border border-gray-200 p-6 mb-8">
          <h2 class="font-semibold text-gray-800 mb-1">Hauptfoto</h2>
          <p class="text-xs text-gray-500 mb-4">Wird gross nach dem Profilnamen angezeigt. Nur öffentliche Fotos wählbar.</p>

          <div v-if="mainPhoto" class="flex items-center gap-4 mb-4">
            <img :src="mainPhoto.url" class="w-24 h-24 object-cover rounded-xl border border-gray-200" />
            <div>
              <p class="text-sm font-semibold text-gray-700 mb-1">Aktuelles Hauptfoto</p>
              <button @click="confirmDelete(mainPhoto)"
                class="text-xs text-red-500 hover:underline">Löschen</button>
            </div>
          </div>
          <div v-else class="text-sm text-gray-400 mb-4 italic">Kein Hauptfoto gesetzt.</div>

          <div v-if="publicPhotos.length" class="grid grid-cols-3 sm:grid-cols-5 gap-2">
            <div v-for="item in publicPhotos" :key="item.id" class="relative group">
              <img :src="item.url" class="w-full aspect-square object-cover rounded-lg border-2 transition"
                :class="item.is_main ? 'border-pink-500' : 'border-transparent hover:border-pink-300'" />
              <button v-if="!item.is_main"
                @click="setMain(item)"
                class="absolute inset-0 bg-black/40 opacity-0 group-hover:opacity-100 transition rounded-lg flex items-center justify-center text-white text-xs font-bold">
                Als Hauptfoto
              </button>
              <div v-else class="absolute top-1 right-1 bg-pink-500 text-white text-[10px] font-bold px-1.5 py-0.5 rounded-full">
                Haupt
              </div>
            </div>
          </div>
          <p v-else class="text-xs text-gray-400">Lade zuerst öffentliche Fotos hoch.</p>
        </div>

        <MediaSection
          title="Öffentliche Fotos"
          :items="publicMedia"
          @delete="confirmDelete"
        />
        <MediaSection
          title="Private Fotos & Videos"
          :items="privateMedia"
          class="mt-8"
          @delete="confirmDelete"
        />
      </template>
    </div>

    <!-- Delete Confirm Modal -->
    <div v-if="deleteTarget" class="fixed inset-0 bg-black/50 flex items-center justify-center z-50" @click.self="deleteTarget=null">
      <div class="bg-white rounded-xl p-6 max-w-sm w-full mx-4 shadow-xl">
        <h3 class="font-bold text-gray-900 mb-2">Datei löschen?</h3>
        <p class="text-sm text-gray-500 mb-5">Diese Aktion kann nicht rückgängig gemacht werden.</p>
        <div class="flex gap-3">
          <button @click="deleteTarget=null" class="flex-1 px-4 py-2 border border-gray-300 rounded-lg text-sm hover:bg-gray-50">
            Abbrechen
          </button>
          <button @click="doDelete" :disabled="deleting" class="flex-1 px-4 py-2 bg-red-600 text-white rounded-lg text-sm hover:bg-red-700 disabled:opacity-50">
            {{ deleting ? 'Löschen…' : 'Ja, löschen' }}
          </button>
        </div>
      </div>
    </div>
  </AppLayout>
</template>

<script setup>
import { ref, computed } from 'vue';
import { Link, router, useForm } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import DropZone from './DropZone.vue';
import MediaSection from './MediaSection.vue';

const props = defineProps({
  media:   { type: Array, default: () => [] },
  profile: { type: Object, required: true },
  limits:  { type: Object, required: true },
});

const publicMedia  = computed(() => props.media.filter(m => m.visibility === 'public'));
const privateMedia = computed(() => props.media.filter(m => m.visibility === 'private'));
const publicPhotos = computed(() => publicMedia.value.filter(m => m.type === 'image'));
const mainPhoto    = computed(() => publicPhotos.value.find(m => m.is_main) ?? null);

const deleteTarget = ref(null);
const deleting     = ref(false);

function confirmDelete(item) {
  deleteTarget.value = item;
}

function doDelete() {
  deleting.value = true;
  router.delete(route('inserat.media.destroy', deleteTarget.value.id), {
    onFinish: () => { deleting.value = false; deleteTarget.value = null; },
  });
}

function setMain(item) {
  router.post(route('inserat.media.setMain', item.id), {}, { preserveScroll: true });
}

function upload(file, visibility) {
  const form = new FormData();
  form.append('file', file);
  form.append('visibility', visibility);
  router.post(route('inserat.media.store'), form, {
    forceFormData: true,
    preserveScroll: true,
  });
}
</script>
