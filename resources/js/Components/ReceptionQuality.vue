<script setup>
import { ref } from 'vue'

const props = defineProps({
    quality: [String, Number]
})


var quality = ref(props.quality)

const emit = defineEmits(['quality'])

function changeQuality() {
    emit('quality', quality)
}

</script>

<template>

    <span class="bg-gray-300">Reception quality:</span>
    <div class="text-sm mb-2">(1 = poor, 3 = excellent)</div>

    <label class="m-1 px-2 bg-gray-300 border border-black rounded inline-block">
        1
        <input type="radio" v-model="quality" value="1" class="m-2" @change="changeQuality">
    </label>

    <label class="m-1 px-2 bg-gray-300 border border-black rounded inline-block">
        2
        <input type="radio" v-model="quality" value="2" class="m-2" @change="changeQuality">
    </label>

    <label class="m-1 px-2 bg-gray-300 border border-black rounded inline-block">
        3
        <input type="radio" v-model="quality" value="3" class="m-2" @change="changeQuality">
    </label>

</template>
