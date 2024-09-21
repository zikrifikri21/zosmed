<script setup>
defineProps({
    friend: Object,
})
</script>
<template>
    <div class="flex flex-col gap-1">
        <div class="flex items-center space-x-2 rtl:space-x-reverse">
            <slot />
            <span class="text-sm font-semibold text-gray-900 dark:text-white">
                {{ friend.name }}
            </span>
            <span class="text-xs flex justify-start w-full font-normal text-gray-500 dark:text-gray-400">
                mengetik...
            </span>
        </div>
        <div
            class="flex ms-11 max-w-[100px] rounded-e-xl rounded-es-xl bg-gray-100 leading-1.5 p-2 border-gray-200 dark:bg-gray-700">
            <div class="flex items-center space-x-2">
                <div class="flex gap-1">
                    <span
                        class="size-1.5 rounded-full bg-neutral-600 motion-safe:animate-[bounce_1s_ease-in-out_infinite] dark:bg-neutral-300"></span>
                    <span
                        class="size-1.5 rounded-full bg-neutral-600 motion-safe:animate-[bounce_0.5s_ease-in-out_infinite] dark:bg-neutral-300"></span>
                    <span
                        class="size-1.5 rounded-full bg-neutral-600 motion-safe:animate-[bounce_1s_ease-in-out_infinite] dark:bg-neutral-300"></span>
                </div>
            </div>
        </div>
    </div>
</template>
