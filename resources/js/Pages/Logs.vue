<script setup>
import { Head, Link } from '@inertiajs/vue3'
import { onMounted, reactive, ref, watch } from 'vue'
import { base_url } from '@/base_url.js'
import { logs } from '@/logs.js'
import Layout from '@/Pages/Layout.vue'
import AddLog from '@/Components/AddLog.vue'
import LogFilters from '@/Components/LogFilters.vue'
import LogRow from '@/Components/LogRow.vue'
import Pagination from '@/Components/Pagination.vue'


const props = defineProps({
    stations: Object,
    languages: Object,
    user: Object,
    page: Number,
    station_type: Number,
    frequency: Number,
    weekday: Number,
    time_range: Number,
    half_hour_blocks: Boolean,
    bottom_time_range: String,
    top_time_range: String,
    time_filter: Boolean,
    make_time_now: Boolean,
    station_id: Number,
    station_name: String,
    language_id: Number,
    language_name: String,
    quality: Number,
    commentSearch: String,
    log_owners: Boolean,
    order_by: String
})

function makeTime() {

    var datetime = new Date()
    var year = datetime.getUTCFullYear()
    var month = datetime.getUTCMonth()
    month++
    month = month.toString().padStart(2, '0')
    var day = datetime.getUTCDate().toString().padStart(2, '0')

    // get day of the week for the weekday dropdown in filters
    logs.today = datetime.getUTCDay() + 1

    var hour = datetime.getUTCHours().toString().padStart(2, '0')
    var minute = datetime.getUTCMinutes().toString().padStart(2, '0')
    var second = datetime.getSeconds().toString().padStart(2, '0')

    if (logs.newlog.make_time_now) {
        logs.newlog.datetime = year + '-' + month + '-' + day + 'T' + hour + ':' + minute
    }
    if (logs.filters.make_time_now) {
        logs.filters.time = hour + ':' + minute + ':' + second

      // reevaluate the time ranges half-hourly if half_hour_blocks is true, or every 5 minutes if it is false
      if (logs.filters.time_filter &&
          (logs.filters.half_hour_blocks && (minute === '00' || minute === '30') && second === '00')
          ||
          (!logs.filters.half_hour_blocks && minute.substring(1,2) === '5')
      ) {
        logs.updateTimeRange()
        logs.updateLogs()
      }
    }
}

function updateStationsAndLanguages() {

    logs.stations = props.stations
    logs.languages = props.languages
}

function updateFilters() {

    logs.filters.page = props.page
    logs.filters.station_type = props.station_type
    logs.filters.frequency = props.frequency > 100 && props.frequency < 30000 ? props.frequency : null
    logs.filters.weekday = props.weekday
    logs.filters.time_filter = props.time_filter
    logs.filters.time_range = props.time_range
    logs.filters.half_hour_blocks = props.half_hour_blocks
    logs.filters.bottom_time_range = props.bottom_time_range
    logs.filters.top_time_range = props.top_time_range
    logs.filters.make_time_now = props.make_time_now
    logs.filters.station_id = props.station_id
    logs.filters.station_name = props.station_name ? props.station_name : ''
    logs.filters.language_id = props.language_id
    logs.filters.language_name = props.language_name ? props.language_name : ''
    logs.filters.quality = props.quality
    logs.filters.commentSearch = props.commentSearch
    logs.filters.log_owners = props.log_owners
    logs.filters.order_by = props.order_by
}

setInterval(makeTime, 1000)

function changeStationType() {

    logs.filters.station_type = parseInt(logs.filters.station_type)
    logs.newlog.station_type = logs.filters.station_type
    logs.filters.half_hour_blocks = ! logs.filters.half_hour_blocks
    logs.updateTimeRange()

    axios.get(base_url + 'getStations/' + logs.filters.station_type).then(stations => {
        logs.stations = stations.data
        })
    .catch(error => {
        console.error(error)
    })

    axios.get(base_url + 'getLanguages/' + logs.filters.station_type).then(languages => {
        logs.languages = languages.data
        })
    .catch(error => {
        console.error(error)
    })

    logs.updateLogs()
}

onMounted(() => {

    updateStationsAndLanguages()
    updateFilters()
    makeTime()
    logs.updateTimeRange()
    logs.updateLogs()
})


</script>

<template>

<Head title="HF / logs" />

<Layout page="Logs" :user="user">

    <label class="mb-2">Broadcast <input type="radio" v-model="logs.filters.station_type" value="1" @change="changeStationType()"></label>, or <label>Utility <input type="radio" v-model="logs.filters.station_type" value="2" @change="changeStationType()"></label> stations

  <br><br>
    <button
        class="block text-xl border border-l border-black rounded-sm p-2 mb-2"
        @click="logs.showHideAddLog = ! logs.showHideAddLog"
    >
        Add Log
    </button>

    <AddLog
    id="add_log"
        v-if="logs.showHideAddLog"
    />

    <button
        class="block text-xl border border-l border-black rounded-sm p-2 mb-2"
        @click="logs.showHideLogFilters = ! logs.showHideLogFilters"
    >
    Filter Logs
    </button>

    <LogFilters
        v-if="logs.showHideLogFilters"
    />

    <!-- ////////////////////////////////// -->
    <!-- log table -->
    <div class="my-4 sm:my-8">
        {{ logs.logs.total }} logs found
        <div v-if="logs.logs.total > 0">
            <div v-if="logs.logs.total > logs.logs.per_page">Page {{ logs.logs.current_page }} of {{ logs.logs.last_page }}</div>
            Showing logs {{ logs.logs.from }} to {{ logs.logs.to }}
        </div>
    </div>

    <Pagination v-if="logs.logs.total > 10" :links="logs.logs.links" :filters="logs.filters_querystring" />

    <!-- <div class="hidden lg:grid lg:grid-cols-12 lg:gap-1 lg:mb-1">
        <div class="col-span-2 border-b border-r border-black p-4">
            Frequency:
        </div>

        <div class="col-span-3 border-b border-r border-black p-4">
            Station and Language:
        </div>

        <div class="col-span-2 border-b border-r border-black p-4">
            Time:
        </div>

        <div class="col-span-2 border-b border-r border-black p-4">
            Comments:
        </div>

        <div class="col-span-1 border-b border-r border-black p-4">
            Reception<br>Quality:
        </div>

        <div class="col-span-1 border-b border-r border-black p-4">
            Logged By:
        </div>

    </div> -->

    <LogRow
        v-for="log in logs.logs.data"
        :log="log"
        :user="user"
        :stations="stations"
        :languages="languages"
    />

    <Pagination v-if="logs.logs.total > 10" :links="logs.logs.links" :filters="logs.filters_querystring" />


</Layout>

</template>
