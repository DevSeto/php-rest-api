<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UsersRegistrationSteps extends Model
{
    protected $fillable = [
        'email',
        'first_name',
        'last_name',
        'company_name',
        'company_url',
        'password',
        'subdomain',
        'demo_mailbox_name',
        'mailbox_email',
        'mailbox_name',
        'mailbox_forwarding',
        'step',
    ];
}
