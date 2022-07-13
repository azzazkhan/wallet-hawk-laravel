<?php

namespace App\Traits\Opensea;

use Illuminate\Support\Facades\Http;
use Symfony\Component\HttpKernel\Exception\HttpException;

trait InteractsWithApi
{
    use HasCounter;

    /**
     * Fetches ERC721 and ERC1155 transactions for specified wallet address
     * from Opensea API and also applies specified filers.
     *
     * @param string $wallet
     * @param ?string $type
     * @param ?string $cursor
     * @param ?int $before_date
     * @param ?int $after_date
     *
     * @return array
     *
     * @throws \Symfony\Component\HttpKernel\Exception\TooManyRequestsHttpException|\Symfony\Component\HttpKernel\Exception\HttpException
     */
    private function getEventsFromAPI(
        string $wallet,
        ?string $type,
        ?string $cursor,
        ?int $before_date,
        ?int $after_date,
    ): array {
        // Increment the daily API calls counter, if daily limit is reached an
        // exception will be thrown and 503 service unavailable response will
        // be sent to the client
        $this->incrementAPICallsCounter();

        // Increment the API calls/sec counter, if limit is reached an
        // exception will be thrown and 429 too many requests response will be
        // sent to the client
        $this->incrementCounter();

        // Make the API call query and request data from the API
        $response = Http::retry(3, 300)
            ->acceptJson()
            ->withHeaders([
                "X-API-KEY" => config('hawk.opensea.api_key')
            ])
            ->get('https://api.opensea.io/api/v1/events', [
                'account_address' => $wallet,
                'event_type'      => in_array($type, config('hawk.opensea.event.types')) ? $type : null,
                'cursor'          => $cursor && preg_match('/(([A-z0-9])=?){60,}$/', $cursor) ? $cursor : null,
                'occurred_before' => $before_date,
                'occurred_after'  => $after_date,
            ]);

        // Decrement the counter so freed up resources can be used by other
        // processes
        $this->decrementCounter();

        // If API sent an error response then throw a 500 internal server error
        // exception
        if ($response->serverError() || $response->clientError())
            throw new HttpException(500, 'The records server sent an invalid response!');

        // Return the API response with events and cursors
        return $response->json(default: [
            'asset_events' => [],
            'next' => null,
            'previous' => null,
        ]);
    }
}
