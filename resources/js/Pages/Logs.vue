<script setup>
import { Head, Link } from '@inertiajs/vue3'
import { onMounted, reactive, ref, watch } from 'vue'
import { base_url } from '@/base_url.js'
import { logs } from '@/logs.js'
import LogsLayout from '@/Pages/LogsLayout.vue'
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
    order_by: String,
    group_results: Boolean
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
    logs.filters.group_results = props.group_results
}

setInterval(makeTime, 1000)

function changeStationType() {

    logs.filters.station_type = parseInt(logs.filters.station_type)
    logs.newlog.station_type = logs.filters.station_type
    logs.filters.half_hour_blocks = logs.filters.station_type === 1 ? true : false
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

function toggleFilterAdd(box) {

  if (box === 'add') {
    logs.showHideFilterLogs = false
    logs.showHideAddLog = ! logs.showHideAddLog
  }
  else if (box === 'filter') {
    logs.showHideAddLog = false
    logs.showHideFilterLogs = ! logs.showHideFilterLogs
  }
}

onMounted(() => {

    updateStationsAndLanguages()
    updateFilters()
    makeTime()
    logs.updateTimeRange()
    logs.updateLogs()
    if (window.innerWidth >= 1024) logs.showHideAddLog = logs.showHideFilterLogs = true

})




</script>

<template>

<Head title="HF / logs" />

<LogsLayout page="" :user="user">

  <!--  buttons sticky for mobile layout - sticky doesn't work inside elements with overflow-scroll -->
  <div
      class="sm:hidden left-0 top-0 right-0 bg-white w-full p-3 z-20"
      :class="{'fixed' : logs.showHideAddLog || logs.showHideFilterLogs,
               'sticky' : ! logs.showHideAddLog && ! logs.showHideFilterLogs}"
  >
    <button
        class="text-xl p-2 mr-2 border-black"
        :class="{
          'border-2 ' : logs.showHideAddLog,
          'border' : ! logs.showHideAddLog}"
        @click="toggleFilterAdd('add')"
    >
      Add Log
    </button>

    <button
        class="text-xl p-2 border-black"
        :class="{
          'border-2' : logs.showHideFilterLogs,
          'border' : ! logs.showHideFilterLogs}"
        @click="toggleFilterAdd('filter')"
    >
      Filter Logs
    </button>
  </div>

    <div class="lg:grid lg:grid-cols-4">

        <!-- add/filter column -->
        <div class="lg:h-screen lg:overflow-y-scroll lg:sticky lg:top-0">


            <label class="inline-block bg-black text-white text-sm my-7 mx-2 p-2">Broadcast <input type="radio" value="1" v-model="logs.filters.station_type" @change="changeStationType()"></label>

            <label class="inline-block bg-black text-white text-sm my-7 mx-2 p-2">Utility <input type="radio" value="2" v-model="logs.filters.station_type" @change="changeStationType()"></label>

            <br><br>

            <button
                class="hidden sm:block text-xl p-2 mr-2"
                :class="{
                  'border border-black' : ! logs.showHideAddLog,
                  'border-t border-x border-black' : logs.showHideAddLog}"
                @click="logs.showHideAddLog = ! logs.showHideAddLog"
            >
                Add Log
            </button>


            <AddLog
                v-if="logs.showHideAddLog"
                class="fixed top-16 bottom-0 left-0 right-0 overflow-y-scroll bg-white sm:bg-transparent sm:overflow-y-auto z-10 sm:static"
            />

            <button
                class="hidden sm:block text-xl p-2 sm:mt-4"
                :class="{
                  'border border-black' : ! logs.showHideFilterLogs,
                  'border-t border-x border-black' : logs.showHideFilterLogs}"
                @click="logs.showHideFilterLogs = ! logs.showHideFilterLogs"
            >
            Filter Logs
            </button>


            <LogFilters
                v-if="logs.showHideFilterLogs"
                class="fixed top-16 bottom-0 left-0 right-0 overflow-y-scroll bg-white sm:h-auto sm:bg-transparent sm:overflow-y-auto z-10 sm:static"
            />

        </div>

        <!-- logs column -->
        <div class="lg:col-span-3">

            <div class="m-12">
                {{ logs.logs.total }} logs found
                <div v-if="logs.logs.total > 0">
                    <div v-if="logs.logs.total > logs.logs.per_page">Page {{ logs.logs.current_page }} of {{ logs.logs.last_page }}</div>
                    Showing logs {{ logs.logs.from }} to {{ logs.logs.to }}
                </div>
            </div>

            <div class="sm:overflow-x-auto lg:overflow-x-visible">
                <Pagination class="mb-12" v-if="logs.logs.total > 10" :links="logs.logs.links" :filters="logs.filters_querystring" />

                <div class="grid grid-cols-12 lg:text-sm xl:text-base sm:min-w-[1000px] lg:w-auto">
                    <div
                        class="border-b border-r border-black sm:p-1 xl:p-4"
                        :class="{
                          'lg:col-span-2 xl:col-span-1 lg:text-sm': !logs.filters.group_results,
                          'col-span-2': logs.filters.group_results}"
                    >
                        Frequency:
                    </div>

                    <div class="border-b border-r border-black sm:p-1 xl:p-4 lg:text-sm xl:text-base"
                         :class="{
                          'sm:col-span-4 xl:col-span-3': !logs.filters.group_results,
                          'col-span-4': logs.filters.group_results}"
                    >
                        Station and Language:
                    </div>

                    <div v-if="!logs.filters.group_results" class="lg:col-span-2 xl:col-span-1 border-b border-r border-black lg:p-1 xl:p-4 lg:text-sm xl:text-base">
                        Time:
                    </div>

                    <div class="col-span-1 border-b border-r border-black text-center sm:p-1 xl:p-4 lg:text-sm xl:text-base">
                      <svg xmlns="http://www.w3.org/2000/svg" height="1em" viewBox="0 0 576 512" class="inline-block mr-3"><path d="M80.3 44C69.8 69.9 64 98.2 64 128s5.8 58.1 16.3 84c6.6 16.4-1.3 35-17.7 41.7s-35-1.3-41.7-17.7C7.4 202.6 0 166.1 0 128S7.4 53.4 20.9 20C27.6 3.6 46.2-4.3 62.6 2.3S86.9 27.6 80.3 44zM555.1 20C568.6 53.4 576 89.9 576 128s-7.4 74.6-20.9 108c-6.6 16.4-25.3 24.3-41.7 17.7S489.1 228.4 495.7 212c10.5-25.9 16.3-54.2 16.3-84s-5.8-58.1-16.3-84C489.1 27.6 497 9 513.4 2.3s35 1.3 41.7 17.7zM352 128c0 23.7-12.9 44.4-32 55.4V480c0 17.7-14.3 32-32 32s-32-14.3-32-32V183.4c-19.1-11.1-32-31.7-32-55.4c0-35.3 28.7-64 64-64s64 28.7 64 64zM170.6 76.8C163.8 92.4 160 109.7 160 128s3.8 35.6 10.6 51.2c7.1 16.2-.3 35.1-16.5 42.1s-35.1-.3-42.1-16.5c-10.3-23.6-16-49.6-16-76.8s5.7-53.2 16-76.8c7.1-16.2 25.9-23.6 42.1-16.5s23.6 25.9 16.5 42.1zM464 51.2c10.3 23.6 16 49.6 16 76.8s-5.7 53.2-16 76.8c-7.1 16.2-25.9 23.6-42.1 16.5s-23.6-25.9-16.5-42.1c6.8-15.6 10.6-32.9 10.6-51.2s-3.8-35.6-10.6-51.2c-7.1-16.2 .3-35.1 16.5-42.1s35.1 .3 42.1 16.5z"/></svg>
                    </div>

                    <div v-if="!logs.filters.group_results" class="col-span-2 border-b border-r border-black sm:p-1 xl:p-4 lg:text-sm xl:text-base">
                        Comments:
                    </div>

                    <div
                      class="sm:col-span-3 border-b border-r border-black sm:p-1 xl:p-4 lg:text-sm xl:text-base"
                      :class="{'lg:hidden xl:block': !logs.filters.group_results}"
                      >
                        <!-- buttons -->
                    </div>

                    <div v-if="logs.filters.group_results" class="col-span-1 border-b border-r border-black sm:p-1 xl:p-4 lg:text-sm xl:text-base">
                      No. of logs:
                    </div>

                    <div v-if="!logs.filters.group_results" class="col-span-1 border-b border-r border-black sm:p-1 xl:p-4 lg:text-sm xl:text-base">
                        Logged By:
                    </div>

                </div>

                    <LogRow
                        v-for="log in logs.logs.data"
                        :log="log"
                        :user="user"
                        :stations="stations"
                        :languages="languages"
                        class="sm:min-w-[1000px] lg:w-auto"
                    />

                <Pagination v-if="logs.logs.total > 10" :links="logs.logs.links" :filters="logs.filters_querystring" />
            </div>

        </div>


    </div>



</LogsLayout>

</template>
