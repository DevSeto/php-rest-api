<?php

use Illuminate\Database\Seeder;
use App\Models\Customers;
use Illuminate\Support\Facades\DB;

class CustomerSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $customersCount = Customers::count();
        if ($customersCount < count($this->source())) {
            Customers::insert(array_slice($this->source(), $customersCount));
        }
    }

    private function source()
    {
        return [
            [
                'email' => 'hello@' . env('PAGE_URL_DOMAIN'),
                'first_name' => 'Lesley',
                'last_name' => 'Yarbrough'
            ]
        ];
    }
}
