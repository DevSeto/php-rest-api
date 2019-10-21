<?php

use Illuminate\Database\Seeder;
use App\Models\UserRoles;

class UserRolesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $rolesCount = UserRoles::count();
        if ($rolesCount < count($this->source())) {
            UserRoles::insert(array_slice($this->source(), $rolesCount));
        }
    }

    private function source()
    {
        return [
            [
                "name" => "super_admin",
                "default_permissions_ids" => '["1", "2", "3", "4", "5", "6", "7", "8", "9", "10", "11", "12", "13", "14"]',
                "permissions_ids" => '[]',
                "display_name" => "Super Admin"
            ],
            [
                "name" => "account_owner",
                "default_permissions_ids" => '["1", "2", "3", "4", "5", "6", "7", "8"]',
                "permissions_ids" => '["9", "10", "11", "12", "13", "14"]',
                "display_name" => "Account Owner"
            ],
            [
                "name" => "admin",
                "default_permissions_ids" => '["1", "2", "3", "4", "5", "6", "7"]',
                "permissions_ids" => '["8", "9", "10", "11", "12", "13", "14"]',
                "display_name" => "Admin"
            ],
            [
                "name" => "agent",
                "default_permissions_ids" => '["1", "2", "3", "4"]',
                "permissions_ids" => '["5", "6", "7", "8", "9", "10", "11", "12", "13"]',
                "display_name" => "Agent"
            ],
            [
                "name" => "blocked_user",
                "default_permissions_ids" => '[]',
                "permissions_ids" => '[]',
                "display_name" => "Blocked User"
            ]
        ];
    }
}
