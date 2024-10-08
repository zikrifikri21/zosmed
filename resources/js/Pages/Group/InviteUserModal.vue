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
import { useForm, usePage } from "@inertiajs/vue3";
import TextInput from "@/Components/TextInput.vue";
import InputLabel from "@/Components/InputLabel.vue";

const props = defineProps({
    modelValue: Boolean,
});

const page = usePage();

const formErrors = ref({});
const form = useForm({
    email: "",
});

const show = computed({
    get: () => props.modelValue,
    set: (value) => emit("update:modelValue", value),
});

const emit = defineEmits(["update:modelValue", "hide", "create"]);

function closeModal() {
    resetModal();
    show.value = false;
    emit("hide");
}

function resetModal() {
    form.reset();
    formErrors.value = {};
}

function submit() {
    form.post(route("group.inviteUsers", page.props.group.slug), {
        onSuccess: (res) => {
            console.log(res);
            closeModal();
        },
        onError: (res) => {
            console.log(res);
        },
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
                                class="w-full max-w-md transform overflow-hidden rounded-2xl bg-white dark:bg-gray-800 text-left align-middle shadow-xl transition-all">
                                <DialogTitle as="h3"
                                    class="flex items-center justify-between py-3 px-4 font-medium text-gray-800 dark:text-gray-200">
                                    Invite Users
                                    <button aria-label="true" @click="closeModal"
                                        class="w-8 h-8 rounded-full hover:bg-black/5 transition flex items-center justify-center">
                                        <XMarkIcon class="w-4 h-4" />
                                    </button>
                                </DialogTitle>
                                <div class="p-4">
                                    <div class="mb-3">
                                        <InputLabel for="name" value="Username or Email" />
                                        <TextInput v-model="form.email" placeholder="Username or Email" type="text"
                                            class="w-full block mt-1" required autofocus :class="page.props.errors.email
                                                ? 'border-red-500 focus:border-red-500 focus:ring-red-500'
                                                : ''
                                                " />
                                        <div class="text-red-500" v-if="page.props.errors.email">
                                            {{ page.props.errors.email }}
                                        </div>
                                    </div>
                                </div>

                                <div class="flex justify-end gap-2 py-3 px-4">
                                    <button aria-label="true" @click="closeModal"
                                        class="text-gray-800 flex gap-1 flex-center justify-center bg-gray-100 rounded-md hover:bg-gray-200 py-2 px-4">
                                        <XMarkIcon class="w-5 h-5 mr-2" />
                                        Cancle
                                    </button>
                                    <button aria-label="true" type="button"
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
