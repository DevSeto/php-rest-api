
********************************    Tickets    ****************************************
    -----------------------------------------------------------------------
    #   Get all tickets
    -----------------------------------------------------------------------
        URL                 -   subdomain.birddesk-spa-backend.dev/api/tickets
        Method              -   GET
        URL Params          -   {
                                    status=[string],    //  'open' || 'pending' || 'closed' || 'spam' || ""
                                    date=[string]   //  'last_updated' || 'new_updated' || 'last_added' || 'new_added' || ""
                                    label=[int]
                                } // for example: subdomain.birddesk-spa-backend.dev/api/tickets?status=pending&date=&label=3
        Success Response    -   {
                                    "success": true,
                                    "content": "U2FsdGVkX19wU1KJO..." // encrypted object to string
                                }
    -------------------------  #  #  #  -----------------------------------

    -----------------------------------------------------------------------
    #   Create new ticket
    -----------------------------------------------------------------------
        URL                 -   subdomain.birddesk-spa-backend.dev/api/tickets
        Method              -   POST
        URL Params          -
        Data Params         -   {
                                    owner_id : [int],
                                    customer_name : [string],   // 'required|max:35'
                                    customer_email : [string]   // 'required|email'
                                    subject : [string]          // 'required'
                                    body : [string]             // 'required'
                                    assign_agent_id : [int],
                                    status : enum[open || closed || spam || pending]
                                }
        Success Response    -   {
                                    "success": true,
                                    "content": "0nMWEPUuco+WJTHiL7k/yzlZAROp9rimAwJa1PLD4 ....." // last inserted encoded ticket data
                                }
    -------------------------  #  #  #  -----------------------------------

    -----------------------------------------------------------------------
    #   Get ticket
    -----------------------------------------------------------------------
        URL                 -   subdomain.birddesk-spa-backend.dev/api/tickets/{ticketId}
        Method              -   GET
        URL Params          -
        Success Response    -   {
                                    "success": true,
                                    "content": "0nMWEPUuco+WJTHiL7k/yzlZAROp9rimAwJa1PLD4 ....." // encoded ticket data
                                }
    -------------------------  #  #  #  -----------------------------------

    -----------------------------------------------------------------------
    #   Update ticket
    -----------------------------------------------------------------------
        URL                 -   subdomain.birddesk-spa-backend.dev/api/tickets/{ticketId}
        Method              -   PUT
        URL Params          -
        Data Params         -   {
                                {                               // example:
                                        customer_name : [string],
                                        subject : [string]
                                        body : [string]
                                        status : [string] // enum[open || closed || spam || pending]
                                    }
                                }

        Success Response    -   {               // example:
                                    "success":true
                                }
    -------------------------  #  #  #  -----------------------------------

    -----------------------------------------------------------------------
    #   Delete ticket
    -----------------------------------------------------------------------
        URL                 -   subdomain.birddesk-spa-backend.dev/api/tickets/{ticketId}
        Method              -   DELETE
        URL Params          -
        Success Response    -   {               // example:
                                    "success":true,
                                    "ticketId":"U2FsdGVkX1/Qh3ir6C/EDSR3hVg+lVAXRC5ZUToyO3U=" // deleted ticket id
                                }
    -------------------------  #  #  #  -----------------------------------

    -----------------------------------------------------------------------
    #   Delete all tickets
    -----------------------------------------------------------------------
        URL                 -   subdomain.birddesk-spa-backend.dev/api/tickets/
        Method              -   DELETE
        URL Params          -
        Success Response    -   {                   // example:
                                    "success": true,
                                    "deletedTicketsCount": "U2FsdGVkX1/94ok0A6GHvRQH48XDpirjFIMS7REpmJ8="
                                }
    -------------------------  #  #  #  -----------------------------------

    -----------------------------------------------------------------------
    #   Upload Ticket file  
    -----------------------------------------------------------------------
        Examople            -   subdomain.birddesk-spa-backend.dev/api/tickets/18/upload_file
        
        URL                 -   subdomain.birddesk-spa-backend.dev/api/tickets/{ticketId}/upload_file
        Method              -   POST    
        URL Params          -   
        Data Params         -   {
                                    file : [file]       
                                }
        Success Response    -   {               // example:
                                    "success":true,
                                    "file":"U2FsdGVkX1/Qh3ir6C/EDSR3hVg+lVAXRC5ZUToyO3U="
                                }
    -------------------------  #  #  #  -----------------------------------

    -----------------------------------------------------------------------
    #   Delete Ticket file
    -----------------------------------------------------------------------
        URL                 -   subdomain.birddesk-spa-backend.dev/api/tickets/{ticketId}/upload_file/{fileId}
        Method              -   DELETE
        URL Params          -   
        Success Response    -   {               // example:
                                    "success":true,
                                    "ticketId":"U2FsdGVkX1/Qh3ir6C/EDSR3hVg+lVAXRC5ZUToyO3U=" // deleted ticket id
                                }
    -------------------------  #  #  #  -----------------------------------

    -----------------------------------------------------------------------
    #   Get sorted tickets count by ticket status
    -----------------------------------------------------------------------
        URL                 -   subdomain.birddesk-spa-backend.dev/api/tickets/sorted
        Method              -   GET
        URL Params          -   
        Success Response    -   {               // example:
                                    "success":true, || false
                                    "data":"U2FsdGVkX1/Qh3ir6C/EDSR3hVg+lVAXRC5ZUToyO3U=" 
                                }
    -------------------------  #  #  #  -----------------------------------


    -----------------------------------    Notes

            -----------------------------------------------------------------------
            #   Get all notes
            -----------------------------------------------------------------------
                URL                 -   subdomain.birddesk-spa-backend.dev/api/tickets/{$ticketId}/notes
                Method              -   GET
                URL Params          -
                Success Response    -   {
                                            "success": true,
                                            "content": "U2FsdGVkX18x\/s9M61zMROD5yRt+J\/pyc1f . . ." encoded data
                                        }
            -------------------------  #  #  #  -----------------------------------

            -----------------------------------------------------------------------
            #   Create note
            -----------------------------------------------------------------------
                URL                 -   subdomain.birddesk-spa-backend.dev/api/tickets/{ticketId}/notes
                Method              -   POST
                URL Params          -
                Data Params         -   {                   // example:
                                            author_id : [int],
                                            note : [string]
                                        }
                Success Response    -   {                   // example:
                                            "success": true,
                                            ,"content":"U2FsdGVkX1\/ZheI2885oI6xuxjwMo7+vRhYPvNIUu . . . "
                                        }
            -------------------------  #  #  #  -----------------------------------

            -----------------------------------------------------------------------
            #   Get note
            -----------------------------------------------------------------------
                URL                 -   subdomain.birddesk-spa-backend.dev/api/tickets/{ticketId}/notes/{noteId}
                Method              -   GET
                URL Params          -
                Success Response    -   {
                                            "success": true,
                                            "content": "U2FsdGVkX1\/73mUGA1O5vDks9pJxLAer . . ."
                                        }
            -------------------------  #  #  #  -----------------------------------

            -----------------------------------------------------------------------
            #   Update note
            -----------------------------------------------------------------------
                URL                 -   subdomain.birddesk-spa-backend.dev/api/tickets/{ticketId}/notes/{noteId}
                Method              -   PUT
                URL Params          -   noteId
                Data Params         -   {       // example:
                                            note : [string]
                Success Response    -   {
                                            "success": true,
                                            "noteId": "2"
                                        }
            -------------------------  #  #  #  -----------------------------------

            -----------------------------------------------------------------------
            #   Delete all notes
            -----------------------------------------------------------------------
                URL                 -   subdomain.birddesk-spa-backend.dev/api/tickets/{ticketId}/notes
                Method              -   DELETE
                URL Params          -
                Data Params         -
                Success Response    -   {               // example:
                                            "success": true,
                                            "deletedTicketsCount": "2"
                                        }
            -------------------------  #  #  #  -----------------------------------

            -----------------------------------------------------------------------
            #   Delete note
            -----------------------------------------------------------------------
                URL                 -   subdomain.birddesk-spa-backend.dev/api/tickets/{ticketId}/notes/{noteId}
                Method              -   DELETE
                URL Params          -
                Data Params         -
                Success Response    -   {
                                            "success": true,
                                            "deletedNoteId": "2"
                                        }
            -------------------------  #  #  #  -----------------------------------

    -----------------------------------    * * * * * /


    -----------------------------------    Tickets Merge

            -----------------------------------------------------------------------
            #   Get all merged tickets
            -----------------------------------------------------------------------
                URL                 -   subdomain.birddesk-spa-backend.dev/api/tickets/merge
                Method              -   GET
                URL Params          -
                Data Params         -
                Success Response    -   {
                                            "success": true,
                                            "data": "U2FsdGVkX18x\/s9M61zMROD5yRt+J\/pyc1f . . ." encoded data
                                        }
            -------------------------  #  #  #  -----------------------------------

            -----------------------------------------------------------------------
            #   Get merged tickets by master ticket ID
            -----------------------------------------------------------------------
                URL                 -   subdomain.birddesk-spa-backend.dev/api/tickets/merge/{masterTicketId}
                Method              -   GET
                URL Params          -
                Data Params         -
                Success Response    -   {
                                            "success": true,
                                            "data": "U2FsdGVkX18x\/s9M61zMROD5yRt+J\/pyc1f . . ." encoded data
                                        }
            -------------------------  #  #  #  -----------------------------------

            -----------------------------------------------------------------------
            #   To merge tickets
            -----------------------------------------------------------------------
                URL                 -   subdomain.birddesk-spa-backend.dev/api/tickets/merge/{masterTicketID}
                Method              -   PUT
                URL Params          -
                Data Params         -   ticket_id [ index ] : [array]
                Success Response    -   {
                                            "success": true,
                                            "data": "U2FsdGVkX18x\/s9M61zMROD5yRt+J\/pyc1f . . ." encoded data
                                        }
            -------------------------  #  #  #  -----------------------------------

            -----------------------------------------------------------------------
            #   Restore merged tickets
            -----------------------------------------------------------------------
                URL                 -   subdomain.birddesk-spa-backend.dev/api/tickets/merge/{masterTicketID}
                Method              -   DELETE
                URL Params          -
                Data Params         -
                Success Response    -   {
                                            "success": true,
                                            "data": "U2FsdGVkX18x\/s9M61zMROD5yRt+J\/pyc1f . . ." encoded data
                                        }
            -------------------------  #  #  #  -----------------------------------

    -----------------------------------    * * * * * /


    -----------------------------------    Ticket Labels

            -----------------------------------------------------------------------
            #   To stick a label
            -----------------------------------------------------------------------
                URL                 -   subdomain.birddesk-spa-backend.dev/api/tickets/labels/{ticketId}
                Method              -   POST
                URL Params          -
                Data Params         -
                Success Response    -   {
                                            "success": true,
                                            "data": "U2FsdGVkX18x\/s9M61zMROD5yRt+J\/pyc1f . . ." encoded data
                                        }
            -------------------------  #  #  #  -----------------------------------

            -----------------------------------------------------------------------
            #   Delete stick label
            -----------------------------------------------------------------------
                URL                 -   subdomain.birddesk-spa-backend.dev/api/tickets/labels/{labelOfTicketId}
                Method              -   DELETE
                URL Params          -
                Data Params         -
                Success Response    -   {
                                            "success": true,
                                            "data": "U2FsdGVkX18x\/s9M61zMROD5yRt+J\/pyc1f . . ." encoded data
                                        }
            -------------------------  #  #  #  -----------------------------------

    -----------------------------------    * * * * * /
    
    
    -----------------------------------    Tickets Histories
    
            -----------------------------------------------------------------------
            #  Get all tickets histories
            -----------------------------------------------------------------------
                URL                 -   subdomain.birddesk-spa-backend.dev/api/tickets/history/
                Method              -   GET
                URL Params          -
                Data Params         -
                Success Response    -   {
                                            "success": true, // || false
                                            "data": "U2FsdGVkX18x\/s9M61zMROD5yRt+J\/pyc1f . . ." encoded data // || false
                                        }
            -------------------------  #  #  #  -----------------------------------
            
            -----------------------------------------------------------------------
            #  Get ticket history
            -----------------------------------------------------------------------
                URL                 -   subdomain.birddesk-spa-backend.dev/api/tickets/history/{ticketId}
                Method              -   GET
                URL Params          -
                Data Params         -
                Success Response    -   {
                                            "success": true, // || false
                                        }
            -------------------------  #  #  #  -----------------------------------
            
            -----------------------------------------------------------------------
            #  Delete all tickets histories
            -----------------------------------------------------------------------
                URL                 -   subdomain.birddesk-spa-backend.dev/api/tickets/history
                Method              -   DELETE
                URL Params          -
                Data Params         -
                Success Response    -   {
                                            "success": true, // || false
                                            "count": Int // detleted items count
                                        }
            -------------------------  #  #  #  -----------------------------------
    -----------------------------------    * * * * * /

********************************    * * * *    ****************************************


********************************    Settings    ****************************************

    -----------------------------------    User

            -----------------------------------    Profile

                    -----------------------------------------------------------------------
                    #   Get user/owner profile data
                    -----------------------------------------------------------------------
                        URL                 -   subdomain.birddesk-spa-backend.dev/api/settings/user/profile/{userId}
                        Method              -   GET
                        URL Params          -
                        Data Params         -
                        Success Response    -   {
                                                    "success": true,
                                                    "content": "U2FsdGVkX1\/73mUGA1O5vDks9pJxLAer . . ."
                                                }
                    -------------------------  #  #  #  -----------------------------------


                    -----------------------------------------------------------------------
                    #   Change or add user/owner profile avatar
                    -----------------------------------------------------------------------
                        URL                 -   subdomain.birddesk-spa-backend.dev/api/settings/user/profile/avatar
                        Method              -   POST
                        URL Params          -
                        Data Params         -   {
                                                    "avatar": [file],
                                                    "user_id": [int]
                                                }
                        Success Response    -   {
                                                    "success": true,
                                                    "avatar": "U2FsdGVkX1\/73mUGA1O5vDks9pJxLAer . . ."
                                                }
                    -------------------------  #  #  #  -----------------------------------


                    -----------------------------------------------------------------------
                    #   Delete user/owner profile avatar
                    -----------------------------------------------------------------------
                        URL                 -   subdomain.birddesk-spa-backend.dev/api/settings/user/profile/avatar/{userId}
                        Method              -   DELETE
                        URL Params          -
                        Data Params         -
                        Success Response    -   {
                                                    "success": true,
                                                    "deletedAvatar": "U2FsdGVkX1\/73mUGA1O5vDks9pJxLAer . . ." deleted image data
                                                }
                    -------------------------  #  #  #  -----------------------------------


                    -----------------------------------------------------------------------
                    #   Update user(admin) profile
                    -----------------------------------------------------------------------
                        URL                 -   subdomain.birddesk-spa-backend.dev/api/settings/user/profile
                        Method              -   PUT
                        URL Params          -
                        Data Params         -
                        Success Response    -   {
                                                    "success": true \\ Encrypted ( || false )
                                                }
                    -------------------------  #  #  #  -----------------------------------

            -----------------------------------    * * * * * /


            -----------------------------------    Notifications
                                * * *
            -----------------------------------    * * * * * /


    -----------------------------------    * * * * * /


    -----------------------------------    Company

            -----------------------------------    Profile

                    -----------------------------------------------------------------------
                    #   Get company profile data
                    -----------------------------------------------------------------------
                        URL                 -   subdomain.birddesk-spa-backend.dev/api/settings/company/profile
                        Method              -   GET
                        URL Params          -
                        Data Params         -
                        Success Response    -   {
                                                    "success": true,
                                                    "content": "U2FsdGVkX1\/73mUGA1O5vDks9pJxLAer . . ."
                                                }
                    -------------------------  #  #  #  -----------------------------------

            -----------------------------------    * * * * * /


            -----------------------------------    Users

                    -----------------------------------------------------------------------
                    #   Get company all users
                    -----------------------------------------------------------------------
                        URL                 -   subdomain.birddesk-spa-backend.dev/api/settings/company/users
                        Method              -   GET
                        URL Params          -   ?user_status=[int] // if need to sort users by roles -  table `user_roles` id
                        Data Params         -
                        Success Response    -   {
                                                    "success": true,
                                                    "content": "U2FsdGVkX1\/73mUGA1O5vDks9pJxLAer . . ."
                                                }
                    -------------------------  #  #  #  -----------------------------------


                    -----------------------------------------------------------------------
                    #   Get company user
                    -----------------------------------------------------------------------
                        URL                 -   subdomain.birddesk-spa-backend.dev/api/settings/company/users/{userId}
                        Method              -   GET
                        URL Params          -
                        Data Params         -
                        Success Response    -   {
                                                    "success": true,
                                                    "content": "U2FsdGVkX1\/73mUGA1O5vDks9pJxLAer . . ."
                                                }
                    -------------------------  #  #  #  -----------------------------------


                    -----------------------------------------------------------------------
                    #   Send user invitation
                    -----------------------------------------------------------------------
                        URL                 -   subdomain.birddesk-spa-backend.dev/api/settings/company/users/invite
                        Method              -   POST
                        URL Params          -
                        Data Params         -   {
                                                    to_email:[string|email],
                                                    to_name: [string],
                                                    sender_id:[int],
                                                    role_id:[int]
                                                }
                        Success Response    -   {
                                                    "success": true,
                                                    "data": {
                                                        "options": {
                                                            "toEmail": "Amis1990@rhyta.com",
                                                            "toName": "Amis1990",
                                                            "fromEmail": "info@fornewdevtest.birddesk.com",
                                                            "fromName": "ForDev",
                                                            "token": "b29b346c29ea5d3e56bf5feaba5a89c080063e12",
                                                            "subject": "test subject visca",
                                                            "commentText": "Source: <a href=http://fornewdevtest.birddesk-spa-backend.dev/api/users/invite/confirm?confirmation_token=TKMNiKZTdhbQzpxkagE3XUSDOvblmd7y&user_status=4&user_email=Amis1990@rhyta.com target='_blank'>fornewdevtest.birddesk.com</a>",
                                                            "replyEmail": "https://testapi.birddesk.com/receive_email/",
                                                            "reply_to": "https://testapi.birddesk.com/receive_email"
                                                        },
                                                        "sendEmailResult": {
                                                            "total_rejected_recipients": 0,
                                                            "total_accepted_recipients": 1,
                                                            "id": "48662255601900690"
                                                        }
                                                    }
                                                }
                    -------------------------  #  #  #  -----------------------------------


                    -----------------------------------------------------------------------
                    #   Get invitation
                    -----------------------------------------------------------------------
                        URL                 -   http://fornewdevtest.birddesk-spa-backend.dev/api/settings/company/users/invite/confirm
                        Method              -   GET
                        URL Params          -   {
                                                    confirmation_token:[string],
                                                    user_status:[int],
                                                    user_email:[string|email]
                                                }// example: ?confirmation_token=ltibtAZ3m3qyUf7e415QSgNZfCMIv70n&user_status=3&user_email=Mhen1960@fleckens.hu
                        Data Params         -
                        Success Response    -   {
                                                    "success":true,
                                                    "data":{
                                                        "confirmation_token":"TKMNiKZTdhbQzpxkagE3XUSDOvblmd7y",
                                                        "user_status":"4",
                                                        "user_email":"Amis1990@rhyta.com"
                                                        }
                                                }
                    -------------------------  #  #  #  -----------------------------------


                    -----------------------------------------------------------------------
                    #   Confirm invitation
                    -----------------------------------------------------------------------
                        URL                 -   http://fornewdevtest.birddesk-spa-backend.dev/api/settings/company/users/invite/confirm
                        Method              -   POST
                        URL Params          -   {
                                                    confirmation_token:[string],
                                                    user_status:[int],
                                                    user_email:[string|email]
                                                    title:[string],
                                                    phone:[string],
                                                    time_zone:[string]
                                                }// example: ?confirmation_token=ltibtAZ3m3qyUf7e415QSgNZfCMIv70n&user_status=3&user_email=Mhen1960@fleckens.hu
                        Data Params         -   {
                                                    first_name : [string],
                                                    last_name : [string],
                                                    password : [string],
                                                    password_confirmation : [string]
                                                },
                        Success Response    -   {
                                                    "success":true,
                                                    "data": "TKMNiKZTdhbQzpxkagE3XUSDOvblmd7y . . " // encoded data
                                                }
                    -------------------------  #  #  #  -----------------------------------


                    -----------------------------------------------------------------------
                    #   Update user role
                    -----------------------------------------------------------------------
                        URL                 -   http://fornewdevtest.birddesk-spa-backend.dev/api/settings/company/users/{userId}
                        Method              -   PUT
                        URL Params          -   {
                                                    role_id:[string|email]
                                                } // example: http://fornewdevtest.birddesk-spa-backend.dev/api/settings/company/users/5?role_id=3,
                        Data Params         -   ,
                        Success Response    -   {
                                                    "success":true
                                                }
                    -------------------------  #  #  #  -----------------------------------


                    -----------------------------------------------------------------------
                    #   Delete user
                    -----------------------------------------------------------------------
                        URL                 -   http://fornewdevtest.birddesk-spa-backend.dev/api/settings/company/users/{userId}
                        Method              -   PUT
                        URL Params          -   {
                                                    role_id:5 // "role_id"  MOST BE 5
                                                } // example: http://fornewdevtest.birddesk-spa-backend.dev/api/settings/company/users/5?role_id=5,
                        Data Params         -
                        Success Response    -   {
                                                    "success":true
                                                }
                    -------------------------  #  #  #  -----------------------------------

            -----------------------------------    * * * * * /

    -----------------------------------    * * * * * /


    -----------------------------------    Ticketing /

            -----------------------------------    Canned Replies
                    -----------------------------------------------------------------------
                    #   Create new canned reply
                    -----------------------------------------------------------------------
                        URL                 -   subdomain.birddesk-spa-backend.dev/api/settings/ticketing/canned_replies
                        Method              -   POST
                        URL Params          -
                        Data Params         -   {
                                                    reply_name : [string],
                                                    body : [string]
                                                }
                        Success Response    -   {                   // example:
                                                    "success": true,
                                                    "data":"U2FsdGVkX19jeZsigS3Lzg7eldRLbzCLTuPsCcoK8SS . . . " // encoded data
                                                }
                    -------------------------  #  #  #  -----------------------------------


                    -----------------------------------------------------------------------
                    #   Get all canned replies
                    -----------------------------------------------------------------------
                        URL                 -   subdomain.birddesk-spa-backend.dev/api/settings/ticketing/canned_replies
                        Method              -   GET
                        URL Params          -
                        Data Params         -
                        Success Response    -   {                   // example:
                                                    "success": true,
                                                    "data":"U2FsdGVkX19jeZsigS3Lzg7eldRLbzCLTuPsCcoK8SS . . . " // encoded data
                                                }
                    -------------------------  #  #  #  -----------------------------------


                    -----------------------------------------------------------------------
                    #   Get all canned reply
                    -----------------------------------------------------------------------
                        URL                 -   subdomain.birddesk-spa-backend.dev/api/settings/ticketing/canned_replies/{replyId}
                        Method              -   GET
                        URL Params          -
                        Data Params         -
                        Success Response    -   {                   // example:
                                                    "success": true,
                                                    "data":"U2FsdGVkX19jeZsigS3Lzg7eldRLbzCLTuPsCcoK8SS . . . " // encoded data
                                                }
                    -------------------------  #  #  #  -----------------------------------


                    -----------------------------------------------------------------------
                    #   Update canned reply
                    -----------------------------------------------------------------------
                        URL                 -   subdomain.birddesk-spa-backend.dev/api/settings/ticketing/canned_replies/{replyId}
                        Method              -   PUT
                        URL Params          -
                        Data Params         -
                        Success Response    -   {                   // example:
                                                    "success": true // or false
                                                }
                    -------------------------  #  #  #  -----------------------------------


                    -----------------------------------------------------------------------
                    #   Delete canned reply
                    -----------------------------------------------------------------------
                        URL                 -   subdomain.birddesk-spa-backend.dev/api/settings/ticketing/canned_replies/{replyId}
                        Method              -   DELETE
                        URL Params          -
                        Data Params         -
                        Success Response    -   {                   // example:
                                                    "success": true // or false
                                                }
                    -------------------------  #  #  #  -----------------------------------


                    -----------------------------------------------------------------------
                    #   Delete all canned replies
                    -----------------------------------------------------------------------
                        URL                 -   subdomain.birddesk-spa-backend.dev/api/settings/ticketing/canned_replies
                        Method              -   DELETE
                        URL Params          -
                        Data Params         -
                        Success Response    -   {
                                                    "success": 2 // count deleted canned replies
                                                }
                    -------------------------  #  #  #  -----------------------------------
            -----------------------------------    * * * * * /


            -----------------------------------    Labels

                    -----------------------------------------------------------------------
                    #   Get all labels
                    -----------------------------------------------------------------------
                        URL                 -   subdomain.birddesk-spa-backend.dev/api/settings/ticketing/labels/
                        Method              -   GET
                        URL Params          -
                        Data Params         -
                        Success Response    -   {                   // example:
                                                    "success": true,
                                                    "data": "U2FsdGVkX1/Cl5dHo9XHl3MxcpuRs/9I6F5Z/ZFHsNLohTvrAvNj58qtwrT6s . . . "
                                                }
                    -------------------------  #  #  #  -----------------------------------


                    -----------------------------------------------------------------------
                    #   Create label
                    -----------------------------------------------------------------------
                        URL                 -   subdomain.birddesk-spa-backend.dev/api/settings/ticketing/labels/
                        Method              -   POST
                        URL Params          -
                        Data Params         -   {
                                                    color : [string],
                                                    body : [string]
                                                }
                        Success Response    -   {                   // example:
                                                    "success": true,
                                                    "data":"U2FsdGVkX19jeZsigS3Lzg7eldRLbzCLTuPsCcoK8SS . . . " // encoded data
                                                }
                    -------------------------  #  #  #  -----------------------------------


                    -----------------------------------------------------------------------
                    #   Get label
                    -----------------------------------------------------------------------
                        URL                 -   subdomain.birddesk-spa-backend.dev/api/settings/ticketing/labels/{label_id}
                        Method              -   GET
                        URL Params          -
                        Data Params         -
                        Success Response    -   {                   // example:
                                                    "success": true,
                                                    "data":"U2FsdGVkX1\/3kUm7lLimX1DT9sbx49\/vyxMZNR7cI2ey\/3HYgXH1EukmUonanu8oJ . . ."
                                                }
                    -------------------------  #  #  #  -----------------------------------


                    -----------------------------------------------------------------------
                    #   Update label
                    -----------------------------------------------------------------------
                        URL                 -   subdomain.birddesk-spa-backend.dev/api/settings/ticketing/labels/{label_id}
                        Method              -   PUT
                        URL Params          -
                        Data Params         -   {
                                                    body : [string]
                                                }
                        Success Response    -   {
                                                    "success": true
                                                }
                    -------------------------  #  #  #  -----------------------------------


                    -----------------------------------------------------------------------
                    #   Delete label
                    -----------------------------------------------------------------------
                        URL                 -   subdomain.birddesk-spa-backend.dev/api/settings/ticketing/labels/{label_id}
                        Method              -   DELETE
                        URL Params          -
                        Data Params         -
                        Success Response    -   {
                                                    "success": true,
                                                    "deletedLabelId": "2"
                                                }
                    -------------------------  #  #  #  -----------------------------------


                    -----------------------------------------------------------------------
                    #   Delete all labels
                    -----------------------------------------------------------------------
                        URL                 -   subdomain.birddesk-spa-backend.dev/api/settings/ticketing/{ticket_id}/labels
                        Method              -   DELETE
                        URL Params          -
                        Data Params         -
                        Success Response    -   {
                                                    "success": true,
                                                    "deletedLabels": "2"    // deleted labels count
                                                }
                    -------------------------  #  #  #  -----------------------------------

            -----------------------------------    * * * * * /


            -----------------------------------    Mailboxes
                    -----------------------------------------------------------------------
                    #   Create new mailbox
                    -----------------------------------------------------------------------
                        URL                 -   subdomain.birddesk-spa-backend.dev/api/settings/ticketing/mailboxes
                        Method              -   POST
                        URL Params          -
                        Data Params         -   {
                                                    user_id : [int],
                                                    name : [string],
                                                    email : [string]
                                                    signature : [string],
                                                    auto_reply : [int],
                                                    send_email : [string]
                                                    auto_bcc : [int]
                                                }
                        Success Response    -   {               // example:
                                                    "success": true,
                                                    "data":"U2FsdGVkX1\/+KpL . . ."
                                                }
                    -------------------------  #  #  #  -----------------------------------

                    -----------------------------------------------------------------------
                    #   Get all mailboxes
                    -----------------------------------------------------------------------
                        URL                 -   subdomain.birddesk-spa-backend.dev/api/settings/ticketing/mailboxes
                        Method              -   GET
                        URL Params          -
                        Data Params         -
                        Success Response    -   {                   // example:
                                                    "success": true,
                                                    "data":"U2FsdGVkX19zkMtInj . . . "
                                                }
                    -------------------------  #  #  #  -----------------------------------

                    -----------------------------------------------------------------------
                    #   Get mailbox
                    -----------------------------------------------------------------------
                        URL                 -   subdomain.birddesk-spa-backend.dev/api/settings/ticketing/mailboxes/{mailboxId}
                        Method              -   GET
                        URL Params          -
                        Success Response    -   {                   // example:
                                                    "success": true,
                                                    "data":"U2FsdGVkX188aL . . ."
                                                }
                    -------------------------  #  #  #  -----------------------------------

                    -----------------------------------------------------------------------
                    #   Update mailbox
                    -----------------------------------------------------------------------
                        URL                 -   subdomain.birddesk-spa-backend.dev/api/settings/ticketing/mailboxes/{mailboxId}
                        Method              -   PUT
                        URL Params          -
                        Data Params         -   {
                                                    name : [string]
                                                }

                        Success Response    -   {               // example:
                                                    "success":true
                                                }
                    -------------------------  #  #  #  -----------------------------------

                    -----------------------------------------------------------------------
                    #   Delete mailbox
                    -----------------------------------------------------------------------
                        URL                 -   subdomain.birddesk-spa-backend.dev/api/settings/ticketing/mailboxes/{mailboxId}
                        Method              -   DELETE
                        URL Params          -
                        Data Params         -
                        Success Response    -   {               // example:
                                                    "success":1,
                                                    "mailboxId":"2"
                                                }
                    -------------------------  #  #  #  -----------------------------------

                    -----------------------------------------------------------------------
                    #   Delete all mailboxes
                    -----------------------------------------------------------------------
                        URL                 -   subdomain.birddesk-spa-backend.dev/api/settings/ticketing/mailboxes
                        Method              -   DELETE
                        URL Params          -
                        Data Params         -
                        Success Response    -   {               // example:
                                                    "success":2 // deleted mailboxes count
                                                }
                    -------------------------  #  #  #  -----------------------------------

            -----------------------------------    * * * * * /

    -----------------------------------    * * * * * /


********************************    * * * *    ****************************************