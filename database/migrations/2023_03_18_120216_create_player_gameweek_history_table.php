<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreatePlayerGameWeekHistoryTable extends Migration
{
    /**
     * Run the migrations.
     *
     *
     * @return void
     */
    public function up()
    {
        Schema::create('player_gameweek_history', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('player_id')->nullable()->default(0);
            $table->integer('team_id');
            $table->integer('club')->nullable()->default(0);
            $table->integer('week_number')->nullable()->default(0);
            $table->tinyInteger('injured')->nullable()->default(0);
            $table->integer('missing')->nullable()->default(0);
            $table->integer('suspended')->nullable()->default(0);
            $table->tinyInteger('c_v_c')->nullable()->default(0);
            $table->tinyInteger('bench_boost')->nullable()->default(0);
            $table->tinyInteger('triple_captain')->nullable()->default(0);
            $table->tinyInteger('on_bench')->nullable()->default(0);
            $table->string('position_used_for')->nullable()->default('');
            $table->integer('points')->nullable()->default(0);
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
        Schema::drop('player_gameweek_history');
    }
}
