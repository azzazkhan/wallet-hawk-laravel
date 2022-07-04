<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Opensea>
 */
class OpenseaFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        return [
            'schema'          => $this->faker->randomElement(config('hawk.opensea.event.schema')),
            'event_id'        => random_int(100000, 9999999),
            'event_type'      => $this->faker->randomElement(config('hawk.opensea.event.types')),
            'event_timestamp' => now()->subSeconds(random_int(0, 10000))->format('Y-m-d H:i:s'),
            'media'           => [
                'images'    => [
                    'url'       => $this->faker->imageUrl(),
                    'original'  => $this->faker->imageUrl(),
                    'preview'   => $this->faker->imageUrl(),
                    'thumbnail' => $this->faker->imageUrl(),
                ],
                'animation' => [
                    'url'      => $this->faker->imageUrl(),
                    'original' => $this->faker->imageUrl(),
                ],
            ],
            'asset'           => [
                'id'            => random_int(100000, 9999999),
                'name'          => $this->faker->words(random_int(1, 3), true),
                'description'   => $this->faker->sentences(random_int(0, 5), true) ?: null,
                'external_link' => $this->faker->url()
            ],
            'payment_token'   => random_int(0, 1) ? [
                'decimals' => random_int(4, 20),
                'symbol' => 'ETH',
                'eth' => sprintf("%s", random_int(0, 3) * ((float)rand() / (float)getrandmax())),
                'usd' => sprintf("%s.00", random_int(1000, 99999)),
            ] : null,
            'contract'        => [
                'address' => sprintf('0x%s', hash('sha1', microtime() . rand(100, 10000))),
                'type'    => $this->faker->randomElement(['fungible', 'semi-fungible', 'non-fungible']),
                'date'    => now()->subSeconds(random_int(0, 10000))->format('Y-m-d H:i:s'),
            ],
            'accounts'        => rand(0, 1) ? [
                'from'   => sprintf('0x%s', hash('sha1', microtime() . rand(100, 10000))),
                'to'     => sprintf('0x%s', hash('sha1', microtime() . rand(100, 10000))),
                'seller' => null,
                'winner' => null,
            ] : [
                'from'   => null,
                'to'     => null,
                'seller' => sprintf('0x%s', hash('sha1', microtime() . rand(100, 10000))),
                'winner' => sprintf('0x%s', hash('sha1', microtime() . rand(100, 10000))),
            ],
        ];
    }
}
