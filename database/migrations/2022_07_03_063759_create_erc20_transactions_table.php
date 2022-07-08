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
        Schema::create('erc20_transactions', function (Blueprint $table) {
            $table->id();

            $table->json('accounts'); // Sender and receiver

            // Block information
            $table->timestamp('block_timestamp');
            $table->unsignedInteger('block_number')->unique();
            $table->string('block_hash')->unique();

            $table->string('hash')->unique(); // Transaction hash

            // Pricing details
            $table->json('gas');

            $table->json('token');

            $table->string('input');

            $table->unsignedInteger('nonce');
            $table->unsignedInteger('value');
            $table->unsignedInteger('confirmations');
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
        Schema::dropIfExists('erc20_transactions');
    }
};
