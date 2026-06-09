<template>
  <div class="relative w-full h-full">
    <!-- Video thumbnail -->
    <template v-if="item.type === 'video'">
      <video
        :src="item.url"
        preload="metadata"
        muted
        playsinline
        class="w-full h-full object-cover"
      />
      <!-- Play button overlay -->
      <div class="absolute inset-0 flex items-center justify-center bg-black/30 group-hover:bg-black/20 transition">
        <div class="w-12 h-12 rounded-full bg-black/60 backdrop-blur-sm flex items-center justify-center shadow-lg">
          <svg class="w-5 h-5 text-white ml-0.5" fill="currentColor" viewBox="0 0 24 24">
            <path d="M8 5v14l11-7z"/>
          </svg>
        </div>
      </div>
      <!-- Video label -->
      <div class="absolute bottom-2 left-2 bg-black/60 backdrop-blur-sm rounded px-1.5 py-0.5 flex items-center gap-1">
        <svg class="w-3 h-3 text-white" fill="currentColor" viewBox="0 0 24 24">
          <path d="M17 10.5V7a1 1 0 00-1-1H4a1 1 0 00-1 1v10a1 1 0 001 1h12a1 1 0 001-1v-3.5l4 4v-11l-4 4z"/>
        </svg>
        <span class="text-white text-[10px] font-semibold">Video</span>
      </div>
    </template>

    <!-- Image thumbnail -->
    <img
      v-else
      :src="eager ? item.url : undefined"
      :data-src="!eager ? item.url : undefined"
      :class="['w-full h-full object-cover object-center group-hover:scale-105 transition duration-300', !eager ? 'lazyload' : '']"
      :alt="alt"
      :loading="eager ? 'eager' : 'lazy'"
    />
  </div>
</template>

<script setup>
defineProps({
  item:  { type: Object,  required: true },
  alt:   { type: String,  default: '' },
  eager: { type: Boolean, default: false },
});
</script>
