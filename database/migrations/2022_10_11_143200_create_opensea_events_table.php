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
        Schema::create('opensea_events', function (Blueprint $table) {
            $table->id();

            $table->string('wallet');
            $table->unsignedInteger('event_id');

            $table->string('event_type')->default('unknown');
            $table->unsignedBigInteger('value')->default(0);
            $table->json('accounts');
            $table->string('contract_address')->nullable();
            $table->string('collection_slug')->nullable();
            $table->unsignedInteger('created_at')->nullable();
            $table->unsignedInteger('event_timestamp')->default(0);
            $table->json('payment_token')->nullable();
            $table->string('schema')->default('unknown');
            $table->json('contract')->nullable();
            $table->json('media')->nullable();
            $table->json('collection')->nullable();
            $table->json('asset')->nullable();

            $table->unique(['wallet', 'event_id']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('opensea_events');
    }
};
