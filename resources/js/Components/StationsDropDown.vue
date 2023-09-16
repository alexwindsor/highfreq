<script setup>
import { ref, watch } from 'vue'
import { logs } from '@/logs.js'

const props = defineProps({
    required: Boolean,
    station_id: Number,
    station_name: String,
    programmes: Boolean,
    station_programme_id: Number,
    station_programme_name: String,
})

const emit = defineEmits(['stationId', 'stationName', 'programmeId', 'programmeName', 'getProgrammes'])

var station_id = ref(props.station_id)
var station_name = ref(props.station_name)
var station_programme_id = ref(props.station_programme_id)
var station_programme_name = ref(props.station_programme_name)

watch(props, () => {
    station_id = props.station_id
    station_name = props.station_name
    station_programme_id = props.station_programme_id
    station_programme_name = props.station_programme_name
})

function pickStation(id, name) {

    station_id = id
    emit('stationId', id)
    station_name = name
    emit('stationName', name)

    if (props.programmes) emit('getProgrammes', id)
}

function type(station_or_programme) {

    if (station_or_programme === 'station') {
        emit('stationId', 0)
        emit('stationName', station_name)
        emit('programmeId', 0)
        emit('programmeName', '')
        logs.pickedStationProgrammes = []
    }

    else if (station_or_programme === 'programme') {
        emit('programmeId', 0)
        emit('programmeName', station_programme_name)
    }
}

function pickProgramme(id, name) {
    emit('programmeId', id)
    emit('programmeName', name)
}

// watch(station_name, () => {
//     type('station')
// })


</script>

<template>


<span class="bg-gray-300 ml-12 sm:ml-0 sm:w-full">Station:</span><br>
<input
    type="text"
    v-model="station_name"
    class="border-2 border-black rounded-sm text-black text-sm p-1 w-3/4 ml-12 sm:ml-0 sm:w-full"
    :placeholder="required ? '' : 'All Stations'"
    @keyup="type('station')"
>

<div class="border-2 border-black rounded-sm h-16 bg-white text-black text-sm overflow-y-scroll w-3/4 ml-12 sm:ml-0 sm:w-full">
    <div v-for="station in logs.stations" class="cursor-pointer">
        <span
            v-if="station.name.toLowerCase().includes(station_name.toLowerCase())"
            class="inline-block w-full"
            :class="{'bg-black text-white': station.id === station_id}"
            @click="pickStation(station.id, station.name)"
            >
            {{ station.name }}
        </span>
    </div>
</div>




<div
    class="mt-4 ml-12 w-3/4 sm:ml-0 sm:w-full mb-4"
    :class="{'hidden': station_name.length === 0 || props.programmes === false}"
>

    <span class="bg-gray-300">Programme:</span><br>

    <input
        type="text"
        v-model="station_programme_name"
        class="border-2 border-black rounded-sm text-black text-sm w-full"
        placeholder="n/a"
        @keyup="type('programme')"
    >

    <div class="border-2 border-black rounded-sm h-16 bg-white text-black text-sm overflow-y-scroll w-full">
        <div v-for="pickedStationProgramme in logs.pickedStationProgrammes" class="cursor-pointer">
            <span
                v-if="pickedStationProgramme.name.toLowerCase().includes(station_programme_name?.toLowerCase())"
                class="inline-block w-full"
                :class="{'bg-black text-white': pickedStationProgramme.id === station_programme_id}"
                @click="pickProgramme(pickedStationProgramme.id, pickedStationProgramme.name)"
                >
                {{ pickedStationProgramme.name }}
            </span>
        </div>
    </div>
</div>

</template>
