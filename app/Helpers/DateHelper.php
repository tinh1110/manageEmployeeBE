<?php

namespace App\Helpers;

use App\Common\CommonConst;
use Carbon\Carbon;

class DateHelper
{
    /**
     * @param $date
     *
     * @return string
     */
    public static function parseDateToString($date): string
    {
        return Carbon::parse($date)->toDateTimeString();
    }

    /**
     * @param          $date
     * @param string $format
     *
     * @return string
     */
    public static function parseDateToServerDate($date, string $format = 'Y-m-d'): string
    {
        return Carbon::parse($date)->format($format);
    }

    public static function now($format = 'Y-m-d H:i:s'): string
    {
        $now = Carbon::now();

        return $now->format($format);
    }

    public static function timestamp(): int
    {
        return Carbon::now()->getTimestamp();
    }

    public static function diffDatetimeWithUnit($date1, $date2, string $unit = CommonConst::DAY): int
    {
        if (!is_a($date1, Carbon::class)) {
            $date1 = Carbon::make($date1);
        }

        if (!is_a($date2, Carbon::class)) {
            $date2 = Carbon::make($date2);
        }
        return match ($unit) {
            CommonConst::SECOND => $date1->diffInSeconds($date2),
            CommonConst::MINUTE => $date1->diffInMinutes($date2),
            CommonConst::HOUR => $date1->diffInHours($date2),
            default => $date1->diffInDays($date2),
        };
    }
}
