<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Casts\Attribute;

class SwInfoBroadcast extends Model
{
    use HasFactory;
    public $timestamps = false;
    public $guarded = ['id'];



    protected function scopeNowTime(Builder $query, bool $ok)
    {
        if ($ok) {
        // get the number for the day of the week
        $day = date('N') + 1;
        $day = $day === 8 ? '1' : $day;
        $day = pow(2, $day);

        $query
            ->where('weekdays', '&', $day)
            ->whereRaw('IF(start_time > end_time, "' . date('H:i:s') . '" BETWEEN `start_time` AND "23:59" OR "' . date('H:i:s') . '" BETWEEN "00:00" AND `end_time`, "' . date('H:i:s') . '" BETWEEN `start_time` AND `end_time`)');
        }
    }

    protected function scopeStation(Builder $query, $station_id)
    {
        if ($station_id > 0) $query->where('sw_info_broadcasts.station_id', '=', $station_id);
    }

    protected function scopeLanguage(Builder $query, int $language_id)
    {
        if ($language_id > 0) $query->where('sw_info_broadcasts.language_id', '=', $language_id);
    }

    protected function scopeTransmitter(Builder $query, int $transmitter_id)
    {
        if ($transmitter_id > 0) $query->where('sw_info_broadcasts.transmitter_id', '=', $transmitter_id);
    }

    protected function scopeFrequency(Builder $query, int $frequency)
    {
        if ($frequency > 0) $query->where('frequency', '=', $frequency);
    }


    protected function weekdays(): Attribute
    {
        return Attribute::make(
            get: function (string $value) {

                $days = [];

                if ($value & 2) $days[] = 'Sun';
                if ($value & 4) $days[] = 'Mon';
                if ($value & 8) $days[] = 'Tue';
                if ($value & 16) $days[] = 'Wed';
                if ($value & 32) $days[] = 'Thu';
                if ($value & 64) $days[] = 'Fri';
                if ($value & 128) $days[] = 'Sat';

                return $days;
            }
        );
    }


    public function station(): BelongsTo
    {
        return $this->belongsTo(Station::class);
    }

    public function station_programme(): BelongsTo
    {
        return $this->belongsTo(StationProgramme::class);
    }

    public function language(): BelongsTo
    {
        return $this->belongsTo(Language::class);
    }

    public function transmitter(): BelongsTo
    {
        return $this->belongsTo(Transmitter::class);
    }

}
