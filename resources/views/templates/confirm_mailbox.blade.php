<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Document</title>
    <style>
        body,
        html {
            padding: 0;
            margin: 0;
            min-height: 100vh;
        }
    </style>
</head>

<body>

<table border="0" cellpadding="0" cellspacing="0"
       style="margin:0 auto; padding:0; width: 600px; background-color: #FAFAFA">
    <tr>
        <td style="padding: 45px 78px 29px 78px;">
            <table border="0" cellpadding="0" cellspacing="0" style="margin:0; padding:0; width: 100%">
                <tr>
                    <td style="padding-bottom: 98px">
                        <div style="text-align: center">
                            <img src="https://api.birdtest.nl/uploads/email/logo.png" width="180" height="36" alt="Logo">
                        </div>
                    </td>
                </tr>
                <tr>
                    <td style="padding-bottom: 24px;">
                        <h1
                                style="margin:0; padding:0; font-family: Arial, Helvetica, sans-serif; font-size: 48px; text-align: center;">
                            Confirm mailbox
                        </h1>
                    </td>
                </tr>
                <tr>
                    <td style="padding-bottom: 12px">
                        <p
                                style="margin:0; padding:0; font-family: Arial, Helvetica, sans-serif; font-size: 18px; line-height: 24px;">
                            BirdDesk created a new mailbox for {{ !empty($email) ? $email: '' }}.
                        </p>
                    </td>
                </tr>
                <tr>
                    <td style="padding-bottom: 50px">
                        <p
                                style="margin:0; padding:0; font-family: Arial, Helvetica, sans-serif; font-size: 18px; color: #929395; line-height: 24px;">
                            To start using your mailbox please open BirdDesk and enter a six - digit code below
                        </p>
                    </td>
                </tr>
                <tr>
                    <td style="padding-bottom: 50px">
                        <p
                                style="margin:0; padding:0; font-family: Arial, Helvetica, sans-serif; font-size: 40px; line-height: 24px; text-align: center;">
                            {{ !empty($confirmationNumber) ? $confirmationNumber: '' }}
                        </p>
                    </td>
                </tr>
                <tr>
                    <td style="padding-bottom: 26px">
                        <p
                                style="margin:0; padding:0; font-family: Arial, Helvetica, sans-serif; font-size: 18px; color: #929395; line-height: 24px;">
                            If you’ve already forwarded your email into BirdDesk, you can ignore the code above. Your mailbox will
                            be auto - confirmed for you.
                        </p>
                    </td>
                </tr>
                <tr>
                    <td style="padding-bottom: 36px">
                        <table border="0" cellpadding="0" cellspacing="0" style="margin:0 auto; padding:0; width: 130px">
                            <tr>
                                <td>
                                    <div style="text-align: left">
                                        <a href="#">
                                            <img src="https://api.birdtest.nl/uploads/email/in.png" style="height: 20px; width: auto;" alt="LinkedIn">
                                        </a>
                                    </div>
                                </td>
                                <td>
                                    <div style="text-align: center;">
                                        <a href="#">
                                            <img src="https://api.birdtest.nl/uploads/email/fb.png" style="height: 20px; width: auto;" alt="Facebook">
                                        </a>
                                    </div>
                                </td>
                                <td>
                                    <div style="text-align: right;">
                                        <a href="#">
                                            <img src="https://api.birdtest.nl/uploads/email/tw.png" style="height: 18px; width: auto;" alt="Twitter">
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        </table>
                    </td>
                </tr>
                <tr>
                    <td style="padding-bottom: 11px;">
                        <p
                                style="margin:0; padding:0; font-family: Arial, Helvetica, sans-serif; font-size: 10px; color: #929395;    text-align: center;">
                            <a href="#" style="color: inherit; text-decoration: none;">BirdDesk</a>
                            <span>436 Lafayette St, 2nd Fl, New York, NY 10003</span>
                        </p>
                    </td>
                </tr>
                <tr>
                    <td>
                        <p
                                style="margin:0; padding:0; font-family: Arial, Helvetica, sans-serif; font-size: 10px; color: #929395; text-align: center;">
                            <a style="color: inherit;" href="#">Manage Subscriptions</a>
                            &#8226;
                            <a style="color: inherit;" href="#">Unsubscribe</a>
                            &#8226;
                            <a style="color: inherit;" href="#">Privacy Policy</a>
                        </p>
                    </td>
                </tr>
            </table>
        </td>
    </tr>
</table>

</body>

</html>