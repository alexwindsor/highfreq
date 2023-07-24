<script setup>
import { Head, Link } from '@inertiajs/vue3'
import { onMounted, reactive, ref, watch } from 'vue'
import { base_url } from '@/base_url.js'
import { logs } from '@/logs.js'
import Layout from '@/Pages/Layout.vue'
import AddLog from '@/Components/AddLog.vue'
import LogFilters from '@/Components/LogFilters.vue'
import LogRow from '@/Components/LogRow.vue'

// import LogFilters from '@/Components/LogFilters.vue'

// import Pagination from '@/Components/Pagination.vue'


const props = defineProps({
    stations: Object,
    languages: Object,
    user: Object
})

// var time_utc = ref('')

function makeTime() {

    var datetime = new Date()
    var year = datetime.getUTCFullYear()
    var month = datetime.getUTCMonth()
    month++
    month = month.toString().padStart(2, '0')
    var day = datetime.getUTCDate().toString().padStart(2, '0')

    var hour = datetime.getUTCHours().toString().padStart(2, '0')
    var minute = datetime.getUTCMinutes().toString().padStart(2, '0')
    var second = datetime.getSeconds().toString().padStart(2, '0')

    if (logs.newlog.make_time_now) {
        logs.newlog.datetime = year + '-' + month + '-' + day + 'T' + hour + ':' + minute
    }
    if (logs.filters.make_time_now) {
        logs.filters.time = hour + ':' + minute + ':' + second
    }
}

setInterval(makeTime, 1000)

onMounted(() => {
    makeTime
    logs.updateLogs()
})

</script>

<template>

<Head title="HF / logs" />
<Layout page="Logs" :user="user">


    <button 
        class="block text-xl border border-l border-black rounded-sm p-2 mb-2" 
        @click="logs.showHideAddLog = ! logs.showHideAddLog"
    >
        Add Log
    </button>

    <AddLog 
        v-if="logs.showHideAddLog" 
        :stations="props.stations" 
        :languages="props.languages" 
    />

    <button 
        class="block text-xl border border-l border-black rounded-sm p-2 mb-2" 
        @click="logs.showHideLogFilters = ! logs.showHideLogFilters"
    >
    Filter Logs
    </button>

    <LogFilters
        v-if="logs.showHideLogFilters" 
        :stations="props.stations" 
    />


    <br><br>

<div v-for="log in logs.logs.data" class="mb-2">
    <!-- {{ log.frequency }} | {{ log.datetime }} | {{ log.station.name }} | {{ log.station_programme?.name ?? 'n/a' }} | {{ log.language.name }} | {{ log.comment }} | {{ log.quality }} -->
    <!-- {{ log }} -->
</div>


    <!-- ////////////////////////////////// -->
    <!-- log table -->
    <div class="my-4 sm:my-8">
        {{ logs.logs.total }} logs found
        <div v-if="logs.logs.total > logs.logs.per_page">Page {{ logs.logs.current_page }} of {{ logs.logs.last_page }}</div>
        <div v-if="logs.logs.total > logs.logs.per_page">Showing logs {{ ((logs.logs.current_page * logs.logs.per_page) - logs.logs.per_page) + 1 }} to {{ logs.logs.current_page * logs.logs.per_page }}</div>
    </div>

    <div class="hidden lg:grid lg:grid-cols-11 lg:gap-1 lg:mb-1">
        <div class="col-span-2 border-b border-r border-black p-4">
            Frequency:
        </div>

        <div class="col-span-3 border-b border-r border-black p-4">
            Station and Language:
        </div>

        <div class="col-span-2 border-b border-r border-black p-4">
            Time:
        </div>

        <div class="col-span-1 border-b border-r border-black p-4">
            Reception<br>Quality:
        </div>

        <div class="col-span-1 border-b border-r border-black p-4">
            Logged By:
        </div>

        <div class="col-span-2 border-b border-black p-4">
            Comments:
        </div>
    </div>

    <LogRow 
        v-for="log in logs.logs.data" 
        :log="log" 
        :user="user" 
        :stations="stations"
        :languages="languages"
    />



</Layout>

</template>