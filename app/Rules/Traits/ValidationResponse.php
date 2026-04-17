<?php

namespace App\Rules\Traits;

trait ValidationResponse
{
    protected static function success(array $extra = []): array
    {
        return array_merge(['allowed' => true], $extra);
    }

    protected static function failure(string $message, array $extra = []): array
    {
        return array_merge(['allowed' => false, 'message' => $message], $extra);
    }
}
