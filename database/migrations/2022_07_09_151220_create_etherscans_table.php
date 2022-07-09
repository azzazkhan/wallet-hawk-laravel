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
        Schema::create('etherscan_transactions', function (Blueprint $table) {
            $table->id();

            $table->timestamp('block_timestamp');
            $table->unsignedInteger('block_number');
            $table->string('hash')->unique();

            $table->json('accounts'); // Recipients information
            $table->json('gas'); // Pricing details
            $table->json('token'); // Asset token details

            $table->unsignedInteger('confirmations');
            $table->unsignedInteger('nonce');
            $table->unsignedBigInteger('value');
            $table->string('input');


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
        Schema::dropIfExists('etherscan_transactions');
    }
};
