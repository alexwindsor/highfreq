<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use \Inertia\Inertia;
use App\Models\Log;
use App\Models\Station;
use App\Models\Language;

class LogController extends Controller
{

    public function index() {
        
        $canLogin = Route::has('login');
        $canRegister = Route::has('register');

        $stations = Station::orderBy('name')->get(['id', 'station_types_id', 'name', 'name']);
        $languages = Language::orderBy('name')->get(['id', 'name']);

        $user = auth()->user();

        return Inertia::render('Logs', compact('stations', 'languages', 'canLogin', 'canRegister', 'user'));
    }


    public function store() {

        $fields = request()->validate([
            'frequency' => ['required', 'integer', 'min:100', 'max:30000'],
            'time' => ['required',  'date'],
            'station_id' => ['required', 'integer', 'min:1'],
            'language_id' => ['required', 'integer', 'min:1'],
            'quality' => ['required', 'integer', 'min:1', 'max:3'],
            'comment' => ['nullable', 'string']
        ]);

        $log = Log::create([
            'user_id' => auth()->user()->id, 
            'frequency' => $fields['frequency'], 
            'time' => $fields['time'], 
            'station_id' => $fields['station_id'], 
            'language_id' => $fields['language_id'], 
            'quality' => $fields['quality'], 
            'comment' => $fields['comment'], 
        ]);
    }


    public function filter() {

        $frequency = intval(request('frequency'));
        $commentSearch = empty(request('commentSearch')) ? '' : request('commentSearch');

        $logs = Log::with('station', 'language', 'user')
            ->userFilter(request('logOwners'))
            ->timeFilter(request('time'), request('time_filter'))
            ->weekdayFilter(request('weekday'))
            ->frequencyFilter($frequency)
            ->stationFilter(request('station_id'))
            ->qualityFilter(request('quality'))
            ->commentSearch($commentSearch)
            ->orderByDesc('time')
            ->get();
            // ->selectRaw('HOUR(time) - HOUR('17:00:00')', [request('time')])
            // ->selectRaw('TIMESTAMPDIFF(HOUR, ?, TIME(`time`)) as `timediff`', [request('time')])

            return $logs;
    }

    public function update($id) {

        $fields = request()->validate([
            'frequency' => ['required', 'integer', 'min:100', 'max:30000'],
            'time' => ['required',  'date'],
            'station_id' => ['required', 'integer', 'min:1'],
            'language_id' => ['required', 'integer', 'min:1'],
            'quality' => ['required', 'integer', 'min:1', 'max:3'],
            'comment' => ['nullable', 'string']
        ]);

        $log = Log::find($id);

        $log->frequency = $fields['frequency'];
        $log->time = $fields['time'];
        $log->station_id = $fields['station_id'];
        $log->language_id = $fields['language_id'];
        $log->quality = $fields['quality'];
        $log->comment = $fields['comment'];

        $log->save();

    }


    public function destroy($log_id) {
        Log::destroy($log_id);
    }


}
