<script setup>

import { logs } from '@/logs.js'
import StationsDropDown from '@/Components/StationsDropDown.vue'
import LanguagesDropDown from '@/Components/LanguagesDropDown.vue'
import ReceptionQuality from '@/Components/ReceptionQuality.vue'

const props = defineProps({
    stations: Object,
    languages: Object
})

function changeTime() {
console.log('change time')
  logs.updateTimeRange()
  logs.updateLogs()

}

</script>

<template>

<div class="sm:grid sm:grid-cols-12 sm:gap-1 md:gap-2 lg:gap-4 mb-10">
    <!-- frequency -->
    <div class="sm:col-span-6 lg:col-span-3 p-2 sm:p-3 mb-1 sm:mb-0 rounded bg-gray-700 text-white text-center">
        <div class="inline-block text-left mx-auto w-full xl:w-2/3">
            Frequency:
            <br>
            <input
                type="number"
                v-model="logs.filters.frequency"
                min="100"
                max="30000"
                class="block w-full border-2 border-black rounded p-1 text-black"
                @keyup="logs.updateLogs('frequency')"
            >
        </div>
    </div>

    <!-- station -->
    <div class="sm:col-span-6 lg:col-span-3 p-2 sm:p-3 mb-1 sm:mb-0 rounded bg-gray-700 text-white text-center">
        <div class="inline-block text-left mx-auto w-full xl:w-2/3">
            <StationsDropDown
                :required="false"
                :station_id="logs.filters.station_id"
                :station_name="logs.filters.station_name"
                :programmes="false"
                :station_programme_id="null"
                :station_programme_name="null"
                @stationId="logs.updateFilters('station_id', $event)"
                @stationName="logs.filters.station_name = $event"
            />
        </div>
    </div>

        <!-- language -->
        <div class="sm:col-span-6 lg:col-span-3 p-2 sm:p-3 mb-1 sm:mb-0 rounded bg-gray-700 text-white text-center">
        <div class="inline-block text-left mx-auto w-full xl:w-2/3">
            <LanguagesDropDown
                :required="false"
                :language_id="logs.filters.language_id"
                :language_name="logs.filters.language_name"
                @languageId="logs.updateFilters('language_id', $event)"
                @languageName="logs.filters.language_name = $event"
            />
        </div>
    </div>

    <!-- day of week -->
    <div class="sm:col-span-6 lg:col-span-3 p-2 sm:p-3 mb-1 sm:mb-0 rounded bg-gray-700 text-white text-center">
        <div class="inline-block text-left mx-auto w-full xl:w-2/3">
            Weekday:
            <br>
            <select
                v-model="logs.filters.weekday"
                class="block border-2 border-black p-2 rounded text-black text-sm sm:text-base w-full"
                @change="logs.updateLogs(filters)"
            >
                <option value="0">All days</option>
                <option value="2">Mon {{ logs.today === 2 ? '(today)' : '' }}</option>
                <option value="3">Tue {{ logs.today === 3 ? '(today)' : '' }}</option>
                <option value="4">Wed {{ logs.today === 4 ? '(today)' : '' }}</option>
                <option value="5">Thu {{ logs.today === 5 ? '(today)' : '' }}</option>
                <option value="6">Fri {{ logs.today === 6 ? '(today)' : '' }}</option>
                <option value="7">Sat {{ logs.today === 7 ? '(today)' : '' }}</option>
                <option value="1">Sun {{ logs.today === 1 ? '(today)' : '' }}</option>
            </select>
        </div>
    </div>

    <!-- time of day -->
    <div class="sm:col-span-6 lg:col-span-3 p-2 sm:p-3 mb-1 sm:mb-0 rounded bg-gray-700 text-white text-center">
        <div class="inline-block text-left mx-auto w-full xl:w-2/3">
            <label>Time? <input type="checkbox" v-model="logs.filters.time_filter" @change="logs.updateLogs('time_filter')" class="ml-4 mb-1"></label>
            <div v-if="logs.filters.time_filter">
                <input
                    type="time"
                    v-model="logs.filters.time"
                    @blur="changeTime"
                    class="border-2 border-black rounded text-black p-1 mr-1 w-5/6"
                    :class="{'bg-gray-600': ! logs.filters.time_filter}"
                    :disabled="logs.filters.make_time_now"
                >
                <b>z</b>


                <br>
                <label class="block m-2">Now? <input type="checkbox" v-model="logs.filters.make_time_now" @change="logs.updateLogs('make_time_now')"></label>

                <br>
                Range, <label>half-hour blocks? <input type="checkbox" v-model="logs.filters.half_hour_blocks" @change="changeTime"></label>
                <input type="range" min="1" max="6" v-model="logs.filters.time_range" @change="changeTime">
                <br>
                Between {{ logs.filters.bottom_time_range }}z and {{ logs.filters.top_time_range }}z
            </div>

        </div>
    </div>

    <!-- reception quality -->
    <div class="sm:col-span-6 lg:col-span-3 p-2 sm:p-3 mb-1 sm:mb-0 rounded bg-gray-700 text-white text-center">
        <div class="inline-block text-left mx-auto w-full xl:w-2/3">
            <ReceptionQuality
                :quality="logs.filters.quality"
                @quality="logs.updateFilters('quality', $event)"
            />
        </div>
    </div>

    <!-- all logs or just your logs -->
    <div class="sm:col-span-6 lg:col-span-3 p-2 sm:p-3 mb-1 sm:mb-0 rounded bg-gray-700 text-white text-center">
        <div class="inline-block text-left mx-auto w-full xl:w-2/3">
            <label>Show everyone's logs <input type="checkbox" v-model="logs.filters.log_owners" @change="logs.updateLogs"></label>
        </div>
    </div>

    <!-- comments filter -->
    <div class="sm:col-span-6 lg:col-span-3 p-2 sm:p-3 mb-1 sm:mb-0 rounded bg-gray-700 text-white text-center">
        <div class="inline-block text-left mx-auto w-full xl:w-2/3">
            Search comments:
            <br>
            <input
                type="text"
                v-model="logs.filters.commentSearch"
                class="border-2 border-black p-2 text-black rounded w-full"
                @keyup="logs.updateLogs(filters)"
            >
        </div>
    </div>

        <!-- order by -->
    <div class="sm:col-span-6 lg:col-span-3 p-2 sm:p-3 mb-1 sm:mb-0 rounded bg-gray-700 text-white text-center">
        <div class="inline-block text-left mx-auto w-full xl:w-2/3">
            Order by:
            <br>
            <select
                v-model="logs.filters.order_by"
                class="block border-2 border-black p-2 rounded text-black text-sm sm:text-base w-full"
                @change="logs.updateLogs(filters)"
            >
                <option value="`station_id`, `frequency`">Station, Frequency</option>
                <option value="`datetime`-DESC">Date &lt;-</option>
                <option value="`frequency`">Frequency -&gt;</option>
                <option value="`frequency`-DESC">Frequency &lt;-</option>
                <option value="time(`datetime`)-DESC">Time &lt;-</option>
                <option value="time(`datetime`)">Time -&gt;</option>
            </select>
        </div>
    </div>

</div>

</template>
