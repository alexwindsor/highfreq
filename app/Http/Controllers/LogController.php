<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;
use \Inertia\Inertia;
use App\Models\Log;
use App\Models\Station;
use App\Models\StationProgramme;
use App\Models\Language;

class LogController extends Controller
{

    private array $order_by_options = [
        '`frequency`',
        '`frequency`-DESC',
        '`datetime`-DESC',
        'time(`datetime`)',
        'time(`datetime`)-DESC',
        '`station_id`, `frequency`',
    ];

    public function index() {

        $user = ['id' => auth()->user()->id, 'name' => auth()->user()->name];

        $filters = $this->sanitiseFilters();
        $stations = $this->getStations($filters['station_type']);
        $languages = $this->getLanguages($filters['station_type']);

        return Inertia::render('Logs', [
            'stations' => $stations,
            'languages' => $languages,
            'user' => $user,
            'page' => $filters['page'],
            'station_type' => $filters['station_type'],
            'frequency' => $filters['frequency'],
            'weekday' => $filters['weekday'],
            'time_filter' => $filters['time_filter'],
            'time_range' => $filters['time_range'],
            'half_hour_blocks' => $filters['half_hour_blocks'],
            'bottom_time_range' => $filters['bottom_time_range'],
            'top_time_range' => $filters['top_time_range'],
            'make_time_now' => $filters['make_time_now'],
            'station_id' => $filters['station_id'],
            'station_name' => $filters['station_name'],
            'language_id' => $filters['language_id'],
            'language_name' => $filters['language_name'],
            'quality' => $filters['quality'],
            'commentSearch' => $filters['commentSearch'],
            'log_owners' => $filters['log_owners'],
            'order_by' => $filters['order_by'],
            'group_results' => $filters['group_results'],
        ]);
    }

    public function getStations($station_type_id) {

        return Station::where('station_type_id', $station_type_id)->orderBy('name')->get(['id', 'name']);
    }


    public function getLanguages($station_type_id) {

        return Language::where('station_type_id', $station_type_id)->orderBy('name')->get(['id', 'name']);
    }


    public function filter() {

        $filters = $this->sanitiseFilters();

        $order = str_replace('-', ' ', $filters['order_by']);

        return Log::applyFilters($filters['log_owners'], $filters['time_filter'], $filters['bottom_time_range'], $filters['top_time_range'], $filters['weekday'], $filters['frequency'], $filters['station_type'], $filters['station_id'], $filters['language_id'], $filters['quality'], $filters['commentSearch'], $order, $filters['group_results']);

    }

    public function checkFrequency() {

        return Log::with('station', 'language')->selectRaw('distinct station_id, language_id')->frequency(request('frequency'))->get();
    }


    private function sanitiseFilters() {

        $page = intval(request('page')) > 0 ? intval(request('page')) : 1;
        $station_type = intval(request('station_type')) === 2 ? 2 : 1;
        $frequency = intval(request('frequency')) >= 100 && intval(request('frequency')) <= 30000 ? request('frequency') : 0;
        $weekday = intval(request('weekday')) >= 0 || intval(request('weekday')) <= 7 ? intval(request('weekday')) : 0;

        if (request('time_filter') === true || request('time_filter') === 'true') $time_filter = true;
        elseif (request('time_filter') === null || request('time_filter') === 'false' || request('time_filter') === false) $time_filter = false;

        // $time = preg_match('/^([01]?[0-9]|2[0-3]):[0-5][0-9]:[0-5][0-9]$/', request('time')) ? request('time') : '';

        $time_range = intval(request('time_range')) < 1 || intval(request('time_range')) > 6 ? 1 : intval(request('time_range'));

        if (request('half_hour_blocks') === null || request('half_hour_blocks') === true || request('half_hour_blocks') === 'true') $half_hour_blocks = true;
        elseif (request('half_hour_blocks') === 'false' || request('half_hour_blocks') === false) $half_hour_blocks = false;

        $bottom_time_range = preg_match('/^([01]?[0-9]|2[0-3]):[0-5][0-9]$/', request('bottom_time_range')) ? request('bottom_time_range') : '';
        $top_time_range = preg_match('/^([01]?[0-9]|2[0-3]):[0-5][0-9]$/', request('top_time_range')) ? request('top_time_range') : '';

        if (request('make_time_now') === null || request('make_time_now') === true || request('make_time_now') === 'true') $make_time_now = true;
        elseif (request('make_time_now') === 'false' || request('make_time_now') === false) $make_time_now = false;

        $station_id = intval(request('station_id'));
        $station_name = request('station_name');
        $language_id = intval(request('language_id'));
        $language_name = request('language_name');
        $quality = intval(request('quality')) < 1 || intval(request('quality')) > 3 ? 1 : intval(request('quality'));
        $commentSearch = strval(request('commentSearch'));

        if (request('log_owners') === null || request('log_owners') === true || request('log_owners') === 'true') $log_owners = true;
        elseif (request('log_owners') === 'false' || request('log_owners') === false) $log_owners = false;

        if (in_array(request('order_by'), $this->order_by_options)) $order_by = request('order_by');
        else $order_by = '`station_id`, `frequency`';

        if (request('group_results') === null || request('group_results') === true || request('group_results') === 'true') $group_results = true;
        elseif (request('group_results') === 'false' || request('group_results') === false) $group_results = false;

        return [
            'page' => $page,
            'station_type' => $station_type,
            'frequency' => $frequency,
            'weekday' => $weekday,
            'time_filter' => $time_filter,
            'time_range' => $time_range,
            'half_hour_blocks' => $half_hour_blocks,
            'bottom_time_range' => $bottom_time_range,
            'top_time_range' => $top_time_range,
            'make_time_now' => $make_time_now,
            'station_id' => $station_id,
            'station_name' => $station_name,
            'language_id' => $language_id,
            'language_name' => $language_name,
            'quality' => $quality,
            'commentSearch' => $commentSearch,
            'log_owners' => $log_owners,
            'order_by' => $order_by,
            'group_results' => $group_results
        ];
    }

    public function store() {

        $fields = request()->validate([
            'frequency' => ['required', 'integer', 'min:100', 'max:30000'],
            'datetime' => ['required',  'date'],
            'station_type' => ['required', 'integer', 'min:1', 'max:2'],
            'station_id' => ['required', 'integer', 'min:0'],
            'station_name' => ['required_if:station_id,0', 'string', 'min:2'],
            'station_programme_id' => ['required', 'integer', 'min:0'],
            'station_programme_name' => ['nullable', 'string', 'min:1'],
            'language_id' => ['required', 'integer', 'min:0'],
            'language_name' => ['required_if:language_id,0', 'string', 'min:2'],
            'quality' => ['required', 'integer', 'min:1', 'max:3'],
            'comment' => ['nullable', 'string']
        ]);

        // check if we need a new station
        if ($fields['station_id'] === 0 && !empty($fields['station_name'])) {
            $station = Station::firstOrCreate(['name' => strtoupper($fields['station_name']), 'station_type_id' => $fields['station_type']]);
            $fields['station_id'] = $station->id;
        }

        // check if we need a new programme
        if ($fields['station_programme_id'] === 0 && !empty($fields['station_programme_name'])) {
            $programme = StationProgramme::firstOrCreate(['station_id' => $fields['station_id'], 'name' => strtoupper($fields['station_programme_name'])]);
            $fields['station_programme_id'] = $programme->id;
        }

        // check if we need a new language
        if ($fields['language_id'] === 0 && !empty($fields['language_name'])) {
            if ($fields['station_type'] === 2) $fields['language_name'] = '.' . $fields['language_name'];
            $language = Language::firstOrCreate(['name' => ucwords($fields['language_name']), 'station_type_id' => $fields['station_type']]);
            $fields['language_id'] = $language->id;
        }

        if ($fields['station_programme_id'] === 0) $fields['station_programme_id'] = null;
        if (strlen($fields['comment']) === 0) $fields['comment'] = null;

        Log::create([
            'user_id' => auth()->user()->id,
            'frequency' => $fields['frequency'],
            'datetime' => $fields['datetime'],
            'station_id' => $fields['station_id'],
            'station_programme_id' => $fields['station_programme_id'],
            'language_id' => $fields['language_id'],
            'quality' => $fields['quality'],
            'comment' => $fields['comment'],
        ]);


    }


    public function update($id) {

        // dd(request());

        $fields = request()->validate([
            'frequency' => ['required', 'integer', 'min:100', 'max:30000'],
            'datetime' => ['required',  'date'],
            'station_type_id' => ['required', 'integer', 'min:1', 'max:2'],
            'station_id' => ['required', 'integer', 'min:0'],
            'station_name' => ['required_if:station_id,0', 'string', 'min:2'],
            'station_programme_id' => ['required', 'integer', 'min:0'],
            'station_programme_name' => ['nullable', 'string', 'min:1'],
            'language_id' => ['required', 'integer', 'min:0'],
            'language_name' => ['required_if:language_id,0', 'string', 'min:1'],
            'quality' => ['required', 'integer', 'min:1', 'max:3'],
            'comment' => ['nullable', 'string']
        ]);

        // check if we need a new station
        if ($fields['station_id'] === 0 && !empty($fields['station_name'])) {
            $station = Station::firstOrCreate(['name' => trim(strtoupper($fields['station_name'])), 'station_type_id' => $fields['station_type_id']]);
            $fields['station_id'] = $station->id;
        }

        // check if we need a new programme
        if ($fields['station_programme_id'] === 0 && !empty($fields['station_programme_name'])) {
            $programme = StationProgramme::firstOrCreate(['station_id' => $fields['station_id'], 'name' => trim(strtoupper($fields['station_programme_name']))]);
            $fields['station_programme_id'] = $programme->id;
        }

        // check if we need a new language
        if ($fields['language_id'] === 0 && !empty($fields['language_name'])) {
            if ($fields['station_type'] === 2) $fields['language_name'] = '.' . $fields['language_name'];
            $language = Language::firstOrCreate(['name' => trim(ucwords($fields['language_name'])), 'station_type_id' => $fields['station_type']]);
            $fields['language_id'] = $language->id;
        }

        if ($fields['station_programme_id'] === 0) $fields['station_programme_id'] = null;
        if (strlen($fields['comment']) === 0) $fields['comment'] = null;

        $log = Log::find($id);

        $log->frequency = $fields['frequency'];
        $log->datetime = $fields['datetime'];
        $log->station_id = $fields['station_id'];
        $log->station_programme_id = $fields['station_programme_id'];
        $log->language_id = $fields['language_id'];
        $log->quality = $fields['quality'];
        $log->comment = $fields['comment'];

        $log->save();

    }


    public function destroy($log_id) {
        Log::destroy($log_id);
    }


}
