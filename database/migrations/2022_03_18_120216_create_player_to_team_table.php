<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreatePlayerToTeamTable extends Migration
{
    /**
     * Run the migrations.
     *
     *
     * @return void
     */
    public function up()
    {
        Schema::create('player_to_team', function (Blueprint $table) {
            $table->integer('player_id')->nullable()->default(0);
            $table->integer('player_club')->nullable()->default(0);
            $table->integer('team_id');
            $table->decimal('player_cost', 10, 2)->nullable()->default(0);
            $table->string('position_used_for')->nullable()->default('');
            $table->tinyInteger('on_bench')->nullable()->default(0);
            $table->timestamps();
            $table->tinyInteger('c_v_c')->nullable()->default(0);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('player_to_team');
    }
}
