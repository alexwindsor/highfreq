<script setup>

import { logs } from '@/logs.js'

</script>

<template>

    <div class="sm:grid sm:grid-cols-2 sm:gap-7 md:gap-10 lg:grid-cols-1 lg:gap-0 border-l border-b border-black p-3 sm:mb-10">

        <!-- order by -->
        <div class="mx-auto mt-36 mb-8 w-full xl:w-5/6">
            <span class="bg-gray-300">Order by:</span>
            <br>
            <select
                v-model="logs.filters.order_by"
                class="block w-3/4 rounded-sm border-2 border-black p-2 text-sm text-black sm:text-base"
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

        <!-- group by -->
        <div class="mx-auto w-full mb-8 xl:w-5/6">
            <label class="bg-gray-300">Group results? <input type="checkbox" v-model="logs.filters.group_results" @change="logs.updateLogs('group_results')"></label>
        </div>

    </div>

</template>
