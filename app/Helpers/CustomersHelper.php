<?php

namespace App\Helpers;

use App\Exceptions\WrongCustomerNameException;
use App\Models\Customers;
use App\Models\Tickets;
use Illuminate\Support\Facades\Lang;

class CustomersHelper
{

    /**
     * If customer exists return customer otherwise create customer than return
     * @param $customerEmail
     * @param $customerName
     * @return array
     * @throws \Exception
     */

    public static function getCustomer($customerEmail, $customerName, $emailName = false)
    {
        $customer = Customers::where('email', $customerEmail)->first();
        $customerFirstName = explode(' ', $customerName)[0];
        $customerLastName = explode(' ', $customerName)[1];
        //if is a new customer
        if (empty($customer)) {
            if (count(explode(' ', $customerName)) > 1) {
                $customer = Customers::create([
                    'email' => $customerEmail,
                    'first_name' => $customerFirstName,
                    'last_name' => $customerLastName,
                    'mailbox_id' => 1,
                    'reply' => '0'
                ]);

            } else {
                throw new WrongCustomerNameException();
            }
        }

        $customer = $customer->toArray();
        if ($customer['first_name'] !== $customerFirstName || $customer['last_name'] !== $customerLastName && $emailName == true) {
            $update = Customers::where('id', $customer['id'])->update([
                'first_name' => $customerFirstName,
                'last_name' => $customerLastName
            ]);

            $ticketCustomerName = "$customerFirstName $customerLastName";
            Tickets::where('customer_id', $customer['id'])->update(['customer_name' => $ticketCustomerName]);

            if ($update) {
                $customer['first_name'] = $customerFirstName;
                $customer['last_name'] = $customerLastName;
            }
        }

        return $customer;
    }

    public static function getCustomerByEmail($email)
    {
        return Customers::where('email', $email)->first();
    }
}