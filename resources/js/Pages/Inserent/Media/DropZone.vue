<template>
  <div
    class="border-2 border-dashed rounded-lg p-6 text-center transition-colors"
    :class="[
      disabled ? 'border-gray-200 bg-gray-50 cursor-not-allowed opacity-60' :
      dragging  ? 'border-pink-500 bg-pink-50' : 'border-gray-300 hover:border-pink-400 cursor-pointer'
    ]"
    @dragover.prevent="!disabled && (dragging = true)"
    @dragleave="dragging = false"
    @drop.prevent="onDrop"
    @click="!disabled && $refs.fileInput.click()"
  >
    <input ref="fileInput" type="file" class="hidden" :accept="acceptStr" :disabled="disabled" @change="onFileChange" />
    <div class="text-3xl mb-2">{{ dragging ? '⬇️' : '📁' }}</div>
    <p class="text-sm font-medium text-gray-700">
      {{ disabled ? 'Limit erreicht' : 'Datei hierher ziehen oder klicken' }}
    </p>
    <p class="text-xs text-gray-400 mt-1">JPG, PNG, WebP, MP4 – max. 50 MB</p>

    <!-- Upload progress -->
    <div v-if="uploading" class="mt-3">
      <div class="h-1.5 bg-gray-200 rounded-full overflow-hidden">
        <div class="h-full bg-pink-500 transition-all" :style="{ width: progress + '%' }"></div>
      </div>
      <p class="text-xs text-gray-500 mt-1">{{ progress }}%</p>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue';

const props = defineProps({
  visibility: { type: String, required: true },
  disabled:   { type: Boolean, default: false },
});
const emit = defineEmits(['upload']);

const dragging  = ref(false);
const uploading = ref(false);
const progress  = ref(0);
const acceptStr = 'image/jpeg,image/png,image/webp,video/mp4,video/quicktime';

function onDrop(e) {
  dragging.value = false;
  if (props.disabled) return;
  const file = e.dataTransfer.files[0];
  if (file) processFile(file);
}

function onFileChange(e) {
  const file = e.target.files[0];
  if (file) processFile(file);
  e.target.value = '';
}

function processFile(file) {
  if (file.size > 52428800) {
    alert('Datei zu groß (max. 50 MB).');
    return;
  }
  emit('upload', file);
}
</script>
