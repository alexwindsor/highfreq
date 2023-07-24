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

    protected function scopeUserFilter(Builder $query, int $logOwners)
    {
        // 0 = just show my logs, 1 = show all logs
        if ($logOwners === 0) $query->where('user_id', '=', auth()->user()->id);
    }

    protected function scopeFrequencyFilter(Builder $query, int $frequency)
    {
        if ($frequency > 1000 && $frequency < 30000) $query->where('frequency', '=', $frequency);
    }

    protected function scopeStationFilter(Builder $query, int $station_id)
    {
        if ($station_id > 0) $query->where('station_id', '=', $station_id);
    }

    protected function scopeQualityFilter(Builder $query, int $quality)
    {
        if ($quality >= 1 && $quality <= 3) $query->where('quality', '>=', $quality);
    }

    protected function scopeWeekdayFilter(Builder $query, int $weekday)
    {
        // 1 = sunday, 7 = saturday, 0 = no filter
        if ($weekday >= 1 && $weekday <= 7) $query->whereRaw('dayofweek(datetime) = '. $weekday);
    }

    protected function scopeTimeFilter(Builder $query, string $time, bool $time_filter)
    {
        if ($time_filter) {
            $query->whereRaw('HOUR(datetime) - HOUR(?) < 1 AND HOUR(datetime) - HOUR(?) > -1', [$time, $time]);
        }
    }

    protected function scopeCommentSearch(Builder $query, string $commentSearch)
    {
        if (strlen($commentSearch) > 0) $query->where('comment', 'LIKE', '%' . $commentSearch . '%');
            
    }





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
