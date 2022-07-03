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

            $table->json('accounts');
            $table->json('block');
            $table->json('gas');
            $table->json('token');

            $table->string('hash');
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
