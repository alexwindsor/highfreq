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
  logs.updateTimeRange()
  logs.updateLogs()

}

</script>

<template>

<div class="mb-10 sm:grid sm:grid-cols-12 sm:gap-1 md:gap-2 lg:gap-4">
    <!-- frequency -->
    <div class="mb-1 rounded bg-gray-700 p-2 text-center text-white sm:col-span-6 sm:mb-0 sm:p-3 lg:col-span-3">
        <div class="mx-auto inline-block w-full text-left xl:w-2/3">
            Frequency:
            <br>
            <input
                type="number"
                v-model="logs.filters.frequency"
                min="100"
                max="30000"
                class="block w-full rounded border-2 border-black p-1 text-black"
                @keyup="logs.updateLogs('frequency')"
            >
        </div>
    </div>

    <!-- station -->
    <div class="mb-1 rounded bg-gray-700 p-2 text-center text-white sm:col-span-6 sm:mb-0 sm:p-3 lg:col-span-3">
        <div class="mx-auto inline-block w-full text-left xl:w-2/3">
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
        <div class="mb-1 rounded bg-gray-700 p-2 text-center text-white sm:col-span-6 sm:mb-0 sm:p-3 lg:col-span-3">
        <div class="mx-auto inline-block w-full text-left xl:w-2/3">
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
    <div class="mb-1 rounded bg-gray-700 p-2 text-center text-white sm:col-span-6 sm:mb-0 sm:p-3 lg:col-span-3">
        <div class="mx-auto inline-block w-full text-left xl:w-2/3">
            Weekday:
            <br>
            <select
                v-model="logs.filters.weekday"
                class="block w-full rounded border-2 border-black p-2 text-sm text-black sm:text-base"
                @change="logs.updateLogs"
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
    <div class="mb-1 rounded bg-gray-700 p-2 text-center text-white sm:col-span-6 sm:mb-0 sm:p-3 lg:col-span-3">
        <div class="mx-auto inline-block w-full text-left xl:w-2/3">
            <label>Time? <input type="checkbox" v-model="logs.filters.time_filter" @change="logs.updateLogs('time_filter')" class="mb-1 ml-4"></label>
            <div v-if="logs.filters.time_filter">
                <input
                    type="time"
                    v-model="logs.filters.time"
                    @blur="changeTime"
                    class="mr-1 w-5/6 rounded border-2 border-black p-1 text-black"
                    :class="{'bg-gray-600': ! logs.filters.time_filter}"
                    :disabled="logs.filters.make_time_now"
                >
                <b>z</b>


                <br>
                <label class="m-2 block">Now? <input type="checkbox" v-model="logs.filters.make_time_now" @change="logs.updateLogs('make_time_now')"></label>

                <br>
                Range, <label>half-hour blocks? <input type="checkbox" v-model="logs.filters.half_hour_blocks" @change="changeTime"></label>
                <input type="range" min="1" max="6" v-model="logs.filters.time_range" @change="changeTime">
                <br>
                Between {{ logs.filters.bottom_time_range }}z and {{ logs.filters.top_time_range }}z
            </div>

        </div>
    </div>

    <!-- reception quality -->
    <div class="mb-1 rounded bg-gray-700 p-2 text-center text-white sm:col-span-6 sm:mb-0 sm:p-3 lg:col-span-3">
        <div class="mx-auto inline-block w-full text-left xl:w-2/3">
            <ReceptionQuality
                :quality="logs.filters.quality"
                @quality="logs.updateFilters('quality', $event)"
            />
        </div>
    </div>

    <!-- all logs or just your logs -->
    <div class="mb-1 rounded bg-gray-700 p-2 text-center text-white sm:col-span-6 sm:mb-0 sm:p-3 lg:col-span-3">
        <div class="mx-auto inline-block w-full text-left xl:w-2/3">
            <label>Show everyone's logs <input type="checkbox" v-model="logs.filters.log_owners" @change="logs.updateLogs"></label>
        </div>
    </div>

    <!-- comments filter -->
    <div v-if="! logs.filters.group_results" class="mb-1 rounded bg-gray-700 p-2 text-center text-white sm:col-span-6 sm:mb-0 sm:p-3 lg:col-span-3">
        <div class="mx-auto inline-block w-full text-left xl:w-2/3">
            Search comments:
            <br>
            <input
                type="text"
                v-model="logs.filters.commentSearch"
                class="w-full rounded border-2 border-black p-2 text-black"
                @keyup="logs.updateLogs"
            >
        </div>
    </div>

    <!-- order by -->
    <div class="mb-1 rounded bg-gray-700 p-2 text-center text-white sm:col-span-6 sm:mb-0 sm:p-3 lg:col-span-3">
        <div class="mx-auto inline-block w-full text-left xl:w-2/3">
            Order by:
            <br>
            <select
                v-model="logs.filters.order_by"
                class="block w-full rounded border-2 border-black p-2 text-sm text-black sm:text-base"
                @change="logs.updateLogs"
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

  <!-- group by -->
  <div class="mb-1 rounded bg-gray-700 p-2 text-center text-white sm:col-span-6 sm:mb-0 sm:p-3 lg:col-span-3">
    <div class="mx-auto inline-block w-full text-left xl:w-2/3">
      <label>Group results? <input type="checkbox" v-model="logs.filters.group_results" @change="logs.updateLogs('group_results')"></label>
    </div>
  </div>

</div>

</template>
