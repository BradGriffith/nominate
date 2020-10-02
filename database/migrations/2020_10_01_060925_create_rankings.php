<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateRankings extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('rankings', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('position_id');
            $table->unsignedBigInteger('nomination_id');
            $table->string('voter', 3);
            $table->string('year', 4);
            $table->unsignedInteger('rank');
            $table->timestamps();
            $table->foreign('position_id')->references('id')->on('positions');
            $table->foreign('nomination_id')->references('id')->on('nominations');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('rankings');
    }
}
