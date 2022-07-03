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
     * The model's default values for attributes.
     *
     * @var array
     */
    protected $attributes = [
        'media' => [],
        'payment_token' => [],
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'schema'          => 'string',
        'event_type'      => 'string',
        'asset_id'        => 'integer',
        'media'           => 'array',
        'asset'           => 'array',
        'payment_token'   => 'array',
        'event'           => 'array',
        'contract'        => 'array',
        'accounts'        => 'array',
        'event_id'        => 'integer',
        'event_timestamp' => 'immutable_datetime:U',
        'schema'          => 'string',
        'event_type'      => 'string',
    ];

    public static function forWallet(string $wallet_id, int $limit = 0): Builder
    {
        $query = static::where('accounts->from', $wallet_id)
            ->orWhere('accounts->to', $wallet_id)
            ->orWhere('accounts->winner', $wallet_id)
            ->orWhere('accounts->seller', $wallet_id);

        if ($limit > 0)
            $query = $query->limit($limit);

        return $query;
    }
}
