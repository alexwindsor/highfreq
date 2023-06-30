import { reactive } from "vue";
import { router } from '@inertiajs/vue3'
import { base_url } from '@/base_url.js'
import { usePage } from '@inertiajs/vue3'



export let logs = reactive({

    datetime: new Date(),

    newlog: {
        frequency: null,
        time: null,
        station_id: 0,
        language_id: '1',
        quality: 2,
        comment: '',
        errors: {}
    },
    
    filters: {
        frequency: null,
        weekday: null,
        time: null,
        time_filter: true,
        station_id: 0,
        quality: 1,
        commentSearch: '',
        logOwners: 1
    },

    edit_errors: {
        frequency: ''
    },

    logs: [],
    swinfoMatches: [],

    showHideAddLog: false,
    showHideFilterLogs: false,
    edit_mode: false, // becomes the id of the log being edited

    getDateTime() {

        // var year = logs.datetime.getUTCFullYear().substring(2, 4)
        var month = logs.datetime.getUTCMonth() + 1
        month = month.toString().padStart(2, '0')
        var day = logs.datetime.getUTCDate().toString().padStart(2, '0')
        var hours = logs.datetime.getUTCHours().toString().padStart(2, '0')
        var minutes = logs.datetime.getUTCMinutes().toString().padStart(2, '0')
        var seconds = logs.datetime.getUTCSeconds().toString().padStart(2, '0')

        return this.datetime.getUTCFullYear() + '-' + month + '-' + day + ' ' + hours + ':' + minutes + ':' + seconds
    },

    getDisplayDate(log_datetime) {

        var logdate = new Date(log_datetime)
    
        var day = logdate.getDate().toString().padStart(2, '0')
        var hours = logdate.getHours().toString().padStart(2, '0')
        var minutes = logdate.getMinutes().toString().padStart(2, '0')
        var month = ''
        var weekday = ''
    
        switch(logdate.getDay()) {
            case 0: 
                weekday = 'Sunday'
                break;
            case 1:
                weekday = 'Monday'
                break;
            case 2:
                weekday = 'Tuesday'
                break;
            case 3:
                weekday = 'Wednesday'
                break;
            case 4:
                weekday = 'Thursday'
                break;
            case 5:
                weekday = 'Friday'
                break;
            case 6:
                weekday = 'Saturday'
                break;
        } 
    
        switch(logdate.getMonth()) {
            case 0: 
                month = 'Jan'
                break;
            case 1:
                month = 'Feb'
                break;
            case 2:
                month = 'Mar'
                break;
            case 3:
                month = 'Apr'
                break;
            case 4:
                month = 'May'
                break;
            case 5:
                month = 'Jun'
                break;
            case 6:
                month = 'Jul'
                break;
            case 7: 
                month = 'Aug'
                break;
            case 8:
                month = 'Sep'
                break;
            case 9:
                month = 'Oct'
                break;
            case 10:
                month = 'Nov'
                break;
            case 11:
                month = 'Dec'
                break;
        } 
    
        return [weekday + ' ' + hours + ':' + minutes, logdate.getDate() + ' ' + month + ' ' + logdate.getFullYear()]
    },
    addLog() {
        axios.post(base_url + 'logs', this.newlog).then(response => {
            alert('*** LOG ADDED ***')
            this.newlog.frequency = null
            this.newlog.quality = 2
            this.newlog.comment = ''
            this.newlog.station_id = 0
            this.newlog.language_id = 1
            this.swinfoMatches = []
            this.updateLogs()
        })
        .catch(error => {
            this.newlog.errors = error.response.data.errors
        })
    },

    updateLogs() {
        axios.post(base_url + 'logs/filter', this.filters).then(logs => {
            this.logs = logs.data
        })
        .catch(error => {
            console.error(error)
        })
    },

    deleteLog(log_id, frequency, station, language) {
        if (confirm('Log: ' + frequency + 'kHz \n' + station + ' in ' + language + '\n\nAre you sure you want to delete this log?')) {
            router.post(base_url + 'logs/delete/' + log_id)
            this.updateLogs()
        }
    },

    checkFreq() {

        if(! this.newlog.frequency || this.newlog.frequency < 100 || this.newlog.frequency > 30000) return false

        // absolutely no idea why axios doesn't work in production
        axios.post(base_url + 'swiDataRip/checkfrequency', {
            frequency: this.newlog.frequency,
            time: this.newlog.time
        }).then(swInfo => {
            if (swInfo.data.length === 0) alert('No match found')
            else this.swinfoMatches = swInfo.data
        })


    },

    addSwinfoMatch(swinfo_match_key) {
        if (! this.swinfoMatches[swinfo_match_key]) return false
        this.newlog.station_id = this.swinfoMatches[swinfo_match_key].station_id
        this.newlog.language_id = this.swinfoMatches[swinfo_match_key].language_id
        this.newlog.comments = this.swinfoMatches[swinfo_match_key].programme
    },

    editLog(log) {

        if (this.edit_mode === log.id) {

            axios.put(base_url + 'logs/update/' + log.id, log).then(response => {
                this.edit_mode = false
                this.updateLogs()
                alert('*** LOG UPDATED ***')
            })
            .catch(error => {
                this.edit_errors.frequency = error.response.data.errors.frequency[0]
            })
        }
        else {
            this.edit_mode = log.id
        }
    }

})