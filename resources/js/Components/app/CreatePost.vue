<script setup>
import { ref } from "vue";
import PostModal from "./PostModal.vue";
import { usePage } from "@inertiajs/vue3";
import { PlusIcon } from "@heroicons/vue/24/solid";

const authUser = usePage().props.auth.user;
const showModal = ref(false);
const newPost = ref({
    id: null,
    body: "",
    user: authUser,
});

defineProps({
    group: {
        type: Object,
        default: null,
    },
    followings: {
        type: Object,
        default: null,
    },
});

function showCreatePostModal() {
    showModal.value = true;
}
</script>
<template>
    <div
        v-if="authUser"
        class="flex flex-row gap-2 overflow-x-auto whitespace-nowrap items-list px-2"
    >
        <div class="flex flex-col mb-3">
            <div
                @click="showCreatePostModal"
                class="py-5 px-4 border-2 h-24 border-gray-200 bg-white dark:bg-gray-800 text-gray-500 dark:border-gray-700 rounded-xl cursor-pointer"
            >
                <div class="relative w-12">
                    <img
                        :src="
                            $page.props.auth.user?.avatar_url ||
                            '/img/default_avatar.jpeg'
                        "
                        class="w-full h-12 object-cover rounded-full hover:opacity-50"
                    />
                    <div
                        class="absolute top-0 left-0 w-full h-full flex flex-col justify-center items-center cursor-pointer hover:bg-black/10 rounded-full"
                    >
                        <PlusIcon
                            class="w-6 h-6 text-gray-800 dark:text-gray-100 hover:scale-110 transition-transform duration-300 opacity-30 hover:opacity-100"
                        />
                    </div>
                </div>
            </div>
            <PostModal :post="newPost" :group="group" v-model="showModal" />
        </div>
        <div class="flex flex-col" v-for="following in followings">
            <div
                class="border-2 border-gray-200 bg-white dark:bg-gray-800 text-gray-500 dark:border-gray-700 rounded-xl cursor-pointer"
            >
                <div class="w-20">
                    <img
                        :src="following.avatar_url"
                        class="w-full p-1 h-24 object-cover rounded-xl"
                    />
                </div>
            </div>
        </div>
    </div>
</template>
