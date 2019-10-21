<?php

use Illuminate\Database\Seeder;
use App\Models\UserPreferences;


class UserPreferencesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $userPreferencesCount = UserPreferences::count();
        if ($userPreferencesCount < count($this->source())) {
            UserPreferences::insert(array_slice($this->source(), $userPreferencesCount));
        }
    }

    private function source()
    {
        return [
            [
                'user_id' => 1,
                'answer' => 'available',
                'assign_after_reply' => 0,
                'take_back_after_reply' => 0,
                'assign_after_note' => 0,
                'take_back_after_note' => 0,
                'take_back_after_update' => 0,
                'delay_sending' => 0
            ]
        ];
    }
}
