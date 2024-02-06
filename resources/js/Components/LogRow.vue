<script setup>
import {Link} from '@inertiajs/vue3'
import {base_url} from '@/base_url.js'
import {logs} from '@/logs.js'
import StationsDropDown from '@/Components/StationsDropDown.vue'
import LanguagesDropDown from '@/Components/LanguagesDropDown.vue'


const props = defineProps({
    log: Object,
    user: Object,
    stations: Object,
    languages: Object,
})

var mode = logs.filters.station_type === 1 ? 'am' : 'usb'

const days = ["Sun", "Mon", "Tue", "Wed", "Thu", "Fri", "Sat"];
var months = ["Jan", "Feb", "Mar", "Apr", "May", "Jun", "Jul", "Aug", "Sep", "Oct", "Nov", "Dec"];

function getHumanDate(datetime) {
    let date = new Date(datetime)
    let year = date.getFullYear().toString()

    // make a string for 1st, 22nd, 33rd
    let num = date.getDate().toString()
    num = num.length === 2 ? num.substring(1) : num
    let ordinal = 'th'
    switch (num) {
        case '1':
            ordinal = 'st'
            break
        case '2':
            ordinal = 'nd'
            break
    }

    return days[date.getDay()] + ' ' + months[date.getMonth()] + ' ' + date.getDate() + ordinal + ' \'' + year.substring(2)
}

</script>

<template>

    <div
        class="flex flex-wrap sm:grid mb-3 sm:mb-2 border-t border-black"
        :class="{
        'sm:grid-cols-8 sm:w-auto': !logs.filters.group_results && logs.edit_mode !== log.id,
        'sm:grid-cols-5': logs.filters.group_results,
        'bg-gray-600 sm:grid-cols-4 sm:fixed sm:top-0 sm:left-0 sm:bottom-0 sm:h-full sm:overflow-y-scroll sm-z-20 lg:grid lg:static lg:grid-cols-12 lg:w-full': logs.edit_mode === log.id
    }"
    >
        <!-- frequency -->
        <div class="p-1 text-lg flex items-center text-white"
             :class="{
            'order-1 w-1/4 bg-black sm:w-full sm:col-span-1': logs.edit_mode !== log.id,
            'order-1 w-full sm:col-span-1 sm:order-1 lg:block lg:h-fit lg:col-span-5 lg:w-3/4 lg:p-0 lg:ml-12 text-sm sm:text-lg text-center sm:text-left sm:p-0 sm:m-0 lg:my-auto': logs.edit_mode === log.id
        }"
        >
            <div v-if="logs.edit_mode === log.id" class="whitespace-nowrap">
                <input
                    type="number"
                    min="100"
                    max="30000"
                    maxlength="5"
                    v-model="log.frequency"
                    class="border-2 border-black p-1 rounded text-lg sm:w-4/5 ml-2 mr-1 text-black sm:text-2xl lg:m-0 lg:w-full lg:mr-1"
                >kHz
                <div v-if="logs.edit_errors.frequency" class="text-xs text-red-900">
                    {{ logs.edit_errors.frequency }}
                </div>
            </div>
            <div v-else>
                {{ log.frequency }}<span class="text-xs ml-0.5 sm:ml-1">kHz</span>
            </div>
        </div>

        <!-- station and language -->
        <div class="p-1 text-sm"
             :class="{
            'order-2 w-3/4 sm:w-full border-b sm:border-r border-black sm:col-span-2': logs.edit_mode !== log.id,
            'order-3 w-4/5 sm:order-2 sm:w-1/2 sm:pl-12 sm:col-span-3 md:w-3/4 md:pl-20 md:pr-12 sm:row-span-5 sm:p-3 lg:w-5/6 xl:w-full lg:col-span-7 lg:order-2 lg:pl-16 xl:pl-24': logs.edit_mode === log.id,
        }"
        >
            <div v-if="logs.edit_mode === log.id">
                <StationsDropDown
                    :required="true"
                    :station_id="log.station_id"
                    :station_name="log.station_name"
                    :programmes="true"
                    :station_programme_id="log.station_programme?.id"
                    :station_programme_name="log.station_programme?.name"
                    @stationId="log.station_id = parseInt($event)"
                    @stationName="log.station_name = $event"
                    @programmeId="log.station_programme.id = parseInt($event)"
                    @programmeName="log.station_programme.name = $event"
                    @getProgrammes="logs.getStationProgrammes($event)"
                    class="sm:bg-red-500"
                />

                <LanguagesDropDown
                    :required="true"
                    :language_id="log.language_id"
                    :language_name="log.language_name"
                    @languageId="log.language_id= parseInt($event)"
                    @languageName="log.language_name = $event"
                />
            </div>

            <div v-else>
                {{ log.station_name }} <span class="text-gray-600">{{ log?.station_programme_name }}</span><br>
                <span class="italic text-xs">{{ log.language_name.replace('.', '') }}</span>
            </div>
        </div>

        <!-- count -->
        <div
            v-if="logs.filters.group_results"
            class="text-center p-1 text-xs text-white bg-black order-4 w-1/12 sm:w-full sm:col-span-1"
        >
            {{ log.count }}
        </div>

        <!-- time -->
        <div
            v-if="!logs.filters.group_results && props.log.datetime"
            class="text-sm p-1 sm:col-span-1"
            :class="{
                'order-3 w-2/5 sm:w-full border-b sm:border-r border-black text-center': logs.edit_mode !== log.id,
                'order-2 w-full sm:w-auto sm:order-3 sm:ml-1 text-white lg:col-span-5 lg:order-3 lg:text-left lg:ml-12 lg:p-0 lg:w-11/12 lg:whitespace-nowrap lg:my-auto': logs.edit_mode === log.id
            }"
        >
            <div v-if="logs.edit_mode === log.id">
                <input type="datetime-local" v-model="log.datetime"
                       class="border-2 border-black rounded text-black w-2/3 p-1 ml-2 mr-1 sm:ml-0 sm:w-5/6 sm:text-sm lg:text-2xl"
                ><b>z</b>
            </div>

            <div v-else>
                <div class="inline-block whitespace-nowrap sm:whitespace-normal text-xs sm:text-sm sm:py-2">

                    <span class="whitespace-nowrap sm:whitespace-normal xl:whitespace-nowrap">{{ props.log?.datetime.substring(11, 16) }}z </span>{{ getHumanDate(props.log?.datetime) }}
                </div>
            </div>
        </div>


        <!-- reception quality -->
        <div
            class="text-center text-xs sm:text-sm sm:col-span-1"
            :class="{
                'w-1/5 sm:w-full order-4 border-b sm:border-r border-black': logs.edit_mode !== log.id && ! logs.filters.group_results,
                'w-2/12 sm:w-full order-4 sm:order-3 border-b sm:border-b border-black pb-1': logs.filters.group_results,
                'w-1/5 order-3 sm:order-4 sm:w-full lg:col-span-5 lg:order-4 lg:my-auto lg:w-3/4 lg:text-left lg:ml-12 lg:p-0': logs.edit_mode === log.id
            }"
        >
            <div v-if="logs.edit_mode === log.id" class="grid grid-rows-3 sm:grid-cols-3 sm:grid-rows-1 pr-2 sm:mt-0 h-full sm:h-auto sm:px-6 content-center lg:px-0">
                <label class="m-auto sm:m-1 p-3 sm:p-2 bg-gray-300 text-black border-2 border-black rounded block text-sm">1
                    <input type="radio" v-model="log.quality" value="1" class="m-1"></label>
                <label class="m-auto sm:m-1 p-3 sm:p-2 bg-gray-300 text-black border-2 border-black rounded block text-sm">2
                    <input type="radio" v-model="log.quality" value="2" class="m-1"></label>
                <label class="m-auto sm:m-1 p-3 sm:p-2 bg-gray-300 text-black border-2 border-black rounded block text-sm">3
                    <input type="radio" v-model="log.quality" value="3" class="m-1"></label>
            </div>

            <div v-else class="flex justify-center items-center mt-1.5">
                <svg xmlns="http://www.w3.org/2000/svg" height="1em" viewBox="0 0 576 512"
                     class="mr-1 sm:hidden">
                    <path
                        d="M80.3 44C69.8 69.9 64 98.2 64 128s5.8 58.1 16.3 84c6.6 16.4-1.3 35-17.7 41.7s-35-1.3-41.7-17.7C7.4 202.6 0 166.1 0 128S7.4 53.4 20.9 20C27.6 3.6 46.2-4.3 62.6 2.3S86.9 27.6 80.3 44zM555.1 20C568.6 53.4 576 89.9 576 128s-7.4 74.6-20.9 108c-6.6 16.4-25.3 24.3-41.7 17.7S489.1 228.4 495.7 212c10.5-25.9 16.3-54.2 16.3-84s-5.8-58.1-16.3-84C489.1 27.6 497 9 513.4 2.3s35 1.3 41.7 17.7zM352 128c0 23.7-12.9 44.4-32 55.4V480c0 17.7-14.3 32-32 32s-32-14.3-32-32V183.4c-19.1-11.1-32-31.7-32-55.4c0-35.3 28.7-64 64-64s64 28.7 64 64zM170.6 76.8C163.8 92.4 160 109.7 160 128s3.8 35.6 10.6 51.2c7.1 16.2-.3 35.1-16.5 42.1s-35.1-.3-42.1-16.5c-10.3-23.6-16-49.6-16-76.8s5.7-53.2 16-76.8c7.1-16.2 25.9-23.6 42.1-16.5s23.6 25.9 16.5 42.1zM464 51.2c10.3 23.6 16 49.6 16 76.8s-5.7 53.2-16 76.8c-7.1 16.2-25.9 23.6-42.1 16.5s-23.6-25.9-16.5-42.1c6.8-15.6 10.6-32.9 10.6-51.2s-3.8-35.6-10.6-51.2c-7.1-16.2 .3-35.1 16.5-42.1s35.1 .3 42.1 16.5z"/>
                </svg>{{ Math.round(log.quality * 100) / 100 }}/3
                <br>
            </div>
        </div>

        <!-- logged by -->
        <div
            v-if="!logs.filters.group_results"
            class="w-2/5 sm:w-full p-1 text-sm flex justify-around items-center sm:col-span-1"
            :class="{
                'order-5 border-b sm:border-r border-black': logs.edit_mode !== log.id,
                'order-6 sm:col-span-1 sm:order-6 sm:w-full lg:col-span-5 lg:order-6': logs.edit_mode === log.id
            }"
        >
            {{ log.user_id === user.id ? '' : log.user_name }}

            <button
                v-if="log.user_id === user.id"
                class="bg-blue-500 border border-black rounded-sm text-sm sm:text-base px-1 sm:px-2 sm:py-1 text-white sm:m-1"
                :class="{
                    'hidden': logs.edit_mode && logs.edit_mode !== log.id,
                    'sm:text-xs': logs.edit_mode !== log.id,
                    }"
                @click="logs.editLog(log)"
                :disabled="logs.edit_mode && logs.edit_mode !== log.id"
                onclick="window.scrollTo(0, window.scrollY);"
            >
                {{ logs.edit_mode === log.id ? 'save' : 'edit' }}
            </button>

            <button
                v-if="log.user_id === user.id"
                class="bg-red-500 border border-black rounded-sm text-sm sm:text-base text-white px-1 sm:px-2 sm:py-1 sm:m-1"
                :class="{
                    'sm:text-xs': logs.edit_mode !== log.id,
                    }"
                type="button"
                @click="logs.deleteLog(log.id, log.frequency, log.station_name, log.language_name)"
            >
                {{ logs.edit_mode === log.id ? 'cancel' : 'delete' }}
            </button>
        </div>

        <!-- comments -->
        <div
            v-if="!logs.filters.group_results"
            class="w-full p-1 text-xs"
            :class="{
                'hidden sm:block sm:col-span-2': log.comment === null && logs.edit_mode !== log.id,
                'order-6 border-b sm:col-span-2 sm:border-r border-black bg-gray-300 sm:bg-white': logs.edit_mode !== log.id,
                'order-3 ml-2 mr-6 my-4 sm:m-0 sm:p-2 sm:col-span-1 sm:order-5 sm:w-full lg:col-span-5 lg:order-5 lg:ml-12 lg:p-0': logs.edit_mode === log.id
            }"
        >
            <textarea v-if="logs.edit_mode === log.id" v-model="log.comment" class="border-2 border-black p-2 text-black rounded w-full sm:h-full lg:w-3/4" placeholder="comments"></textarea>
            <div v-if="logs.edit_mode !== log.id" class="" v-html="log?.comment ?? ''.replace('\n', '<br><br>')"></div>
        </div>

        <!-- buttons -->
        <div
            class="text-xs sm:hidden"
            :class="{
                'w-full text-black order-7 pr-1 flex justify-end gap-2 mr-2': logs.edit_mode !== log.id && ! logs.filters.group_results,
                'w-9/12 order-5 text-center border-b border-black pt-1': logs.filters.group_results,
                'w-full text-white mb-4 order-7 pr-1 flex justify-end gap-2 mr-2': logs.edit_mode === log.id
            }"
        >
            <button
                class="inline-block underline"
                @click="logs.duplicateLog(log.frequency, log.station_id, log.station_name, log.station_programme_id, log.station_programme_name, log.language_id, log.language_name)"
            >
                duplicate log
            </button>
            |
            <a
                v-if="logs.filters.station_type === 1"
                class="inline-block underline"
                :href="base_url + 'shortWaveInfoData?frequency=' + log.frequency + '&broadcasting_now=false&station_id=' + log.station_id + '&language_id=' + log.language_id"
                target="_blank"
            >
                short-wave.info
            </a>
            |
            <a
                class="inline-block underline"
                :href="'http://websdr.ewi.utwente.nl:8901/?tune=' + log.frequency + mode"
                target="_blank"
            >
                websdr
            </a>
        </div>


        <!-- show buttons here if in sm to lg screen and results not grouped -->
        <div
            class="hidden sm:block text-xs"
            :class="{
                'sm:col-span-8 sm:order-8': ! logs.filters.group_results && logs.edit_mode !== log.id,
                'sm:col-span-5 sm:order-6': logs.filters.group_results,
                'sm:order-7 sm:col-span-4 text-white p-2 lg:block lg:col-span-12': logs.edit_mode === log.id
            }"
        >
            <button
                class="inline-block underline"
                @click="logs.duplicateLog(log.frequency, log.station_id, log.station_name, log.station_programme_id, log.station_programme_name, log.language_id, log.language_name)"
            >
                duplicate log
            </button>
            |
            <a
                v-if="logs.filters.station_type === 1"
                class="inline-block underline"
                :href="base_url + 'shortWaveInfoData?frequency=' + log.frequency + '&broadcasting_now=false&station_id=' + log.station_id + '&language_id=' + log.language_id"
                target="_blank"
            >
                short-wave.info
            </a>
            |
            <a
                class="inline-block underline"
                :href="'http://websdr.ewi.utwente.nl:8901/?tune=' + log.frequency + mode"
                target="_blank"
            >
                websdr
            </a>
        </div>

    </div>


</template>
