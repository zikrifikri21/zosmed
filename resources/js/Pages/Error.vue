<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';

defineProps({
    title: String,
    body: String
})
</script>
<template>
    <AuthenticatedLayout>
        <div class="py-32 text-center">
            <h1 class="text-2xl">{{ title }}</h1>
            <div>{{ body }}</div>
        </div>
    </AuthenticatedLayout>
</template>
