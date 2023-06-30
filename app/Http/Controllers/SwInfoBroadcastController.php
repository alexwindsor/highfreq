<?php

namespace App\Http\Controllers;

use \Inertia\Inertia;
use App\Models\Station;
use Illuminate\Http\Request;
use App\Models\SwInfoBroadcast;
use App\Models\SwInfoTransmitter;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\SwInfo\Weekdays;
use App\Http\Controllers\SwInfo\SwInfoDataRip;

class SwInfoBroadcastController extends Controller
{

    
    public function index() {

        $page = intval(request('page')) > 0 ? intval(request('page')) : 1;
        $frequency = intval(request('frequency')) >= 300 && intval(request('frequency')) <= 30000 ? intval(request('frequency')) : null;
        $broadcasting_now = request('broadcasting_now') === 'false' ? false : true;
        $station_id = intval(request('station_id'));
        $swinfo_transmitter_id = intval(request('swinfo_transmitter_id'));

        $canLogin = Route::has('login');
        $canRegister = Route::has('register');
        
        $dateOfData = SwInfoDataRip::getServerDateOfData();
        $dateOfData = substr($dateOfData, 6, 2) . '/' . substr($dateOfData, 4, 2) . '/' . substr($dateOfData, 0, 4);
        $stations = Station::orderBy('name')->get(['id', 'station_types_id', 'name', 'name']);
        $transmitters = SwInfoTransmitter::orderBy('name')->get();

        $time = date('H:i');
        $day = date('l');

        $user = auth()->user();

        return Inertia::render('ShortWaveInfoData', compact('dateOfData', 'stations', 'transmitters', 'day', 'time', 'user', 'page', 'frequency', 'broadcasting_now', 'station_id', 'swinfo_transmitter_id'));
    }


    public function filterBroadcasts() {

        $frequency = intval(request('frequency'));
        
        $swInfoBroadcasts = SwInfoBroadcast::with('station', 'language', 'swInfoTransmitter')
        ->nowTime(request('broadcasting_now'))
        ->frequency($frequency)
        ->station(request('station_id'))
        ->transmitter(request('swinfo_transmitter_id'))
        ->orderBy('frequency')
        ->paginate(100);

        return $swInfoBroadcasts;
    }

    public function checkFrequency() {

        $time = substr(request('time'), 11);

        // get a bitwise number for the day of the week
        $day = date('N') + 1;
        $day = $day === 8 ? '1' : $day;
        $day = pow(2, $day);
        
        $swInfoBroadcast = SwInfoBroadcast::with('station', 'language')
            ->where('frequency', '=', request('frequency'))
            ->whereRaw('time(`start_time`) < "' . $time . '"')
            ->whereRaw('time(`end_time`) > "' . $time . '"')
            ->whereRaw('weekdays & ' . $day)
            ->get();

        return $swInfoBroadcast;
    }




    public function rip() {
        echo SwInfoDataRip::shortWaveInfoGet();
        die();
    }

}
