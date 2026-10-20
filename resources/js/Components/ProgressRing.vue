<template>
  <div class="flex flex-col items-center gap-1.5">
    <div class="relative" :style="{ width: size + 'px', height: size + 'px' }">
      <svg :width="size" :height="size" :viewBox="`0 0 ${size} ${size}`" class="-rotate-90">
        <circle :cx="c" :cy="c" :r="r" fill="none" stroke="rgba(255,255,255,0.1)" :stroke-width="stroke" />
        <circle :cx="c" :cy="c" :r="r" fill="none" stroke="#e35d8f" :stroke-width="stroke" stroke-linecap="round"
          :stroke-dasharray="circumference" :stroke-dashoffset="offset"
          style="transition: stroke-dashoffset .6s ease" />
      </svg>
      <div class="absolute inset-0 flex flex-col items-center justify-center">
        <b class="font-extrabold leading-none text-white" :style="{ fontSize: Math.round(size * 0.22) + 'px' }">{{ percent }}%</b>
        <span v-if="sub" class="text-[11px] text-gray-500 mt-0.5">{{ sub }}</span>
      </div>
    </div>
    <span v-if="label" class="text-xs text-gray-400 font-semibold">{{ label }}</span>
  </div>
</template>

<script setup>
import { computed } from 'vue';

const props = defineProps({
  percent: { type: Number, default: 0 },
  size:    { type: Number, default: 118 },
  stroke:  { type: Number, default: 10 },
  label:   { type: String, default: '' },
  sub:     { type: String, default: '' },
});

const c = computed(() => props.size / 2);
const r = computed(() => props.size / 2 - props.stroke);
const circumference = computed(() => 2 * Math.PI * r.value);
const offset = computed(() => circumference.value * (1 - Math.max(0, Math.min(100, props.percent)) / 100));
</script>
