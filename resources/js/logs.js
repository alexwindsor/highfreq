import {reactive, ref} from "vue";
import { router } from '@inertiajs/vue3'
import { base_url } from '@/base_url.js'

export let logs = reactive({


    showHideAddLog: false,
    showHideFilterLogs: false,
    showHideSortLogs: false,

    today: null,
    mode: 'browse',
    stations: [],
    languages: [],

    newlog: {
        frequency: null,
        datetime: null,
        make_time_now: true,
        station_type: 1,
        station_id: 0,
        station_name: '',
        station_programme_id: 0,
        station_programme_name: '',
        language_id: 0,
        language_name: '',
        quality: "2",
        comment: '',
        errors: {}
    },

    frequencyToCheck: null,

    filters: {},
    filters_querystring: '',

    swinfoMatches: [],
    pickedStationProgrammes: [],

    logs: [],
    edit_mode: false, // becomes the id of the log being edited
    edit_errors: {
        frequency: ''
    },




    async checkFrequency() {

        this.swinfoMatches = []
        this.frequencyToCheck = null

        if(! this.newlog.frequency) return false

        this.frequencyToCheck = this.newlog.frequency

        let route = this.filters.station_type === 1 ? 'swiDataRip' : 'logs'

        await axios.post(base_url + route + '/checkfrequency', {
            frequency: this.frequencyToCheck,
            time: this.newlog.datetime
        }).then(swInfo => {
            if (swInfo.data.length === 0) alert('No match found')
            else this.swinfoMatches = swInfo.data
        })

    },


    addSwinfoMatch(swinfo_match_key) {

        if (! this.swinfoMatches[swinfo_match_key]) return false

        this.newlog.station_id = this.swinfoMatches[swinfo_match_key].station_id
        this.newlog.station_name = this.swinfoMatches[swinfo_match_key].station.name
        if (this.swinfoMatches[swinfo_match_key].station_programme) {
            this.newlog.station_programme_id = this.swinfoMatches[swinfo_match_key].station_programme.id
            this.newlog.station_programme_name = this.swinfoMatches[swinfo_match_key].station_programme.name
        }
        else {
            this.newlog.station_programme_id = 0
            this.newlog.station_programme_name = ''
        }
        this.newlog.language_id = this.swinfoMatches[swinfo_match_key].language_id
        this.newlog.language_name = this.swinfoMatches[swinfo_match_key].language.name

        this.getStationProgrammes(this.swinfoMatches[swinfo_match_key].station_id)
    },


    async getStationProgrammes(station_id) {


        this.pickedStationProgrammes = []
        if (parseInt(station_id) > 0)  {
            await axios.post(base_url + 'swiDataRip/getStationProgrammes', {
                station_id: station_id
            }).then(swInfo => {
                if (swInfo.data.length > 0) this.pickedStationProgrammes = swInfo.data
            })
        }
    },


    async addLog() {

        this.newlog.quality = parseInt(this.newlog.quality)

        await axios.post(base_url + 'logs/add', this.newlog).then(response => {
            alert('Log added')
            this.resetNewlog()
            this.updateLogs()
        })
        .catch(error => {
            this.newlog.errors = error.response.data.errors
        })
    },

    resetNewlog() {
        // reset the values for the next log to add
        this.newlog.frequency = null
        this.newlog.quality = 2
        this.newlog.make_time_now = true
        this.newlog.comment = ''
        this.newlog.station_type = logs.filters.station_type
        this.newlog.station_id = 0
        this.newlog.station_name = ''
        this.newlog.station_programme_id = 0
        this.newlog.station_programme_name = ''
        this.newlog.language_id = 0
        this.newlog.language_name = ''
        this.newlog.errors = {}
        this.pickedStationProgrammes = []
        this.swinfoMatches = []
    },

    async updateLogs(filter_change = null) {

        if (filter_change === 'frequency' && (this.filters.frequency < 100 || this.filters.frequency > 30000)) return false
        if (filter_change === 'make_time_now' && this.filters.make_time_now === true) await new Promise(r => setTimeout(r, 1000))
        if (filter_change === 'time_filter' && this.filters.time_filter === true && this.filters.time === '') return false
        if (filter_change === 'group_results' && this.filters.group_results === false) this.mode = 'add'
        else if (filter_change === 'group_results' && this.filters.group_results === true) this.mode = 'browse'

        await axios.post(base_url + 'logs', this.filters).then(logs => {
            this.logs = logs.data
            // if the current page is not greater than the total number of pages then redirect to the last page
            if (this.logs.last_page < this.logs.current_page) {
                this.filters.page = this.logs.last_page
                this.updateLogs()
            }
            this.filters_querystring = this.makeQuerystring()
            })
        .catch(error => {
            console.error(error)
        })

        // any logs with no station_programme need to be filled with blank data for the edit-log feature to work
        let i = 0
        for (i = 0; i < this.logs.data.length; i++) {
            if (this.logs.data[i].station_programme === null) {
                this.logs.data[i].station_programme = {id: 0,name: ''}
            }
        }
    },


    makeQuerystring() {
        return '&station_type=' + this.filters.station_type + '&frequency=' + this.filters.frequency + '&weekday=' + this.filters.weekday + '&time_filter=' + this.filters.time_filter + '&time_range=' + this.filters.time_range + '&half_hour_blocks=' + this.filters.half_hour_blocks + '&time=' + this.filters.time + '&make_time_now=' + this.filters.make_time_now + '&station_id=' + this.filters.station_id + '&station_name=' + this.filters.station_name + '&language_id=' + this.filters.language_id + '&language_name=' + this.filters.language_name + '&quality=' + this.filters.quality + '&commentSearch=' + this.filters.commentSearch + '&log_owners=' + this.filters.log_owners + '&order_by=' + this.filters.order_by + '&group_results=' + this.filters.group_results + '&match_swinfo=' + this.filters.match_swinfo + '&antimatch_swinfo=' + this.filters.antimatch_swinfo
    },

    updateTimeRange() {

        this.filters.time_range = parseInt(this.filters.time_range)

        if (this.filters.half_hour_blocks) {
            // are we in the top or bottom half of the hour?
            let half = parseInt(this.filters.time.substring(3, 5)) >= 30

            let offset = half ? this.filters.time_range : this.filters.time_range + 1
            var bottom_hour = parseInt(this.filters.time.substring(0, 2)) - parseInt(offset / 4)

            offset = half ? this.filters.time_range - 1 : this.filters.time_range + 2
            var top_hour = parseInt(this.filters.time.substring(0, 2)) + (half ? 1 : 0) + parseInt(offset / 4)

            offset = parseInt(this.filters.time_range / 2) + 1
            let minutes_a = offset % 2 === 1 ? 30 : 0

            offset = parseInt((this.filters.time_range + 1) / 2)
            let minutes_b = offset % 2 === 1 ? 0 : 30

            var bottom_minute = half ? minutes_a : minutes_b
            var top_minute = half ? minutes_b : minutes_a

            if (bottom_hour < 0) bottom_hour = bottom_hour + 24
            if (top_hour > 23) top_hour = top_hour - 24

        }
        else {
            let gap = this.filters.time_range * 15

            var top_hour = parseInt(this.filters.time.substring(0, 2)) + Math.floor(gap/60)
            var top_minute = parseInt(this.filters.time.substring(3, 5)) + gap % 60
            var bottom_hour = parseInt(this.filters.time.substring(0, 2)) - Math.floor(gap/60)
            var bottom_minute = parseInt(this.filters.time.substring(3, 5)) - gap % 60

            if (bottom_hour < 0) bottom_hour = bottom_hour + 24
            if (bottom_minute > 60) {
                bottom_minute = bottom_minute - 60
                bottom_hour++
            }
            if (bottom_minute < 0) {
                bottom_minute = bottom_minute + 60
                bottom_hour--
            }

            if (top_hour > 23) top_hour = top_hour - 24
            if (top_hour < 0) top_hour = top_hour + 24
            if (top_minute > 60) {
                top_minute = top_minute - 60
                top_hour++
            }
            if (top_minute < 0) {
                top_minute = top_minute + 60
                top_hour--
            }
        }

        this.filters.bottom_time_range = bottom_hour.toString().padStart(2, '0') + ':' + bottom_minute.toString().padStart(2, '0')
        this.filters.top_time_range = top_hour.toString().padStart(2, '0') + ':' + top_minute.toString().padStart(2, '0')

    },

    updateFilters(filter, value) {

        let do_update = false

        if (filter === 'quality') {
            this.filters.quality = value
            do_update = true
        }
        else if (filter === 'station_id') {
            if (this.filters.station_id !== value) do_update = true
            this.filters.station_id = value
        }
        else if (filter === 'language_id') {
            if (this.filters.language_id !== value) do_update = true
            this.filters.language_id = value
        }

        if (do_update) this.updateLogs(filter)
    },

    autoFilters(mode) {

        this.filters.time_filter = true
        this.filters.make_time_now = true

        if (mode === 'add') {
            this.filters.group_results = false
            this.filters.order_by = '`datetime`-DESC'
        }
        else if (mode === 'browse') {
            this.filters.group_results = true
            this.filters.order_by = '`frequency`'
        }

        this.updateLogs()

    },


    async editLog(log) {

        if (log.station_programme_id < 1) log.station_programme_id = 0

        // update the log
        if (this.edit_mode === log.id) {
            await axios.put(base_url + 'logs/update/' + log.id, log).then(response => {
                this.edit_mode = false
                this.updateLogs()
                alert('Log updated')
            })
            .catch(error => {
                this.edit_errors.frequency = error.response.data.errors.frequency[0]
            })
            this.pickedStationProgrammes = [] // clear all the programmes for the station
        }
        // go into edit mode
        else {
            // close the addlog box
            this.showHideAddLog = false
            // reset all the data that was in there
            this.resetNewlog()
            // get the programmes (if any) for this station
            this.getStationProgrammes(log.station_id)
            this.edit_mode = log.id
        }
    },


    async deleteLog(log_id, frequency, station, language) {

        // cancel editing
        if (this.edit_mode === log_id) {
            this.edit_mode = 0
        }
        // delete the log
        else if (confirm('Log: ' + frequency + 'kHz \n' + station + ' in ' + language + '\n\nAre you sure you want to delete this log?')) {
            router.delete(base_url + 'logs/delete/' + log_id, {
                preserveScroll: true
            })
            this.updateLogs()
        }

    },

    duplicateLog(frequency, station_id, station_name, station_programme_id, station_programme_name, language_id, language_name) {

        this.newlog.frequency = frequency
        this.newlog.station_id = station_id
        this.newlog.station_name = station_name
        this.newlog.station_programme_id = station_programme_id === null ? 0 : station_programme_id
        this.newlog.station_programme_name = station_programme_name
        this.newlog.language_id = language_id
        this.newlog.language_name = language_name

        this.showHideAddLog = true

        window.scrollTo({
            top: 0,
            left: 0,
            behavior: 'smooth'
        })


    },



})
