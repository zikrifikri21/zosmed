<script setup>
import { computed, ref } from "vue";
import { XMarkIcon, BookmarkIcon } from "@heroicons/vue/24/solid";
import {
    TransitionRoot,
    TransitionChild,
    Dialog,
    DialogPanel,
    DialogTitle,
} from "@headlessui/vue";
import axiosClient from "@/axiosClient";
import { useForm } from "@inertiajs/vue3";
import GroupForm from "./GroupForm.vue";

const props = defineProps({
    modelValue: Boolean,
});

const formErrors = ref({});
const form = useForm({
    name: "",
    auto_approval: true,
    about: "",
});

const show = computed({
    get: () => props.modelValue,
    set: (value) => emit("update:modelValue", value),
});

const emit = defineEmits(["update:modelValue", "hide", "create"]);

function closeModal() {
    show.value = false;
    emit("hide");
    resetModal();
}

function resetModal() {
    form.reset();
    formErrors.value = {};
}

function submit() {
    axiosClient.post(route("group.create"), form).then(({ data }) => {
        closeModal();
        emit("create", data);
    });
}
</script>

<template>
    <teleport to="body">
        <TransitionRoot appear :show="show" as="template">
            <Dialog as="div" @close="closeModal" class="relative z-50">
                <TransitionChild as="template" enter="duration-300 ease-out" enter-from="opacity-0"
                    enter-to="opacity-100" leave="duration-200 ease-in" leave-from="opacity-100" leave-to="opacity-0">
                    <div class="fixed inset-0 bg-black/25" />
                </TransitionChild>

                <div class="fixed inset-0 overflow-y-auto">
                    <div class="flex min-h-full items-center justify-center p-4 text-center">
                        <TransitionChild as="template" enter="duration-300 ease-out" enter-from="opacity-0 scale-95"
                            enter-to="opacity-100 scale-100" leave="duration-200 ease-in"
                            leave-from="opacity-100 scale-100" leave-to="opacity-0 scale-95">
                            <DialogPanel
                                class="w-full max-w-md transform overflow-hidden rounded-2xl bg-white text-left align-middle shadow-xl transition-all">
                                <DialogTitle as="h3"
                                    class="flex items-center justify-between py-3 px-4 font-medium text-gray-900">
                                    Create New Group
                                    <button @click="closeModal"
                                        class="w-8 h-8 rounded-full hover:bg-black/5 transition flex items-center justify-center"
                                        aria-label="close">
                                        <XMarkIcon class="w-4 h-4" />
                                    </button>
                                </DialogTitle>
                                <div class="p-4">
                                    <GroupForm :form="form" />
                                </div>

                                <div class="flex justify-end gap-2 py-3 px-4">
                                    <button
                                        class="text-gray-800 flex gap-1 flex-center justify-center bg-gray-100 rounded-md hover:bg-gray-200 py-2 px-4"
                                        aria-label="cancle">
                                        <XMarkIcon class="w-5 h-5 mr-2" />
                                        Cancle
                                    </button>
                                    <button type="button" aria-label="submit"
                                        class="flex items-center justify-center rounded-md bg-indigo-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600"
                                        @click="submit">
                                        <BookmarkIcon class="w-4 h-4 mr-2" />
                                        Submit
                                    </button>
                                </div>
                            </DialogPanel>
                        </TransitionChild>
                    </div>
                </div>
            </Dialog>
        </TransitionRoot>
    </teleport>
</template>
