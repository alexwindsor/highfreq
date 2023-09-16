<script setup>
import { ref } from 'vue'
import { Link } from '@inertiajs/vue3'

const props = defineProps({
    links: Object,
    filters: String
})


// console.log(props.filters)


</script>

<template>

<div class="text-center w-full overflow-x-scroll sm:overflow-x-visible whitespace-nowrap sm:whitespace-normal">
    <div v-for="link in links" class="inline-block">
        <Link
            v-if="link.url !== null"
            :href="link.url.replace('filter', '') + props.filters"
            class="inline-block border border-black py-0.5 px-1 sm:py-1 m-1 sm:m-2 text-center"
            :class="{
                'bg-black text-white': link.active,
                'w-7': link.label <= 9,
                'w-8': link.label > 9 && link.label <= 99,
                'w-10': link.label > 99,
                'w-14': link.label === 'Next &raquo;'
            }"
            v-html="link.label"
        ></Link>
        <div v-if="link.url === null" class="border border-black py-0.5 px-1 sm:py-1 m-1 sm:m-2 text-center">...</div>
    </div>
</div>

</template>
