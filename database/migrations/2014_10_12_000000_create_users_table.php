<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('users', function (Blueprint $table) {
            $table->increments('id');
            $table->increments('artist_id');
            $table->foreign('artist_id')
                ->references('id')->on('artists')
                ->onDelete('cascade');
            $table->string('username')->unique();
            $table->string('email')->unique();
            $table->string('phone_number', 11)->unique()->nullable();
            $table->string('password');
            $table->boolean('confirmed')->nullable()->unique();
            $table->string('confirmation_token', 25);
            $table->rememberToken();
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
        Schema::dropIfExists('users');
    }
}
