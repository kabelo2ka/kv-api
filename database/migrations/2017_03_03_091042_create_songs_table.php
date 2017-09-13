<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateSongsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('songs', function (Blueprint $table) {
            $table->increments('id');

            $table->integer('user_id')->unsigned();
            $table->foreign('user_id')
                ->references('id')->on('users')
                ->onDelete('cascade');

            $table->integer('album_id')->nullable()->default(NULL);
            $table->foreign('album_id')
                ->references('id')->on('albums')
                ->onDelete('cascade');

            $table->integer('genre_id')->unsigned();
            $table->foreign('genre_id')
                ->references('id')->on('genres')
                ->onDelete('cascade');

            $table->string('name', 80);

            $table->string('file_name');

            $table->string('slug')->unique()->nullable();

            $table->integer('likes')->default(0);

            $table->longText('lyrics')->nullable();

            $table->integer('length')->default(0);

            $table->integer('active', [0,1])->default(1);
            $table->integer('downloadable', [0,1])->default(0);
            $table->integer('commentable', [0,1])->default(1);
            $table->integer('private', [0,1])->default(0);

            $table->softDeletes();
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
        Schema::dropIfExists('songs');
    }
}
