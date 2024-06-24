<script setup>
import GroupListItems from "@/Components/app/GroupListItems.vue";
import { Disclosure, DisclosureButton, DisclosurePanel } from "@headlessui/vue";
import { ChevronRightIcon } from "@heroicons/vue/24/solid";

defineProps({
    groups: Array,
});
</script>
<template>
    <div
        class="px-3 bg-white dark:bg-gray-800 dark:border-gray-700 dark:text-gray-300 rounded border h-full py-3 overflow-hidden flex flex-col"
    >
        <div class="block lg:hidden">
            <Disclosure v-slot="{ open }">
                <DisclosureButton class="w-full">
                    <div class="flex justify-between items-center">
                        <h2 class="text-xl font-bold">My Group</h2>
                        <ChevronRightIcon
                            class="w-6 h-6 transition-all"
                            :class="open ? 'rotate-90 transform' : ''"
                        />
                    </div>
                </DisclosureButton>
                <DisclosurePanel>
                    <GroupListItems :groups="groups" />
                </DisclosurePanel>
            </Disclosure>
        </div>
        <div class="h-full overflow-hidden flex-col hidden lg:flex">
            <div class="flex justify-between">
                <h2 class="text-xl font-bold">My Group</h2>
            </div>
            <GroupListItems :groups="groups" />
        </div>
    </div>
</template>
