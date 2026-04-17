<?php

namespace App\Services;

use Carbon\Carbon;
use NumberFormatter;

class FormatService
{
    protected static ?NumberFormatter $numberFormatter = null;

    protected static ?NumberFormatter $currencyFormatter = null;

    public static function money(float $value): string
    {
        if (! self::$currencyFormatter) {
            self::$currencyFormatter = new NumberFormatter('pt_BR', NumberFormatter::CURRENCY);
        }

        return self::$currencyFormatter->formatCurrency($value, 'BRL');
    }

    public static function date(Carbon|string|null $date): string
    {
        if (! $date) {
            return '-';
        }

        $carbon = $date instanceof Carbon ? $date : Carbon::parse($date);

        return $carbon->format('d/m/Y');
    }

    public static function datetime(Carbon|string|null $date): string
    {
        if (! $date) {
            return '-';
        }

        $carbon = $date instanceof Carbon ? $date : Carbon::parse($date);

        return $carbon->format('d/m/Y H:i');
    }

    public static function number(float $value, int $decimals = 2): string
    {
        return number_format($value, $decimals, ',', '.');
    }

    public static function percent(float $value): string
    {
        return self::number($value, 1).'%';
    }
}
