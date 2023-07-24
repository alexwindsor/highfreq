import { reactive, ref } from "vue";
import { router } from '@inertiajs/vue3'
import { base_url } from '@/base_url.js'

export let logs = reactive({


    showHideAddLog: false,
    showHideLogFilters: false,

    newlog: {
        frequency: null,
        datetime: null,
        make_time_now: true,
        station_id: 0,
        station_name: '',
        programme_id: 0,
        programme_name: '',
        language_id: 1,
        language_name: 'Unknown or n/a',
        quality: "2",
        comment: '',
        errors: {}
    },

    filters: {
        frequency: null,
        weekday: 0,
        time_filter: false,
        time: '',
        make_time_now: true,
        station_id: 0,
        station_name: '',
        quality: "1",
        commentSearch: '',
        logOwners: 1
    },

    swinfoMatches: [],
    pickedStationProgrammes: [],
    
    logs: [],
    edit_mode: false, // becomes the id of the log being edited
    edit_errors: {
        frequency: ''
    },


    async checkFrequency() {

        if(! this.newlog.frequency || this.newlog.frequency < 100 || this.newlog.frequency > 30000) return false

        await axios.post(base_url + 'swiDataRip/checkfrequency', {
            frequency: this.newlog.frequency,
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
        if (this.swinfoMatches[swinfo_match_key].programme) {
            this.newlog.programme_id = this.swinfoMatches[swinfo_match_key].programme.id
            this.newlog.programme_name = this.swinfoMatches[swinfo_match_key].programme.name
        }
        this.newlog.language_id = this.swinfoMatches[swinfo_match_key].language_id
        this.newlog.language_name = this.swinfoMatches[swinfo_match_key].language.name

        this.getStationProgrammes(this.swinfoMatches[swinfo_match_key].station_id)
    },


    async getStationProgrammes(id) {

        if (parseInt(id) === 0) this.pickedStationProgrammes = []
        else {
            await axios.post(base_url + 'swiDataRip/getStationProgrammes', {
                station_id: id
            }).then(swInfo => {
                if (swInfo.data.length > 0) this.pickedStationProgrammes = swInfo.data
            })
        }
    },


    async addLog() {

        this.newlog.quality = parseInt(this.newlog.quality)

        await axios.post(base_url + 'logs', this.newlog).then(response => {
            alert('Log added')
            // reset the values for the next log to add
            this.newlog.frequency = null
            this.newlog.quality = 2
            this.newlog.make_time_now = true
            this.newlog.comment = ''
            this.newlog.station_id = 0
            this.newlog.station_name = ''
            this.newlog.programme_id = 0
            this.newlog.programme_name = ''
            this.newlog.language_id = 1
            this.newlog.language_name = 'Unknown or n/a'
            this.newlog.errors = {}
            this.pickedStationProgrammes = []
            this.swinfoMatches = []
            // update the logs list
            this.updateLogs()
        })
        .catch(error => {
            this.newlog.errors = error.response.data.errors
        })
    },


    async updateLogs() {
        await axios.post(base_url + 'logs/filter', this.filters).then(logs => {
            this.logs = logs.data
        })
        .catch(error => {
            console.error(error)
        })
    },


    updateFilters(filter, value) {

        if (filter === 'quality') this.filters.quality = value
        if (filter === 'station_id') this.filters.station_id = value

        this.updateLogs()
    },


    async editLog(log) {

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
        }
        // go into edit mode
        else {
            // this.edit = log
            this.edit_mode = log.id
        }
    },


    async deleteLog(log_id, frequency, station, language) {

        // cancel editing
        if (this.edit_mode === log_id){
            await this.updateLogs()
            this.edit_mode = 0
        }
        // delete the log
        else if (confirm('Log: ' + frequency + 'kHz \n' + station + ' in ' + language + '\n\nAre you sure you want to delete this log?')) {
            await router.delete(base_url + 'logs/delete/' + log_id, { 
                preserveScroll: true 
            })
            this.updateLogs()
        }

    },




    makeDate(d) {
        date = new Date(d)

        return 'asd';
    } 


})