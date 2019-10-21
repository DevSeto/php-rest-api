<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <script src="https://code.jquery.com/jquery-3.3.1.min.js"></script>
    <title>Document</title>
    <style>
        body {
            min-height: 100vh;
            padding: 0;
            margin: 0;
        }
    </style>
</head>

<body>
<!--  -->
<div style="background-color: #F8F8F8">
    <div style="width: 600px;background-color: #FFFFFF; padding: 64px 40px; margin: 0 auto">
        <table cellpadding="0" cellspacing="0" style="margin:0; padding:0; font-family: Arial, Helvetica, sans-serif;">
            <thead>
            <tr>
                <td colspan="2" style="padding-bottom: 40px; line-height: 1;">
                    <div style="text-align: center;">
                        <img src="https://api.birdtest.nl/uploads/email/logo.png" alt="Birddesk">
                    </div>
                </td>
            </tr>
            </thead>
            <tbody>
            <tr>
                <td style="font-size: 18px;padding-top: 24px; padding-bottom: 24px; line-height: 27px;">
              <span>
                Welcome, <span style="color: #09B0E2">{{ !empty($to_email) ? $to_email : '' }}</span>
              </span>
                </td>
            </tr>

            <tr>
                <td style="font-size: 18px; padding-bottom: 24px; line-height: 27px;">
              <span>
                «{{ !empty($from_name) ? $from_name : '' }}» has invited you to join BirdDesk.
              </span>
                </td>
            </tr>

            <tr>
                <td style="font-size: 18px; padding-bottom: 32px;">
                    <a href="{{ !empty($source) ? $source : '#' }}">
                        <img style="height: 44px; width: 201px;" src="https://api.birdtest.nl/uploads/email/agent-invite-template.png"
                             alt="Confirm Email Address">
                    </a>
                </td>
            </tr>
            <tr>
                <td>
              <span style="font-size: 18px; padding-bottom: 48px; line-height: 27px;">
                BirdDesk is a easy-to-use help desk built for teams to work together to deliver exceptional customer
                assigned different people on your team, private notes can be added to tickets and performances can be
                measured with detailed support metrics.
                <br /><br />
                We are here at BirdDesk to help. If you need anything, please get in touch with us or have a look at our <a href="#" style="color: #09B0E2; text-decoration: none;">knowledge base.</a>
                <br /><br />
                And your feedback, we glad to hear you at
                <a href="mailto:support@birddesk.com" style="color: #09B0E2; text-decoration: none;">support@birddesk.com</a>
                <br /><br />
                We hope you enjoy using BirdDesk!
                <br />
                Thank you The BirdDesk team
              </span>
                </td>
            </tr>
            </tbody>
        </table>
    </div>
</div>
<!--  -->

</body>

</html>