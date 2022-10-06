<?php

use Illuminate\Support\Carbon;

if (!function_exists('carbon')) {
    function carbon(...$args): Carbon
    {
        return new Carbon(...$args);
    }
}
