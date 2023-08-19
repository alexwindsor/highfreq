<script setup>
import { Head, Link, router } from '@inertiajs/vue3'
import { reactive, ref, onMounted } from 'vue'
import Layout from '@/Pages/Layout.vue'
import { base_url } from '@/base_url.js'
import Pagination from '@/Components/Pagination.vue'

const props = defineProps({
    stations: Object,
    transmitters: Object,
    languages: Object,
    dateOfData: String,
    day: String,
    time: String,
    user: Object,
    page: Number,
    frequency: Number,
    broadcasting_now: Boolean,
    station_id: Number,
    language_id: Number,
    transmitter_id: Number,
    order_by: String
})


const filters = reactive({
    frequency: props.frequency === 0 ? null : props.frequency,
    broadcasting_now: props.broadcasting_now,
    station_id: props.station_id,
    language_id: props.language_id,
    transmitter_id: props.transmitter_id,
    order_by: props.order_by
})

const broad = reactive({
        casts: [],
        querystring: []
    })


function makeQuerystring() {

    return '&frequency=' + filters.frequency + '&broadcasting_now=' + filters.broadcasting_now + '&station_id=' + filters.station_id + '&transmitter_id=' + filters.transmitter_id + '&order_by=' + filters.order_by
}

function updateData() {

    broad.querystring = makeQuerystring()

    axios.post(base_url + 'shortWaveInfoData?page=' + props.page, filters).then(swInfoBroadcasts => {
        broad.casts = swInfoBroadcasts.data
        // check that the current page is not greater than the total number of pages
        var last_page = Math.floor(broad.casts.total / broad.casts.per_page) + 1
        // if it is, then redirect to the last page
        if (last_page < broad.casts.current_page) {
            router.visit(base_url + 'shortWaveInfoData?page=' + last_page + broad.querystring)
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


    <div class="mb-10 sm:grid sm:grid-cols-12 sm:gap-1 md:gap-2 lg:gap-4">

        <div class="mb-1 rounded bg-gray-700 p-2 text-center text-white sm:col-span-6 sm:mb-0 sm:p-3 lg:col-span-2">
            <div class="mx-auto inline-block w-full text-left xl:w-2/3">
                Frequency :<br>
                <input type="number" max="30000" min="3000" class="block w-full rounded border-2 border-black p-1 text-black" v-model="filters.frequency" @keyup="updateData">
            </div>
        </div>

        <div class="mb-1 rounded bg-gray-700 p-2 text-center text-white sm:col-span-6 sm:mb-0 sm:p-3 lg:col-span-2">
            <div class="mx-auto inline-block w-full text-left xl:w-2/3">
                Station:
                <select class="block w-full rounded border-2 border-black p-2 text-black" v-model="filters.station_id" @change="updateData">
                    <option value="0">All Stations</option>
                    <option v-for="station in stations" :value="station.id">{{ station.name }}</option>
                </select>
            </div>
        </div>

        <div class="mb-1 rounded bg-gray-700 p-2 text-center text-white sm:col-span-6 sm:mb-0 sm:p-3 lg:col-span-2">
            <div class="mx-auto inline-block w-full text-left xl:w-2/3">
                Language:
                <select class="block w-full rounded border-2 border-black p-2 text-black" v-model="filters.language_id" @change="updateData">
                    <option value="0">Any Language</option>
                    <option v-for="language in languages" :value="language.id">{{ language.name }}</option>
                </select>
            </div>
        </div>

        <div class="mb-1 rounded bg-gray-700 p-2 text-center text-white sm:col-span-6 sm:mb-0 sm:p-3 lg:col-span-2">
            <div class="mx-auto inline-block w-full text-left xl:w-2/3">
                Transmitter:
                <select class="block w-full rounded border-2 border-black p-2 text-black" v-model="filters.transmitter_id" @change="updateData">
                    <option value="0">All Transmitters</option>
                    <option v-for="transmitter in transmitters" :value="transmitter.id">{{ transmitter.name }}</option>
                </select>
            </div>
        </div>

        <div class="mb-1 rounded bg-gray-700 p-2 text-center text-white sm:col-span-6 sm:mb-0 sm:p-3 lg:col-span-2">
            <div class="mx-auto inline-block w-full text-left xl:w-2/3">
                Order by:
                <br>
                <select class="block w-full rounded border-2 border-black p-2 text-black" v-model="filters.order_by" @change="updateData">
                    <option value="frequency">Frequency</option>
                    <option value="station_id">Station</option>
                    <option value="start_time">Start Time</option>
                </select>
            </div>
        </div>

        <div class="mb-1 rounded bg-gray-700 p-2 text-center text-white sm:col-span-6 sm:mb-0 sm:p-0 lg:col-span-2 lg:p-2">
            <div class="mx-auto inline-block w-full xl:w-2/3">
                <label class="mx-2 my-0 inline-block p-1 lg:my-4">Broadcasting now <input type="checkbox" v-model="filters.broadcasting_now" @change="updateData" class="mx-5"></label>
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

        <Pagination v-if="broad.casts.total > 50" :links="broad.casts.links" :filters="broad.querystring" />

        <div class="overflow-x-scroll">
            <div class="mt-8 grid grid-cols-8 bg-gray-700 p-1 text-white min-w-[800px]">
                <div class="col-span-1">frequency</div>
                <div class="col-span-2">station</div>
                <div class="col-span-1">start / end</div>
                <div class="col-span-1">days</div>
                <div class="col-span-1">language</div>
                <div class="col-span-1">transmitter</div>
                <div class="col-span-1 text-right sm:text-left">strength</div>
            </div>

            <div class="mb-10 grid grid-cols-8 border-t border-black p-1 min-w-[800px]" v-for="swInfoBroadcast in broad.casts.data">
                <!-- frequency -->
                <div class="col-span-1 pt-1 pl-5 text-lg font-bold">
                    <Link class="underline" :href="base_url + 'shortWaveInfoData?frequency=' + swInfoBroadcast.frequency + '&broadcasting_now=false'">{{ swInfoBroadcast.frequency }}</Link>
                    <br>
                    <a :href="'http://websdr.ewi.utwente.nl:8901/?tune=' + swInfoBroadcast.frequency + 'am'" target="_blank" class="text-xs underline">websdr</a>
                </div>

                <div class="col-span-2">
                    <!-- station and programme -->
                    <Link class="underline" :href="base_url + 'shortWaveInfoData?station_id=' + swInfoBroadcast.station_id + '&broadcasting_now=false'">{{ swInfoBroadcast.station_name }}</Link>
                    <div class="text-sm text-gray-600">{{ swInfoBroadcast?.station_programme_name }}</div>
                </div>

                <div class="col-span-1 text-left">
                    <!-- start and end time -->
                    {{ swInfoBroadcast.start_time.substring(0, 5) }}z - {{ swInfoBroadcast.end_time.substring(0, 5) }}z
                </div>

                <div class="col-span-1 text-sm sm:pr-4 lg:pr-8">
                    <!-- days of the week -->
                    <div v-if="swInfoBroadcast.weekdays.length < 7" v-for="day in swInfoBroadcast.weekdays" class="inline-block pl-1">{{ day }}</div>
                    <div v-if="swInfoBroadcast.weekdays.length == 7" class="inline-block pl-1">Daily</div>
                </div>

                <div class="col-span-1 text-sm">
                    <!-- language -->
                    <Link class="underline" :href="base_url + 'shortWaveInfoData?language_id=' + swInfoBroadcast.language_id">{{ swInfoBroadcast.language_name }}</Link>
                </div>

                <div class="col-span-1 text-sm">
                    <!-- transmitter -->
                    <Link class="underline" :href="base_url + 'shortWaveInfoData?transmitter_id=' + swInfoBroadcast.transmitter_id">{{ swInfoBroadcast.transmitter_name }}</Link>
                    <div class="text-xs">
                        <a
                            :href="'https://www.google.com/maps/place/' + swInfoBroadcast.transmitter_longitude + '+' + swInfoBroadcast.transmitter_latitude"
                            target="_blank" class="hover:underline"
                        >
                            {{ swInfoBroadcast.transmitter_longitude }} {{ swInfoBroadcast.transmitter_latitude }}
                        </a>
                    </div>
                </div>

                <div class="col-span-1 pr-2 text-right sm:text-left">
                    <!-- strength -->
                    {{ swInfoBroadcast.strength }}kWz
                </div>
            </div>

        </div>

    </div>

    <br>

    Information updated on {{ dateOfData }}

    <br><br>

    <Pagination v-if="broad.casts.total > 50" :links="broad.casts.links" :filters="broad.querystring" />

    <Link v-if="user && user.id === 1" :href="base_url + 'swiDataRip/rip'" class="mt-8 inline-block rounded border-2 border-red-500 p-2 text-red-500">Update data from short-wave.info</link>

</Layout>


</template>
