<?php

namespace App\Http\Controllers;

use \Inertia\Inertia;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\SwInfo\Weekdays;
use App\Http\Controllers\SwInfo\SwInfoDataRip;
use App\Models\SwInfoBroadcast;
use App\Models\Station;
use App\Models\SwInfoTransmitter;
use App\Models\StationProgramme;


class SwInfoBroadcastController extends Controller
{

    
    public function index() {

        $filters = $this->sanitizeFilters();
        
        $dateOfData = SwInfoDataRip::getServerDateOfData();
        $dateOfData = substr($dateOfData, 6, 2) . '/' . substr($dateOfData, 4, 2) . '/' . substr($dateOfData, 0, 4);
        $stations = Station::orderBy('name')->get(['id', 'name']);
        $transmitters = SwInfoTransmitter::orderBy('name')->get(['id', 'name']);

        $time = date('H:i');
        $day = date('l');

        $user = auth()->user();

        return Inertia::render('ShortWaveInfoData', [ 
            'page' => $filters['page'], 
            'frequency' => $filters['frequency'], 
            'broadcasting_now' => $filters['broadcasting_now'], 
            'station_id' => $filters['station_id'], 
            'swinfo_transmitter_id' => $filters['swinfo_transmitter_id'], 
            'order_by' => $filters['order_by'], 
            'stations' => $stations, 
            'transmitters' => $transmitters, 
            'dateOfData' => $dateOfData, 
            'day' => $day, 
            'time' => $time, 
            'user' => $user,
        ]);
    }


    public function filterBroadcasts() {

        $filters = $this->sanitizeFilters();

        $swInfoBroadcasts = SwInfoBroadcast::with('station', 'programme', 'language', 'swInfoTransmitter')
        ->nowTime($filters['broadcasting_now'])
        ->frequency($filters['frequency'])
        ->station($filters['station_id'])
        ->transmitter($filters['swinfo_transmitter_id'])
        ->orderBy($filters['order_by'])
        ->paginate(100);

        return $swInfoBroadcasts;
    }


    private function sanitizeFilters() {

        $page = intval(request('page')) > 0 ? intval(request('page')) : 1;
        $frequency = intval(request('frequency')) >= 300 && intval(request('frequency')) <= 30000 ? intval(request('frequency')) : 0;

        $broadcasting_now = true;

        if (request('broadcasting_now') === false || request('broadcasting_now') === 'false') $broadcasting_now = false;

        $station_id = intval(request('station_id'));
        $swinfo_transmitter_id = intval(request('swinfo_transmitter_id'));
        $order_by = request('order_by') === 'frequency' || request('order_by') === 'station_id' || request('order_by') === 'start_time' ? request('order_by') : 'frequency';

        return ['page' => $page, 'frequency' => $frequency, 'broadcasting_now' => $broadcasting_now, 'station_id' => $station_id, 'swinfo_transmitter_id' => $swinfo_transmitter_id, 'order_by' => $order_by];
    }



    public function checkFrequency() {

        $time = substr(request('time'), 11);

        // get a bitwise number for the day of the week
        $day = date('N') + 1;
        $day = $day === 8 ? '1' : $day;
        $day = pow(2, $day);

        return SwInfoBroadcast::with('station', 'programme', 'language')
            ->where('frequency', '=', request('frequency'))
            ->whereRaw('time(`start_time`) < "' . $time . '"')
            ->whereRaw('time(`end_time`) > "' . $time . '"')
            ->whereRaw('weekdays & ' . $day)
            ->get();
    }


    public function getStationProgrammes() {
        return StationProgramme::where('station_id', '=', intval(request('station_id')))->get(['id', 'name']);
    }


    public function rip() {
        echo SwInfoDataRip::shortWaveInfoGet();
        die();
    }

}
