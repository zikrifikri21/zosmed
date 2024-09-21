<script setup>
import { MagnifyingGlassIcon } from "@heroicons/vue/24/outline";
import Modal from "../Modal.vue";
import TextInput from "../TextInput.vue";
import { router, usePage } from "@inertiajs/vue3";
import { ref } from "vue";

const showModal = ref(false);
const openModal = () => {
    showModal.value = true;
};

const closeModal = () => {
    showModal.value = false;
};
const keyword = ref(usePage().props.search || "");

function isSearch() {
    router.get(route("search", encodeURIComponent(keyword.value)));
}
</script>
<template>
    <button
        aria-label="true"
        type="button"
        @click="openModal"
        class="inline-flex flex-col items-center justify-center px-5"
    >
        <MagnifyingGlassIcon
            class="w-6 h-6 mb-2 text-gray-800 dark:text-gray-100 hover:scale-110 transition-transform duration-300"
        />
    </button>

    <Modal :show="showModal" @close="closeModal">
        <div class="p-3 bg-white dark:bg-gray-800">
            <TextInput
                v-model="keyword"
                placeholder="Search"
                class="w-full"
                @keyup.enter="isSearch"
            />
        </div>
    </Modal>
</template>
