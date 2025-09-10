<?php

namespace Database\Seeders;

use App\Models\Account;
use App\Models\User;
// use App\Models\Quote;
// use App\Models\QuoteItem;
// use Laravel\Sanctum\Sanctum;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class TestingSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            RolesAndPermissionsSeeder::class,
        ]);

        $accounts = Account::factory( 1 )->create();

        User::factory( 3 )->create();
        User::all()->each( function ( $user ) use ( $accounts ) {
            $role = match( $user->id ) {
                1 => 'root',
                2 => 'admin',
                default => 'retailer',
            };

            $user->assignRole( $role );

            $user->accounts()->attach(
                // $accounts->random( rand(1, 3) )
                $accounts->random( 1 )
                    ->pluck('id')
                    ->toArray()
            );
        });

        $this->call([
            // StateSeeder::class,
            // LocationSeeder::class,
        ]);
    }
}
