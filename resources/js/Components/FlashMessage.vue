<template>
  <Transition
    enter-active-class="transition ease-out duration-300"
    enter-from-class="opacity-0 translate-y-[-8px]"
    enter-to-class="opacity-100 translate-y-0"
    leave-active-class="transition ease-in duration-200"
    leave-from-class="opacity-100"
    leave-to-class="opacity-0"
  >
    <div v-if="visible" :class="[
      'fixed top-16 right-4 z-50 flex items-start gap-3 px-4 py-3 rounded-lg shadow-lg text-sm max-w-sm',
      type === 'success' ? 'bg-green-50 border border-green-200 text-green-800' : 'bg-red-50 border border-red-200 text-red-800',
    ]">
      <span class="text-lg leading-none">{{ type === 'success' ? '✓' : '✕' }}</span>
      <p class="flex-1">{{ message }}</p>
      <button @click="visible = false" class="text-gray-400 hover:text-gray-600 leading-none text-base ml-1">×</button>
    </div>
  </Transition>
</template>

<script setup>
import { ref, watch, onMounted } from 'vue';
import { usePage } from '@inertiajs/vue3';

const page = usePage();
const visible = ref(false);
const message = ref('');
const type = ref('success');

function show(msg, t = 'success') {
  message.value = msg;
  type.value = t;
  visible.value = true;
  setTimeout(() => { visible.value = false; }, 4000);
}

onMounted(() => {
  if (page.props.flash?.success) show(page.props.flash.success, 'success');
  if (page.props.flash?.error)   show(page.props.flash.error,   'error');
});

watch(() => page.props.flash, (flash) => {
  if (flash?.success) show(flash.success, 'success');
  if (flash?.error)   show(flash.error,   'error');
}, { deep: true });
</script>
