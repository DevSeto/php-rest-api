<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Document</title>
    <style>
        html,
        body {
            margin: 0;
            padding: 0;
            min-height: 100vh;
            background-color: #F8F8F8;
        }
    </style>
</head>

<body>

<div
        style="margin: 0 auto; padding: 0; line-height: 1; width: 600px; padding: 80px 40px 64px; background-color: #FFFFFF; box-shadow: 0 5px 15px 0 rgba(0,0,0,0.1);">
    <table style="width: 100%;">
        <tr>
            <td colspan="2" style="padding-bottom: 40px; line-height: 1;">
                <div style="text-align: center;">
                    <img src="https://api.birdtest.nl/uploads/email/logo.png" alt="Birddesk">
                </div>
            </td>
        </tr>

        <tr>
            <td colspan="2">
                <h1
                        style="margin:0 0 24px 0px; padding:0; font-family: Arial, Helvetica, sans-serif;font-size: 28px;color: #000000; line-height: 1; font-weight: 700;">
                    Welcome to BirdDesk
                </h1>
            </td>
        </tr>

        <tr>
            <td colspan="2" style="padding-bottom: 40px; line-height: 1;">
                <p style="color:#1D1D1D; margin: 0; font-size: 16px;line-height: 24px; padding:0; font-family: Arial, Helvetica, sans-serif;">
                    You’ve created the new Birddesk company {{$domain}} . Here are your account details.
                    Your company  is on the free plan, with unlimited ticketing  and the ability to create  individual mailboxes for your different teams. </p>
            </td>
        </tr>

        <tr>
            <td style="text-align: right; padding-right: 32px; padding-bottom: 40px">
                <a href="https://{{ !empty($url) ? $url: '' }}">
                    <img src="https://api.birdtest.nl/uploads/email/login-btn.png" alt="Login">
                </a>
            </td>
            <td style="text-align: left; padding-left: 32px; padding-bottom: 40px">
                <a href="#">
                    <img src="https://api.birdtest.nl/uploads/email/doc-btn.png" alt="Help Doc">
                </a>
            </td>
        </tr>

        <tr>
            <td colspan="2">
                <p style="color:#1D1D1D; margin: 0; font-size: 16px;line-height: 24px; padding:0; font-family: Arial, Helvetica, sans-serif;">We hope you enjoy using BirdDesk</p>
            </td>
        </tr>
    </table>
</div>

</body>

</html>