<script setup>
import { onMounted, ref } from "vue";

const model = defineModel({
    type: String,
    required: false,
});

const input = ref(null);

onMounted(() => {
    if (input.value.hasAttribute("autofocus")) {
        input.value.focus();
    }
});

defineExpose({ focus: () => input.value.focus() });

function resize() {
    input.value.style.height = "auto";
    input.value.style.height = input.value.scrollHeight + "px";
}
</script>

<template>
    <textarea
        @input="resize()"
        class="border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm overflow-hidden"
        ref="input"
        v-model="model"
    ></textarea>
</template>
