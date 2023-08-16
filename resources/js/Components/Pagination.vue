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

<div class="text-center">
    <div v-for="link in links" class="inline-block">
        <Link 
            v-if="link.url !== null"
            :href="link.url.replace('filter', '') + props.filters"
            class="inline-block border border-black p-2 m-2"
            :class="{
                'bg-black text-white': link.active,
                'w-10': Number(link.label) > 0
            }"
            v-html="link.label"
        ></Link>
        <div v-if="link.url === null" class="border border-black p-2 m-2 w-10">...</div>
    </div>
</div>

</template>