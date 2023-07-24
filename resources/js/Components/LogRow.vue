<script setup>
import { reactive, ref } from "vue";
import { logs } from '@/logs.js'
import StationsDropDown from '@/Components/StationsDropDown.vue'
import LanguagesDropDown from '@/Components/LanguagesDropDown.vue'

const props = defineProps({
    log: Object,
    user: Object,
    stations: Object,
    languages: Object,
})

const date = new Date(props.log.datetime)
var month = date.getMonth()
month++
var log_date = date.getHours().toString().padStart(2, '0') + ':' + date.getMinutes().toString().padStart(2, '0')
var log_time = date.getDate().toString().padStart(2, '0') + '/' + month.toString().padStart(2, '0') + '/' + date.getFullYear()

</script>

<template>

<div class="grid grid-cols-11 gap-1 mb-3">
    <!-- frequency -->
    <div class="col-span-4 lg:col-span-2 bg-gray-500 text-white rounded-sm px-1 lg:px-3 py-3 lg:py-4 text-center text-xl">
        <div v-if="logs.edit_mode === log.id">
            <input type="number" min="100" max="30000" maxlength="5" v-model="log.frequency" class="border border-black p-1 rounded text-sm w-full text-black">
            <div v-if="logs.edit_errors.frequency" class="text-xs text-red-900">{{ logs.edit_errors.frequency }}</div>
        </div>
        <div v-else>{{ props.log.frequency }}</div>
    </div>

    <!-- station and language -->
    <div class="col-span-8 lg:col-span-3 bg-gray-500 text-white rounded-sm p-1 lg:px-4 lg:py-1">
        <div v-if="logs.edit_mode === log.id">
            <StationsDropDown 
                :stations="stations" 
                :required="true" 
                :station_id="log.station.id"
                :station_name="log.station.name" 
                :programmes="true"
                :programme_id="log.station_programme?.id"
                :programme_name="log.station_programme?.name" 
                @stationId="log.station.id = parseInt($event)"
                @stationName="log.station.name = $event"
                @programmeId="log.station_programme.id = parseInt($event)"
                @programmeName="log.station_programme.name = $event"
                @getProgrammes="logs.getStationProgrammes($event)"
            />
            <LanguagesDropDown
                :languages="languages" 
                :required="true"
                :language_id="log.language.id"
                :language_name="log.language.name"
                @languageId="log.language.id= parseInt($event)"
                @languageName="log.language.name = $event"
            />
        </div>

        <div v-else>
            {{ log.station.name }} <span class="text-gray-300">{{ log.station_programme?.name }}</span><br>
            <span class="text-lg">{{ log.language.name }}</span>
        </div>
    </div>

    <!-- time -->
    <div class="col-span-12 lg:col-span-2 bg-gray-300 rounded-sm py-1 lg:px-4 lg:py-1 text-center">
        <div v-if="logs.edit_mode === log.id">
            <input type="datetime-local" v-model="log.datetime" class="border-2 border-black rounded text-black p-1 mt-5 mr-1"><b>z</b>
        </div>

        <div v-else>
            <div class="inline-block whitespace-nowrap text-left text-sm">
                {{ log_date }}
                <br>
                {{ log_time }}
            </div>
        </div>
    </div>

    <!-- reception quality -->
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

    <!-- logged by -->
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

            <button 
                class="bg-red-400 text-black border border-black rounded text-lg m-1 px-1" 
                type="button" 
                @click="logs.deleteLog(log.id, log.frequency, log.station.name, log.language.name)
"
            >
            {{ logs.edit_mode === log.id ? 'cancel' : 'delete' }}
            </button>
        </div>
    </div>

    <!-- comments -->
    <div class="col-span-12 lg:col-span-2 bg-gray-300 rounded-sm p-2">
        <textarea v-if="logs.edit_mode === log.id" v-model="log.comment" class="border-2 border-black p-2 text-black rounded my-3"></textarea>
        <div v-if="logs.edit_mode !== log.id" class="col-span-12 lg:col-span-2 bg-gray-300 rounded-sm p-2 w-full h-full" v-html="log?.comment ?? ''.replace('\n', '<br><br>')"></div>
    </div>

</div>

</template>