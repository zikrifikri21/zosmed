<script setup>
import { PaperAirplaneIcon, PhotoIcon } from "@heroicons/vue/24/solid";
import axios from "axios";
import { nextTick, onMounted, ref, watch } from "vue";
import ChatPreSending from "./ChatPreSending.vue";
import ChatBubble from "./ChatBubble.vue";
import SendFile from "./SendFile.vue";
import Modal from "@/Components/Modal.vue";
import { XMarkIcon } from "@heroicons/vue/24/outline";

const props = defineProps({
    friend: {
        type: Object,
        required: true,
    },
    currentUser: {
        type: Object,
        required: true,
    },
});

const messages = ref([]);
const newMessage = ref("");
const messagesContainer = ref(null);
const isFriendTyping = ref(false);
const isFriendTypingTimer = ref(null);

// New variables for file uploads
const files = ref([]);
const maxFiles = 4;
const filePreviews = ref([]);

// Watch the messages for auto-scroll
watch(
    messages,
    () => {
        nextTick(() => {
            messagesContainer.value.scrollTo({
                top: messagesContainer.value.scrollHeight,
                behavior: "smooth",
            });
        });
    },
    { deep: true }
);

// Handle file selection
const handleFiles = (event) => {
    const selectedFiles = Array.from(event.target.files);
    const validFiles = selectedFiles.slice(0, maxFiles - files.value.length);

    validFiles.forEach((file) => {
        files.value.push(file);

        if (file.type.startsWith("image/")) {
            const reader = new FileReader();
            reader.onload = (e) => {
                filePreviews.value.push(e.target.result);
            };
            reader.readAsDataURL(file);
        }
    });
};

// Handle file removal
const removeFile = (index) => {
    files.value.splice(index, 1);
    filePreviews.value.splice(index, 1);
};

// Send message
const sendMessage = () => {
    if (newMessage.value.trim() !== "") {
        axios
            .post(`/messages/${props.friend.id}`, {
                message: newMessage.value,
            })
            .then((response) => {
                messages.value.push(response.data);
                newMessage.value = "";
                files.value = [];
                filePreviews.value = [];
            });
    }
};

// Send file
const sendFile = () => {
    const formData = new FormData();
    files.value.forEach((file) => {
        formData.append("files[]", file);
    });

    axios.post(`/messages/${props.friend.id}/files`, formData, {
        headers: { "Content-Type": "multipart/form-data" }
    }).then((response) => {
        messages.value.push(response.data);
        files.value = [];
        filePreviews.value = [];
        isModalOpen.value = false;
    });
};

// Handle typing event
const sendTypingEvent = () => {
    Echo.private(`chat.${props.friend.id}`).whisper("typing", {
        userID: props.currentUser.id,
    });
};

// On mount, fetch initial messages
onMounted(() => {
    axios.get(`/messages/${props.friend.id}`).then((response) => {
        messages.value = response.data;
    });

    Echo.private(`chat.${props.currentUser.id}`)
        .listen("MessageSent", (response) => {
            messages.value.push(response.message);
        })
        .listenForWhisper("typing", (response) => {
            isFriendTyping.value = response.userID === props.friend.id;

            if (isFriendTypingTimer.value) {
                messagesContainer.value.scrollTo({
                    top: messagesContainer.value.scrollHeight,
                    behavior: "smooth",
                });
                clearTimeout(isFriendTypingTimer.value);
            }

            isFriendTypingTimer.value = setTimeout(() => {
                isFriendTyping.value = false;
            }, 1000);
        });
});

const isModalOpen = ref(false);
const open = () => {
    isModalOpen.value = true;
};
</script>

<template>
    <div class="container-chat">
        <div class="flex flex-col justify-end h-full">
            <div class="p-4 overflow-y-auto max-h-auto" ref="messagesContainer">
                <div v-for="message in messages" :key="message.id" class="flex items-center mb-2">
                    <div v-if="message.sender_id === currentUser.id" class="ml-auto">
                        <ChatBubble :friend="friend" :message="message" :class="message.sender_id"
                            :currentUser="currentUser" />
                    </div>
                    <div v-else class="flex items-start gap-2.5">
                        <img class="w-8 h-8 rounded-full"
                            :src="'/storage/' + friend.avatar_path || '/img/default_avatar.jpeg'"
                            :alt="'zosmed' + friend.username">
                        <ChatBubble :friend="friend" :message="message" :class="message.sender_id"
                            :currentUser="currentUser" />
                    </div>
                </div>
                <ChatPreSending v-if="isFriendTyping" :friend="friend">
                    <img :src="'/storage/' + friend.avatar_path || '/img/default_avatar.jpeg'"
                        class="w-8 h-8 rounded-full" :alt="'zosmed' + friend.username">
                </ChatPreSending>
            </div>
        </div>

        <div id="bottom-banner" tabindex="-1" class="container-input-message">
            <div class="send-message">
                <div class="flex gap-2 items-center bg-white rounded-lg dark:bg-gray-800 w-full">
                    <button type="button" @click="open" class="send-file">
                        <PhotoIcon class="w-5 h-5" />
                    </button>
                    <Modal :show="isModalOpen" @close="isModalOpen = false">
                        <div class="p-6">
                            <div>
                                <input type="file" multiple accept="image/*" @change="handleFiles"
                                    class="block w-full text-sm text-slate-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-violet-100">
                                <p v-if="files.length >= maxFiles" class="text-red-500">
                                    Maximum of 4 files allowed.
                                </p>
                                <div v-if="filePreviews.length" class="flex gap-2 mt-2">
                                    <img v-for="(preview, index) in filePreviews" :key="index" :src="preview"
                                        class="w-24 h-24 object-cover rounded" />
                                    <button type="button" class="text-red-500 hover:text-red-600"
                                        @click="removeFile(index)">
                                        <XMarkIcon class="w-5 h-5" />
                                    </button>
                                </div>
                            </div>
                            <div class="flex justify-end">
                                <button @click="sendFile"
                                    class="bg-blue-500 flex items-center gap-2 hover:bg-blue-600 text-white font-semibold py-2 px-4 rounded">
                                    Send File
                                    <PaperAirplaneIcon class="w-5 h-5" />
                                </button>
                            </div>
                        </div>
                    </Modal>
                    <textarea id="chat" rows="1" required v-model="newMessage" @keydown="sendTypingEvent"
                        class="input-message" placeholder="Your message..."></textarea>
                    <button type="submit" class="button-message" @click="sendMessage">
                        <PaperAirplaneIcon class="w-5 h-5" />
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>
<style scoped>
@import './chat-message.css';
</style>
