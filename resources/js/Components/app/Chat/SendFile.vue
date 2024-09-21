<script setup>
import Modal from '@/Components/Modal.vue';
import { PhotoIcon } from '@heroicons/vue/24/solid';
import { ref } from 'vue';


const isModalOpen = ref(false);
const open = () => {
    isModalOpen.value = true;
}

</script>
<template>
    <button type="button" @click="open"
        class="inline-flex justify-center p-2 text-white0 rounded-lg cursor-pointer hover:text-gray-900 hover:bg-gray-100 dark:text-gray-400 dark:hover:text-white dark:hover:bg-gray-600">
        <PhotoIcon class="w-5 h-5" />
    </button>
    <Modal :show="isModalOpen" @close="isModalOpen = false">
        <div class="space-y-6 px-6 pb-4 sm:pb-6 lg:px-8 xl:pb-8">
            <h3 class="text-xl font-medium text-gray-900 dark:text-white p-2">
                Send File
            </h3>
            <div>
                <input type="file"
                    class="block w-full text-sm text-slate-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-violet-100">
            </div>
        </div>
    </Modal>
</template>
