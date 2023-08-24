<script setup>
import { Link } from '@inertiajs/vue3'
import { base_url } from '@/base_url.js'
import { logs } from '@/logs.js'
import StationsDropDown from '@/Components/StationsDropDown.vue'
import LanguagesDropDown from '@/Components/LanguagesDropDown.vue'

const props = defineProps({
    log: Object,
    user: Object,
    stations: Object,
    languages: Object,
})

var mode = logs.filters.station_type === 1 ? 'am' : 'usb'

</script>

<template>

<div class="grid grid-cols-12 gap-1 mb-3 border-t border-black">
    <!-- frequency -->
    <div class="sm:text-base lg:text-xl text-center p-2"
       :class="{
          'lg:col-span-2 xl:col-span-1': !logs.filters.group_results,
          'col-span-2': logs.filters.group_results}"
    >
        <div v-if="logs.edit_mode === log.id">
            <input type="number" min="100" max="30000" maxlength="5" v-model="log.frequency" class="border border-black p-1 rounded text-sm w-full text-black">
            <div v-if="logs.edit_errors.frequency" class="text-xs text-red-900">{{ logs.edit_errors.frequency }}</div>
        </div>
        <div v-else>
            {{ log.frequency }}<span class="text-xs ml-1">kHz</span>

        </div>
    </div>

    <!-- station and language -->
    <div class="p-2"
       :class="{
        'sm:col-span-4 xl:col-span-3': !logs.filters.group_results,
        'col-span-4': logs.filters.group_results}"
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
            />
            <br>
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
            <span class="italic">{{ log.language_name.replace('.', '') }}</span>
        </div>
    </div>

    <!-- time -->
    <div
        v-if="!logs.filters.group_results && props.log.datetime"
        class="lg:col-span-3 p-2 bg-red-500"
    >
        <div v-if="logs.edit_mode === log.id" class="p-6 whitespace-nowrap">
            <input type="datetime-local" v-model="log.datetime" class="border-2 border-black rounded text-black p-1 mr-0.5"><b>z</b>
        </div>

        <div v-else>
            <div class="inline-block whitespace-nowrap text-left text-base">
                {{ props.log?.datetime.substring(11, 16) }}
                <br>
                {{ props.log?.datetime.substring(8, 10) }}/{{ props.log?.datetime.substring(5, 7) }}/{{ props.log?.datetime.substring(0, 4) }}
            </div>
        </div>
    </div>


    <!-- reception quality -->
    <div class="col-span-1 text-center p-4">
        <div v-if="logs.edit_mode === log.id">
            <label class="m-1 p-2 bg-gray-300 text-black border-2 border-black rounded inline-block text-sm">1 <input type="radio" v-model="log.quality" value="1" class="m-1"></label>
            <label class="m-1 p-2 bg-gray-300 text-black border-2 border-black rounded inline-block text-sm">2 <input type="radio" v-model="log.quality" value="2" class="m-1"></label>
            <label class="m-1 p-2 bg-gray-300 text-black border-2 border-black rounded inline-block text-sm">3 <input type="radio" v-model="log.quality" value="3" class="m-1"></label>
        </div>

        <div v-else>
          {{ Math.round(log.quality * 100) / 100 }}/3
          <br>
        </div>
    </div>

    <!-- comments -->
    <div
        v-if="!logs.filters.group_results"
        class="col-span-2 p-2"
        :class="{'hidden sm:block': log.comment === null && logs.edit_mode !== log.id}"
    >
        <textarea v-if="logs.edit_mode === log.id" v-model="log.comment" class="border-2 border-black p-2 text-black rounded my-3 w-full h-5/6"></textarea>
        <div v-if="logs.edit_mode !== log.id" class="" v-html="log?.comment ?? ''.replace('\n', '<br><br>')"></div>
    </div>

<!-- buttons -->
<div
  class="sm:col-span-2 lg:text-left xl:text-center p-4"
  :class="{'lg:hidden xl:block': !logs.filters.group_results}"
>
  <button
    class="inline-block border border-black rounded text-black text-xs bg-white p-1 m-1"
    @click="logs.duplicateLog(log.frequency, log.station_id, log.station_name, log.station_programme_id, log.station_programme_name, log.language_id, log.language_name)"
  >
    duplicate log
  </button>

  <a
    v-if="logs.filters.station_type === 1"
    class="inline-block border border-black rounded text-black text-xs bg-white p-1 m-1"
    :href="base_url + 'shortWaveInfoData?frequency=' + log.frequency + '&broadcasting_now=false&station_id=' + log.station_id + '&language_id=' + log.language_id"
    target="_blank"
  >
    Find on swinfo
  </a>

  <a
    class="inline-block border border-black rounded text-black text-xs bg-white p-1 m-1"
    :href="'http://websdr.ewi.utwente.nl:8901/?tune=' + log.frequency + mode"
    target="_blank"
  >
    WebSdr
  </a>
</div>

  <!-- count -->
  <div
      v-if="logs.filters.group_results"
      class="col-span-1 text-center p-4"
  >
    {{ log.count }}
  </div>

    <!-- logged by -->
  <div
      v-if="!logs.filters.group_results"
      class="col-span-1 p-2"
  >
      {{ log.user_id === user.id ? '' : log.user_name }}
      <div v-if="log.user_id === user.id">
          <button
              class="bg-green-400 border border-black rounded text-sm text-white m-1 px-1"
              :class="{'text-gray-300': logs.edit_mode && logs.edit_mode != log.id}"
              @click="logs.editLog(log)"
              :disabled="logs.edit_mode && logs.edit_mode != log.id"
              >
              {{ logs.edit_mode === log.id ? 'save' : 'edit' }}
          </button>

          <button
              class="bg-red-400 border border-black rounded text-sm text-white m-1 px-1"
              type="button"
              @click="logs.deleteLog(log.id, log.frequency, log.station_name, log.language_name)"
          >
          {{ logs.edit_mode === log.id ? 'cancel' : 'delete' }}
          </button>
      </div>
  </div>

  <!-- show buttons here if in lg screen and results not grouped -->
    <div v-if="!logs.filters.group_results" class="hidden lg:block xl:hidden lg:col-span-12">
        <button
            class="inline-block border border-black rounded text-black text-xs bg-white p-1 m-1"
            @click="logs.duplicateLog(log.frequency, log.station_id, log.station_name, log.station_programme_id, log.station_programme_name, log.language_id, log.language_name)"
        >
          duplicate log
        </button>
        <a
            v-if="logs.filters.station_type === 1"
            class="inline-block border border-black rounded text-black text-xs bg-white p-1 m-1"
            :href="base_url + 'shortWaveInfoData?frequency=' + log.frequency + '&broadcasting_now=false&station_id=' + log.station_id + '&language_id=' + log.language_id"
            target="_blank"
        >
          Find on swinfo
        </a>
        <a
            class="inline-block border border-black rounded text-black text-xs bg-white p-1 m-1"
            :href="'http://websdr.ewi.utwente.nl:8901/?tune=' + log.frequency + mode"
            target="_blank"
        >
          WebSdr
        </a>
    </div>
</div>


</template>
