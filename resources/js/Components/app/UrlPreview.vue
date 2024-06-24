<script setup>
defineProps({
    preview: Object,
    url: String,
});
</script>
<template>
    <a
        :href="url"
        target="_blank"
        v-if="preview && preview.title"
        class="mt-4 block"
    >
        <img :src="preview.image" class="w-full" :alt="preview.title" />
        <div>
            <h3 class="font-bold mt-2">
                {{ preview.title }}
            </h3>
            <p class="text-sm p-2" :title="preview.description">
                {{
                    preview.description.slice(0, 50) +
                    (preview.description.length > 50 ? "..." : "")
                }}
            </p>
        </div>
    </a>
    <a :href="url" target="_blank" v-else-if="url" class="mt-4 block">
        <div
            class="w-full flex flex-col justify-center items-center h-40 bg-gray-100 dark:bg-gray-800 rounded-lg
            text-gray-800 dark:text-gray-100"
        >
            <p>{{ url }}</p>
            <p>Links do not have titles and descriptions</p>
        </div>
    </a>
</template>
