<?php

namespace App\Http\Controllers;

use Inertia\Inertia;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Arr;
use App\Http\Controllers\SwInfo\Weekdays;
use App\Http\Controllers\SwInfo\SwInfoDataRip;
use App\Models\SwInfoBroadcast;
use App\Models\Station;
use App\Models\Language;
use App\Models\Transmitter;
use App\Models\StationProgramme;


class SwInfoBroadcastController extends Controller
{


    public function index() {

        $filters = $this->sanitizeFilters();

        $dateOfData = SwInfoDataRip::getServerDateOfData();
        $dateOfData = substr($dateOfData, 6, 2) . '/' . substr($dateOfData, 4, 2) . '/' . substr($dateOfData, 0, 4);
        $stations = Station::where('station_type_id', 1)->orderBy('name')->get(['id', 'name']);
        $languages = Language::where('station_type_id', 1)->orderBy('name')->get(['id', 'name']);
        $transmitters = Transmitter::orderBy('name')->get(['id', 'name']);

        $time = date('H:i');
        $day = date('l');

        $user = auth()->user();

        return Inertia::render('ShortWaveInfoData', [
            'page' => $filters['page'],
            'frequency' => $filters['frequency'],
            'broadcasting_now' => $filters['broadcasting_now'],
            'station_id' => $filters['station_id'],
            'language_id' => $filters['language_id'],
            'transmitter_id' => $filters['transmitter_id'],
            'order_by' => $filters['order_by'],
            'stations' => $stations,
            'languages' => $languages,
            'transmitters' => $transmitters,
            'dateOfData' => $dateOfData,
            'day' => $day,
            'time' => $time,
            'user' => $user,
        ]);
    }


    public function filterBroadcasts() {

        $filters = $this->sanitizeFilters();

        return SwInfoBroadcast::join('stations', 'station_id', '=', 'stations.id')
            ->leftJoin('station_programmes', 'station_programme_id', '=', 'station_programmes.id')
            ->join('languages', 'language_id', '=', 'languages.id')
            ->join('transmitters', 'transmitter_id', '=', 'transmitters.id')
            ->select('sw_info_broadcasts.id', 'sw_info_broadcasts.station_id', 'stations.name as station_name', 'sw_info_broadcasts.station_programme_id', 'station_programmes.name as station_programme_name', 'sw_info_broadcasts.language_id', 'languages.name as language_name', 'sw_info_broadcasts.transmitter_id', 'transmitters.name as transmitter_name', 'transmitters.longitude as transmitter_longitude', 'transmitters.latitude as transmitter_latitude', 'sw_info_broadcasts.frequency', 'sw_info_broadcasts.start_time', 'sw_info_broadcasts.end_time', 'sw_info_broadcasts.weekdays', 'sw_info_broadcasts.strength')
            ->nowTime($filters['broadcasting_now'])
            ->frequency($filters['frequency'])
            ->station($filters['station_id'])
            ->language($filters['language_id'])
            ->transmitter($filters['transmitter_id'])
            ->orderBy($filters['order_by'])
            ->paginate(50);
    }


    private function sanitizeFilters() {

        $page = intval(request('page')) > 0 ? intval(request('page')) : 1;
        $frequency = intval(request('frequency')) >= 300 && intval(request('frequency')) <= 30000 ? intval(request('frequency')) : 0;

        $broadcasting_now = true;

        if (request('broadcasting_now') === false || request('broadcasting_now') === 'false') $broadcasting_now = false;

        $station_id = intval(request('station_id'));
        $language_id = intval(request('language_id'));
        $transmitter_id = intval(request('transmitter_id'));
        $order_by = request('order_by') === 'frequency' || request('order_by') === 'station_id' || request('order_by') === 'start_time' ? request('order_by') : 'frequency';

        $querystring = Arr::query(['page' => $page, 'frequency' => $frequency, 'broadcasting_now' => $broadcasting_now, 'station_id' => $station_id, 'language_id' => $language_id, 'transmitter_id' => $transmitter_id, 'order_by' => $order_by]);

        return ['page' => $page, 'querystring' => $querystring, 'frequency' => $frequency, 'broadcasting_now' => $broadcasting_now, 'station_id' => $station_id, 'language_id' => $language_id, 'transmitter_id' => $transmitter_id, 'order_by' => $order_by];
    }


    public function checkFrequency() {

        $frequency = intval(request('frequency'));

        if ($frequency < 2000 || $frequency > 30000) return false;

        $time = substr(request('time'), 11);

        // get a bitwise number for the day of the week
        $day = date('N') + 1;
        $day = $day === 8 ? '1' : $day;
        $day = pow(2, $day);

        return SwInfoBroadcast::with('station', 'station_programme', 'language')
        ->frequency($frequency)
        ->nowTime(true)
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
