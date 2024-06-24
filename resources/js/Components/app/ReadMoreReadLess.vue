<script setup>
import { Disclosure, DisclosureButton, DisclosurePanel } from "@headlessui/vue";
import { Link } from "@inertiajs/vue3";
defineProps({
    content: String,
    contentClass: String,
    uploader: String,
});
</script>
<template>
    <Disclosure v-slot="{ open }">
        <div v-if="content && !open" class="flex items-start -mt-2">
            <div
                class="ck-content-output text-sm"
                v-if="!open"
                v-html="
                    content.substring(0, 45) +
                    (content.length > 45 ? '...' : '')
                "
                :class="contentClass"
            />
        </div>
        <template v-if="content && content.length > 45">
            <DisclosurePanel>
                <div class="flex items-start gap-1 -mt-[10px]">
                    <div
                        class="ck-content-output text-sm pt-[2px]"
                        :class="contentClass"
                        v-html="content"
                    />
                </div>
            </DisclosurePanel>
            <div class="flex justify-start">
                <DisclosureButton class="text-gray-400 text-sm">
                    {{ open ? "less" : "more" }}
                </DisclosureButton>
            </div>
        </template>
    </Disclosure>
</template>
