<template>
  <div class="pt-1">
    <div class="flex items-center justify-between mb-3">
      <span class="text-xs text-gray-500">{{ label }}</span>
      <span class="text-xs font-bold text-[#e35d8f]">{{ display }}</span>
    </div>

    <div class="relative h-5 flex items-center">
      <!-- Track -->
      <div class="absolute inset-x-0 h-1 rounded-full bg-white/10"></div>
      <!-- Aktive Spanne -->
      <div class="absolute h-1 rounded-full bg-[#e35d8f]" :style="fillStyle"></div>

      <input type="range" :min="min" :max="max" :step="step" :value="lo"
        @input="onLo" class="range-thumb" />
      <input type="range" :min="min" :max="max" :step="step" :value="hi"
        @input="onHi" class="range-thumb" />
    </div>

    <div class="flex justify-between mt-1 text-[11px] text-gray-600">
      <span>{{ format(min) }}</span>
      <span>{{ format(max) }}{{ maxSuffix }}</span>
    </div>
  </div>
</template>

<script setup>
import { computed } from 'vue';

const props = defineProps({
  modelValue: { type: Array, required: true },   // [lo, hi]
  min:        { type: Number, required: true },
  max:        { type: Number, required: true },
  step:       { type: Number, default: 1 },
  label:      { type: String, default: '' },
  unit:       { type: String, default: '' },     // z.B. "cm", "kg"
  maxSuffix:  { type: String, default: '+' },     // Anzeige am rechten Ende
  format:     { type: Function, default: (v) => v },
});

const emit = defineEmits(['update:modelValue']);

const lo = computed(() => props.modelValue[0]);
const hi = computed(() => props.modelValue[1]);

const pct = (v) => ((v - props.min) / (props.max - props.min)) * 100;

const fillStyle = computed(() => ({
  left:  pct(lo.value) + '%',
  width: (pct(hi.value) - pct(lo.value)) + '%',
}));

const display = computed(() => {
  const hiLabel = hi.value >= props.max ? props.format(hi.value) + props.maxSuffix : props.format(hi.value);
  return `${props.format(lo.value)} – ${hiLabel}${props.unit ? ' ' + props.unit : ''}`;
});

function onLo(e) {
  const v = Math.min(Number(e.target.value), hi.value);
  emit('update:modelValue', [v, hi.value]);
}
function onHi(e) {
  const v = Math.max(Number(e.target.value), lo.value);
  emit('update:modelValue', [lo.value, v]);
}
</script>

<style scoped>
.range-thumb {
  position: absolute;
  width: 100%;
  margin: 0;
  background: none;
  pointer-events: none;
  -webkit-appearance: none;
  appearance: none;
  height: 20px;
}
.range-thumb::-webkit-slider-thumb {
  -webkit-appearance: none;
  appearance: none;
  height: 18px;
  width: 18px;
  border-radius: 9999px;
  background: #fff;
  border: 3px solid #e35d8f;
  cursor: pointer;
  pointer-events: auto;
  margin-top: 0;
}
.range-thumb::-moz-range-thumb {
  height: 16px;
  width: 16px;
  border-radius: 9999px;
  background: #fff;
  border: 3px solid #e35d8f;
  cursor: pointer;
  pointer-events: auto;
}
.range-thumb::-webkit-slider-runnable-track { background: transparent; }
.range-thumb::-moz-range-track { background: transparent; }
</style>
