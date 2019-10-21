<?php

use Illuminate\Database\Seeder;


class DatabaseSeederCompany extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $this->call(CustomerSeeder::class);
        $this->call(UserRolesSeeder::class);
        $this->call(DemoTicketsSeeder::class);
        $this->call(NotificationsListSeeder::class);
        $this->call(UserPreferencesSeeder::class);

        $this->call(NotificationsSeeder::class);
        $this->call(NotifiedUsersSeeder::class);
    }
}
