<script setup>
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import UserListItem from "@/Components/app/UserListItem.vue";
import GroupItem from "@/Components/app/GroupItem.vue";
import PostList from "@/Components/app/PostList.vue";
import TextInput from "@/Components/TextInput.vue";
import { router, usePage } from "@inertiajs/vue3";
import { ref } from "vue";
const props = defineProps({
    search: String,
    users: Array,
    groups: Array,
    posts: Object,
});
const keyword = ref(usePage().props.search || "");

function isSearch() {
    router.get(route("search", encodeURIComponent(keyword.value)));
}
</script>
<template>
    <AuthenticatedLayout>
        <div class="p-3">
            <div
                class="sm:hidden mb-3 bg-white dark:bg-gray-800 shadow rounded p-5"
            >
                <TextInput
                    v-model="keyword"
                    placeholder="Search"
                    class="w-full"
                    @keyup.enter="isSearch"
                />
            </div>
            <div
                v-if="!search.startsWith('#')"
                class="grid grid-cols-1 sm:grid-cols-2 gap-3"
            >
                <div
                    class="mb-4 bg-white dark:bg-gray-800 text-gray-800 dark:text-gray-100 shadow rounded p-4"
                >
                    <h2 class="text-lg font-bold">Users</h2>
                    <div class="grid-cols-2">
                        <UserListItem
                            v-if="users.length"
                            v-for="user of users"
                            :key="user.id"
                            :user="user"
                        />
                        <div v-else class="py-8 text-center">
                            No Users Found
                        </div>
                    </div>
                </div>
                <div
                    class="mb-4 bg-white dark:bg-gray-800 text-gray-800 dark:text-gray-100 shadow rounded p-4"
                >
                    <h2 class="text-lg font-bold">Groups</h2>
                    <div class="grid-cols-2">
                        <GroupItem
                            v-if="groups.length"
                            v-for="group of groups"
                            :key="group.id"
                            :group="group"
                        />
                        <div v-else class="py-8 text-center">
                            No Groups Found
                        </div>
                    </div>
                </div>
            </div>
            <div class="grid-cols-1 sm:grid-cols-2">
                <div>
                    <h2
                        class="text-lg font-bold text-gray-800 dark:text-gray-100 mb-3"
                    >
                        Posts
                    </h2>
                    <PostList
                        v-if="posts.data"
                        :posts="posts.data"
                        class="flex-1"
                    />
                    <div v-else class="py-8 text-center">No Posts Found</div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
