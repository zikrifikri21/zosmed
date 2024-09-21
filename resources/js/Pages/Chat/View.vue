<script setup>
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import { ChatBubbleOvalLeftIcon } from "@heroicons/vue/24/outline";
import { Head, Link } from "@inertiajs/vue3";

defineProps({
    friends: Array
});


</script>

<template>

    <Head title="Your Frineds" />
    <AuthenticatedLayout>
        <div class="p-12 bg-white dark:bg-gray-800 shadow sm:rounded-lg text-gray-800 dark:text-gray-100">
            <h1 class="text-2xl">Your Frineds</h1>
            <div class="p-3 bg-white dark:bg-gray-800 shadow sm:rounded-lg overflow-hidden">
                <ul class="max-w-md divide-y divide-gray-200 dark:divide-gray-700">
                    <template v-for="friend in friends">
                        <li class="pb-3 sm:pb-4">
                            <div class="flex items-center space-x-4 rtl:space-x-reverse">
                                <div class="flex-shrink-0">
                                    <img class="w-8 h-8 rounded-full"
                                        :src="friend.avatar_url || '/img/default_avatar.jpeg'" alt="Neil image">
                                </div>
                                <div class="flex-1 min-w-0">
                                    <p class="text-sm font-medium text-gray-900 truncate dark:text-white">
                                        {{ friend.name }}
                                    </p>
                                    <p class="text-sm text-gray-500 truncate dark:text-gray-400">
                                        {{ friend.email }}
                                    </p>
                                </div>
                                <Link :href="route('chat', friend.id)" :aria-label="'Chat with ' + friend.name"
                                    class="inline-flex items-center text-base font-semibold text-gray-900 dark:text-white">
                                <ChatBubbleOvalLeftIcon class="w-6 h-6" aria-hidden="true" />
                                </Link>
                            </div>
                        </li>
                    </template>
                </ul>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
