<template>
  <div>
    <label v-if="label" :for="id" class="block text-sm font-medium text-gray-700 mb-1">
      {{ label }} <span v-if="required" class="text-pink-500">*</span>
    </label>
    <input
      :id="id"
      v-bind="$attrs"
      :value="modelValue"
      :type="type"
      :class="[
        'w-full px-3 py-2 border rounded-md text-sm shadow-sm focus:outline-none focus:ring-2 focus:ring-pink-500 focus:border-pink-500 transition',
        error ? 'border-red-400 bg-red-50' : 'border-gray-300 bg-white',
      ]"
      @input="$emit('update:modelValue', $event.target.value)"
    />
    <p v-if="error" class="mt-1 text-xs text-red-600">{{ error }}</p>
    <p v-if="hint && !error" class="mt-1 text-xs text-gray-400">{{ hint }}</p>
  </div>
</template>

<script setup>
defineProps({
  id:         { type: String, default: () => Math.random().toString(36).slice(2) },
  label:      String,
  modelValue: [String, Number],
  type:       { type: String, default: 'text' },
  error:      String,
  hint:       String,
  required:   Boolean,
});
defineEmits(['update:modelValue']);
defineOptions({ inheritAttrs: false });
</script>
