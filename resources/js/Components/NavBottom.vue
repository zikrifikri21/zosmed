<script setup>
import { ArrowLeftEndOnRectangleIcon } from "@heroicons/vue/24/outline";
import { HomeIcon, PlusIcon } from "@heroicons/vue/24/solid";
import { Link } from "@inertiajs/vue3";
import SearchButton from "@/Components/navigation/SearchButton.vue";
import CreatePost from "./navigation/CreatePost.vue";
defineProps({
    authUser: Object,
});

const routeName = route().current();
</script>
<template>
    <div v-if="routeName != 'chat'" class="fixed bottom-0 left-0 z-50 w-full h-12 bg-white dark:bg-gray-900 sm:hidden">
        <div :class="authUser
            ? 'grid h-full max-w-lg grid-cols-4 mx-auto font-medium'
            : 'grid grid-cols-2 h-full mx-auto justify-between'
            ">
            <Link :href="route('dashboard')" class="inline-flex flex-col items-center justify-center px-5"
                aria-label="Home page">
            <HomeIcon
                class="w-6 h-6 mb-2 text-gray-800 dark:text-gray-100 hover:scale-110 transition-transform duration-300" />
            </Link>
            <template v-if="authUser">
                <SearchButton />
                <CreatePost />
            </template>
            <Link v-if="authUser" :href="`/u/${authUser?.username}`" :aria-label="'Profile' + authUser?.username"
                class="inline-flex flex-col items-center justify-center px-5">
            <img :src="authUser?.avatar_url || '/img/default_avatar.jpeg'" :alt="'zosmed' + authUser?.name"
                class="w-6 h-6 mb-2 rounded-full" />
            </Link>
            <Link v-else :href="route('login')" class="inline-flex flex-col items-center justify-center px-5"
                aria-label="Login page">
            <ArrowLeftEndOnRectangleIcon
                class="w-6 h-6 mb-2 text-gray-800 dark:text-gray-100 hover:scale-110 transition-transform duration-300" />
            </Link>
        </div>
    </div>
</template>
