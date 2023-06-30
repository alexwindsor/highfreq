<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Route;

class BandsController extends Controller
{

    private $broadcast_colour = '#f5ed95';
    private $amateur_colour = '#bd95f5';
    

    public function index() {

        return Inertia::render('Bands', [
            'user' => auth()->user(),
            'broadcast_bands' => config('hf_bands.broadcast_bands'),
            'amateur_bands' => config('hf_bands.amateur_bands'),
            'broadcast_colour' => $this->broadcast_colour,
            'amateur_colour' => $this->amateur_colour,
        ]);
    }


    public function changeBandZoom() {

        $full_width = intval(request('zoom')) * 1000;
        $band_types = [config('hf_bands.broadcast_bands'), config('hf_bands.amateur_bands')];
        $band_html = ['', ''];

        foreach ($band_types as $type_key => $band_type) {

            $previous_end = 0;

            foreach ($band_type as $band) {

                $start = round(($band[0] / 30000) * $full_width);
                $end = round(($band[1] / 30000) * $full_width);
                $width = $end - $start;
                $margin_left = $start - $previous_end;
                $color = $type_key === 0 ? $this->broadcast_colour : $this->amateur_colour;
                $margin_top = $type_key === 0 ? 1 : 23;
    
                $band_html[$type_key] .= '<div class="inline-block text-xs p-1 border border-black" style="width:';
                $band_html[$type_key] .= $width;
                $band_html[$type_key] .= 'px;margin-left:';
                $band_html[$type_key] .= $margin_left;
                $band_html[$type_key] .= 'px;margin-top:' . $margin_top . 'px;height:100px;background-color:' . $color . ';">';
                $band_html[$type_key] .= $band[0] . 'khz to ' . $band[1] . 'khz<br>';
                $band_html[$type_key] .= $band[2];
                $band_html[$type_key] .= '</div>';
                $band_html[$type_key] .= "\n";

                $previous_end = $end;
            }
        }

        $measure_html = '';
        $section_width = ($full_width / 30000) * 1000;

        for ($i = 0; $i < 30; $i++) {
            $measure_html .= '<div class="inline-block text-xs p-1 border-r border-black" style="width:' . $section_width . 'px;height:30px;margin-top:45px;background-color:#888">';
            $measure_html .= $i . '000kHz</div>';
        }

        $html = '<div>' . $band_html[0] . '</div><div>' . $band_html[1] . '</div><div>' . $measure_html . '</div>';

        return $html;
    }


    public function getBand($frequency) {

        $frequency_band = null;
        $wave_band = null;
        $metre_band = null;

        // work out whether the frequency is LF, MF or HF
        if ($frequency >= config('hf_bands.low_frequency')[0] && $frequency < config('hf_bands.low_frequency')[1]) $frequency_band = 'LF';
        elseif ($frequency >= config('hf_bands.medium_frequency')[0] && $frequency < config('hf_bands.medium_frequency')[1]) $frequency_band = 'MF';
        elseif ($frequency >= config('hf_bands.high_frequency')[0] && $frequency < config('hf_bands.high_frequency')[1]) $frequency_band = 'HF';

        // check to see if it falls within any of the amateur bands
        foreach (config('hf_bands.amateur_bands') as $amateur_band) {
            if ($frequency >= $amateur_band[0] && $frequency <= $amateur_band[1]) $metre_band = [$amateur_band[2], 'amateur'];
        }
        // check to see if it falls within any of the broadcast bands
        if (! $metre_band) {
            foreach (config('hf_bands.broadcast_bands') as $broadcast_band) {
                if ($frequency >= $broadcast_band[0] && $frequency <= $broadcast_band[1]) $metre_band = [$amateur_band[2], 'broadcast'];
            }
        }

        // check whether the frequency comes within longwave, mediumwave or shortwave
        if ($frequency >= config('hf_bands.longwave')[0] && $frequency < config('hf_bands.longwave')[1]) $wave_band = 'Longwave';
        elseif ($frequency >= config('hf_bands.mediumwave')[0] && $frequency < config('hf_bands.mediumwave')[1]) $wave_band = 'Medium Wave';
        elseif ($frequency >= config('hf_bands.shortwave')[0] && $frequency < config('hf_bands.shortwave')[1]) $wave_band = 'Short Wave';
        
        return [
            'frequency' => $frequency_band,
            'wave' => $wave_band,
            'metre' => $metre_band
        ];
    }



}
