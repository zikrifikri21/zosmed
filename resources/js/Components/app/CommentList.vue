<script setup>
import {
    ChatBubbleLeftEllipsisIcon,
    HandThumbUpIcon,
    PaperAirplaneIcon,
    XMarkIcon,
    CheckIcon,
    HeartIcon,
} from "@heroicons/vue/24/outline";
import { HeartIcon as HeartIconSolid } from "@heroicons/vue/24/solid";
import { Disclosure, DisclosureButton, DisclosurePanel } from "@headlessui/vue";
import InputTextarea from "@/Components/InputTextarea.vue";
import IndigoButton from "../IndigoButton.vue";
import EditDeleteDropdown from "./EditDeleteDropdown.vue";
import DangerButton from "../DangerButton.vue";
import axiosClient from "@/axiosClient";
import { router, useForm, usePage } from "@inertiajs/vue3";
import { computed, ref, watch } from "vue";
import { Link } from "@inertiajs/vue3";
import ReadMoreReadLess from "./ReadMoreReadLess.vue";
import { timeAgo } from "@/helpers";

const authUser = usePage().props.auth.user;
const editingComment = ref(null);

const newCommentText = ref("");

const props = defineProps({
    post: Object,
    data: Object,
    parentComment: {
        type: [Object, null],
        default: null,
    },
});

const emit = defineEmits(["commentCreate", "commentDelete"]);

function createComment() {
    axiosClient
        .post(route("post.comment.create", props.post), {
            comment: newCommentText.value,
            parent_id: props.parentComment?.id || null,
        })
        .then(({ data }) => {
            newCommentText.value = "";
            props.data.comments.unshift(data);
            if (props.parentComment) {
                props.parentComment.num_of_comments++;
            }
            props.post.num_of_comments++;
            emit("commentCreate", data);
        });
}

function deleteComment(comment) {
    if (!window.confirm("Are you sure you want to delete this comment?")) {
        return false;
    }
    axiosClient.delete(route("comment.delete", comment.id)).then(({ data }) => {
        const commentIndex = props.data.comments.findIndex(
            (c) => c.id === comment.id
        );
        props.data.comments.splice(commentIndex, 1);
        if (props.parentComment) {
            props.parentComment.num_of_comments--;
        }
        props.post.num_of_comments--;
        emit("commentDelete", comment);
    });
}

function startEditingComment(comment) {
    editingComment.value = {
        id: comment.id,
        comment: comment.comment.replace(/<br\s*\/?>/gi, "\n"),
    };
}

function updateComment() {
    axiosClient
        .put(route("comment.update", editingComment.value.id), {
            comment: editingComment.value.comment,
        })
        .then(({ data }) => {
            editingComment.value = null;
            props.data.comments = props.data.comments.map((c) => {
                if (c.id === data.id) {
                    return data;
                }
                return c;
            });
        });
}

const form = useForm({ reaction: "like" });
function sendCommentReaction(comment) {
    if (comment.curent_user_has_reactions) {
        comment.num_of_reactions -= 1;
    } else {
        comment.num_of_reactions += 1;
    }
    comment.curent_user_has_reactions = !comment.curent_user_has_reactions;
    axiosClient
        .post(route("comment.reaction", comment.id), {
            reaction: "like",
        })
        .then(({ data }) => {
            comment.curent_user_has_reactions = data.curent_user_has_reactions;
            comment.num_of_reactions = data.num_of_reactions;
        });
}

function onCommentCreate(comment) {
    if (props.parentComment) {
        props.parentComment.num_of_comments++;
    }
    emit("commentCreate", comment);
}
function onCommentDelete(comment) {
    if (props.parentComment) {
        props.parentComment.num_of_comments--;
    }
    emit("commentDelete", comment);
}

// const hasNestedComments = (comment) => {
//     if (comment.comments && comment.comments.length > 0) {
//         return comment.comments.some(
//             (subComment) =>
//                 subComment.comments && subComment.comments.length > 0
//         );
//     }
//     return false;
// };
</script>
<template>
    <div class="flex gap-2 mb-3">
        <a href="javascript:void(0)">
            <img
                :src="authUser.avatar_url"
                alt=""
                class="w-[40px] h-[40px] object-cover rounded-full border-2 transition-all hover:border-blue-400"
            />
        </a>
        <div class="relative flex flex-1 px-1 pt-1">
            <InputTextarea
                v-model="newCommentText"
                placeholder="add a comment..."
                rows="1"
                class="w-full resize-none block overflow-hidden"
            />
            <button
                class="text-white absolute end-2.5 bottom-1.5 bg-blue-700 hover:bg-blue-800 font-medium rounded-md text-sm px-2 py-[5px]"
                @click="createComment"
            >
                <PaperAirplaneIcon class="w-5 h-5" />
            </button>
        </div>
    </div>
    <div>
        <div>
            <div
                v-for="comment in data.comments"
                :key="comment.id"
                class="mb-4"
            >
                <div class="flex justify-between gap-2">
                    <div class="flex gap-2">
                        <Link :href="route('profile', comment.user.username)">
                            <img
                                :src="comment.user.avatar_url"
                                alt=""
                                class="w-[40px] h-[40px] object-cover rounded-full border-2 transition-all hover:border-blue-400"
                            />
                        </Link>
                        <div class="flex flex-row items-center gap-2">
                            <h4 class="font-bold">
                                <Link
                                    :href="
                                        route('profile', comment.user.username)
                                    "
                                    class="hover:underline"
                                >
                                    {{ comment.user.name }}
                                </Link>
                            </h4>
                            <small class="text-gray-400 text-[11px]">
                                {{ timeAgo(comment.updated_at) }}
                            </small>
                        </div>
                    </div>
                    <EditDeleteDropdown
                        :user="comment.user"
                        :post="post"
                        :comment="comment"
                        @edit="startEditingComment(comment)"
                        @delete="deleteComment(comment)"
                    />
                </div>
                <div class="pl-12">
                    <div
                        v-if="
                            editingComment && editingComment.id === comment.id
                        "
                    >
                        <InputTextarea
                            v-model="editingComment.comment"
                            placeholder="add a comment..."
                            rows="1"
                            class="w-full max-h-[160px] resize-none"
                        />
                        <div class="flex gap-2 justify-end">
                            <DangerButton
                                class="h-5"
                                @click="editingComment = null"
                            >
                                <XMarkIcon class="w-4 h-4" />
                            </DangerButton>
                            <IndigoButton class="h-5" @click="updateComment">
                                <CheckIcon class="w-4 h-4" />
                            </IndigoButton>
                        </div>
                    </div>
                    <ReadMoreReadLess
                        v-else
                        :content="comment.comment"
                        :uploader="comment.user.name"
                        content-class="text-sm flex flex-1"
                    />
                    <Disclosure>
                        <div class="flex gap-2 mt-1">
                            <button
                                @click="sendCommentReaction(comment)"
                                class="flex items-center text-xs text-indigo-500 py-0.5 px-1 rounded-lg"
                                :class="[
                                    comment.curent_user_has_reactions
                                        ? 'bg-indigo-50 hover:bg-indigo-50 dark:bg-gray-800 dark:hover:bg-gray-800'
                                        : ' hover:bg-indigo-50 dark:hover:bg-gray-800',
                                ]"
                            >
                                <template
                                    v-if="comment.curent_user_has_reactions"
                                >
                                    <HeartIconSolid class="w-3 h-3 mr-1" />
                                </template>
                                <template v-else>
                                    <HeartIcon class="w-3 h-3 mr-1" />
                                </template>
                                <span class="mr-2">
                                    {{ comment.num_of_reactions ?? 0 }}
                                </span>
                            </button>
                            <DisclosureButton
                                class="flex items-center text-xs text-indigo-500 py-0.5 px-1 hover:bg-indigo-100 dark:hover:bg-gray-800 rounded-lg"
                            >
                                <ChatBubbleLeftEllipsisIcon
                                    class="w-3 h-3 mr-1"
                                />
                                <span class="mr-2">
                                    {{ comment.num_of_comments }}
                                </span>
                            </DisclosureButton>
                        </div>
                        <DisclosurePanel class="mt-3">
                            <CommentList
                                :post="post"
                                :data="{ comments: comment.comments }"
                                :parent-comment="comment"
                                @comment-create="onCommentCreate"
                                @comment-delete="onCommentDelete"
                            />
                        </DisclosurePanel>
                    </Disclosure>
                </div>
            </div>
        </div>
    </div>
</template>
