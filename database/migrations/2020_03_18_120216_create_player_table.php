<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePlayerTable extends Migration
{
    /**
     * Run the migrations.
     *
     * 
     * @return void
     */
    public function up()
    {
        Schema::create('player', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name');
            $table->string('colours')->nullable()->default('');
            $table->tinyInteger('injured_available')->nullable()->default(0);
            $table->tinyInteger('injured_out')->nullable()->default(0);
            $table->tinyInteger('missing')->nullable()->default(0);
            $table->tinyInteger('suspended')->nullable()->default(0);
            $table->decimal('cost', 10, 2)->nullable()->default(0.00);
            $table->string('position')->nullable()->default('');
            $table->string('club')->nullable()->default('');
            $table->integer('points')->nullable()->default(0);
            $table->integer('bought_status')->nullable()->default(0);
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
        Schema::drop('player');
    }
}
