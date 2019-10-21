<?php

namespace App\Helpers;

use DB;
use Response;
use Lang;
use Route;


class CompanyDefaultPermissions
{

    /*----------------------------------------------------------------------------------------------
        If you want to add a new permission, in first you have to add that permission in your DB.
        After that add a property to  Permission class.
        For add a new permission go to  database/seeds/PermissionsSeeder.php file and add a new array, and run db:seed
    -------------------------------------------------------------------------------------------------*/

    /*  Order */

    // Administrator order workflow
//    public  $administratorOrderWorkflow = '';
    public $administratorOrderWorkflow = [
        'path' => '',
        'id' => 1
    ];

    // View customers
    public $viewCustomers = [
        'path' => '',
        'id' => 2
    ];

    // View own orders
    public $viewOwnOrders = [
        'path' => '',
        'id' => 3
    ];

    // View own invoices
    public $viewOwnInvoices = [
        'path' => '',
        'id' => 4
    ];

    // View all orders
    public $viewAllOrders = [
        'path' => '',
        'id' => 5
    ];

    // Create orders
    public $createOrders = [
        'path' => '',
        'id' => 6
    ];

    // Edit orders
    public $editOrders = [
        'path' => '',
        'id' => 7
    ];

    // Delete orders
    public $deleteOrders = [
        'path' => '',
        'id' => 8
    ];

    // Unconditionally delete orders
    public $unconditionallyDeleteOrders = [
        'path' => '',
        'id' => 9
    ];

    /*  Page management */

    // Send Invitation to agent
    public $sendInvitationToAgent = [
        'path' => 'api/settings/company/users/invite',
        'id' => 10
    ];

    // Assign agent
    public $assignAgent = [
        'path' => '',
        'id' => 11
    ];

    // Create Ticket
    public $createTicket = [
        'path' => 'api/tickets',
        'id' => 12
    ];

    // Create Label
    public $createLabel = [
        'path' => 'api/settings/ticketing/labels',
        'id' => 13
    ];
    // Change company profile settings
    public $changeCompanyProfileSettings = [
        'path' => '',
        'id' => 14
    ];


}
