<script setup>
import { Head, Link } from '@inertiajs/vue3'
import { ref, reactive, onMounted } from 'vue'
import Layout from '@/Pages/Layout.vue'
import { base_url } from '@/base_url.js'

defineProps({
    user: Object,
    broadcast_bands: Array,
    amateur_bands: Array,
    both_aeronautical_bands: Array,
    civil_aeronautical_bands: Array,
    military_aeronautical_bands: Array,
    broadcast_colour: String,
    amateur_colour: String,
    aeronautical_colour: String,
})

const band = reactive({
    frequency: null,
    bands: null
})

const aeronautical_bands = ref('both')

function checkFrequency() {

    if (band.frequency < 30 || band.frequency > 30000) {
        band.bands = null
        console.log('null!')
        return false
    }

    axios.post(base_url + 'bands/getBand/' + band.frequency).then((result) => {
        band.bands = result.data
        console.log(band.bands)
    })
}



</script>

<template>

<Head title="HF / bands" />
<Layout page="Shortwave Bands" :user="user">

<div class="overflow-x-scroll">
    <div class="grid grid-cols-4 mt-8 lg:mx-4 xl:mx-20 min-w-[800px]">
        <div class="m-1 p-1 border-t border-black min-w-[150px]">

            <div class="grid grid-cols-2 gap-1 mr-6 mt-6 text-xs sm:text-sm">
                <div class="bg-gray-200 p-1 rounded-sm">
                    Low Frequency [LF]:
                </div>

                <div class="bg-gray-200 p-1 rounded-sm">
                    30 - 300 kHz
                </div>

                <div class="bg-gray-200 p-1 rounded-sm">
                    Medium Frequency [MF]:
                </div>

                <div class="bg-gray-200 p-1 rounded-sm">
                    300 - 3000 kHz
                </div>

                <div class="bg-gray-200 p-1 rounded-sm mb-3">
                    High Frequency [HF]:
                </div>

                <div class="bg-gray-200 p-1 rounded-sm mb-3">
                    3000 - 30000 kHz
                </div>

                <div class="bg-gray-200 p-1 rounded-sm">
                    Long Wave (LW):
                </div>

                <div class="bg-gray-200 p-1 rounded-sm">
                    148.5 - 283.5 kHz
                </div>

                <div class="bg-gray-200 p-1 rounded-sm">
                    Medium Wave (MW):
                </div>

                <div class="bg-gray-200 p-1 rounded-sm">
                    531 - 1701 kHz
                </div>

                <div class="bg-gray-200 p-1 rounded-sm">
                    Shortwave (SW):
                </div>

                <div class="bg-gray-200 p-1 rounded-sm">
                    3000 - 30000 kHz
                </div>
            </div>

            <br><br>
            Check a frequency :<br>
            <input type="number"
                   v-model="band.frequency"
                   min="100"
                   max="30000"
                   maxlength="5"
                   class="block border border-black rounded p-1 mt-2 mb-8 w-[180px]"
                   @keyup="checkFrequency"
            >


            <span v-if="band.bands?.wave" class="mt-8 text-lg">
                [ {{ band.bands.wave }} ]
            </span>
            <span v-if="band.bands?.frequency" class="mt-8 text-lg">
                ( {{ band.bands.frequency }} )
            </span>

            <div v-if="band.bands?.metre" class="mt-2 text-lg">
                {{ band.bands?.metre[0] }} on the
                <span class="font-bold capitalize">{{ band.bands?.metre[1] }}</span> band
            </div>

            <div v-if="band.bands?.aeronautical" class="mt-2 text-lg">
                <span class="font-bold capitalize">{{ band.bands?.aeronautical }}</span> band
            </div>

        </div>

        <div class="m-1 p-1 border-t border-black min-w-[150px]">
            Broadcast Bands:
            <div v-for="broadcast_band in broadcast_bands">
                <div
                    class="m-1 p-1 text-sm h-[50px]"
                    :class="{'border-2 border-black' : band.frequency >= broadcast_band[0] && band.frequency < broadcast_band[1]}"
                    :style="'background-color:' + broadcast_colour + ';'"
                >
                    {{ broadcast_band[0] }} - {{ broadcast_band[1] }} kHz<br>
                    {{ broadcast_band[2] }}
                </div>
            </div>
        </div>

        <div class="m-1 p-1 border-t border-black min-w-[150px]">
            Amateur Bands:
            <div v-for="amateur_band in amateur_bands">
                <div
                    class="m-1 p-1 text-sm h-[50px]"
                    :class="{'border-2 border-black' : band.frequency >= amateur_band[0] && band.frequency < amateur_band[1]}"
                    :style="'background-color:' + amateur_colour + ';'"
                >
                    {{ amateur_band[0] }} - {{ amateur_band[1] }} kHz<br>
                    {{ amateur_band[2] }}
                </div>

            </div>
        </div>

        <div class="m-1 p-1 border-t border-black min-w-[150px]">
            Aeronautical Bands:

            <br>

            <button
                class="border border-black rounded mx-1 my-2 w-[50px] text-xs"
                :class="{
                    'bg-white text-black': aeronautical_bands !== 'civil',
                    'bg-black text-white': aeronautical_bands === 'civil'
                }"
                @click="aeronautical_bands = 'civil'"
            >
                Civil
            </button>
            <button
                class="border border-black rounded mx-1 my-2 w-[50px] text-xs"
                :class="{
                    'bg-white text-black': aeronautical_bands !== 'military',
                    'bg-black text-white': aeronautical_bands === 'military'
                }"
                @click="aeronautical_bands = 'military'"
            >
                Military
            </button>
            <button
                class="border border-black rounded mx-1 my-2 w-[50px] text-xs"
                :class="{
                    'bg-white text-black': aeronautical_bands !== 'both',
                    'bg-black text-white': aeronautical_bands === 'both'
                }"
                @click="aeronautical_bands = 'both'"
            >
                Both
            </button>

            <div
                v-if="aeronautical_bands === 'both'"
                v-for="aeronautical_band in both_aeronautical_bands"
            >
                <div
                    class="flex items-center m-1 p-1 text-sm h-[50px]"
                    :style="'background-color:' + aeronautical_colour + ';'"
                    :class="{'border-2 border-black' : band.frequency >= aeronautical_band[0] && band.frequency < aeronautical_band[1]}"
                >
                    {{ aeronautical_band[0] }} - {{ aeronautical_band[1] }} kHz
                </div>
            </div>

            <div
                v-if="aeronautical_bands === 'civil'"
                v-for="aeronautical_band in civil_aeronautical_bands"
            >
                <div
                    class="flex items-center m-1 p-1 text-sm h-[50px]"
                    :style="'background-color:' + aeronautical_colour + ';'"
                    :class="{'border-2 border-black' : band.frequency >= aeronautical_band[0] && band.frequency < aeronautical_band[1]}"
                >
                    {{ aeronautical_band[0] }} - {{ aeronautical_band[1] }} kHz
                </div>
            </div>

            <div
                v-if="aeronautical_bands === 'military'"
                v-for="aeronautical_band in military_aeronautical_bands"
            >
                <div
                    class="flex items-center m-1 p-1 text-sm h-[50px]"
                    :style="'background-color:' + aeronautical_colour + ';'"
                    :class="{'border-2 border-black' : band.frequency >= aeronautical_band[0] && band.frequency < aeronautical_band[1]}"
                >
                    {{ aeronautical_band[0] }} - {{ aeronautical_band[1] }} kHz
                </div>
            </div>
        </div>

    </div>
</div>




</Layout>
</template>

