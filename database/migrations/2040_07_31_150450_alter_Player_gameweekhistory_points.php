<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AlterPlayerGameweekHistoryPoints extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('player_gameweek_history', function (Blueprint $table) {
            $table->decimal('points', 10, 2)->default(0.00)->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('player_gameweek_history', function (Blueprint $table) {
            //$table->decimal('points', 10, 2)->default(0.00);
        });
    }
}
