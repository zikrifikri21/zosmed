<script setup>
import { Menu, MenuButton, MenuItems, MenuItem } from "@headlessui/vue";
import {
    PencilIcon,
    TrashIcon,
    EllipsisVerticalIcon,
} from "@heroicons/vue/20/solid";
import { ClipboardIcon } from "@heroicons/vue/24/outline";
import { EyeIcon } from "@heroicons/vue/24/solid";
import { Link, usePage } from "@inertiajs/vue3";
import { computed } from "vue";
import Pinned from "./Icons/Pinned.vue";

const props = defineProps({
    post: {
        type: Object,
        default: null,
    },
    comment: {
        type: Object,
        default: null,
    },
});

const authUser = usePage().props.auth.user;
const group = usePage().props.group;

// Membuat properti terkomputasi `user`, yang bergantung pada props.comment dan props.post.
// Jika props.comment tidak ada, maka user akan menjadi props.post.user.
// Jika props.comment ada, maka user akan menjadi props.comment.user.
const user = computed(() => props.comment?.user || props.post?.user);

// Membuat properti terkomputasi `editAllowed`, yang bergantung pada nilai `user` dan `authUser`.
// Jika `user` memiliki id yang sama dengan `authUser.id`, maka editAllowed akan menjadi true.
const editAllowed = computed(() => user.value.id === authUser.id);
const pinAllowed = computed(() => {
    return (
        user.value.id === authUser.id ||
        (props.post.group && props.post.group.role === "admin")
    );
});

const isPinned = computed(() => {
    if (group?.id) {
        return group?.pinned_post_id === props.post.id;
    }

    return authUser?.pinned_post_id === props.post.id;
});

// Membuat properti terkomputasi `deleteAllowed` untuk menentukan apakah pengguna diizinkan untuk menghapus.
const deleteAllowed = computed(() => {
    // Jika pengguna yang sedang terautentikasi adalah pemilik komentar, maka diizinkan untuk menghapus.
    if (user.value.id === authUser.id) return true;

    // Jika pengguna yang sedang terautentikasi adalah pemilik postingan, maka diizinkan untuk menghapus.
    if (props.post.user.id === authUser.id) return true;

    // Jika tidak ada komentar dan pengguna adalah administrator dari grup postingan, maka diizinkan untuk menghapus.
    return !props.comment && props.post.group?.role === "admin";
});

defineEmits(["edit", "delete", "pin"]);

function copyToClipboard() {
    const textCopy = route("post.view", props.post.id);
    const tempInput = document.createElement("input");
    tempInput.value = textCopy;
    document.body.appendChild(tempInput);

    tempInput.select();
    document.execCommand("copy");

    document.body.removeChild(tempInput);
}
</script>
<template>
    <Menu as="div" class="relative inline-block text-left" v-if="authUser">
        <div>
            <MenuButton class="w-8 h-8 z-10 rounded-full hover:bg-black/5 transition flex items-center justify-center">
                <EllipsisVerticalIcon class="w-5 h-5" aria-hidden="true" />
            </MenuButton>
        </div>

        <transition enter-active-class="transition duration-100 ease-out"
            enter-from-class="transform scale-95 opacity-0" enter-to-class="transform scale-100 opacity-100"
            leave-active-class="transition duration-75 ease-in" leave-from-class="transform scale-100 opacity-100"
            leave-to-class="transform scale-95 opacity-0">
            <MenuItems
                class="absolute z-20 right-0 mt-2 w-32 origin-top-right divide-y divide-gray-100 rounded-md bg-white shadow-lg ring-1 ring-black/5 focus:outline-none">
                <div class="px-1 py-1">
                    <MenuItem v-slot="{ active }">
                    <Link :href="route('post.view', post.id)" :class="[
                        active
                            ? 'bg-indigo-500 text-white'
                            : 'text-gray-900',
                        'group flex w-full items-center rounded-md px-2 py-2 text-sm',
                    ]" aria-label="Open Post">
                    <EyeIcon class="mr-2 h-5 w-5" aria-hidden="true" />
                    Open Post
                    </Link>
                    </MenuItem>
                    <MenuItem v-slot="{ active }">
                    <button @click="copyToClipboard" :class="[
                        active
                            ? 'bg-indigo-500 text-white'
                            : 'text-gray-900',
                        'group flex w-full items-center rounded-md px-2 py-2 text-sm',
                    ]" aria-label="Copy Link">
                        <ClipboardIcon class="mr-2 h-5 w-5" aria-hidden="true" />
                        Copy Link
                    </button>
                    </MenuItem>
                    <MenuItem v-if="editAllowed" v-slot="{ active }">
                    <button @click="$emit('edit')" :class="[
                        active
                            ? 'bg-indigo-500 text-white'
                            : 'text-gray-900',
                        'group flex w-full items-center rounded-md px-2 py-2 text-sm',
                    ]" aria-label="Edit Post">
                        <PencilIcon class="mr-2 h-5 w-5" aria-hidden="true" />
                        Edit
                    </button>
                    </MenuItem>
                    <MenuItem v-if="pinAllowed" v-slot="{ active }">
                    <button @click="$emit('pin')" :class="[
                        active
                            ? 'bg-indigo-500 text-white'
                            : 'text-gray-900',
                        'group flex w-full items-center rounded-md px-2 py-2 text-sm',
                    ]" aria-label="Pin Post">
                        <Pinned />
                        {{ isPinned ? "Unpin" : "Pin" }}
                    </button>
                    </MenuItem>
                    <MenuItem v-if="deleteAllowed" v-slot="{ active }">
                    <button @click="$emit('delete')" :class="[
                        active
                            ? 'bg-indigo-500 text-white'
                            : 'text-gray-900',
                        'group flex w-full items-center rounded-md px-2 py-2 text-sm',
                    ]" aria-label="Delete Post">
                        <TrashIcon class="mr-2 h-5 w-5" aria-hidden="true" />
                        Delete
                    </button>
                    </MenuItem>
                </div>
            </MenuItems>
        </transition>
    </Menu>
</template>
