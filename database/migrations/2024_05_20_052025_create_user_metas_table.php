<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('user_metas', function (Blueprint $table) {
            $table->unsignedBigInteger('user_id')
                ->unique()
                ->comment('1-to-1 relation')
            ;

            $table->foreign('user_id')
                ->references('id')
                ->on('users')
                ->onDelete('cascade')
            ;

            // $table->foreignIdFor( User::class )
                // ->constrained()
                // ->onDelete('CASCADE')
            // ;

            $table->string('first_name')
                ->nullable()
            ;

            $table->string('last_name')
                ->nullable()
            ;

            $table->string('middle_name')
                ->nullable()
            ;

            $table->jsonb('phones')
                ->nullable()
            ;

            $table->jsonb('addresses')
                ->nullable()
            ;

            $table->jsonb('notes')
                ->nullable()
            ;

            $table->jsonb('extra')
                ->nullable()
                ->comment('JSON')
            ;

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_metas');
    }
};
