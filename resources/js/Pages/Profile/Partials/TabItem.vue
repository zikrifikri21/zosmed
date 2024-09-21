<script setup>
defineProps({
    selected: Boolean,
    text: String,
});
</script>
<template>
    <button type="button" aria-label="true" :class="[
        'px-3 py-2.5 outline-none text-sm ',
        selected
            ? 'text-blue-500 border-b-2 border-blue-500 dark:text-blue-400 dark:border-blue-400'
            : 'text-gray-700 hover:text-blue-500 dark:text-gray-400 dark:hover:text-blue-400',
    ]">
        {{ text }}
    </button>
</template>
