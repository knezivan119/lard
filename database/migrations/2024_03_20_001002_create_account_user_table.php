<?php

use App\Models\Account;
use App\Models\User;

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
        Schema::create('account_user', function (Blueprint $table) {
            // $table->id();

            $table->foreignIdFor( Account::class )
                ->constrained()
                ->onDelete('CASCADE')
            ;

            $table->foreignIdFor( User::class )
                ->constrained()
                ->onDelete('CASCADE')
            ;

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('account_user');
    }
};
