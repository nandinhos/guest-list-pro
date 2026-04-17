<?php

use App\Services\FormatService;
use Carbon\Carbon;

if (! function_exists('format_money')) {
    function format_money(float $value): string
    {
        return FormatService::money($value);
    }
}

if (! function_exists('format_date')) {
    function format_date(Carbon|string|null $date): string
    {
        return FormatService::date($date);
    }
}

if (! function_exists('format_datetime')) {
    function format_datetime(Carbon|string|null $date): string
    {
        return FormatService::datetime($date);
    }
}

if (! function_exists('format_number')) {
    function format_number(float $value, int $decimals = 2): string
    {
        return FormatService::number($value, $decimals);
    }
}

if (! function_exists('format_percent')) {
    function format_percent(float $value): string
    {
        return FormatService::percent($value);
    }
}
