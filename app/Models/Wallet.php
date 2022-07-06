<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Wallet extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'wallet_id',
        'opensea_indexed',
        'etherscan_indexed',
        'last_opensea_request',
        'last_etherscan_request',
        'last_opensea_pagination',
        'last_etherscan_pagination',
    ];

    /**
     * The model's default values for attributes.
     *
     * @var array
     */
    protected $attributes = [
        'opensea_indexed' => false,
        'etherscan_indexed'   => false,
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'opensea_indexed'           => 'boolean',
        'etherscan_indexed'         => 'boolean',
        'last_opensea_request'      => 'datetime:U',
        'last_etherscan_request'    => 'datetime:U',
        'last_opensea_pagination'   => 'datetime:U',
        'last_etherscan_pagination' => 'datetime:U',
    ];
}
