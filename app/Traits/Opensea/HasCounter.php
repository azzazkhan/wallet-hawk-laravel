<?php

namespace App\Traits\Opensea;

use Illuminate\Support\Facades\Cache;
use Symfony\Component\HttpKernel\Exception\ServiceUnavailableHttpException;
use Symfony\Component\HttpKernel\Exception\TooManyRequestsHttpException;

trait HasCounter
{
    private static string $__counter_daily_limit_key = "opensea_daily_calls_count";
    private static string $__counter_limit_key = "opensea_calls_count";

    /**
     * Increments the Opensea API calls/sec counter and throws an exception if
     * max calls/sec limit is reached.
     *
     * @return bool
     *
     * @throws Symfony\Component\HttpKernel\Exception\TooManyRequestsHttpException
     */
    private function incrementCounter(): bool
    {
        if (!$this->canIncrement())
            throw new TooManyRequestsHttpException(
                5,
                'Records server is overloaded, please try again in few seconds'
            );

        Cache::increment(static::$__counter_limit_key);

        return true;
    }

    /**
     * Decrements the opensea API calls counter.
     *
     * @return void
     */
    private function decrementCounter(): void
    {
        // If counter does not exist then create one and set it to zero
        $counter = Cache::get(static::$__counter_limit_key, function () {
            Cache::put(static::$__counter_limit_key, 0);

            return 0;
        });

        // If somehow counter has gone below zero then set it back to zero
        if ($counter < 0) {
            static::resetCounter();
            return;
        }

        // Decrement the counter if it could be
        Cache::decrement(static::$__counter_limit_key);
    }

    /**
     * Checks if we can increment the opensea calls counter or not. This
     * counter's intended purpose is that we cannot exceed our specified max
     * calls/sec and violate Opensea API TOS.
     *
     * @return bool
     */
    private function canIncrement(): bool
    {
        $counter = Cache::get(static::$__counter_limit_key, function () {
            Cache::put(static::$__counter_limit_key, 0);

            return 0;
        });

        return $counter < config('hawk.opensea.network.max_calls_sec');
    }

    /**
     * Resets Opensea API calls/sec counter to zero.
     *
     * @return void
     */
    public static function resetCounter(): void
    {
        Cache::put(static::$__counter_limit_key, 0);
    }

    /**
     * Increments the total Opensea API call counter used to cap daily number
     * of API calls sent or throws an exception if daily API calls limit is
     * reached.
     *
     * @return void
     *
     * @throws \Symfony\Component\HttpKernel\Exception\ServiceUnavailableHttpException
     */
    private function incrementAPICallsCounter(): void
    {
        if (!$this->canIncrementAPICallsCounter())
            throw new ServiceUnavailableHttpException(5, 'Daily API calls limit is reached!');

        Cache::increment(static::$__counter_daily_limit_key);
    }

    /**
     * Checks if have reached daily API calls limit or not.
     *
     * @return bool
     */
    private function canIncrementAPICallsCounter(): bool
    {
        $counter = Cache::get(static::$__counter_daily_limit_key, function () {
            Cache::put(static::$__counter_daily_limit_key, 0);

            return 0;
        });

        return $counter < config('hawk.opensea.network.max_calls_daily', INF);
    }

    /**
     * Resets daily API calls counter.
     *
     * @return void
     */
    public static function resetAPICallsCounter(): void
    {
        Cache::put(static::$__counter_daily_limit_key);
    }
}
