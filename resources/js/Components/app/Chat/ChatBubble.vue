<script setup>

defineProps({
    friend: Object,
    currentUser: Object,
    class: String,
    message: Object
})

const formatedDate = (date) => {
    return new Intl.DateTimeFormat("id-ID", {
        hour: "numeric", minute: "numeric", hour12: true
    }).format(new Date(date));
}


const downloadFile = (file) => {
    router.post(route("post.download"), {
        file: file
    })
}
</script>
<template>
    <div class="flex flex-col gap-1">
        <div class="flex items-center space-x-2 rtl:space-x-reverse">
            <span v-if="message.sender_id !== currentUser.id"
                class="text-sm font-semibold text-gray-900 dark:text-white">
                {{ friend.name }}
            </span>
            <span class="text-xs flex w-full font-normal text-gray-500 dark:text-gray-400"
                :class="{ 'justify-end': message.sender_id === currentUser.id }">
                {{ formatedDate(message.created_at) }}
            </span>
        </div>
        <div class="flex flex-col w-full max-w-[320px] leading-1.5 p-2 border-gray-200 dark:bg-gray-700"
            :class="{ 'rounded-e-xl rounded-es-xl bg-gray-100': message.sender_id !== currentUser.id, 'rounded-s-xl rounded-se-xl bg-blue-200': message.sender_id === currentUser.id }">
            <template v-if="message.text">
                <p class="text-sm font-normal text-gray-900 dark:text-white">
                    {{ message.text }}
                </p>
            </template>
            <template v-else>
                <img :src="'/storage/' + message.file" :alt="message.file" class="w-full object-cover rounded-md">
                <a @click.stop :href="route('download.file', { file: message.id })"
                    class="text-blue-500 hover:underline font-semibold text-sm block mt-2">
                    Download
                </a>
            </template>
        </div>
    </div>
</template>
