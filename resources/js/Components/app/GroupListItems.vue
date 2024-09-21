<script setup>
import GroupItem from "@/Components/app/GroupItem.vue";
import TextInput from "@/Components/TextInput.vue";
import { ref } from "vue";

const searchKeyword = ref("");
import GroupModal from "./GroupModal.vue";

const showNewGroupModal = ref(false);

const props = defineProps({
    groups: Array
})

function search() {
    return props.groups.filter(group => {
        return group.name.toLowerCase().includes(searchKeyword.value.toLowerCase());
    });
}

function onGroupCreate(group) {
    props.groups.unshift(group);
}
</script>
<template>
    <div class="flex gap-2 mt-4 items-center mx-1">
        <div class="w-full">
            <TextInput v-model="searchKeyword" placeholder="Cari Grup" class="w-full" />
        </div>
        <button @click="showNewGroupModal = true" aria-label="add group"
            class="text-white text-sm bg-indigo-600 hover:bg-indigo-500 px-2 py-1 rounded w-min-[100px]">
            Add Group
        </button>
    </div>
    <div class="mt-3 h-[200px] lg:flex-1 overflow-auto">
        <div v-if="!search().length" class="text-gray-400 text-center p-3">
            ko blm join, join dlu kanda
        </div>
        <div v-else>
            <GroupItem v-for="group in search()" :group="group" :key="group.id" />
        </div>
    </div>
    <GroupModal v-model="showNewGroupModal" @create="onGroupCreate" />
</template>
