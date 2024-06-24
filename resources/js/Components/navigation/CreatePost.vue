<script setup>
import { PlusIcon } from "@heroicons/vue/24/solid";
import PostModal from "../app/PostModal.vue";
import { ref } from "vue";
import { usePage } from "@inertiajs/vue3";

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
});

function showCreatePostModal() {
    showModal.value = true;
}
</script>
<template>
    <button
        type="button"
        @click="showCreatePostModal"
        class="inline-flex flex-col items-center justify-center px-5"
    >
        <PlusIcon
            class="w-6 h-6 mb-2 border-2 border-gray-800 dark:border-gray-100 rounded-md text-gray-800 dark:text-gray-100 hover:scale-110 transition-transform duration-300"
        />
    </button>
    <PostModal :post="newPost" :group="group" v-model="showModal" />
</template>

<style lang="scss" scoped></style>
