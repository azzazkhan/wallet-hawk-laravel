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
        Schema::create('wallets', function (Blueprint $table) {
            $table->id();
            $table->string('wallet_id')->unique();

            // Have we indexed all the data?
            $table->boolean('opensea_indexed')->default(false);
            $table->boolean('etherscan_indexed')->default(false);

            // Last requested
            $table->timestamp('last_opensea_request')->nullable();
            $table->timestamp('last_etherscan_request')->nullable();

            // Last paginated
            $table->timestamp('last_opensea_pagination')->nullable();
            $table->timestamp('last_etherscan_pagination')->nullable();

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
        Schema::dropIfExists('wallets');
    }
};
