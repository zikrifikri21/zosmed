<script setup>
import Modal from "@/Components/Modal.vue";
import { ref, watch } from "vue";
import MyStories from "./MyStories.vue";

const props = defineProps({
    story: {
        type: Array,
        default: null,
    },
    openModal: {
        type: Boolean,
        default: false,
    },
});

const emit = defineEmits(["close"]);

const showModal = ref(props.openModal);

watch(() => props.openModal, (newVal) => {
    showModal.value = newVal;
});

function onModalHide() {
    showModal.value = false;
    emit("close");
}
</script>

<template>
    <Modal :show="showModal" @close="onModalHide">
        <MyStories :stories="props.story" />
    </Modal>
</template>
