<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Log extends Model
{
    use HasFactory;

    public $timestamps = false;
    public $guarded = ['id'];

    protected function scopeUser(Builder $query, bool $logOwners): void
    {
        // 0 = just show my logs, 1 = show all logs
        if ($logOwners === false) $query->where('user_id', '=', auth()->user()->id);
    }

    protected function scopeFrequency(Builder $query, int $frequency): void
    {
        if ($frequency >= 100 && $frequency <= 30000) $query->where('frequency', '=', $frequency);
    }

    protected function scopeStationType(Builder $query, int $station_type_id): void
    {
        $query->whereRaw('stations.station_type_id = ?', [$station_type_id]);
    }

    protected function scopeStation(Builder $query, int $station_id): void
    {
        if ($station_id > 0) $query->where('logs.station_id', '=', $station_id);
    }

    protected function scopeLanguage(Builder $query, int $language_id): void
    {
        if ($language_id > 0) $query->where('logs.language_id', '=', $language_id);
    }

    protected function scopeQuality(Builder $query, int $quality): void
    {
        if ($quality === 2 || $quality === 3) $query->where('quality', '>=', $quality);
    }

    protected function scopeWeekday(Builder $query, int $weekday): void
    {
        // 1 = sunday, 7 = saturday, 0 = no filter
        if ($weekday >= 1 && $weekday <= 7) $query->whereRaw('dayofweek(datetime) = '. $weekday);
    }

    protected function scopeTime(Builder $query, bool $time_filter, string $bottom_time_range, string $top_time_range): void
    {
        if ($time_filter) {
            if (strtotime($bottom_time_range) < strtotime($top_time_range)) {
                $query->whereRaw('DATE_FORMAT(`datetime`, "%H:%i") BETWEEN "' . $bottom_time_range . '" AND "' . $top_time_range . '"');
            }
            else {
                $query->whereRaw('(DATE_FORMAT(`datetime`, "%H:%i") BETWEEN "' . $bottom_time_range . '" AND "23:59" OR DATE_FORMAT(`datetime`, "%H:%i") BETWEEN "00:00" AND "' . $top_time_range . '")');
            }
        }
    }

    protected function scopeCommentSearch(Builder $query, string $commentSearch): void
    {
        if (strlen($commentSearch) > 0) $query->where('comment', 'LIKE', '%' . $commentSearch . '%');

    }


    // relationships
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function station(): BelongsTo
    {
        return $this->belongsTo(Station::class);
    }


    public function stationProgramme(): BelongsTo
    {
        return $this->belongsTo(StationProgramme::class);
    }

    public function language(): BelongsTo
    {
        return $this->belongsTo(Language::class);
    }


}
