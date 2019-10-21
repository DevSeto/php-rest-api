<?php

//header("Access-Control-Allow-Origin: *");


use Illuminate\Http\Request;
use App\Helpers\Helper;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/
Route::post('/login', 'Auth\AuthController@login');
Route::post('/register', 'Auth\AuthController@register');
Route::post('/reg-step1', 'Auth\AuthController@regStep1');
Route::post('/reg-step2', 'Auth\AuthController@regStep2');
Route::post('/reg-step3', 'Auth\AuthController@regStep3');
//Route::post('/reg-step4', 'Auth\AuthController@regStep4');
//Route::post('/reg-step5', 'Auth\AuthController@regStep5');
Route::post('/reg-step-final', 'Auth\AuthController@registerFinalStep');
Route::post('/test', 'TestController@index');
Route::post('/update_token', 'Auth\AuthController@updateUserApiToken');
Route::post('/receive_email', 'MailController@index');
Route::post('/receive_events', 'MailController@getEvent');
Route::get('/check_subdomain/{subdomain}', 'Auth\AuthController@checkIfSubDomainExists');
Route::get('/get-workspace/{email}', 'Auth\AuthController@getWorkspace');
Route::get('/get-subdomain/{subdomain}', 'Auth\AuthController@getSubdomain');
Route::get('/user', 'Settings\User\ProfileController@getUser');
Route::get('/sub_domain/check', 'SuperAdmin\SuperAdminController@checkSubDomain');

// Get invitation
Route::get('/agent-login/{token}', 'Settings\Company\UsersController@getInvitation');

// Send user invite confirm
Route::post('/agent-login/{token}', 'Settings\Company\UsersController@confirmInvitation');

// Forget password
Route::post('/find_email', 'Auth\AuthController@findUserByEmail');
Route::post('/forget_password/check_token', 'Auth\ForgotPasswordController@checkToken');
Route::post('/forget_password/confirm_email', 'Auth\ForgotPasswordController@sendConfirmationEmail');
Route::post('/forget_password/new_password', 'Auth\ForgotPasswordController@createNewPassword');

/****Search****/

// Search email in customers emails
Route::get('/search/customers_emails', 'SearchController@searchCustomers');

// Search tickets
Route::get('/search', 'SearchController@index');

// Tickets main search
Route::get('/main_search', 'SearchController@mainSearch');

/****Search****/


/************************************  Tickets  ***********************************/
Route::group(['prefix' => 'tickets', 'where' => ['ticketId' => '[0-9]+', 'labelId' => '[0-9]+']], function () {

    // Get all tickets
    Route::get('/mailbox/{mailbox_id}', 'Tickets\TicketController@index');
    Route::get('/search', 'Tickets\TicketController@searchTickets');

    // Get all tickets
    Route::get('/without_users', 'Tickets\TicketController@getTicketsForNotifications');

    // Get all tickets
    Route::get('/customer/{id}', 'Tickets\TicketController@getTicketsByCustomerId');

    // Create new ticket
    Route::post('/', 'Tickets\TicketController@create');

    // Assign ticket to user
    Route::post('/assign/{user_id?}', 'Tickets\TicketController@assignTicket');

    // Get ticket information
    Route::get('/{ticketId}/{replies?}', 'Tickets\TicketController@show');
    Route::get('/timeline/{ticketId}/{replies?}', 'Tickets\TicketController@getTimeLine');

    // Update ticket
    Route::match(['put', 'patch'], '/{ticketId}', 'Tickets\TicketController@update');

    // Delete ticket
    Route::delete('/{ticketId}', 'Tickets\TicketController@delete');

    // Delete all tickets
    Route::post('/delete_all', 'Tickets\TicketController@deleteAll');

    // Ticket file upload
    Route::post('/upload_file', 'Tickets\TicketController@uploadFile');

    // Ticket history set as viewed
    Route::post('/{ticketHistoryId}/set_viewed', 'Tickets\TicketController@setViewed');

    // Ticket set as viewed
    Route::post('/{ticketIdHash}/set_viewed_ticket', 'Tickets\TicketController@setTicketViewed');

    // Delete file of ticket
    Route::delete('/{ticketId}/upload_file/{fileId}', 'Tickets\TicketController@deleteUploadedFile');

    // Set snoozing time
    Route::post('/snooze/{time}', 'Tickets\TicketController@setSnooze');

    // Remove snoozing
    Route::post('/remove/snooze', 'Tickets\TicketController@removeSnoozeByTicketIdHash');

    // Get user available tickets
    Route::get('/customer/{customer_id}/{mailbox_id}', 'Tickets\TicketController@getTicketsByCustomerEmailAndMailboxId');

    // Change ticket(s) status
    Route::match(['put', 'patch'], 'status', 'Tickets\TicketController@changeStatus');

    //get Deleted tickets
    Route::get('/deleted/{mailbox_id}', 'Tickets\TicketController@getDeletedTicketsByMailboxId');

    /************ Draft ***********************/
    Route::group(['prefix' => '{mailboxId}/drafts'], function () {
        Route::post('/', 'Tickets\TicketController@createDraft');
        Route::get('/{draftId}', 'Tickets\TicketController@draft');
        Route::match(['put', 'patch'], '/{draftId}', 'Tickets\TicketController@updateDraft');
        Route::delete('/{draftId}', 'Tickets\TicketController@deleteDraft');
        Route::post('/delete_drafts', 'Tickets\TicketController@deleteDrafts');
    });
    /************ * * * ***********************/

    /************  Ticket Labels  ***********/
    // To stick a label
    Route::post('/labels/{labelId}', 'Tickets\TicketController@stickLabel');

    // Delete stick label
    Route::delete('/labels/{labelId}/{ticket_id}', 'Tickets\TicketController@removeLabel');

    Route::group(['prefix' => '{ticketId}/labels'], function () {

        // Get label
        Route::get('/{labelId}', 'Tickets\TicketController@getLabel');

        // Update label
        Route::match(['put', 'patch'], '/{labelId}', 'Tickets\TicketController@updateLabel');

    });
    /****************  * * *  ***************/

    /************  Merge Tickets  ***********/
    Route::group(['prefix' => 'merge', 'where' => ['ticketId' => '[0-9]+', 'masterTicketId' => '[0-9]+']], function () {

        //  Get merged tickets
        Route::get('/{masterTicketIdHash}', 'Tickets\TicketController@getTicketsToMerge');

        // To merge tickets
        Route::match(['put', 'patch'], '/{ticketIdHash}', 'Tickets\TicketController@mergeTickets');

        // Restore merge tickets
        Route::delete('/{ticketId}', 'Tickets\TicketController@restoreMergedTicket');


        // Get ticket information
        Route::get('/tree/{masterTicketId}', 'Tickets\TicketController@showLikeTree');
    });

    // Restore tickets
    Route::post('/restore', 'Tickets\TicketController@restoreTickets');
    /************  * * *  ***********/

    /************  Notes  ***********/
    Route::group(['prefix' => '{ticketIdHash}/notes'], function () {

        // Get all notes
        Route::get('/', 'Tickets\NoteController@index');

        // Create new note
        Route::post('/', 'Tickets\NoteController@create');

        // Get note
        Route::get('/{noteId}', 'Tickets\NoteController@show');
    });
    /************  * * *  ***********/

    /************  Comments  ***********/
    Route::group(['prefix' => 'comment'], function () {
        // Create new note
        Route::post('/', 'Tickets\TicketComments@create');
    });
    /****************** * * * ****************/

    /************  History  ***********/
    Route::group(['prefix' => 'history'], function () {
        // Get ticket history
        Route::get('/{ticketId}', 'Tickets\TicketsHistoryController@show');
    });
    /****************** * * * ****************/
});
/**************************************** * * * ***********************************/

/***********************************  User notifications  ***********************************/
Route::group(['prefix' => 'notifications'], function () {

    // get user notifications
    Route::get('/', 'Settings\User\UserNotificationsController@getUserNotifications');

    // get user notification
    Route::get('/{notificationId}', 'Settings\User\UserNotificationsController@getUserNotification');

    // delete user notification
    Route::delete('/{notificationId}', 'Settings\User\UserNotificationsController@deleteUserNotification');

    // delete user notifications
    Route::delete('/', 'Settings\User\UserNotificationsController@deleteUserNotifications');
});
/**************************************** * * * ***********************************/

/***********************************  Settings  ***********************************/
Route::group(['prefix' => 'settings'], function () {

    // User Settings
    Route::group(['prefix' => 'user'], function () {
        Route::post('/reset_password/{id}', 'Settings\User\ProfileController@resetPassword');

        // User profile
        Route::group(['prefix' => 'profile', 'where' => ['userId' => '[0-9]+', 'id' => '[0-9]+']], function () {

            // Get user(admin) profile
            Route::get('/', 'Settings\User\ProfileController@index');

            // Update user(admin) profile
            Route::post('/{id}', 'Settings\User\ProfileController@update');

            Route::post('/step/{step_id}', 'Settings\User\ProfileController@setRegisterStep');

            // Change account avatar
            Route::post('/avatar/{userId}', 'Settings\User\ProfileController@uploadAvatar');

            // Delete account avatar
            Route::delete('/avatar/{userId}', 'Settings\User\ProfileController@deleteAvatar');

            // User notifications settings
            Route::group(['prefix' => 'notifications'], function () {

                // get user notifications settings
                Route::get('/', 'Settings\User\UserNotificationsController@index');

                // update user notifications
                Route::post('/', 'Settings\User\UserNotificationsController@changeConditions');

                Route::group(['prefix' => 'customize', 'where' => ['mailboxId' => '[0-9]+']], function () {
                    Route::post('/{mailbox_id?}', 'Settings\User\UserNotificationsController@customizeNotifications');
                    Route::get('/{mailbox_id?}', 'Settings\User\UserNotificationsController@getCustomizedNotifications');
                });
            });

            Route::delete('/deactivate/{userId}', 'Settings\User\ProfileController@deactivateUser');
            Route::delete('/deactivate_my_profile', 'Settings\User\ProfileController@deactivateMyProfile');

            // Get assigned tickets
            Route::get('/assigned_tickets', 'Settings\User\ProfileController@getAssignedTickets');

            Route::post('/image_generate', 'Settings\User\ProfileController@generateUserProfileDefaultImage');

            // Push notifications settings
            Route::group(['prefix' => 'push_notification'], function () {
                Route::get('/', 'Settings\User\ProfileController@checkUserPushNotificationsStatus');
                Route::put('/', 'Settings\User\ProfileController@updateUserPushNotificationsStatus');
            });
        });

        // User preferences
        Route::group(['prefix' => 'preferences'], function () {

            // get user preferences
            Route::get('/', 'Settings\User\PreferenceController@index');

            // save user preferences
            Route::post('/', 'Settings\User\PreferenceController@changePreferences');
        });

        // User teams
        Route::group(['prefix' => 'teams'], function () {

            // Get all teams
            Route::get('/', 'Settings\User\TeamController@index');

            // Create new team
            Route::post('/', 'Settings\User\TeamController@create');

            // Get team
            Route::get('/{teamId}', 'Settings\User\TeamController@show');

            // Update team
            Route::match(['put', 'patch'], '/{teamLeadId}', 'Settings\User\TeamController@update');

            // Delete all teams
            Route::delete('/', 'Settings\User\TeamController@deleteAll');

            // Delete team
            Route::delete('/{teamId}', 'Settings\User\TeamController@destroy');

            // To manage the members of team
            Route::post('/manage/{teamId}', 'Settings\User\TeamController@manageTeam');
        });
    });

    // Company Settings
    Route::group(['prefix' => 'company'], function () {

        // Company profiles
        Route::group(['prefix' => 'settings'], function () {

            // Get company profile
            Route::get('/', 'Settings\Company\CompanySettingsController@index');

            // Upload avatar
            Route::post('/', 'Settings\Company\CompanySettingsController@uploadLogo');

            // Update profile
            Route::match(['put', 'patch'], '/', 'Settings\Company\CompanySettingsController@update');

            /************  Notes  ***********/
            Route::group(['prefix' => 'permissions'], function () {

                // Get all permissions
                Route::get('/', 'Settings\Company\CompanySettingsController@getPermissions');

                // Create new user role
                Route::post('/', 'Settings\Company\CompanySettingsController@createUserRole');
            });
            /************  * * *  ***********/
        });

        // Company users
        Route::group(['prefix' => 'users', 'where' => ['userId' => '[0-9]+']], function () {

            // Get all users
            Route::get('/', 'Settings\Company\UsersController@index');

            // Get user
            Route::get('/{userId}', 'Settings\Company\UsersController@show');

            // Get invited users of company
            Route::get('/invite', 'Settings\Company\UsersController@invitedUsers');

            // Send user invitation
            Route::post('/invite', 'Settings\Company\UsersController@sendInvitation');

            // Resend user invitation
            Route::post('/invite/resend', 'Settings\Company\UsersController@resendInvitation');

            // Update user role
            Route::match(['put', 'patch'], '/{userId}', 'Settings\Company\UsersController@update');

            // Delete user
            Route::delete('/{userId}', 'Settings\Company\UsersController@destroy');

            // Get user tickets
            Route::get('/{userId}/tickets', 'Settings\Company\UsersController@getUserAssignedTickets');

            // Get mentioned users
            Route::get('/mentioned/{mailbox_id}/{keyword?}', 'Settings\Company\UsersController@getMentionedUsers');

            // Get customer by email
            Route::get('/customer/{email}', 'Settings\Company\UsersController@getCustomerData');
        });
    });


    // Workflow
    Route::group(['prefix' => 'workflow', 'where' => ['workflowId' => '[0-9]+']], function () {

        // Get all workflow
        Route::post('/', 'Settings\Workflow\WorkflowController@create');

        Route::get('/{workflowId}', 'Settings\Workflow\WorkflowController@show');
        Route::get('/conditions', 'Settings\Workflow\WorkflowController@conditions');
        Route::get('/conditions/{id}', 'Settings\Workflow\WorkflowController@condition');
        Route::get('/data_conditions/{workflowId}', 'Settings\Workflow\WorkflowConditionController@index');
        Route::post('/conditions', 'Settings\Workflow\WorkflowConditionController@create');
        Route::get('/actions', 'Settings\Workflow\WorkflowController@actions');
        Route::get('/actions/{id}', 'Settings\Workflow\WorkflowController@action');
        Route::get('/data_actions/{workflowId}', 'Settings\Workflow\WorkflowActionController@show');
        Route::post('/actions', 'Settings\Workflow\WorkflowActionController@create');
    });

    // Ticketing Settings
    Route::group(['prefix' => 'ticketing', 'where' => ['userId' => '[0-9]+', 'replyId' => '[0-9]+', 'categoryId' => '[0-9]+'], 'mailboxId' => '[0-9]+'], function () {

        //  Ticketing canned replies
        Route::group(['prefix' => '/canned_replies'], function () {

            // Get canned replies
            Route::get('/', 'Settings\Ticketing\CannedReplyController@index');

            // Create new canned reply
            Route::post('/', 'Settings\Ticketing\CannedReplyController@create');

            // Get canned reply
            Route::get('/{replyId}', 'Settings\Ticketing\CannedReplyController@show');

            // Update canned reply
            Route::match(['put', 'patch'], '/{replyId}', 'Settings\Ticketing\CannedReplyController@update');

            // Delete canned reply
            Route::delete('/{replyId}', 'Settings\Ticketing\CannedReplyController@destroy');

            // Delete all canned replies
            Route::delete('/', 'Settings\Ticketing\CannedReplyController@deleteAll');

            //  Canned reply categories
            Route::group(['prefix' => 'categories', 'where' => ['categoryId' => '[0-9]+']], function () {

                // Get all canned replies categories
                Route::get('/', 'Settings\Ticketing\CannedReplyCategoriesController@index');

                // Create new canned replies category
                Route::post('/', 'Settings\Ticketing\CannedReplyCategoriesController@create');

                // Get canned reply category
                Route::get('/{categoryId}', 'Settings\Ticketing\CannedReplyCategoriesController@show');

                // Update canned reply category
                Route::match(['put', 'patch'], '/{categoryId}', 'Settings\Ticketing\CannedReplyCategoriesController@update');

                // Delete canned reply category
                Route::delete('/{categoryId}', 'Settings\Ticketing\CannedReplyCategoriesController@destroy');

            });
        });

        //  Ticketing labels
        Route::group(['prefix' => 'labels', 'where' => ['labelId' => '[0-9]+']], function () {

            // Get all labels
            Route::get('/', 'Settings\Ticketing\LabelController@index');

            // Create new label
            Route::post('/', 'Settings\Ticketing\LabelController@create');

            // Get label
            Route::get('/{labelId}', 'Settings\Ticketing\LabelController@show');

            // Update label
            Route::match(['put', 'patch'], '/{labelId}', 'Settings\Ticketing\LabelController@update');

            // Delete label
            Route::delete('/{labelId}', 'Settings\Ticketing\LabelController@destroy');

            // Delete all labels
            Route::delete('/', 'Settings\Ticketing\LabelController@deleteAll');

        });

        //  Ticketing mailboxes
        Route::group(['prefix' => 'mailboxes', 'where' => ['mailboxId' => '[0-9]+']], function () {

            // Get mailboxes
            Route::get('/', 'Settings\Ticketing\MailboxController@index');

            // Create new mailbox
            Route::post('/', 'Settings\Ticketing\MailboxController@create');

            // Create new mailbox
            Route::post('/{mailbox_id}/auto_reply', 'Settings\Ticketing\MailboxController@editAutoReply');

            // Create new mailbox
            Route::post('/{mailbox_id}/check_forwarding', 'Settings\Ticketing\MailboxController@checkForwarding');

            // Create new mailbox
            Route::post('/verify/{id}', 'Settings\Ticketing\MailboxController@verifyMailboxDomain');

            // Get mailbox information
            Route::get('/{mailboxId}', 'Settings\Ticketing\MailboxController@show');

            // Update mailbox
            Route::match(['put', 'patch'], '/{mailboxId}', 'Settings\Ticketing\MailboxController@update');

            // Delete mailbox
            Route::delete('/{mailboxId}', 'Settings\Ticketing\MailboxController@destroy');

            // Get mailboxes
            Route::get('/{mailbox_id}/users', 'Settings\Ticketing\MailboxController@getUsersOfMailbox');

            // Change Mailbox User permission
            Route::post('/permissions/{mailbox_id}/{user_id}/{status}', 'Settings\Ticketing\MailboxController@setMailboxUserPermissions');

            // Create Mailbox available hours
            Route::post('/hours/{mailbox_id}/', 'Settings\Ticketing\MailboxController@setAvailableHours');

            // Create Mailbox available hours
            Route::get('/hours/{mailbox_id}/', 'Settings\Ticketing\MailboxController@getAvailableHours');

            // Confirm mailbox
            Route::post('/confirm/{confirmNumber}', 'Settings\Ticketing\MailboxController@confirmMailbox');

            // Confirm mailbox
            Route::post('/resend_confirmation/{id}', 'Settings\Ticketing\MailboxController@resendMailboxConfirmationEmail');
        });
    });
});
/************************************ * * * ***************************************/

//Update default mailbox name
Route::match(['put', 'patch'], '/default_mailbox', 'Settings\Ticketing\MailboxController@updateDefaultMailboxName');


/************************************ SUPER ADMIN ***************************************/
Route::group(['prefix' => 'super_admin'], function () {
    // Get all labels
    Route::post('/', 'SuperAdmin\SuperAdminController@updateAllDBs');
});
/************************************ * * * ***************************************/

/************************************ * * * ***************************************/


/************************************ Countries ***************************************/
// Get Countries
Route::get('/countries', 'CountriesController@index');

// Get Country
Route::get('/countries/{countryId}', 'CountriesController@show');
/************************************ * * * ***************************************/