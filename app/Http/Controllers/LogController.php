<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use \Inertia\Inertia;
use App\Models\Log;
use App\Models\Station;
use App\Models\StationProgramme;
use App\Models\Language;

class LogController extends Controller
{

    public function index() {

        $stations = Station::orderBy('name')->get(['id', 'name']);
        $languages = Language::orderBy('name')->get(['id', 'name']);

        $user = auth()->user();

        return Inertia::render('Logs', compact('stations', 'languages', 'user'));
    }


    public function filter() {

        $filters = $this->sanitiseFilters();

        $logs = Log::with('station', 'language', 'user', 'stationProgramme')
            ->userFilter($filters['log_owners'])
            ->timeFilter($filters['time'], $filters['time_filter'])
            ->weekdayFilter($filters['weekday'])
            ->frequencyFilter($filters['frequency'])
            ->stationFilter($filters['station_id'])
            ->qualityFilter($filters['quality'])
            ->commentSearch($filters['commentSearch'])
            ->orderByDesc('datetime')
            // ->get();
            ->paginate(10);
            // ->selectRaw('TIMESTAMPDIFF(HOUR, ?, TIME(`time`)) as `timediff`', [request('time')])

            return $logs;
    }


    private function sanitiseFilters() {

        $frequency = intval(request('frequency'));
        $weekday = intval(request('weekday')) >= 0 || intval(request('weekday')) <= 7 ? intval(request('weekday')) : 0;
        $time = preg_match('/^([01]?[0-9]|2[0-3]):[0-5][0-9]:[0-5][0-9]$/', request('time')) ? request('time') : '';
        $time_filter = boolval(request('time_filter'));
        $station_id = intval(request('station_id'));
        $quality = intval(request('quality')) < 1 || intval(request('quality')) > 3 ? 1 : intval(request('quality'));
        $commentSearch = strval(request('commentSearch'));
        $log_owners = boolval(request('logOwners'));

        return [
            'frequency' => $frequency,
            'weekday' => $weekday,
            'time' => $time,
            'time_filter' => $time_filter,
            'station_id' => $station_id,
            'quality' => $quality,
            'commentSearch' => $commentSearch,
            'log_owners' => $log_owners
        ];

    }

    public function store() {

        $fields = request()->validate([
            'frequency' => ['required', 'integer', 'min:100', 'max:30000'],
            'datetime' => ['required',  'date'],
            'station_id' => ['required', 'integer', 'min:0'],
            'station_name' => ['required_if:station_id,0', 'string', 'min:2'],
            'programme_id' => ['required', 'integer', 'min:0'],
            'programme_name' => ['nullable', 'string', 'min:1'],
            'language_id' => ['required', 'integer', 'min:0'],
            'language_name' => ['required_if:language_id,0', 'string', 'min:2'],
            'quality' => ['required', 'integer', 'min:1', 'max:3'],
            'comment' => ['nullable', 'string']
        ]);

        // check if we need a new station
        if ($fields['station_id'] === 0 && !empty($fields['station_name'])) {
            $station = Station::firstOrCreate(['name' => trim(strtoupper($fields['station_name'])), 'station_types_id' => 1]);
            $fields['station_id'] = $station->id;
        }

        // check if we need a new programme
        if ($fields['programme_id'] === 0 && !empty($fields['programme_name'])) {
            $programme = StationProgramme::firstOrCreate(['station_id' => $fields['station_id'], 'name' => trim(strtoupper($fields['programme_name']))]);
            $fields['programme_id'] = $programme->id;
        }

        // check if we need a new language
        if ($fields['language_id'] === 0 && !empty($fields['language_name'])) {
            $language = Language::firstOrCreate(['name' => trim(ucwords($fields['language_name']))]);
            $fields['language_id'] = $language->id;
        }

        

        if ($fields['programme_id'] === 0) $fields['programme_id'] = null;

        $log = Log::create([
            'user_id' => auth()->user()->id, 
            'frequency' => $fields['frequency'], 
            'datetime' => $fields['datetime'], 
            'station_id' => $fields['station_id'], 
            'station_programme_id' => $fields['programme_id'], 
            'language_id' => $fields['language_id'], 
            'quality' => $fields['quality'], 
            'comment' => trim($fields['comment']), 
        ]);

        return true;
    }

    
    public function update($id) {

        $fields = request()->validate([
            'frequency' => ['required', 'integer', 'min:100', 'max:30000'],
            'datetime' => ['required',  'date'],
            'station.id' => ['required', 'integer', 'min:0'],
            'station.name' => ['required_if:station_id,0', 'string', 'min:2'],
            'station_programme.id' => ['required', 'integer', 'min:0'],
            'station_programme.name' => ['nullable', 'string', 'min:1'],
            'language.id' => ['required', 'integer', 'min:0'],
            'language.name' => ['required_if:language_id,0', 'string', 'min:1'],
            'quality' => ['required', 'integer', 'min:1', 'max:3'],
            'comment' => ['nullable', 'string']
        ]);

        // dd($fields);

        // check if we need a new station
        if ($fields['station']['id'] === 0 && !empty($fields['station']['name'])) {
            $station = Station::firstOrCreate(['name' => trim(strtoupper($fields['station']['name'])), 'station_types_id' => 1]);
            $fields['station']['id'] = $station->id;
        }

        // check if we need a new programme
        if ($fields['station_programme']['id'] === 0 && !empty($fields['station_programme']['name'])) {
            $programme = StationProgramme::firstOrCreate(['station_id' => $fields['station']['id'], 'name' => trim(strtoupper($fields['station_programme']['name']))]);
            $fields['station_programme']['id'] = $programme->id;
        }

        // check if we need a new language
        if ($fields['language']['id'] === 0 && !empty($fields['language']['name'])) {
            $language = Language::firstOrCreate(['name' => trim(ucwords($fields['language']['name']))]);
            $fields['language']['id'] = $language->id;
        }

        

        $log = Log::find($id);

        $log->frequency = $fields['frequency'];
        $log->datetime = $fields['datetime'];
        $log->station_id = $fields['station']['id'];
        $log->station_programme_id = $fields['station_programme']['id'];
        $log->language_id = $fields['language']['id'];
        $log->quality = $fields['quality'];
        $log->comment = $fields['comment'];

        $log->save();

    }


    public function destroy($log_id) {
        Log::destroy($log_id);
    }


}
