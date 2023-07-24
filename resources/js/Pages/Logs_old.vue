<script setup>
import { Head, Link } from '@inertiajs/vue3'
import { onMounted, ref } from 'vue'
import { base_url } from '@/base_url.js'
import { logs } from '@/logs.js'
import Layout from '@/Pages/Layout.vue'
import Pagination from '@/Components/Pagination.vue'
// import StationsDropDown from '@/Components/StationsDropDown.vue'
// import LanguagesDropDown from '@/Components/LanguagesDropDown.vue'
// import LogRow from '@/Components/LogRow.vue'


const props = defineProps({
    stations: Object,
    languages: Object,
    user: Object
})

function makeLiveTime(start = false) {
    if (! start) logs.datetime.setSeconds(logs.datetime.getSeconds() + 1) // add one second to time
    var datetime = logs.getDateTime()
    logs.newlog.time = datetime.substring(0,16)
    logs.filters.time = datetime.substring(11)
}
setInterval(makeLiveTime, 1000);

function changeStationFilter(station_id) {
    logs.filters.station_id = station_id
    logs.updateLogs()
}

onMounted(() => {
    // logs.filters.weekday = logs.datetime.getDay() + 1
    logs.filters.weekday = 0
    logs.datetime.getUTCDay() + 1
    makeLiveTime(true)

    logs.updateLogs()
})

</script>

<template>

<Head title="HF / logs" />
<Layout page="Logs" :user="user">
    <!-- ////////////////////////////////// -->
    <!-- add log -->

    <br><br>

    {{ logs.newlog.station_id }} {{ logs.newlog.station_name }}

    <br><br>

    {{ logs.newlog.programme_id }} {{ logs.newlog.programme_name }}

    <br><br>

    {{ logs.swinfoMatches }}

    <br><br>

    <button 
        class="block text-xl border border-l border-black rounded-sm p-2 mb-2" @click="logs.showHideAddLog = ! logs.showHideAddLog"
    >Add Log</button>

    <form @submit.prevent="logs.addLog">

    <div v-if="logs.showHideAddLog" class="sm:grid sm:grid-cols-12 sm:gap-1 md:gap-2 lg:gap-4 mb-10">
        <div class="sm:col-span-6 lg:col-span-3 p-2 sm:p-3 mb-1 sm:mb-0 rounded bg-gray-700 text-white text-center">
            <div class="inline-block text-left mx-auto w-full xl:w-2/3">
                Frequency:
                <br>
                <input type="number" max="30000" min="100" maxlength="5" v-model="logs.newlog.frequency" class="border-2 border-black rounded p-1 text-black inline-block w-2/3 sm:inline">
                <button @click="logs.checkFreq" type="button" class="border border-white bg-gray-800 px-2 py-1 ml-2 rounded">CHECK</button>
                <a v-if="logs.newlog.frequency > 100" class="block text-xs mt-1 underline" :href="base_url + 'shortWaveInfoData?frequency=' + logs.newlog.frequency + '&broadcasting_now=false'" target="_blank">[s-w.info]</a>

                <div v-if="logs.newlog.errors.frequency" class="text-sm text-red-300">Please add the frequency</div>

                <div v-if="logs.swinfoMatches.length > 0" class="border border-gray-500 p-2 rounded my-5 text-sm">
                    Shortwave.info found {{ logs.swinfoMatches.length }} stations on {{ logs.swinfoMatches[0].frequency }}kHz
                    <div 
                        v-for="n in logs.swinfoMatches.length"
                        :key="n" 
                        class="text-xs ml-2 m-1 p-1 bg-gray-800 rounded-sm hover:cursor-move"
                        @click="logs.addSwinfoMatch(n-1)">
                        {{ logs.swinfoMatches[n-1].station.name }} in {{ logs.swinfoMatches[n-1].language.name }}
                    </div>
                </div>
            </div>
        </div>

        <div class="sm:col-span-6 lg:col-span-3 p-2 sm:p-3 mb-1 sm:mb-0 rounded bg-gray-700 text-white text-center">
            <div class="inline-block text-left mx-auto w-full xl:w-2/3 whitespace-nowrap">
                Time:<br>
                <input type="datetime-local" v-model="logs.newlog.time" class="border-2 border-black rounded text-black p-1 mr-2"><b>z</b>
                <div v-if="logs.newlog.errors.time" class="text-sm text-red-300">Please add the time</div>
            </div>
        </div>

        <div class="sm:col-span-6 lg:col-span-3 p-2 sm:p-3 mb-1 sm:mb-0 rounded bg-gray-700 text-white text-center">
            <div class="inline-block text-left mx-auto w-full xl:w-2/3">
                <StationsDropDown 
                    @chooseStation_id="logs.newlog.station_id = $event"
                    @chooseStation_name="logs.newlog.station_name = $event"
                    @chooseProgramme_id="logs.newlog.programme_id = $event"
                    @chooseProgramme_name="logs.newlog.programme_name = $event"
                    :stations="stations" 
                    :required="true" 
                    :station_id="logs.newlog.station_id"
                    :station_name="logs.newlog.station_name" 
                    :programme_id="logs.newlog.programme_id"
                    :programme_name="logs.newlog.programme_name" 
                    ref="triggerProgrammes"
                />
                <div v-if="logs.newlog.errors.station_id" class="text-sm text-red-300">Please choose the station</div>
            </div>
        </div>

        <div class="sm:col-span-6 lg:col-span-3 p-2 sm:p-3 mb-1 sm:mb-0 rounded bg-gray-700 text-white text-center">
            <div class="inline-block text-left mx-auto w-full xl:w-2/3">
                <LanguagesDropDown
                    @chooseLanguage_id="logs.newlog.language_id = $event"
                    @chooseLanguage_name="logs.newlog.language_name = $event"
                    :languages="languages" 
                    :required="true"
                    :language_id="logs.newlog.language_id"
                    :language_name="logs.newlog.language_name"
                />
                <div v-if="logs.newlog.errors.language_id" class="text-sm text-red-300">Please choose the language</div>
            </div>
        </div>

        <div class="sm:col-span-6 lg:col-span-4 p-2 sm:p-3 mb-1 sm:mb-0 rounded bg-gray-700 text-white text-center">
            <div class="inline-block text-left mx-auto w-full xl:w-2/3">
                Reception quality (1 = poor, 3 = excellent):<br>
                <label class="m-1 px-2 bg-gray-800 border border-white rounded inline-block">1 <input type="radio" v-model="logs.newlog.quality" value="1" class="m-2"></label>
                <label class="m-1 px-2 bg-gray-800 border border-white rounded inline-block">2 <input type="radio" v-model="logs.newlog.quality" value="2" class="m-2"></label>
                <label class="m-1 px-2 bg-gray-800 border border-white rounded inline-block">3 <input type="radio" v-model="logs.newlog.quality" value="3" class="m-2"></label>

                <div v-if="logs.newlog.errors.quality" class="text-sm text-red-300">Please choose a reception quality</div>
            </div>
        </div>

        <div class="sm:col-span-6 lg:col-span-4 p-2 sm:p-3 mb-1 sm:mb-0 rounded bg-gray-700 text-white text-center">
            <div class="inline-block text-left mx-auto w-full xl:w-2/3">
                Comments:<br>
                <textarea v-model="logs.newlog.comment" class="border-2 border-black p-2 text-black rounded w-full"></textarea>
            </div>

        </div>

        <div class="sm:col-span-12 lg:col-span-4 p-2 sm:p-3 mb-1 sm:mb-0 rounded bg-gray-700 text-white">
            <button class="block border border-white bg-gray-800 text-xl p-2 rounded w-full h-full" type="submit">ADD LOG</button>
        </div>
    </div>
    </form>

    <!-- ////////////////////////////////// -->
    <!-- log filters -->
    <button 
        class="block text-xl border border-l border-black rounded-sm p-2 mb-2" @click="logs.showHideFilterLogs = ! logs.showHideFilterLogs"
    >
    Filter Logs
    </button>

    <div v-if="logs.showHideFilterLogs" class="sm:grid sm:grid-cols-12 sm:gap-1 md:gap-2 lg:gap-4 mb-10">
        <div class="sm:col-span-6 lg:col-span-3 p-2 sm:p-3 mb-1 sm:mb-0 rounded bg-gray-700 text-white text-center">
            <div class="inline-block text-left mx-auto w-full xl:w-2/3">
                Frequency:
                <br>
                <input type="number" v-model="logs.filters.frequency" min="100" max="30000" class="block w-full border-2 border-black rounded p-1 text-black" @change="logs.updateLogs(filters)">
            </div>
        </div>

        <div class="sm:col-span-6 lg:col-span-3 p-2 sm:p-3 mb-1 sm:mb-0 rounded bg-gray-700 text-white text-center">
            <div class="inline-block text-left mx-auto w-full xl:w-2/3">
                Weekday:
                <br>
                <select v-model="logs.filters.weekday" class="block border-2 border-black p-2 rounded text-black text-sm sm:text-base w-full" @change="logs.updateLogs(filters)">
                    <option value="0">All days</option>
                    <option value="2">Mon</option>
                    <option value="3">Tue</option>
                    <option value="4">Wed</option>
                    <option value="5">Thu</option>
                    <option value="6">Fri</option>
                    <option value="7">Sat</option>
                    <option value="1">Sun</option>
                </select>
            </div>
        </div>

        <div class="sm:col-span-6 lg:col-span-3 p-2 sm:p-3 mb-1 sm:mb-0 rounded bg-gray-700 text-white text-center">
            <div class="inline-block text-left mx-auto w-full xl:w-2/3">
                <label>Time? <input type="checkbox" @click="logs.filters.time_filter = ! logs.filters.time_filter" @change="logs.updateLogs" class="ml-4 mb-1" checked></label>
                <br>
                <input 
                    type="time" 
                    v-model="logs.filters.time" 
                    class="border-2 border-black rounded text-black p-1 mr-2 w-5/6" 
                    :class="{'bg-gray-600': ! logs.filters.time_filter}" 
                    :disabled="! logs.filters.time_filter"
                ><b>z</b>
            </div>
        </div>

        <div class="sm:col-span-6 lg:col-span-3 p-2 sm:p-3 mb-1 sm:mb-0 rounded bg-gray-700 text-white text-center">
            <div class="inline-block text-left mx-auto w-full xl:w-2/3">
                <StationsDropDown 
                    @chooseStation_id="changeStationFilter($event)"
                    :stations="stations" 
                    :required="false" 
                    :station_id="logs.filters.station_id"
                    :station_name="logs.filters.station_name" 
                />
            </div>
        </div>

        <div class="sm:col-span-6 lg:col-span-3 p-2 sm:p-3 mb-1 sm:mb-0 rounded bg-gray-700 text-white text-center">
            <div class="inline-block text-left mx-auto w-full xl:w-2/3">
                Reception quality:
                <br>
                <label class="m-1 px-2 bg-gray-800 border border-white rounded inline-block">1 <input type="radio" v-model="logs.filters.quality" value="1" @change="logs.updateLogs(filters)" class="m-2"></label>
                <label class="m-1 px-2 bg-gray-800 border border-white rounded inline-block">2 <input type="radio" v-model="logs.filters.quality" value="2" @change="logs.updateLogs(filters)" class="m-2"></label>
                <label class="m-1 px-2 bg-gray-800 border border-white rounded inline-block">3 <input type="radio" v-model="logs.filters.quality" value="3" @change="logs.updateLogs(filters)" class="m-2"></label>
            </div>
        </div>
        
        <div class="sm:col-span-6 lg:col-span-3 p-2 sm:p-3 mb-1 sm:mb-0 rounded bg-gray-700 text-white text-center">
            <div class="inline-block text-left mx-auto w-full xl:w-2/3">
                <label>Your logs <input type="radio" v-model="logs.filters.logOwners" value="0" @change="logs.updateLogs(filters)"></label> or <label>all logs <input type="radio" v-model="logs.filters.logOwners" value="1" @change="logs.updateLogs(filters)"></label>
            </div>
        </div>

        <div class="sm:col-span-6 lg:col-span-3 p-2 sm:p-3 mb-1 sm:mb-0 rounded bg-gray-700 text-white text-center">
            <div class="inline-block text-left mx-auto w-full xl:w-2/3">
                Search comments:
                <br>
                <input type="text" v-model="logs.filters.commentSearch" class="border-2 border-black p-2 text-black rounded w-full" @change="logs.updateLogs(filters)">
            </div>
        </div>
    </div>

    <!-- ////////////////////////////////// -->
    <!-- log table -->
    <div class="my-4 sm:my-8">
        {{ logs.logs.total }} logs found
        <div v-if="logs.logs.total > logs.logs.per_page">Page {{ logs.logs.current_page }} of {{ logs.logs.last_page }}</div>
        <div v-if="logs.logs.total > logs.logs.per_page">Showing logs {{ ((logs.logs.current_page * logs.logs.per_page) - logs.logs.per_page) + 1 }} to {{ logs.logs.current_page * logs.logs.per_page }}</div>
    </div>

    <div class="hidden lg:grid lg:grid-cols-11 lg:gap-1 lg:mb-1">
        <div class="col-span-2 border-b border-r border-black p-4">
            Frequency:
        </div>

        <div class="col-span-3 border-b border-r border-black p-4">
            Station and Language:
        </div>

        <div class="col-span-2 border-b border-r border-black p-4">
            Time:
        </div>

        <div class="col-span-1 border-b border-r border-black p-4">
            Reception<br>Quality:
        </div>

        <div class="col-span-1 border-b border-r border-black p-4">
            Logged By:
        </div>

        <div class="col-span-2 border-b border-black p-4">
            Comments:
        </div>
    </div>

    <LogRow 
        v-for="log in logs.logs.data" 
        :log="log" 
        :user="user" 
        :stations="stations"
        :languages="languages"
    />

    <!-- <br><br>
    <Pagination v-if="logs.logs.total > logs.logs.per_page" :links="logs.logs.links" :filters="filters"></Pagination>
    <br><br> -->




</Layout>

</template>