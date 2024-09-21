<script setup>
import Chatting from "@/Components/app/Chat/Chatting.vue";
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import { Head, usePage } from "@inertiajs/vue3";
defineProps({
    friend: Object,
})
const page = usePage().props;

</script>

<template>

    <Head title="Chat Room" />
    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                {{ friend.name }}
            </h2>
        </template>
        <Chatting :friend="friend" :currentUser="page.auth.user"></Chatting>
    </AuthenticatedLayout>
</template>
