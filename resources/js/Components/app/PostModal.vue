<script setup>
import { computed, ref, watch } from "vue";
import {
    XMarkIcon,
    PaperClipIcon,
    BookmarkIcon,
    ArrowUturnLeftIcon,
    SparklesIcon,
} from "@heroicons/vue/24/solid";
import {
    TransitionRoot,
    TransitionChild,
    Dialog,
    DialogPanel,
    DialogTitle,
} from "@headlessui/vue";
import PostUserHeader from "./PostUserHeader.vue";
import { useForm, usePage } from "@inertiajs/vue3";
import { isImage } from "@/helpers";
import axiosClient from "@/axiosClient";
import UrlPreview from "./UrlPreview.vue";
import TextareaInput from "@/Components/TextareaInput.vue";
// import AiButton from "@/plugins/ai-button";

const props = defineProps({
    post: {
        type: Object,
        required: true,
    },
    group: {
        type: Object,
        default: null,
    },
    modelValue: Boolean,
});

const attachmentExtentions = usePage().props.attachmentExtentions;

const form = useForm({
    body: "",
    attachments: [],
    group_id: null,
    deleted_file_ids: [],
    preview: {},
    preview_url: null,
    _method: "POST",
});

const show = computed({
    get: () => props.modelValue,
    set: (value) => emit("update:modelValue", value),
});

const computedAttachments = computed(() => {
    return [...attachmentFiles.value, ...(props.post.attachments || [])];
});

const emit = defineEmits(["update:modelValue", "hide"]);

watch(
    () => props.post,
    () => {
        form.body = props.post.body || "";
        onInputChange();
    }
);

function closeModal() {
    show.value = false;
    emit("hide");
    resetModal();
}

const showExtentionText = computed(() => {
    for (let myFile of attachmentFiles.value) {
        let file = myFile.file;
        let parts = file.name.split(".");
        let ext = parts.pop().toLowerCase();
        if (!attachmentExtentions.includes(ext)) {
            return true;
        }
    }
});

function resetModal() {
    form.reset();
    attachmentFiles.value = [];
    formErrors.value = {};
    attachmentErrors.value = [];
    if (props.post.attachments) {
        props.post.attachments.forEach((file) => (file.deleted = false));
    }

    return false;
}

function submit() {
    if (props.group) {
        form.group_id = props.group.id;
    }
    form.attachments = attachmentFiles.value.map((myFile) => myFile.file);
    if (props.post.id) {
        form._method = "PUT";
        form.post(route("posts.update", props.post.id), {
            preserveScroll: true,
            onSuccess: () => {
                closeModal();
            },
            onError: (errors) => {
                proccessErrors(errors);
            },
        });
    } else {
        form.post(route("posts.create"), {
            preserveScroll: true,
            onSuccess: () => {
                closeModal();
            },
            onError: (errors) => {
                proccessErrors(errors);
            },
        });
    }
}

const attachmentFiles = ref([]);
const attachmentErrors = ref([]);
const formErrors = ref({});

function proccessErrors(errors) {
    formErrors.value = errors;
    for (const key in errors) {
        if (key.includes(".")) {
            const [, index] = key.split(".");
            attachmentErrors.value[index] = errors[key];
        }
    }
}

const messageMaxChoseFiles = ref("");
const maxAttachmentFiles = 4;

watch(
    () => attachmentFiles.value,
    () => {
        messageMaxChoseFiles.value =
            attachmentFiles.value.length > maxAttachmentFiles
                ? "Max " + maxAttachmentFiles + " files can be chosen"
                : "";
    }
);

async function onAttachmentChoose($event) {
    showExtentionText.value = false;
    const files = Array.from($event.target.files);
    const newFiles = files.slice(
        0,
        maxAttachmentFiles - attachmentFiles.value.length
    );

    if (newFiles.length === 0) {
        messageMaxChoseFiles.value =
            "Max " + maxAttachmentFiles + " files can be chosen";
        return;
    }

    await Promise.all(
        newFiles.map(async (file) => {
            const myFile = {
                file,
                url: await readFile(file),
            };
            attachmentFiles.value.push(myFile);
        })
    );

    $event.target.value = null;
}

async function readFile(file) {
    return new Promise((res, rej) => {
        if (isImage(file)) {
            const reader = new FileReader();
            reader.onload = () => {
                res(reader.result);
            };
            reader.onerror = rej;

            reader.readAsDataURL(file);
        } else {
            res(null);
        }
    });
}

function removeFile(myFile) {
    if (myFile.file) {
        attachmentFiles.value = attachmentFiles.value.filter(
            (f) => f !== myFile
        );
    } else {
        form.deleted_file_ids.push(myFile.id);
        myFile.deleted = true;
    }
}

function undoDeleted(myFile) {
    myFile.deleted = false;
    form.deleted_file_ids = form.deleted_file_ids.filter(
        (id) => id !== myFile.id
    );
}

function getAiContent() {
    if (!form.body) {
        return;
    }
    axiosClient
        .post(route("post.aiContent", props.post.id), {
            prompt: form.body,
        })
        .then(({ data }) => {
            form.body = data.content;
        })
        .catch((err) => {
            if (err.response.status === 500) {
                const text =
                    "Miskin developernya, da nda bisa beli apinya cet gipiti. kalo mau blikan mi dia🤣🤣🤣";
                form.body = "";
                let i = 0;
                const id = setInterval(() => {
                    form.body += text[i];
                    i++;
                    if (i >= text.length) {
                        clearInterval(id);
                    }
                }, 70);
            } else {
                form.body = "Something went wrong.";
            }
        });
}

function fetchPreview(url) {
    if (url === form.preview_url) {
        return;
    }

    form.preview_url = url;
    form.preview = {};
    if (url) {
        axiosClient
            .post(route("post.fetchUrlPreview"), { url })
            .then(({ data }) => {
                form.preview = {
                    title: data["og:title"],
                    description: data["og:description"],
                    image: data["og:image"],
                };
            })
            .catch((err) => {
                console.log(err);
            });
    }
}

function onInputChange() {
    let url = matchHref();

    if (!url) {
        url = matchLink();
    }
    fetchPreview(url);
}

function matchHref() {
    const urlRegex = /<a.+href="((https?):\/\/[^"]+)"/;

    const match = form.body.match(urlRegex);

    if (match && match.length > 0) {
        return match[1];
    }

    return null;
}
function matchLink() {
    const urlRegex = /(?:https?):\/\/[^\s<]+/;

    const match = form.body.match(urlRegex);

    if (match && match.length > 0) {
        return match[0];
    }

    return null;
}
</script>

<template>
    <teleport to="body">
        <TransitionRoot appear :show="show" as="template">
            <Dialog as="div" @close="closeModal" class="relative z-50">
                <TransitionChild
                    as="template"
                    enter="duration-300 ease-out"
                    enter-from="opacity-0"
                    enter-to="opacity-100"
                    leave="duration-200 ease-in"
                    leave-from="opacity-100"
                    leave-to="opacity-0"
                >
                    <div class="fixed inset-0 bg-black/25" />
                </TransitionChild>

                <div class="fixed inset-0 overflow-y-auto">
                    <div
                        class="flex min-h-full items-center justify-center p-4 text-center"
                    >
                        <TransitionChild
                            as="template"
                            enter="duration-300 ease-out"
                            enter-from="opacity-0 scale-95"
                            enter-to="opacity-100 scale-100"
                            leave="duration-200 ease-in"
                            leave-from="opacity-100 scale-100"
                            leave-to="opacity-0 scale-95"
                        >
                            <DialogPanel
                                class="w-full max-w-md transform overflow-hidden rounded-2xl bg-white dark:bg-gray-800 text-left align-middle shadow-xl transition-all"
                            >
                                <DialogTitle
                                    as="h3"
                                    class="flex items-center justify-between py-3 px-4 font-medium text-gray-900 dark:text-white"
                                >
                                    {{
                                        post.id ? "Update Post" : "Create Post"
                                    }}
                                    <button aria-label="true"
                                        @click="closeModal"
                                        class="w-8 h-8 rounded-full hover:bg-black/5 transition flex items-center justify-center"
                                    >
                                        <XMarkIcon class="w-4 h-4" />
                                    </button>
                                </DialogTitle>
                                <div
                                    class="p-4 text-gray-900 dark:text-gray-100"
                                >
                                    <PostUserHeader
                                        :post="post"
                                        is-modal="ada"
                                        :show-time="false"
                                        class="mb-4"
                                    />

                                    <div
                                        v-if="formErrors.group_id"
                                        class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-3"
                                    >
                                        {{ formErrors.group_id }}
                                    </div>

                                    <div class="relative group mb-3">
                                        <TextareaInput
                                            v-model="form.body"
                                            @input="onInputChange"
                                            :maxlength="2200"
                                            id="body"
                                            type="text"
                                            class="mt-1 block w-full"
                                        />
                                        <div
                                            class="absolute bottom-2 right-2 text-xs text-gray-400 group-hover:opacity-100 opacity-0 transition-all leading-none"
                                            v-text="
                                                `${form.body.length} / 2,200`
                                            "
                                        />
                                        <UrlPreview
                                            :preview="form.preview"
                                            :url="form.preview_url"
                                        />
                                        <button aria-label="true"
                                            @click="getAiContent"
                                            data-tooltip-target="tooltip-default"
                                            class="absolute top-2 right-2 w-8 h-8 rounded bg-indigo-500 hover:bg-indigo-600 text-white flex items-center justify-center opacity-0 group-hover:opacity-100 transition-all"
                                        >
                                            <SparklesIcon class="w-5 h-5" />
                                        </button>
                                    </div>
                                    <div v-if="messageMaxChoseFiles">
                                        <div
                                            class="bg-red-100 border-l-4 border-red-500 text-red-700 p-1 mb-3"
                                        >
                                            {{ messageMaxChoseFiles }}
                                        </div>
                                    </div>

                                    <div
                                        v-if="showExtentionText"
                                        class="border-l-4 border-lime-300 py-2 px-3 bg-lime-100 mt-3 text-gray-800"
                                    >
                                        File must be one following extention:
                                        <br />
                                        <small>
                                            {{
                                                attachmentExtentions.join(", ")
                                            }}
                                        </small>
                                    </div>

                                    <div
                                        v-if="formErrors.attachments"
                                        class="border-l-4 border-red-300 py-2 px-3 bg-red-100 mt-3 text-gray-800"
                                    >
                                        {{ formErrors.attachments }}
                                    </div>

                                    <div
                                        class="grid gap-2 my-3"
                                        :class="[
                                            computedAttachments.length === 1
                                                ? 'grid-cols-1'
                                                : 'grid-cols-2',
                                        ]"
                                    >
                                        <div
                                            v-for="(
                                                myFile, ind
                                            ) in computedAttachments"
                                        >
                                            <div
                                                class="group aspect-square bg-gray-900 flex flex-col items-center justify-center text-gray-500 relative rounded-2xl"
                                                :class="
                                                    attachmentErrors[ind]
                                                        ? 'border-red-500'
                                                        : ''
                                                "
                                            >
                                                <div
                                                    v-if="myFile.deleted"
                                                    class="absolute left-0 bottom-0 right-0 py-2 px-3 text-sm bg-black text-white flex items-center justify-between z-10"
                                                >
                                                    To be deleted
                                                    <ArrowUturnLeftIcon
                                                        @click="
                                                            undoDeleted(myFile)
                                                        "
                                                        class="w-4 h-4 cursor-pointer"
                                                    />
                                                </div>

                                                <button aria-label="true"
                                                    @click="removeFile(myFile)"
                                                    class="z-20 absolute top-3 right-3 w-7 h-7 flex items-center justify-center bg-black/30 text-white rounded-full hover:bg-black/40"
                                                >
                                                    <XMarkIcon
                                                        class="h-5 w-5"
                                                    />
                                                </button>
                                                <img
                                                    v-if="
                                                        isImage(
                                                            myFile.file ||
                                                                myFile
                                                        )
                                                    "
                                                    :src="myFile.url"
                                                    class="object-cover aspect-square rounded-2xl"
                                                    :class="
                                                        myFile.deleted
                                                            ? 'opacity-50'
                                                            : ''
                                                    "
                                                />
                                                <div
                                                    v-else
                                                    class="flex felx-col justify-center items-center"
                                                    :class="
                                                        myFile.deleted
                                                            ? 'opacity-50'
                                                            : ''
                                                    "
                                                >
                                                    <PaperClipIcon
                                                        class="w-10 h-10 mb-3"
                                                    />
                                                    <small class="text-center">
                                                        {{
                                                            (
                                                                myFile.file ||
                                                                myFile
                                                            ).name
                                                        }}
                                                    </small>
                                                </div>
                                            </div>
                                            <small
                                                class="text-red-400 text-center"
                                            >
                                                {{ attachmentErrors[ind] }}
                                            </small>
                                        </div>
                                    </div>
                                </div>

                                <div class="flex gap-2 py-5 px-4">
                                    <button aria-label="true"
                                        type="button"
                                        class="flex items-center justify-center rounded-md bg-indigo-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600 w-full relative"
                                    >
                                        <PaperClipIcon class="w-4 h-4 mr-2" />
                                        Attach Files
                                        <input
                                            @click.stop
                                            @change="onAttachmentChoose"
                                            type="file"
                                            multiple
                                            class="absolute left-0 bottom-0 top-0 right-0 opacity-0"
                                        />
                                    </button>
                                    <button aria-label="true"
                                        type="button"
                                        class="flex items-center justify-center rounded-md bg-indigo-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600 w-full"
                                        :class="{
                                            'opacity-50': form.processing,
                                            'cursor-not-allowed':
                                                form.processing,
                                        }"
                                        :disabled="form.processing"
                                        @click="submit"
                                    >
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
