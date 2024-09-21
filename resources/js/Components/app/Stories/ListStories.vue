<script setup>
import ShowStory from "@/Components/app/Stories/ShowStory.vue";
import { ref, computed } from "vue";

const props = defineProps({
    followings: Array,
});

const showStory = ref(false);
const selectedStory = ref(null);

const followingsWithStories = computed(() => {
    return props.followings.filter(following => following.stories && following.stories.length > 0);
});

function openStoryModal(following) {
    selectedStory.value = following.stories; // setel stories yang dipilih
    showStory.value = true; // buka modal
}
</script>

<template>
    <div class="flex flex-col" v-for="(following, index) in followingsWithStories" :key="index">
        <div @click="openStoryModal(following)"
            class="border-2 border-gray-200 bg-white dark:bg-gray-800 text-gray-500 dark:border-gray-700 rounded-xl cursor-pointer">
            <div class="w-20">
                <img :src="following.avatar_url" class="w-full p-1 h-24 object-cover rounded-xl"
                    :alt="'zosmed' + following.name" />
            </div>
        </div>
    </div>
    <ShowStory :story="selectedStory" :openModal="showStory" @close="showStory = false" />
</template>
