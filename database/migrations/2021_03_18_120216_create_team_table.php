<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTeamTable extends Migration
{
    /**
     * Run the migrations.
     *
     * 
     * @return void
     */
    public function up()
    {
        Schema::create('team', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name');
            $table->string('formation')->nullable()->default('');
            $table->tinyInteger('wildcard_used')->nullable()->default(0);
            $table->tinyInteger('bench_boost_used')->nullable()->default(0);
            $table->tinyInteger('triple_captain_used')->nullable()->default(0);
            $table->tinyInteger('bench_boost_week_number')->nullable()->default(0);
            $table->tinyInteger('triple_captain_week_number')->nullable()->default(0);
            $table->integer('user_id');
            $table->string('position')->nullable()->default('');
            $table->decimal('budget', 10, 2);
            $table->tinyInteger('free_transfer_used')->nullable()->default(0);
            $table->integer('spark_team_id')->nullable()->default(0);
            $table->integer('overall_rank')->nullable()->default(0);
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
        Schema::drop('team');
    }
}
