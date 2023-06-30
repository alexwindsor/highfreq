<?php

namespace App\Http\Controllers\SwInfo;


class Weekdays extends SwInfoDataRip
{

    protected static function swInfoDaysToBitwise(string $swInfoDays) : int
    {

        $days = str_split($swInfoDays);

        $bitwise = 0;

        foreach ($days as $day) {
            $bitwise += $day == 1 ? 2 : 0;
            $bitwise += $day == 2 ? 4 : 0;
            $bitwise += $day == 3 ? 8 : 0;
            $bitwise += $day == 4 ? 16 : 0;
            $bitwise += $day == 5 ? 32 : 0;
            $bitwise += $day == 6 ? 64 : 0;
            $bitwise += $day == 7 ? 128 : 0;
        }

        return $bitwise;

    }

}
