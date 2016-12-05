<?php

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        factory(\App\Models\Branch::class, 5)->create()->each(function (\App\Models\Branch $branch) {
            $branch->users()->saveMany(factory(\App\Models\User::class, 3)->make());
        });
    }
}
