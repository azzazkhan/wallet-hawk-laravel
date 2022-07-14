<?php

namespace App\Models;

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
        'wallet',
        'schema',
        'asset_id',
        'event_type',
        'event_timestamp',
        'media',
        'asset',
        'payment_token',
        'contract',
        'accounts',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'wallet'          => 'string',
        'schema'          => 'string',
        'event_id'        => 'integer',
        'event_type'      => 'string',
        'event_timestamp' => 'integer',
        'media'           => 'array',
        'asset'           => 'array',
        'payment_token'   => 'array',
        'contract'        => 'array',
        'accounts'        => 'array',
    ];
}
