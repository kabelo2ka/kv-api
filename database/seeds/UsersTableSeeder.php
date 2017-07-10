<?php

use Illuminate\Database\Seeder;
use Faker\Factory as Faker;
use App\User;

class UsersTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        //User::truncate();
        $faker = Faker::create();

        foreach( range(1, 30) as $index ){
            User::create([
                'username' => $faker->unique()->userName(),
                'email' => $faker->unique()->email(),
                'phone_number' => rtrim($faker->unique()->countryISOAlpha3(), '+'),
                'password' => bcrypt( $faker->password(8) ),
            ]);
        }

    }
}
