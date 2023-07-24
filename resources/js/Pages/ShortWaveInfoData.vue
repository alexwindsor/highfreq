<script setup>
import { Head, Link, router } from '@inertiajs/vue3'
import { reactive, onMounted } from 'vue'
import Layout from '@/Pages/Layout.vue'
import { base_url } from '@/base_url.js'
import Pagination from '@/Components/Pagination.vue'

const props = defineProps({
    stations: Object,
    transmitters: Object,
    dateOfData: String,
    day: String,
    time: String,
    user: Object,
    page: Number,
    frequency: Number,
    broadcasting_now: Boolean,
    station_id: Number,
    swinfo_transmitter_id: Number,
    order_by: String
})


const filters = reactive({
    frequency: props.frequency === 0 ? null : props.frequency,
    broadcasting_now: props.broadcasting_now,
    station_id: props.station_id,
    swinfo_transmitter_id: props.swinfo_transmitter_id,
    order_by: props.order_by
})

const broad = reactive({
        casts: []
    })


function updateData() {

    axios.post(base_url + 'shortWaveInfoData?page=' + props.page, filters).then(swInfoBroadcasts => {
        broad.casts = swInfoBroadcasts.data
        // check that the current page is not greater than the total number of pages
        var last_page = Math.floor(broad.casts.total / broad.casts.per_page) + 1
        // if it is, then redirect to the last page
        if (last_page < broad.casts.current_page) {
            router.visit(base_url + 'shortWaveInfoData?page=' + last_page + '&frequency=' + filters.frequency + '&broadcasting_now=' + filters.broadcasting_now + '&station_id=' + filters.station_id + '&swinfo_transmitter_id=' + filters.swinfo_transmitter_id)
        }
    })
    .catch(error => {
        console.error(error)
    })
}


onMounted(() => {
    updateData()
})

</script>

<template>


<Head title="HF / short-wave.info" />
<Layout page="Short-Wave.info Listings" :user="user">

    <div class="sm:grid sm:grid-cols-12 sm:gap-1 md:gap-2 lg:gap-4 mb-10">

        <div class="sm:col-span-6 lg:col-span-2 p-2 sm:p-3 mb-1 sm:mb-0 rounded bg-gray-700 text-white text-center">
            <div class="inline-block text-left mx-auto w-full xl:w-2/3">
                Frequency :<br>
                <input type="number" max="30000" min="3000" class="block w-full border-2 border-black rounded p-1 text-black" v-model="filters.frequency" @keyup="updateData">
            </div>
        </div>

        <div class="sm:col-span-6 lg:col-span-3 p-2 sm:p-3 mb-1 sm:mb-0 rounded bg-gray-700 text-white text-center">
            <div class="inline-block text-left mx-auto w-full xl:w-2/3">
                Station:
                <select class="block border-2 border-black p-2 w-full rounded text-black" v-model="filters.station_id" @change="updateData">
                    <option value="0">All Stations</option>
                    <option v-for="station in stations" :value="station.id">{{ station.name }}</option>
                </select>
            </div>
        </div>

        <div class="sm:col-span-6 lg:col-span-3 p-2 sm:p-3 mb-1 sm:mb-0 rounded bg-gray-700 text-white text-center">
            <div class="inline-block text-left mx-auto w-full xl:w-2/3">
                Transmitter:
                <select class="block border-2 border-black p-2 rounded w-full text-black" v-model="filters.swinfo_transmitter_id" @change="updateData">
                    <option value="0">All Transmitters</option>
                    <option v-for="transmitter in transmitters" :value="transmitter.id">{{ transmitter.name }}</option>
                </select>
            </div>
        </div>

        <div class="sm:col-span-6 lg:col-span-2 p-2 sm:p-3 mb-1 sm:mb-0 rounded bg-gray-700 text-white text-center">
            <div class="inline-block text-left mx-auto w-full xl:w-2/3">
                Order by:
                <br>
                <select class="block border-2 border-black p-2 rounded w-full text-black" v-model="filters.order_by" @change="updateData">
                    <option value="frequency">Frequency</option>
                    <option value="station_id">Station</option>
                    <option value="start_time">Start Time</option>
                </select>
            </div>
        </div>

        <div class="sm:col-span-6 lg:col-span-2 p-2 sm:p-0 lg:p-2 mb-1 sm:mb-0 rounded bg-gray-700 text-white text-center">
            <div class="inline-block mx-auto w-full xl:w-2/3">
                <label class="inline-block mx-2 my-0 lg:my-4 p-1">Broadcasting now <input type="checkbox" v-model="filters.broadcasting_now" @change="updateData" class="mx-5"></label>
            </div>
        </div>
    </div>

    <div v-if="broad.casts.total === 0" class="mb-5">
        No broadcasts were found
    </div>

    <div v-if="broad.casts.total > 0">

        {{ broad.casts.total }} broadcasts found <span v-if="filters.broadcasting_now">broadcasting now, {{ day }} {{ time }}z</span>

        <br><br>

        Showing broadcasts {{ ((broad.casts.current_page - 1) * broad.casts.per_page) + 1 }} to {{ broad.casts.next_page_url ? broad.casts.to : broad.casts.total }}

        <Pagination v-if="broad.casts.total > 100" :links="broad.casts.links" :filters="filters"></Pagination>

        <div class="overflow-x-scroll">
            <div class="min-w-[800px] bg-gray-700 text-white grid grid-cols-9 mt-8 p-1">
                <div class="col-span-1">frequency</div>
                <div class="col-span-2">station</div>
                <div class="col-span-2">start / end</div>
                <div class="col-span-1">days</div>
                <div class="col-span-1">language</div>
                <div class="col-span-1">transmitter</div>
                <div class="col-span-1 text-right sm:text-left">strength</div>
            </div>

            <div class="min-w-[800px] grid grid-cols-9 mb-2 border-t border-black p-1" v-for="swInfoBroadcast in broad.casts.data">

                <div class="col-span-1 font-bold text-lg pl-5 pt-1">
                    {{ swInfoBroadcast.frequency }}
                </div>
                    
                <div class="col-span-2">
                    {{ swInfoBroadcast.station.name }}
                    <div class="text-sm ">{{ swInfoBroadcast?.programme?.name }}?</div>
                </div>
                        
                <div class="col-span-2 text-center">
                    {{ swInfoBroadcast.start_time.substring(0, 5) }} - {{ swInfoBroadcast.end_time.substring(0, 5) }}
                </div>

                <div class="text-sm col-span-1 sm:pr-4 lg:pr-8">
                    <div v-if="swInfoBroadcast.weekdays.length < 7" v-for="day in swInfoBroadcast.weekdays" class="pl-1 inline-block">{{ day }}</div>
                    <div v-if="swInfoBroadcast.weekdays.length == 7" class="pl-1 inline-block">Daily</div>
                </div>

                <div class="col-span-1 text-sm">
                    {{ swInfoBroadcast.language.name }}
                </div>

                <div class="col-span-1 text-sm">
                    {{ swInfoBroadcast.sw_info_transmitter.name }}
                    <div class="text-xs">
                        <a :href="'https://www.google.com/maps/place/' + swInfoBroadcast.sw_info_transmitter.longitude + '+' + swInfoBroadcast.sw_info_transmitter.latitude" target="_blank" class="hover:underline">{{ swInfoBroadcast.sw_info_transmitter.longitude }} {{ swInfoBroadcast.sw_info_transmitter.latitude }}</a>
                    </div>
                </div>

                <div class="col-span-1 pr-2 text-right sm:text-left">
                    {{ swInfoBroadcast.strength }}kWz
                </div>
            </div>

        </div>

    </div>

    Information updated on {{ dateOfData }}

    <Pagination v-if="broad.casts.total > 100" :links="broad.casts.links" :filters="filters"></Pagination>

    <br><br><br>
    
    <Link v-if="user && user.id === 1" href="/swiDataRip/rip" class="inline-block border-2 border-red-500 text-red-500 rounded p-2">Update data from short-wave.info</link>

</Layout>


</template>