<script setup>
import { Head, Link } from '@inertiajs/vue3'
import { onMounted } from 'vue'
import Layout from '@/Pages/Layout.vue'
import { base_url } from '@/base_url.js'
import { logs } from '@/logs.js'

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
    <button 
        class="block text-xl border border-l border-black rounded-sm p-2 mb-2" @click="logs.showHideAddLog = ! logs.showHideAddLog"
    >
        Add Log
    </button>

    <form @submit.prevent="logs.addLog">
    <div v-if="logs.showHideAddLog" class="sm:grid sm:grid-cols-12 sm:gap-1 md:gap-2 lg:gap-4 mb-10">
        <div class="sm:col-span-6 lg:col-span-3 p-2 sm:p-3 mb-1 sm:mb-0 rounded bg-gray-700 text-white text-center">
            <div class="inline-block text-left mx-auto w-full xl:w-2/3">
                Frequency:
                <br>
                <input type="number" max="30000" min="100" maxlength="5" v-model="logs.newlog.frequency" class="border-2 border-black rounded p-1 text-black inline-block w-2/3 sm:inline">
                <button @click="logs.checkFreq" type="button" class="border border-white bg-gray-800 px-2 py-1 ml-2 rounded">CHK</button>
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
                Station:<br>
                <select class="block border-2 border-black p-2 rounded text-black text-sm sm:text-base w-full" v-model="logs.newlog.station_id">
                    <option value="0"></option>
                    <option v-for="station in stations" :value="station.id">{{ station.name }}</option>
                </select>
                <div v-if="logs.newlog.errors.station_id" class="text-sm text-red-300">Please choose the station</div>
            </div>
        </div>

        <div class="sm:col-span-6 lg:col-span-3 p-2 sm:p-3 mb-1 sm:mb-0 rounded bg-gray-700 text-white text-center">
            <div class="inline-block text-left mx-auto w-full xl:w-2/3">
                Language:<br>
                <select class="block border-2 border-black p-2 rounded text-black text-sm sm:text-base w-full" v-model="logs.newlog.language_id">
                        <option v-for="language in languages" :value="language.id">{{ language.name }}</option>
                </select>
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
                Station:
                <br>
                <select class="block border-2 border-black p-2 rounded text-black text-sm sm:text-base w-full" v-model="logs.filters.station_id" @change="logs.updateLogs(filters)">
                    <option value="0">All Stations</option>
                    <option v-for="station in stations" :value="station.id">{{ station.name }}</option>
                </select>
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
        {{ logs.logs.length }} logs found
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

    <div class="grid grid-cols-11 gap-1 mb-3" v-for="log in logs.logs">
        <div class="col-span-4 lg:col-span-2 bg-gray-500 text-white rounded-sm px-1 lg:px-3 py-3 lg:py-4 text-center text-xl">
            <div v-if="logs.edit_mode === log.id">
                <input type="number" min="100" max="30000" maxlength="5" v-model="log.frequency" class="border border-black p-1 rounded text-sm w-full text-black">
                <div v-if="logs.edit_errors.frequency" class="text-xs text-red-900">{{ logs.edit_errors.frequency }}</div>
            </div>
            <div v-else>{{ log.frequency }}</div>
        </div>

        <div class="col-span-8 lg:col-span-3 bg-gray-500 text-white rounded-sm p-1 lg:px-4 lg:py-1">
            <div v-if="logs.edit_mode === log.id">
                <select v-model="log.station_id" class="block border-2 border-black p-2 my-1 rounded text-black text-xs sm:text-sm w-full">
                    <option v-for="station in stations" :value="station.id">{{ station.name }}</option>
                </select>

                <select v-model="log.language_id" class="block border-2 border-black p-2 my-1 rounded text-black text-xs sm:text-sm w-full">
                    <option v-for="language in languages" :value="language.id">{{ language.name }}</option>
                </select>
            </div>

            <div v-else>
                {{ log.station.name }}<br>
                <span class="text-lg">{{ log.language.name }}</span>
            </div>

        </div>

        <div class="col-span-12 lg:col-span-2 bg-gray-300 rounded-sm py-1 lg:px-4 lg:py-1 text-center">
            <div v-if="logs.edit_mode === log.id">
                <input type="datetime-local" v-model="log.time" class="border-2 border-black rounded text-black p-1 mt-5 mr-1"><b>z</b>
            </div>

            <div v-else>
                <div class="inline-block whitespace-nowrap">{{ logs.getDisplayDate(log.time)[0] }},&nbsp;&nbsp;</div>
                <div class="inline-block whitespace-nowrap">{{ logs.getDisplayDate(log.time)[1] }}</div> 
            </div>

        </div>

        <div class="col-span-6 lg:col-span-1 bg-gray-300 rounded-sm text-center px-1 py-4">
            <div v-if="logs.edit_mode === log.id">
                <label class="m-1 px-1 bg-gray-300 border border-black rounded inline-block text-xs">1 <input type="radio" v-model="log.quality" value="1" class="m-1"></label>
                <label class="m-1 px-1 bg-gray-300 border border-black rounded inline-block text-xs">2 <input type="radio" v-model="log.quality" value="2" class="m-1"></label>
                <label class="m-1 px-1 bg-gray-300 border border-black rounded inline-block text-xs">3 <input type="radio" v-model="log.quality" value="3" class="m-1"></label>
            </div>

            <div v-else>
                <svg xmlns="http://www.w3.org/2000/svg" height="1em" viewBox="0 0 576 512" class="inline-block mr-3"><path d="M80.3 44C69.8 69.9 64 98.2 64 128s5.8 58.1 16.3 84c6.6 16.4-1.3 35-17.7 41.7s-35-1.3-41.7-17.7C7.4 202.6 0 166.1 0 128S7.4 53.4 20.9 20C27.6 3.6 46.2-4.3 62.6 2.3S86.9 27.6 80.3 44zM555.1 20C568.6 53.4 576 89.9 576 128s-7.4 74.6-20.9 108c-6.6 16.4-25.3 24.3-41.7 17.7S489.1 228.4 495.7 212c10.5-25.9 16.3-54.2 16.3-84s-5.8-58.1-16.3-84C489.1 27.6 497 9 513.4 2.3s35 1.3 41.7 17.7zM352 128c0 23.7-12.9 44.4-32 55.4V480c0 17.7-14.3 32-32 32s-32-14.3-32-32V183.4c-19.1-11.1-32-31.7-32-55.4c0-35.3 28.7-64 64-64s64 28.7 64 64zM170.6 76.8C163.8 92.4 160 109.7 160 128s3.8 35.6 10.6 51.2c7.1 16.2-.3 35.1-16.5 42.1s-35.1-.3-42.1-16.5c-10.3-23.6-16-49.6-16-76.8s5.7-53.2 16-76.8c7.1-16.2 25.9-23.6 42.1-16.5s23.6 25.9 16.5 42.1zM464 51.2c10.3 23.6 16 49.6 16 76.8s-5.7 53.2-16 76.8c-7.1 16.2-25.9 23.6-42.1 16.5s-23.6-25.9-16.5-42.1c6.8-15.6 10.6-32.9 10.6-51.2s-3.8-35.6-10.6-51.2c-7.1-16.2 .3-35.1 16.5-42.1s35.1 .3 42.1 16.5z"/></svg>{{ log.quality }}/3
            </div>
        </div>

        <div class="col-span-6 lg:col-span-1 bg-gray-300 rounded-sm p-1">
            {{ log.user.id === user.id ? 'you' : log.user.name }}
            <div v-if="log.user.id === user.id">
                <button 
                    class="bg-green-400 text-black border border-black rounded text-lg m-1 px-1" 
                    :class="{'text-gray-300': logs.edit_mode && logs.edit_mode != log.id}"
                    @click="logs.editLog(log)"
                    :disabled="logs.edit_mode && logs.edit_mode != log.id"
                    >
                    {{ logs.edit_mode === log.id ? 'save' : 'edit' }}
                </button>
                <button class="bg-red-400 text-black border border-black rounded text-lg m-1 px-1" type="button" @click="logs.deleteLog(log.id, log.frequency, log.station.name, log.language.name)">delete</button>
            </div>
        </div>


        <div v-if="logs.edit_mode === log.id" class="col-span-12 lg:col-span-1 bg-gray-300 rounded-sm p-2">
            <textarea v-model="log.comment" class="border-2 border-black p-2 text-black rounded w-full my-3"></textarea>
        </div>

        <div v-if="logs.edit_mode !== log.id && log.comment && log.comment.length > 0" class="col-span-12 lg:col-span-2 bg-gray-300 rounded-sm p-2 w-full h-full" v-html="log.comment.replace('\n', '<br><br>')"></div>

        <div v-if="logs.edit_mode !== log.id && !log.comment || log.comment.length === 0" class="hidden lg:block lg:col-span-2 bg-gray-300 rounded-sm h-full"></div>



    </div>

</Layout>

</template>