<script setup>

import { logs } from '@/logs.js'
import StationsDropDown from '@/Components/StationsDropDown.vue'
import LanguagesDropDown from '@/Components/LanguagesDropDown.vue'
import ReceptionQuality from '@/Components/ReceptionQuality.vue'

const props = defineProps({
    stations: Object,
    languages: Object, 
})

</script>

<template>

<form @submit.prevent="logs.addLog">

<div class="sm:grid sm:grid-cols-12 sm:gap-1 md:gap-2 lg:gap-4 mb-10">
    <!-- frequency -->
    <div class="sm:col-span-6 lg:col-span-3 p-2 sm:p-3 mb-1 sm:mb-0 rounded bg-gray-700 text-white text-center">
        <div class="inline-block text-left mx-auto w-full xl:w-2/3">

            Frequency:
            <br>

            <input type="number" max="30000" min="100" maxlength="5" v-model="logs.newlog.frequency" class="border-2 border-black rounded p-1 text-black inline-block w-3/5 sm:inline">

            <!-- button to check frequency with sw-info -->
            <button 
                @click="logs.checkFrequency" 
                type="button" 
                class="border border-white bg-gray-800 px-2 py-1 ml-2 rounded"
            >CHECK</button>

            <!-- <a v-if="logs.newlog.frequency > 100" class="block text-xs mt-1 underline" :href="base_url + 'shortWaveInfoData?frequency=' + logs.newlog.frequency + '&broadcasting_now=false'" target="_blank">[s-w.info]</a> -->

            <!-- error: missing frequency -->
            <div v-if="logs.newlog.errors.frequency" class="text-sm text-red-300">Please add the frequency</div>

            <!-- frequencies matched from sw-info -->
            <div v-if="logs.swinfoMatches.length > 0" class="border border-gray-500 p-2 rounded my-5 text-sm">
                Shortwave.info found {{ logs.swinfoMatches.length }} stations on {{ logs.swinfoMatches[0].frequency }}kHz
                <div 
                    v-for="n in logs.swinfoMatches.length"
                    :key="n" 
                    class="text-xs ml-2 m-1 p-1 bg-gray-800 rounded-sm hover:cursor-move"
                    @click="logs.addSwinfoMatch(n-1)">
                    {{ logs.swinfoMatches[n-1].station.name }}<span v-if="logs.swinfoMatches[n-1].programme" class="text-gray-400">&nbsp;{{ logs.swinfoMatches[n-1].programme.name }}</span>,
                    <br>
                    {{ logs.swinfoMatches[n-1].language.name }}
                </div>
            </div>

        </div>
    </div>

    <!-- time of day UTC -->
    <div class="sm:col-span-6 lg:col-span-3 p-2 sm:p-3 mb-1 sm:mb-0 rounded bg-gray-700 text-white text-center">
        <div class="inline-block text-left mx-auto w-full xl:w-2/3 whitespace-nowrap">
            Time:
            <br>
            <label>Now? <input type="checkbox" v-model="logs.newlog.make_time_now"></label>
            <br>
            <input 
                type="datetime-local" 
                v-model="logs.newlog.datetime" 
                class="border-2 border-black rounded text-black p-1 mr-2" 
                :disabled="logs.newlog.make_time_now"
            >
            <b>z</b>

            <!-- error: missing time -->
            <div v-if="logs.newlog.errors.time" class="text-sm text-red-300">Please add the time</div>
        </div>
    </div>

    <!-- station -->
    <div class="sm:col-span-6 lg:col-span-3 p-2 sm:p-3 mb-1 sm:mb-0 rounded bg-gray-700 text-white text-center">
        <div class="inline-block text-left mx-auto w-full xl:w-2/3">
            <StationsDropDown 
                :stations="stations" 
                :required="true" 
                :station_id="logs.newlog.station_id"
                :station_name="logs.newlog.station_name" 
                :programmes="true"
                :programme_id="logs.newlog.programme_id"
                :programme_name="logs.newlog.programme_name" 
                @stationId="logs.newlog.station_id = parseInt($event)"
                @stationName="logs.newlog.station_name = $event"
                @programmeId="logs.newlog.programme_id = parseInt($event)"
                @programmeName="logs.newlog.programme_name = $event"
                @getProgrammes="logs.getStationProgrammes($event)"
            />
            <!-- error: station -->
            <div v-if="logs.newlog.errors.station_name || logs.newlog.errors.station_id" class="text-sm text-red-300">Please choose a station</div>
        </div>
    </div>

    <!-- language -->
    <div class="sm:col-span-6 lg:col-span-3 p-2 sm:p-3 mb-1 sm:mb-0 rounded bg-gray-700 text-white text-center">
        <div class="inline-block text-left mx-auto w-full xl:w-2/3">
            <LanguagesDropDown
                :languages="languages" 
                :required="true"
                :language_id="logs.newlog.language_id"
                :language_name="logs.newlog.language_name"
                @languageId="logs.newlog.language_id = parseInt($event)"
                @languageName="logs.newlog.language_name = $event"
            />
            <!-- error: language -->
            <div v-if="logs.newlog.errors.language_id" class="text-sm text-red-300">Please choose the language</div>
        </div>
    </div>

    <!-- reception quality -->
    <div class="sm:col-span-6 lg:col-span-4 p-2 sm:p-3 mb-1 sm:mb-0 rounded bg-gray-700 text-white text-center">
        <div class="inline-block text-left mx-auto w-full xl:w-2/3">
            <ReceptionQuality 
                :quality="logs.newlog.quality" 
                @quality="logs.newlog.quality = $event" 
            />
            <!-- error: quality -->
            <div v-if="logs.newlog.errors.quality" class="text-sm text-red-300">Please choose a reception quality</div>
        </div>
    </div>

    <!-- comments -->
    <div class="sm:col-span-6 lg:col-span-4 p-2 sm:p-3 mb-1 sm:mb-0 rounded bg-gray-700 text-white text-center">
        <div class="inline-block text-left mx-auto w-full xl:w-2/3">
            Comments:<br>
            <textarea v-model="logs.newlog.comment" class="border-2 border-black p-2 text-black rounded w-full"></textarea>
        </div>
    </div>

    <!-- submit -->
    <div class="sm:col-span-12 lg:col-span-4 p-2 sm:p-3 mb-1 sm:mb-0 rounded bg-gray-700 text-white">
        <button class="block border border-white bg-gray-800 text-xl p-2 rounded w-full h-full" type="submit">ADD LOG</button>
    </div>
</div>
</form>


</template>