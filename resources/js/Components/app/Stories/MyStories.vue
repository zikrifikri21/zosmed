<!-- <template>
    <pre>{{ stories }}</pre>
    <Stories :stories="stories.map(story => story.files)" />
</template>

<script>
import { Stories } from "vue-insta-stories";

export default {
    components: { Stories },
    props: {
        stories: {
            type: Array,
            required: true
        }
    }
};

// export default {
//     components: { Stories },
//     data: () => ({
//         items: [
//             "https://picsum.photos/350/200/",
//             "https://picsum.photos/400/201/",
//             {
//                 url: "http://larasosmed.zik/storage/attachments/61/wusYpxGWLazLv7Kwb1yjcQOUIBalLrmSCdoVhu60.mp4",
//                 type: "video"
//             },
//         ]
//     })
// };
</script> -->
<!-- <template>
    <Stories :stories="formattedStories" />
</template>

<script>
import { Stories } from "vue-insta-stories";

export default {
    components: { Stories },
    props: {
        stories: {
            type: Array,
            required: true
        }
    },
    computed: {
        formattedStories() {
            return this.stories.map(story => {
                if (!story.files) {
                    return story; // Kembalikan story apa adanya jika files tidak ada
                }

                const extension = story.files.split('.').pop().toLowerCase();
                const type = ['mp4', 'webm', 'ogg'].includes(extension) ? 'video' : 'image';

                return {
                    ...story,
                    url: story.files, // Ganti 'files' dengan 'url'
                    type,
                };
            });
        }
    }
};
</script> -->


<!-- MyStories.vue -->
<template>
    <Stories :stories="formattedStories" />
</template>

<script>
import { Stories } from "vue-insta-stories";

export default {
    components: { Stories },
    props: {
        stories: {
            type: Array,
            required: true
        }
    },
    computed: {
        formattedStories() {
            return this.stories.map(story => {
                if (!story.files) return story;

                const extension = story.files.split('.').pop().toLowerCase();
                const type = ['mp4', 'webm', 'ogg'].includes(extension) ? 'video' : 'image';

                return {
                    url: story.files,
                    type,
                };
            });
        }
    }
};
</script>
