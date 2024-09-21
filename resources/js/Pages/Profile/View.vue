<script setup>
import { ref } from "vue";
import { TabGroup, TabList, Tab, TabPanels, TabPanel } from "@headlessui/vue";
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import TabItem from "@/Pages/Profile/Partials/TabItem.vue";
import Edit from "./Edit.vue";
import { usePage } from "@inertiajs/vue3";
import { computed } from "vue";
import {
    XMarkIcon,
    CheckCircleIcon,
    CameraIcon,
} from "@heroicons/vue/24/outline";
import { Head, useForm } from "@inertiajs/vue3";
import PrimaryButton from "@/Components/PrimaryButton.vue";
import DangerButton from "@/Components/DangerButton.vue";
import CreatePost from "@/Components/app/CreatePost.vue";
import PostList from "@/Components/app/PostList.vue";
import UserListItem from "@/Components/app/UserListItem.vue";
import TextInput from "@/Components/TextInput.vue";
import TabPhotos from "./TabPhotos.vue";

const imagesForm = useForm({
    cover: null,
    avatar: null,
});

const showNotification = ref(true);
const coverImageSrc = ref("");
const avatarImageSrc = ref("");
const searchFollowersKeyword = ref("");
const searchFollowingsKeyword = ref("");
const authUser = usePage().props.auth.user;
const isMyProfile = computed(() => authUser && authUser.id === props.user.id);

const props = defineProps({
    errors: Object,
    mustVerifyEmail: {
        type: Boolean,
    },
    status: {
        type: String,
    },
    success: {
        type: String,
    },
    isCurrentUserFollower: Boolean,
    followersCount: Number,
    user: {
        type: Object,
    },
    posts: Object,
    followers: Array,
    followings: Array,
    photos: Array,
});

function onCoverChange(e) {
    imagesForm.cover = e.target.files[0];
    if (imagesForm.cover) {
        const reader = new FileReader();
        reader.onload = () => {
            coverImageSrc.value = reader.result;
        };
        reader.readAsDataURL(imagesForm.cover);
    }
}
function onAvatarChange(e) {
    imagesForm.avatar = e.target.files[0];
    if (imagesForm.avatar) {
        const reader = new FileReader();
        reader.onload = () => {
            avatarImageSrc.value = reader.result;
        };
        reader.readAsDataURL(imagesForm.avatar);
    }
}

function cancleCoverImage() {
    imagesForm.cover = null;
    coverImageSrc.value = null;
}
function cancleAvatarImage() {
    imagesForm.avatar = null;
    avatarImageSrc.value = null;
}

function submitCoverImage() {
    imagesForm.post(route("profile.updateImages"), {
        preserveScroll: true,
        onSuccess: () => {
            showNotification.value = true;
            cancleCoverImage();
            setTimeout(() => {
                showNotification.value = false;
            }, 3000);
        },
    });
}
function submitAvatarImage() {
    imagesForm.post(route("profile.updateImages"), {
        preserveScroll: true,
        onSuccess: () => {
            showNotification.value = true;
            cancleAvatarImage();
            setTimeout(() => {
                showNotification.value = false;
            }, 3000);
        },
    });
}

function followUser() {
    const form = useForm({
        follow: !props.isCurrentUserFollower,
    });

    form.post(route("user.follow", props.user.id), {
        preserveScroll: true,
    });
}
</script>

<template>
    <Head title="Profile" />
    <AuthenticatedLayout>
        <div class="max-w-[768px] mx-auto h-full overflow-auto">
            <div
                v-show="showNotification && success"
                class="my-2 py-2 px-3 font-medium text-sm text-white bg-emerald-500"
            >
                {{ success }}
            </div>
            <div
                v-if="errors.cover"
                class="my-2 py-2 px-3 font-medium text-sm text-white bg-red-400"
            >
                {{ errors.cover }}
            </div>
            <div
                class="group relative bg-white dark:bg-gray-900 text-gray-800 dark:text-gray-100"
            >
                <img
                    :src="
                        coverImageSrc ||
                        user.cover_url ||
                        '/img/default_cover.jpeg'
                    "
                    class="w-full h-[200px] object-cover"
                />
                <div
                    class="absolute top-2 right-2"
                    v-if="authUser && user.id === authUser.id"
                >
                    <button aria-label="true"
                        v-if="!coverImageSrc"
                        class="bg-gray-50 hover:bg-gray-100 text-gray-800 py-1 px-2 text-xs rounded flex items-center opacity-0 group-hover:opacity-100 transition"
                    >
                        <CameraIcon class="w-3 h-3 mr-2" />

                        update cover
                        <input
                            type="file"
                            class="absolute left-0 top-0 right-0 bottom-0 opacity-0"
                            @change="onCoverChange"
                        />
                    </button>
                    <div
                        v-else
                        class="flex gap-2 bg-white p-2 opacity-0 group-hover:opacity-100"
                    >
                        <button aria-label="true"
                            @click="cancleCoverImage"
                            class="bg-gray-50 hover:bg-gray-100 text-gray-800 py-1 px-2 text-xs rounded flex items-center transition"
                        >
                            <XMarkIcon class="h-3 w-3 mr-2" />
                            Cancle
                        </button>
                        <button aria-label="true"
                            @click="submitCoverImage"
                            class="bg-gray-800 hover:bg-gray-900 text-gray-100 py-1 px-2 text-xs rounded flex items-center transition"
                        >
                            <CheckCircleIcon class="h-3 w-3 mr-2" />
                            Submit
                        </button>
                    </div>
                </div>
                <div class="flex">
                    <div
                        class="flex items-center justify-center rounded-full relative group/avatar -mt-[64px] ml-[48px] w-[128px] h-[128px]"
                    >
                        <img
                            :src="
                                avatarImageSrc ||
                                user.avatar_url ||
                                '/img/default_avatar.jpeg'
                            "
                            class="w-full h-full object-cover rounded-full"
                        />
                        <template v-if="authUser && user.id === authUser.id">
                            <button aria-label="true"
                                v-if="!avatarImageSrc"
                                class="absolute left-0 top-0 right-0 bottom-0 bg-black/50 text-gray-200 rounded-full flex items-center justify-center opacity-0 group-hover/avatar:opacity-100"
                            >
                                <CameraIcon class="w-8 h-8" />
                                <input
                                    type="file"
                                    class="absolute left-0 top-0 right-0 bottom-0 opacity-0"
                                    @change="onAvatarChange"
                                />
                            </button>
                            <div
                                v-else
                                class="absolute top-1 right-0 flex flex-col gap-2"
                            >
                                <button aria-label="true"
                                    @click="cancleAvatarImage"
                                    class="w-7 h-7 flex items-center justify-center bg-red-500/80 text-white rounded-full"
                                >
                                    <XMarkIcon class="h-5 w-5" />
                                </button>
                                <button aria-label="true"
                                    @click="submitAvatarImage"
                                    class="w-7 h-7 flex items-center justify-center bg-emerald-500/80 text-white rounded-full"
                                >
                                    <CheckCircleIcon class="h-5 w-5" />
                                </button>
                            </div>
                        </template>
                    </div>
                    <div
                        class="flex flex-wrap justify-between items-center flex-1 p-4"
                    >
                        <div class="mb-2">
                            <h2 class="font-bold text-lg">
                                {{ user.name }}
                            </h2>
                            <p class="text-xs text-gray-500">
                                {{ followersCount }} followers
                            </p>
                        </div>
                        <div v-if="authUser?.id !== user?.id">
                            <PrimaryButton
                                v-if="!isCurrentUserFollower"
                                @click="followUser"
                            >
                                Follow
                            </PrimaryButton>
                            <DangerButton
                                class="h-8 sm:h-5"
                                v-else
                                @click="followUser"
                            >
                                Unfollow
                            </DangerButton>
                        </div>
                    </div>
                </div>
            </div>
            <div class="border-t">
                <TabGroup>
                    <TabList class="flex bg-white dark:bg-gray-800">
                        <Tab v-slot="{ selected }" as="template">
                            <TabItem :selected="selected" text="Posts" />
                        </Tab>
                        <Tab v-slot="{ selected }" as="template">
                            <TabItem :selected="selected" text="Followers" />
                        </Tab>
                        <Tab v-slot="{ selected }" as="template">
                            <TabItem :selected="selected" text="Followings" />
                        </Tab>
                        <Tab v-slot="{ selected }" as="template">
                            <TabItem :selected="selected" text="Photos" />
                        </Tab>
                        <Tab
                            v-if="isMyProfile"
                            v-slot="{ selected }"
                            as="template"
                        >
                            <TabItem :selected="selected" text="My Profile" />
                        </Tab>
                    </TabList>

                    <TabPanels class="mt-2">
                        <TabPanel>
                            <template v-if="posts">
                                <CreatePost />
                                <PostList :posts="posts.data" class="flex-1" />
                            </template>
                            <div v-else class="py-8 text-center">
                                You don't have persmission to view these posts
                            </div>
                        </TabPanel>
                        <TabPanel class="p-3">
                            <div class="mb-3">
                                <TextInput
                                    :model-value="searchFollowersKeyword"
                                    placeholder="Cari apa saja"
                                    class="w-full"
                                />
                            </div>
                            <div
                                v-if="followers.length"
                                class="grid grid-cols-2 gap-2"
                            >
                                <UserListItem
                                    v-for="user of followers"
                                    :user="user"
                                    :key="user.id"
                                    class="shadow rounded-md"
                                />
                            </div>
                            <div v-else class="text-gray-400 text-center p-3">
                                Sok seleb ini akun
                            </div>
                        </TabPanel>
                        <TabPanel class="p-3">
                            <div class="mb-3">
                                <TextInput
                                    :model-value="searchFollowingsKeyword"
                                    placeholder="Cari apa saja"
                                    class="w-full"
                                />
                            </div>
                            <div
                                v-if="followings.length"
                                class="grid grid-cols-2 gap-2"
                            >
                                <UserListItem
                                    v-for="user of followings"
                                    :user="user"
                                    :key="user.id"
                                    class="shadow rounded-md"
                                />
                            </div>
                            <div v-else class="text-gray-400 text-center p-3">
                                Sok seleb ini akun
                            </div>
                        </TabPanel>
                        <TabPanel class="rounded-xl bg-white p-3 shadow">
                            <TabPhotos :photos="photos" />
                        </TabPanel>
                        <TabPanel v-if="isMyProfile">
                            <Edit
                                :must-verify-email="mustVerifyEmail"
                                :status="status"
                            />
                        </TabPanel>
                    </TabPanels>
                </TabGroup>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
