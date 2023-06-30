<script setup>
import { Head, Link } from '@inertiajs/vue3'
import { reactive, onMounted } from 'vue'
import Layout from '@/Pages/Layout.vue'
import { base_url } from '@/base_url.js'

defineProps({
    user: Object,
    broadcast_bands: Array,
    amateur_bands: Array,
    broadcast_colour: String,
    amateur_colour: String,
})

const band = reactive({
    frequency: null,
    bands: null
})

const bandscale = reactive({
    html: '',
    zoom: 5
})


function checkFrequency() {

    // if (band.frequency < 100 || band.frequency > 30000) return false

    axios.post(base_url + 'bands/getBand/' + band.frequency).then((result) => {
        band.bands = result.data
    })
}


function changeBandZoom() {

    // console.log(base_url + 'bands/changeBandZoom')
    // console.log(bandscale.zoom)

    axios.post(base_url + 'bands/changeBandZoom', {zoom: bandscale.zoom}).then(band_html => {
        bandscale.html = band_html.data
    })
    // console.log(bandscale.html)
}


onMounted(() => {
    changeBandZoom()
})


</script>

<template>

<Head title="HF / bands" />
<Layout page="Shortwave Bands" :user="user">

On this page I am experimenting with different ways to visualise the various bands in the HF range (0 - 30000kHz). It is 

<br><br>
Zoom : {{ (30000 / (bandscale.zoom * 1000)).toFixed(2) }}px per kHz ({{ bandscale.zoom * 1000 }}px wide)
<input type="range" min="1" max="30" v-model="bandscale.zoom" @change="changeBandZoom" class="block w-full sm:w-2/3 lg:w-1/2 xl:w-1/3">

<!-- bands display -->
<div class="grid grid-cols-1 grid-rows-3 gap-0 w-full h-[240px] my-5 overflow-x-scroll whitespace-nowrap bg-gray-300 border border-black rounded-sm" v-html="bandscale.html"></div>
<!-- ------------- -->

<div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 mt-12">
    <div class="col-span-2 order-1 md:order-3 md:col-span-2 lg:col-span-2 lg:pl-20 my-auto">
        <br><br>
        Check a frequency :<br>
        <input type="number" v-model="band.frequency" min="100" max="30000" maxlength="5" class="border border-black rounded p-1" @keyup="checkFrequency">
    </div>

    <div class="col-span-2 order-2 md:order-4 md:col-span-2 lg:col-span-2 lg:pl-20">
        <div v-if="band.bands">
        {{ band.bands.frequency }}
        <br>
        {{ band.bands.wave }}
        <br>
        <span v-if="band.bands.metre">{{ band.bands.metre[0] }} on the {{ band.bands.metre[1] }} band</span>
    </div>
    </div>


    <div class="order-3 md:order-1 md:row-span-2 m-1 p-1 border-t border-black">
        Broadcast Bands:
        <div v-for="broadcast_band in broadcast_bands" class="m-1 p-1 text-sm" :style="'background-color:' + broadcast_colour + ';'">
            {{ broadcast_band[0] }} - {{ broadcast_band[1] }}<br>
            {{ broadcast_band[2] }}
        </div>
    </div>

    <div class="order-4 md:order-2 md:row-span-2 m-1 p-1 border-t border-black">
        Amateur Bands:
        <div v-for="amateur_band in amateur_bands" class="m-1 p-1 text-sm" :style="'background-color:' + amateur_colour + ';'">
            {{ amateur_band[0] }} - {{ amateur_band[1] }}<br>
            {{ amateur_band[2] }}
        </div>
    </div>
</div>





</Layout>
</template>

