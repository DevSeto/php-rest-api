<?php
/**
 * Created by PhpStorm.
 * User: user
 * Date: 2/15/19
 * Time: 11:54 AM
 */

namespace App\Services;


use App\Models\UserPreferences;

class UserPreferenceService
{
    public static function assignAfterReply($userId){
        return boolval(UserPreferences::where('user_id', $userId)->first()['assign_after_reply']);
    }

    public static function assignAfterNote($userId){
        return boolval(UserPreferences::where('user_id', $userId)->first()['assign_after_note']);
    }
}