<script setup>
import { timeAgo } from "@/helpers";
import { ChevronRightIcon } from "@heroicons/vue/24/solid";
import { Link } from "@inertiajs/vue3";
const props = defineProps({
    post: {
        type: Object,
    },
    showTime: {
        type: Boolean,
        default: true,
    },
    isModal: String,
});

let text1 = timeAgo(props.post.updated_at);
</script>
<template>
    <div class="flex gap-2" :class="isModal ? 'items-center' : ''">
        <Link :href="route('profile', post.user.username)" aria-label="Profile">
        <img :src="post.user.avatar_url"
            class="w-[40px] h-[40px] object-cover rounded-full border-2 transition-all hover:border-blue-400"
            :alt="'zosmed' + post.user.username" />
        </Link>
        <div>
            <h4 class="flex items-center gap-2 font-bold leading-none">
                <Link :href="route('profile', post.user.username)" aria-label="Profile">
                {{ post.user.name }}
                </Link>
                <template v-if="post.group">
                    <ChevronRightIcon class="w-4 h-4 text-gray-400" />
                    <Link :href="route('group.profile', post.group.slug)" class="hover:underline" aria-label="Group">
                    {{ post.group.name }}
                    </Link>
                </template>
            </h4>
            <small v-if="showTime" class="text-gray-400 text-xs">
                {{ post.updated_at }}
                <div class="text-xs inline text-swap">
                    <span>{{ text1 }}</span>
                </div>
            </small>
        </div>
    </div>
</template>
<style>
@keyframes slideUp {

    0%,
    25% {
        transform: translateY(100%);
        opacity: 0;
    }

    30%,
    45% {
        transform: translateY(0);
        opacity: 1;
    }

    50%,
    75% {
        transform: translateY(-100%);
        opacity: 0;
    }

    80%,
    100% {
        transform: translateY(100%);
        opacity: 0;
    }
}

.text-swap {
    display: inline-block;
    overflow: hidden;
    position: relative;
    height: 1.5em;
    width: 100%;
}

.text-swap span {
    position: absolute;
    width: 100%;
    height: 100%;
    top: 0;
    left: 0;
    text-align: start;
    animation: slideUp 8s infinite;
}

.text-swap span:nth-child(2) {
    animation-delay: 4s;
}
</style>
