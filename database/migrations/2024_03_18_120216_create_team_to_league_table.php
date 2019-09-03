<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTeamToLeagueTable extends Migration
{
    /**
     * Run the migrations.
     *
     * 
     * @return void
     */
    public function up()
    {
        Schema::create('team_to_league', function (Blueprint $table) {
            $table->integer('league_id');
            $table->integer('team_id');
            $table->integer('current_position')->nullable()->default(0);
            $table->integer('previous_position')->nullable()->default(0);
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
        Schema::drop('team_to_league');
    }
}
