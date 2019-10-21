<?php

namespace App\Exceptions;


use Illuminate\Support\Facades\Lang;

class UnverifiedMailboxException extends \Exception
{
    protected $message;

    public function __construct($message = "", $code = 0, Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
        $this->message = Lang::get('mailbox.unverified_domain');
    }
}