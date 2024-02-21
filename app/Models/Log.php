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

    protected static function applyFilters(bool $log_owners, bool $time_filter, string $bottom_time_range, string $top_time_range, int $weekday, int $frequency, int $station_type, int $station_id, int $language_id, int $quality, string $commentSearch, string $order, bool $group_by, bool $match_swinfo, bool $antimatch_swinfo)
    {
        $logQuery = (new Log)->newQuery();

        if (!$group_by) $logQuery->join('users', 'logs.user_id', '=', 'users.id');

        if ($group_by) {
            $logQuery->select('logs.station_id', 'logs.station_programme_id', 'logs.language_id', 'logs.frequency', 'stations.name as station_name', 'stations.station_type_id as station_type_id', 'station_programmes.name as station_programme_name', 'languages.name as language_name');
            $logQuery->selectRaw('count(*) as count');
            $logQuery->selectRaw('avg(logs.quality) as quality');
        }

        if (!$group_by) {
            $logQuery->select('logs.id', 'logs.user_id', 'logs.station_id', 'logs.station_programme_id', 'logs.language_id', 'logs.frequency', 'logs.datetime', 'logs.quality', 'logs.comment', 'users.name as username', 'stations.name as station_name', 'stations.station_type_id as station_type_id', 'station_programmes.name as station_programme_name', 'languages.name as language_name');
        }

        $logQuery->join('stations', 'logs.station_id', '=', 'stations.id');
        $logQuery->leftJoin('station_programmes', 'logs.station_programme_id', '=', 'station_programmes.id');
        $logQuery->join('languages', 'logs.language_id', '=', 'languages.id');
        $logQuery->user($log_owners);
        $logQuery->time($time_filter, $bottom_time_range, $top_time_range);
        $logQuery->weekday($weekday);
        $logQuery->frequency($frequency);
        $logQuery->stationType($station_type);
        $logQuery->station($station_id);
        $logQuery->language($language_id);
        $logQuery->quality($quality);
        $logQuery->orderByRaw($order);

        if (!$group_by && !empty($commentSearch)) $logQuery->commentSearch($commentSearch);

        if ($group_by) $logQuery->groupBy(['logs.station_id', 'stations.name', 'stations.station_type_id', 'logs.station_programme_id', 'station_programmes.name', 'logs.frequency', 'logs.language_id', 'languages.name']);

        if ($match_swinfo || $antimatch_swinfo) {
            $logQuery->swInfoMatch($match_swinfo, $antimatch_swinfo);
        }


        return $logQuery->paginate(20);
    }

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

    protected function scopeSwInfoMatch(Builder $query, bool $match_swinfo, bool $antimatch_swinfo): void
    {
        if ($match_swinfo) $sql = 'in';
        else if ($antimatch_swinfo) $sql = 'not in';

        $day = date('N') + 1;
        $day = $day === 8 ? '1' : $day;
        $day = pow(2, $day);

        $query->whereRaw('
            (`logs`.`station_id`, `logs`.`language_id`, `logs`.`frequency`)
            ' . $sql . ' (
            SELECT `sw_info_broadcasts`.`station_id`, `sw_info_broadcasts`.`language_id`, `sw_info_broadcasts`.`frequency`
            FROM `sw_info_broadcasts`
            WHERE `sw_info_broadcasts`.`weekdays` & ' . $day . ' AND
            IF(
                `sw_info_broadcasts`.`start_time` > `sw_info_broadcasts`.`end_time`,
                    "' . gmdate('H:i:s') . '" BETWEEN `sw_info_broadcasts`.`start_time` AND "23:59:59" OR
                    "' . gmdate('H:i:s') . '" BETWEEN "00:00:00" AND `sw_info_broadcasts`.`end_time`,
                    "' . gmdate('H:i:s') . '" BETWEEN `sw_info_broadcasts`.`start_time` AND `sw_info_broadcasts`.`end_time`
            )
            )');
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
