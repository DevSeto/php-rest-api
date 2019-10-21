<?php

use Illuminate\Database\Seeder;
use App\Models\Permissions;

class PermissionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $rolesCount = Permissions::count();
        if ($rolesCount < count($this->source())) {
            Permissions::insert(array_slice($this->source(), $rolesCount));
        }
    }

    private function source()
    {
        return [

            //  Order
            [
                "name" => "Administrator order workflow",
                "type" => "order"
            ],
            [
                "name" => "View customers",
                "type" => "order"
            ],
            [
                "name" => "View own orders",
                "type" => "order"
            ],
            [
                "name" => "View own invoices",
                "type" => "order"
            ],
            [
                "name" => "View all orders",
                "type" => "order"
            ],
            [
                "name" => "Create orders",
                "type" => "order"
            ],
            [
                "name" => "Edit orders",
                "type" => "order"
            ],
            [
                "name" => "Delete orders",
                "type" => "order"
            ],
            [
                "name" => "Unconditionally delete orders",
                "type" => "order"
            ],


            //  Page Management
            [
                "name" => "Send Invitation to agent",
                "type" => "page_manager"
            ],
            [
                "name" => "Assign agent",
                "type" => "page_manager"
            ],
            [
                "name" => "Create Ticket",
                "type" => "page_manager"
            ],
            [
                "name" => "Create Label",
                "type" => "page_manager"
            ],
            [
                "name" => "Change company profile settings",
                "type" => "page_manager"
            ]
        ];
    }
}
