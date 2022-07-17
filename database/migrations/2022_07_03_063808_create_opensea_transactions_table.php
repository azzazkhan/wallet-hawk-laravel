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
            $table->string('wallet');

            $table->enum('schema', config('hawk.opensea.event.schema'));
            $table->unsignedBigInteger('event_id');
            $table->enum('event_type', config('hawk.opensea.event.types'));
            $table->unsignedInteger('event_timestamp');

            // [images => [url, original, preview, thumbnail], animation => [url, original]]
            $table->json('media')->nullable();
            $table->json('asset'); // [id, name, description, external_link]
            $table->json('payment_token')->nullable(); // [decimals, symbol, eth, usd]
            $table->json('contract'); // [address, type, date]
            $table->json('accounts'); // [from, to, winner, seller]

            $table->unique(['wallet', 'event_id']);

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
