<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('opensea_transactions', function (Blueprint $table) {
            $table->id();
            $table->enum('schema', ['ERC721', 'ERC1155']);
            $table->enum('event_type', config('hawk.opensea.event.types'));
            $table->unsignedInteger('asset_id')->unique();

            // [images => [url, preview, thumbnail, original], animation => [url, original]]
            $table->json('media')->nullable();
            $table->json('asset'); // [name, description, external_link]
            $table->json('payment_token')->nullable(); // [symbol, decimals, eth, usd]
            $table->json('contract'); // [address, type, date]
            $table->json('accounts'); // [from, to, winner, seller]

            $table->unsignedInteger('event_id');
            $table->timestamp('event_timestamp');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('opensea_transactions');
    }
};
