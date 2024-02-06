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

<div class="sm:grid sm:grid-cols-2 sm:gap-7 md:gap-10 lg:grid-cols-1 lg:gap-0 border-l border-b border-black p-4 sm:mb-10">

  <!-- frequency -->
  <div class="mx-auto my-8 w-full xl:w-5/6">
    <span class="inline-block bg-gray-300">Frequency:</span>
    <br>
    <input
        type="number"
        v-model="logs.filters.frequency"
        min="100"
        max="30000"
        class="block w-full rounded-sm border-2 border-black p-1 text-black"
        @keyup="logs.updateLogs('frequency')"
    >
  </div>

  <!-- time of day -->
  <div class="mx-auto mb-8 w-full xl:w-5/6">
    <label class="inline-block bg-gray-300 mr-3 mb-1.5">
        Time?
        <input type="checkbox" v-model="logs.filters.time_filter" @change="logs.updateLogs('time_filter')" class="">
    </label>
    <div v-if="logs.filters.time_filter" class="ml-4">
      <input
          type="time"
          v-model="logs.filters.time"
          @blur="changeTime"
          class="rounded-sm border-2 border-black p-1 text-black w-24 my-2"
          :class="{'bg-gray-600': ! logs.filters.time_filter}"
          :disabled="logs.filters.make_time_now"
      >
      <b>z</b>


      <br>
      <label class="inline-block bg-gray-300 my-1.5">Now? <input type="checkbox" v-model="logs.filters.make_time_now" @change="logs.updateLogs('make_time_now')"></label>

      <br>
      Range, <label class="inline-block bg-gray-300 my-1.5">half-hour blocks? <input type="checkbox" v-model="logs.filters.half_hour_blocks" @change="changeTime"></label>
      <br>
      <input type="range" min="1" max="6" v-model="logs.filters.time_range" @change="changeTime">
      <br>
      Between {{ logs.filters.bottom_time_range }}z and {{ logs.filters.top_time_range }}z
    </div>
  </div>

  <!-- station -->
  <div class="mx-auto w-full mb-8 xl:w-5/6">
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

  <!-- language -->
  <div class="mx-auto w-full mb-8 xl:w-5/6">
    <LanguagesDropDown
        :required="false"
        :language_id="logs.filters.language_id"
        :language_name="logs.filters.language_name"
        @languageId="logs.updateFilters('language_id', $event)"
        @languageName="logs.filters.language_name = $event"
    />
  </div>

  <!-- day of week -->
  <div class="mx-auto w-full mb-8 xl:w-5/6">
    <span class="bg-gray-300">Weekday:</span>
    <br>
    <select
        v-model="logs.filters.weekday"
        class="block w-3/4 rounded-sm border-2 border-black p-2 text-sm text-black sm:text-base"
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

  <!-- reception quality -->
  <div class="mx-auto w-full mb-8 xl:w-5/6">
    <ReceptionQuality
        :quality="logs.filters.quality"
        @quality="logs.updateFilters('quality', $event)"
    />
  </div>

  <!-- all logs or just your logs -->
  <div class="mx-auto w-full mb-8 xl:w-5/6">
    <label class="bg-gray-300">Show everyone's logs <input type="checkbox" v-model="logs.filters.log_owners" @change="logs.updateLogs"></label>
  </div>

  <!-- comments filter -->
  <div v-if="! logs.filters.group_results" class="mx-auto w-full mb-8 xl:w-5/6">
    <span class="bg-gray-300">Search comments:</span>
    <br>
    <input
        type="text"
        v-model="logs.filters.commentSearch"
        class="w-full rounded-sm border-2 border-black p-2 text-black"
        @keyup="logs.updateLogs"
    >
  </div>

</div>

</template>
