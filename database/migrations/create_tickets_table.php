<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('tickets', function (Blueprint $table) {
            $table->id();
            $table->string('identifier', 10)->unique();
            $table->string('ticketable_type')->nullable();
            $table->bigInteger('ticketable_id')->nullable();
            $table->unsignedBigInteger('category_id')->nullable();
            $table->unsignedBigInteger('user_id');
            $table->tinyInteger('status')->default(0);
            $table->tinyInteger('priority')->default(0);
            $table->string('title');
            $table->text('content');
            $table->timestamps();
            $table->foreign('category_id')->references('id')->on('categories')->cascadeOnUpdate()->nullOnDelete();
            $table->foreign('user_id')->references('id')->on('users')->cascadeOnUpdate()->cascadeOnDelete();
        });

        Schema::create('ticket_user', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('ticket_id');
            $table->unsignedBigInteger('user_id');
            $table->timestamps();
            $table->foreign('ticket_id')->references('id')->on('tickets')->cascadeOnUpdate()->cascadeOnDelete();
            $table->foreign('user_id')->references('id')->on('users')->cascadeOnUpdate()->cascadeOnDelete();
        });

    }

    public function down()
    {
        Schema::dropIfExists('ticket_user');
        Schema::dropIfExists('tickets');
    }
};
