<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Route;

class BandsController extends Controller
{

    private $broadcast_colour = '#f5ed95';
    private $amateur_colour = '#bd95f5';
    private $aeronautical_colour = '#95f598';



    public function index() {

        return Inertia::render('Bands', [
            'user' => auth()->user(),
            'broadcast_bands' => config('hf_bands.broadcast_bands'),
            'amateur_bands' => config('hf_bands.amateur_bands'),
            'both_aeronautical_bands' => config('hf_bands.aeronautical_bands'),
            'civil_aeronautical_bands' => config('hf_bands.civil_aeronautical_bands'),
            'military_aeronautical_bands' => config('hf_bands.military_aeronautical_bands'),
            'broadcast_colour' => $this->broadcast_colour,
            'amateur_colour' => $this->amateur_colour,
            'aeronautical_colour' => $this->aeronautical_colour,
        ]);
    }


    public function getBand($frequency) {

        if ($frequency < 30 || $frequency > 30000) return false;

        $frequency_band = null;
        $wave_band = null;
        $aeronautical_wave_band = null;
        $metre_band = null;

        // dd(config('hf_bands.low_frequency'));


        // work out whether the frequency is LF, MF or HF
        if ($frequency >= config('hf_bands.low_frequency')[0] && $frequency < config('hf_bands.low_frequency')[1]) {
            $frequency_band = 'LF';
        }
        elseif ($frequency >= config('hf_bands.medium_frequency')[0] && $frequency < config('hf_bands.medium_frequency')[1]) {
            $frequency_band = 'MF';
        }
        elseif ($frequency >= config('hf_bands.high_frequency')[0] && $frequency < config('hf_bands.high_frequency')[1]) {
            $frequency_band = 'HF';
        }

        // check whether the frequency comes within longwave, mediumwave or shortwave
        if ($frequency >= config('hf_bands.longwave')[0] && $frequency < config('hf_bands.longwave')[1]) {
            $wave_band = 'LW';
        }
        elseif ($frequency >= config('hf_bands.mediumwave')[0] && $frequency < config('hf_bands.mediumwave')[1]) {
            $wave_band = 'MW';
        }
        elseif ($frequency >= config('hf_bands.shortwave')[0] && $frequency < config('hf_bands.shortwave')[1]) {
            $wave_band = 'SW';
        }


        // check to see if it falls within any of the broadcast bands

        foreach (config('hf_bands.broadcast_bands') as $band) {
            if ($frequency >= $band[0] && $frequency < $band[1])
                $metre_band = [$band[2], 'broadcast'];
        }

        if (! $metre_band) {
            // check to see if it falls within any of the amateur bands
            foreach (config('hf_bands.amateur_bands') as $band) {
                if ($frequency >= $band[0] && $frequency < $band[1])
                    $metre_band = [$band[2], 'amateur'];
            }
        }

        // check to see if it falls within any of the civil aeronautical bands
        foreach (config('hf_bands.civil_aeronautical_bands') as $band) {
            if ($frequency >= $band[0] && $frequency < $band[1])
                $aeronautical_wave_band = 'aeronautical (civil)';
        }

        // check to see if it falls within any of the military aeronautical bands
        foreach (config('hf_bands.military_aeronautical_bands') as $band) {
            if ($frequency >= $band[0] && $frequency < $band[1])
                $aeronautical_wave_band = 'aeronautical (military)';
        }


        return [
            'frequency' => $frequency_band,
            'metre' => $metre_band,
            'wave' => $wave_band,
            'aeronautical' => $aeronautical_wave_band,
        ];
    }



}
