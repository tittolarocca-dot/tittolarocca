<template>
  <div>
    <h3 class="text-lg font-semibold text-gray-800 mb-4">
      {{ title }}
      <span class="text-sm font-normal text-gray-400 ml-2">({{ items.length }})</span>
    </h3>
    <div v-if="items.length === 0" class="text-sm text-gray-400 italic py-4">Keine Medien in dieser Kategorie.</div>
    <div v-else class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 gap-3">
      <div
        v-for="item in items"
        :key="item.id"
        class="relative group aspect-square rounded-lg overflow-hidden border border-gray-200 bg-gray-100"
      >
        <!-- Image -->
        <img
          v-if="item.type === 'image'"
          :src="item.url"
          :alt="`Media ${item.id}`"
          class="w-full h-full object-cover"
          loading="lazy"
        />
        <!-- Video thumbnail -->
        <div v-else class="w-full h-full flex items-center justify-center bg-gray-800 text-white text-3xl">
          ▶
        </div>

        <!-- Status badge -->
        <div class="absolute top-1.5 left-1.5">
          <span
            class="text-xs px-1.5 py-0.5 rounded-full font-semibold"
            :class="{
              'bg-yellow-400 text-yellow-900': item.status === 'pending',
              'bg-green-500 text-white':        item.status === 'approved',
              'bg-red-500 text-white':          item.status === 'rejected',
            }"
          >
            {{ statusLabel(item.status) }}
          </span>
        </div>

        <!-- Delete button (hover) -->
        <button
          @click="$emit('delete', item)"
          class="absolute top-1.5 right-1.5 w-6 h-6 bg-black/60 text-white rounded-full text-xs flex items-center justify-center opacity-0 group-hover:opacity-100 transition hover:bg-red-600"
          title="Löschen"
        >✕</button>

        <!-- Rejection reason tooltip -->
        <div v-if="item.status === 'rejected' && item.rejection_reason"
          class="absolute bottom-0 left-0 right-0 bg-red-600 text-white text-xs p-1 text-center truncate"
          :title="item.rejection_reason"
        >
          {{ item.rejection_reason }}
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
defineProps({
  title: { type: String, required: true },
  items: { type: Array, default: () => [] },
});
defineEmits(['delete']);

function statusLabel(status) {
  return { pending: 'Prüfung', approved: 'OK', rejected: 'Abgelehnt' }[status] ?? status;
}
</script>
