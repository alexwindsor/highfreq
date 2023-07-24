<script setup>
import { ref, watch } from 'vue'
import { logs } from '@/logs.js'

const props = defineProps({
    stations: Object,
    required: Boolean,
    station_id: Number,
    station_name: String,
    programmes: Boolean,
    programme_id: Number,
    programme_name: String,
})

const emit = defineEmits(['stationId', 'stationName', 'programmeId', 'programmeName', 'getProgrammes'])

var station_id = ref(props.station_id)
var station_name = ref(props.station_name)
var programme_id = ref(props.programme_id)
var programme_name = ref(props.programme_name)

watch(props, () => {
    station_id = props.station_id
    station_name = props.station_name
    programme_id = props.programme_id
    programme_name = props.programme_name
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
        emit('programmeName', programme_name)
    } 
}

function pickProgramme(id, name) {
    emit('programmeId', id)
    emit('programmeName', name)
}

</script>

<template>

Station:<br>

<input 
    type="text" 
    v-model="station_name" 
    class="border-2 border-black rounded text-black text-sm p-1 mr-2 w-full" 
    :placeholder="required ? '' : 'All Stations'"
    @keyup="type('station')"
>

<div class="border-2 border-black rounded h-16 bg-white text-black text-sm overflow-y-scroll">
    <div v-for="station in stations" class="cursor-pointer">
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

<div :class="{'hidden': station_name.length === 0 || props.programmes === false}">
    Programme:<br>

    <input 
        type="text" 
        v-model="programme_name" 
        class="border-2 border-black rounded text-black text-sm p-1 mr-2 w-full" 
        placeholder="n/a"
        @keyup="type('programme')"
    >

    <div class="border-2 border-black rounded h-16 bg-white text-black text-sm overflow-y-scroll">
        <div v-for="pickedStationProgramme in logs.pickedStationProgrammes" class="cursor-pointer">
            <span 
                v-if="pickedStationProgramme.name.toLowerCase().includes(programme_name.toLowerCase())"
                class="inline-block w-full"
                :class="{'bg-black text-white': pickedStationProgramme.id === programme_id}"
                @click="pickProgramme(pickedStationProgramme.id, pickedStationProgramme.name)"
                >
                {{ pickedStationProgramme.name }}
            </span>
        </div>
    </div>
</div>

</template>