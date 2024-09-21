<script setup>
import { ChatBubbleOvalLeftIcon } from "@heroicons/vue/24/outline";
import { TrashIcon } from "@heroicons/vue/24/solid";
import { Link } from "@inertiajs/vue3";
defineProps({
    user: Object,
    forApprove: {
        type: Boolean,
        default: false,
    },
    showRoleDropdown: {
        type: Boolean,
        default: false,
    },
    disableRoleDropdown: {
        type: Boolean,
        default: false,
    },
});

defineEmits(["approve", "reject", "roleChange", "delete"]);
</script>

<template>
    <div
        class="cursor-pointer border-2 transition duration-300 border-transparent rounded-md bg-white hover:border-indigo-500 dark:bg-gray-800 dark:hover:border-indigo-600">
        <div class="flex items-center gap-2 py-2 px-2 text-gray-800 dark:text-gray-100">
            <Link :href="route('profile', user.username)" aria-label="Profile">
            <img :src="user.avatar_url || '/img/default_avatar.jpeg'" :alt="'zosmed' + user.username"
                class="w-[32px] h-[32px] object-cover rounded-full" />
            </Link>
            <div class="flex justify-between flex-1">
                <Link :href="route('profile', user.username)" aria-label="Profile">
                <h3 class="font-black text-lg">{{ user.name }}</h3>
                </Link>
                <Link :href="route('chat', user)" aria-label="Chat" class="mt-1">
                <ChatBubbleOvalLeftIcon class="w-6 h-6" />
                </Link>
                <div class="flex gap-1" v-if="forApprove">
                    <button class="text-xs py-1 px-2 rounded bg-emerald-500 hover:bg-emerald-600"
                        @click.prevent.stop="$emit('approve', user)" aria-label="Approve user">
                        Approve
                    </button>
                    <button class="text-xs py-1 px-2 rounded bg-red-500 hover:bg-red-600"
                        @click.prevent.stop="$emit('reject', user)" aria-label="Reject user">
                        Reject
                    </button>
                </div>
                <div v-if="showRoleDropdown">
                    <select @change="$emit('roleChange', user, $event.target.value)"
                        class="rounded-md border-0 bg-transparent bg-none py-1 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm"
                        :disabled="disableRoleDropdown">
                        <option class="text-gray-800" :selected="user.role === 'admin'">admin</option>
                        <option class="text-gray-800" :selected="user.role === 'user'">user</option>
                    </select>
                    <button
                        class="text-xs p-1 rounded bg-red-400 hover:bg-red-500 ml-3 disabled:bg-gray-300 dark:disabled:text-gray-800"
                        :disabled="disableRoleDropdown" @click="$emit('delete', user)" aria-label="Delete user">
                        <TrashIcon class="w-3 h-3" />
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>
