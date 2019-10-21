<?php

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

Route::post('/login','Auth\AuthController@login');

Route::post('/register','Auth\AuthController@register');

Route::get('/update_token','Auth\AuthController@updateUserApiToken');


Route::group([ 'domain' => '{subdomain}'.env('SESSION_DOMAIN') ], function () {

    /************  Ticketing  ****************/
    Route::group(['prefix' => 'tickets'], function(){

        // Get all tickets
        Route::get('/{filter?}', 'TicketController@index');

        // Create new ticket
        Route::post('/', 'TicketController@create');

        // Delete all tickets
        Route::delete('/', 'TicketController@deleteAll');

        // Get ticket information
        Route::get('/ticket/{ticketId}', 'TicketController@show');

        // Update ticket
        Route::match(['put', 'patch'], '/{ticketId}', 'TicketController@update');

        // Delete ticket
        Route::delete('/{ticketId}', 'TicketController@delete');

        // Change ticket status
        Route::match(['put', 'patch'], '{ticketId}/status', 'TicketController@changeStatus');
    });
    /************** * * * ********************/

    
    /************  Mailboxes  ****************/
    Route::group(['prefix' => 'mailbox'], function(){

        // Get all mailboxes
        Route::get('/', 'MailboxController@index');

        // Create new mailbox
        Route::post('/', 'MailboxController@create');

        // Delete all mailboxes
        Route::delete('/', 'MailboxController@deleteAll');

        // Get mailbox information
        Route::get('/{mailboxId}', 'MailboxController@show');

        // Update mailbox
        Route::match(['put', 'patch'], '/{mailboxId}', 'MailboxController@update');

        // Delete mailbox
        Route::delete('/{mailboxId}', 'MailboxController@destroy');
    });
    /************** * * * ********************/


    /************  Tickets History  **********/
    Route::group(['prefix' => 'tickets-history'], function(){

        // Get all tickets history
        Route::get('/{authorId}', 'TicketsHistoryController@index');

        // Add to tickets history
//        Route::post('/', 'TicketsHistoryController@create');
//
//        // Delete all tickets history
//        Route::delete('/', 'TicketsHistoryController@deleteAll');
//
//        // Get ticket history
//        Route::get('/{ticketId}', 'TicketsHistoryController@show');
//
//        // Delete ticket history
//        Route::delete('/{ticketId}', 'TicketsHistoryController@destroy');
    });
    /****************** * * * ****************/


    /************  Canned Replies  ***********/
    Route::group(['prefix' => 'canned_replies'], function(){

//        // Get all canned replies
//        Route::get('/', 'CannedReplyController@index');
//
        // Create new canned reply
        Route::post('/', 'CannedReplyController@create');
//
//        // Delete all mailboxes
//        Route::delete('/', 'CannedReplyController@deleteAll');
//
//        // Get canned reply information
//        Route::get('/{ticketId}', 'CannedReplyController@show');
//
//        // Update canned reply
//        Route::match(['put', 'patch'], '/{ticketId}', 'CannedReplyController@update');
//
//        // Delete canned reply
//        Route::delete('/{ticketId}', 'CannedReplyController@destroy');
    });
    /****************** * * * ****************/


    /************  Notes  ***********/
    Route::group(['prefix' => 'notes'], function(){

        // Get all notes
        Route::get('/{ticketId}', 'NoteController@index');

        // Create new note
        Route::post('/', 'NoteController@create');

        // Get note
        Route::get('/note/{noteId}', 'NoteController@show');

        // Update note
        Route::match(['put', 'patch'], 'note/{noteId}', 'NoteController@update');

        // Delete ticket
        Route::delete('note/{noteId}', 'NoteController@destroy');
    });
    /****************** * * * ****************/


    /************  Profile  ***********/
    Route::group(['prefix' => 'profile'], function(){

        // Get user profile
        Route::get('/{userId}', 'ProfileController@show');

        // Change account avatar
        Route::post('/avatar', 'ProfileController@uploadAvatar');
//        Route::post('/avatar', 'ProfileController@postImage');

        // Delete account avatar
        Route::delete('/avatar/{userId}', 'ProfileController@deleteAvatar');

    });
    /****************** * * * ****************/


    /************  Labels  ***********/
    Route::group(['prefix' => 'labels'], function(){

        // Get all labels
        Route::get('/{ticketId}', 'LabelController@index');

        // Create new label
        Route::post('/', 'LabelController@create');

//         Get label
        Route::get('/label/{labelId}', 'LabelController@show');

        // Update label
        Route::match(['put', 'patch'], '/label/{labelId}', 'LabelController@update');

        // Delete label
        Route::delete('/label/{labelId}', 'LabelController@destroy');

        // Delete all labels
        Route::delete('/', 'LabelController@deleteAll');

    });
    /****************** * * * ****************/
    
});




