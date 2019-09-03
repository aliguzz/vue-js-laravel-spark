<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreatePlayerGameWeekTable extends Migration
{
    /**
     * Run the migrations.
     *
     *
     * @return void
     */
    public function up()
    {
        Schema::create('player_gameweek', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('player_id');
            $table->integer('week_number');
            $table->integer('clean_sheet');
            $table->integer('number_of_goals');
            $table->integer('number_of_assists');
            $table->integer('penalty_miss');
            $table->integer('penalty_save');
            $table->integer('number_of_yellow_cards');
            $table->integer('number_of_red_cards');
            $table->integer('number_of_goals_conceded');
            $table->integer('match_start');
            $table->integer('played_for_60_mins');
            $table->integer('best_player');
            $table->integer('second_best_player');
            $table->integer('third_best_player');
            $table->integer('hattrick');
            $table->integer('points');
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
        Schema::drop('player_gameweek');
    }
}
