<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\AsCollection;
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
    protected $table = 'opensea_events';

    /**
     * Indicates if the model should be timestamped.
     *
     * @var bool
     */
    public $timestamps = false;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'wallet',
        'event_id',
        'event_type',
        'value',
        'accounts',
        'contract_address',
        'collection_slug',
        'created_at',
        'event_timestamp',
        'payment_token',
        'schema',
        'contract',
        'media',
        'collection',
        'asset',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'wallet'           => 'string',
        'event_id'         => 'integer',
        'event_type'       => 'string',
        'value'            => 'integer',
        'accounts'         => AsCollection::class,
        'contract_address' => 'string',
        'collection_slug'  => 'string',
        'created_at'       => 'integer',
        'payment_token'    => AsCollection::class,
        'event_timestamp'  => 'integer',
        'schema'           => 'string',
        'contract'         => AsCollection::class,
        'media'            => AsCollection::class,
        'collection'       => AsCollection::class,
        'asset'            => AsCollection::class,
    ];

    /**
     * Scope a query to only include events for a given wallet address.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @param  string  $wallet
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeForWallet(Builder $query, string $wallet): Builder
    {
        return $query->where('wallet', $wallet);
    }
}
