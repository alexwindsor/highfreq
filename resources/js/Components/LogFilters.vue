<script setup>

import { logs } from '@/logs.js'
import StationsDropDown from '@/Components/StationsDropDown.vue'
import ReceptionQuality from '@/Components/ReceptionQuality.vue'

const props = defineProps({
    stations: Object,
})

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
                @keyup="logs.updateLogs(filters)"
            >
        </div>
    </div>

    <!-- station -->
    <div class="sm:col-span-6 lg:col-span-3 p-2 sm:p-3 mb-1 sm:mb-0 rounded bg-gray-700 text-white text-center">
        <div class="inline-block text-left mx-auto w-full xl:w-2/3">
            <StationsDropDown 
                :stations="stations" 
                :required="false" 
                :station_id="logs.filters.station_id"
                :station_name="logs.filters.station_name" 
                :programmes="false"
                :programme_id="null"
                :programme_name="null" 
                @stationId="logs.updateFilters('station_id', $event)"
                @stationName="logs.filters.station_name = $event"
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

    <!-- time of day -->
    <div class="sm:col-span-6 lg:col-span-3 p-2 sm:p-3 mb-1 sm:mb-0 rounded bg-gray-700 text-white text-center">
        <div class="inline-block text-left mx-auto w-full xl:w-2/3">
            <label>Time? <input type="checkbox" v-model="logs.filters.time_filter" @change="logs.updateLogs" class="ml-4 mb-1"></label>
            <div v-if="logs.filters.time_filter">
                <input 
                    type="time" 
                    v-model="logs.filters.time" 
                    @keyup="logs.updateLogs"
                    class="border-2 border-black rounded text-black p-1 mr-1 w-5/6" 
                    :class="{'bg-gray-600': ! logs.filters.time_filter}" 
                    :disabled="logs.filters.make_time_now"
                >
                <b>z</b><br>
                <label>Now? <input type="checkbox" v-model="logs.filters.make_time_now" @change="logs.updateLogs"></label>
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
            <label>Your logs <input type="radio" v-model="logs.filters.logOwners" value="0" @change="logs.updateLogs(filters)"></label> or <label>all logs <input type="radio" v-model="logs.filters.logOwners" value="1" @change="logs.updateLogs(filters)"></label>
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

</div>

</template>