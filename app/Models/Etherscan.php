<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Etherscan extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'etherscan_transactions';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'block_timestamp',
        'block_number',
        'hash',
        'accounts',
        'gas',
        'token',
        'confirmations',
        'input',
        'nonce',
        'value',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'block_timestamp' => 'integer',
        'block_number'    => 'integer',
        'hash'            => 'string',
        'accounts'        => 'array',
        'gas'             => 'array',
        'token'           => 'array',
        'confirmations'   => 'integer',
        'nonce'           => 'integer',
        'value'           => 'integer',
        'input'           => 'string',
    ];
}
