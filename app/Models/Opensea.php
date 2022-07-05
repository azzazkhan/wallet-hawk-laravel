<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Opensea extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'opensea_transactions';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'schema',
        'event_type',
        'asset_id',
        'media',
        'asset',
        'payment_token',
        'event',
        'contract',
        'accounts',
        'event_id',
        'event_timestamp',
        'schema',
        'event_type'
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'schema'          => 'string',
        'event_id'        => 'integer',
        'event_type'      => 'string',
        'event_timestamp' => 'immutable_datetime:U',
        'media'           => 'array',
        'asset'           => 'array',
        'payment_token'   => 'array',
        'contract'        => 'array',
        'accounts'        => 'array',
    ];

    public static function forWallet(
        string $wallet_id,
        string $type = null,
        int|null $limit = 0,
    ): Builder {
        return static::query()
            // If event type is specified then grab events of only specified event type
            ->when(
                is_string($type) && in_array($type, config('hawk.opensea.event.types')),
                fn (Builder $builder) => $builder->where('event_type', $type)
            )
            // Grab records for passed wallet address
            ->where(function (Builder $builder) use ($wallet_id) {
                return $builder
                    ->where('accounts->from', $wallet_id)
                    ->orWhere('accounts->to', $wallet_id)
                    ->orWhere('accounts->winner', $wallet_id)
                    ->orWhere('accounts->seller', $wallet_id);
            })
            ->orderBy('event_timestamp', 'desc')
            // If limit is specified then limit the records
            ->when($limit && $limit > 0, fn (Builder $builder) => $builder->limit($limit));
    }
}
