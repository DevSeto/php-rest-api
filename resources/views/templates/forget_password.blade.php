{{--<!DOCTYPE html>--}}
{{--<html>--}}
{{--<head>--}}
{{--<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />--}}
{{--<title>BirdDesk</title>--}}
{{--<meta name="viewport" content="width=device-width, initial-scale=1.0"/>--}}
{{--</head>--}}
{{--<body>--}}
{{--<table align="center"  cellpadding="0" cellspacing="0" width="615px" style="border-collapse: collapse;background-color: rgb(255, 255, 255);margin: 40px auto">--}}
{{--<tr>--}}
{{--<td>--}}
{{--<table style="width: 100%;">--}}
{{--<tr>--}}
{{--<td style="padding: 40px">--}}

{{--<ul style="text-align: center;list-style-type: none; padding: 0">--}}
{{--<li style="vertical-align:middle; display: inline-block">--}}
{{--<a href="#" style="font-family:'Montserrat',sans-serif ;font-size: 24px;color: #0a0a0a;text-decoration: none;font-weight: 700">--}}
{{--<img src="https://login.birdtest.nl/public/images/logo.png" alt="BirdDesk" style="margin-bottom: -10px;">--}}
{{--<span style="margin: 0 0 0 5px;">BirdDesk</span>--}}
{{--</a>--}}
{{--</li>--}}
{{--</ul>--}}
{{--</td>--}}
{{--</tr>--}}
{{--</table>--}}
{{--</td>--}}
{{--</tr>--}}
{{--<tr>--}}
{{--<td>--}}
{{--<table style="width: 100%;">--}}
{{--<tr>--}}
{{--<td style="padding: 0 42px 70px 42px">--}}
{{--<h2 style="font-family:'Montserrat', sans-serif ;color: #263238;font-size: 18px;font-weight: 400;margin-top: 0">--}}
{{--Hi,<span style="font-weight: 600;text-decoration: none">  "{{ !empty($user_email) ? $user_email : '' }}"</span>--}}
{{--</h2>--}}
{{--<p style="color: #263238;font-size: 14px;font-family: 'Montserrat', sans-serif; margin-bottom: 30px">--}}
{{--It appears that you’ve forgotten your password. If you would like to create a new password, you can do so here.--}}
{{--</p>--}}

{{--<a href="{{ !empty($new_password_url) ? $new_password_url : '' }}" target="_blank" style="font-size: 16px;--}}
{{--background-color: #4dd0e1;--}}
{{--color: #fff;--}}
{{--display: table;--}}
{{--text-align: center;--}}
{{--margin: 50px auto;--}}
{{--font-family:'Montserrat', sans-serif;--}}
{{--padding: 13px 40px;--}}
{{---webkit-border-radius: 50px;-moz-border-radius: 50px;border-radius: 50px;--}}
{{--text-decoration: none;">Change password</a>--}}

{{--<p style="color: #263238;font-size: 14px;font-family: 'Montserrat', sans-serif; margin-bottom: 30px;font-weight: 400;line-height: 21px">--}}
{{--If you did not mean to change your password, you can disgard this email; your password will remain as it was.--}}
{{--</p>--}}

{{--<p style="color: #263238;font-size: 14px;font-family: 'Montserrat', sans-serif; margin-bottom: 25px;font-weight: 400;line-height: 21px">--}}
{{--Thank you--}}
{{--</p>--}}
{{--<p style="color: #263238;font-size: 14px;font-family: 'Montserrat', sans-serif;font-weight: 400;line-height: 21px">--}}
{{--The BirdDesk team--}}
{{--</p>--}}
{{--</td>--}}
{{--</tr>--}}
{{--</table>--}}
{{--</td>--}}
{{--</tr>--}}
{{--</table>--}}
{{--</body>--}}
{{--</html>--}}

<div style="background-color: #F8F8F8">
    <div style="height: 340px; width: 600px;background-color: #FFFFFF; padding: 64px 40px; margin: 0 auto">
        <table cellpadding="0" cellspacing="0" style="margin:0; padding:0; font-family: Arial, Helvetica, sans-serif;">
            <thead>
            <tr>
                <td>
                    <span style="font-size: 32px; font-weight: bold; line-height: 39px;">Hi, "{{ !empty($user_name) ? $user_name : '' }}
                        "</span>
                </td>
            </tr>
            </thead>
            <tbody>
            <tr>
                <td style="font-size: 18px;padding-top: 24px; padding-bottom: 24px; line-height: 27px;">
                     <span>
                         It appears that you’ve forgotten your password. If you would like to create a new password, you can do so here.
                     </span>
                </td>
            </tr>
            <tr>
                <td style="font-size: 18px; padding-bottom: 48px;">
                    <a href="{{ !empty($new_password_url) ? $new_password_url : '' }}" target="_blank">
                        <img style="height: 44px; width: 201px;"
                             src="../../../../../public/images/email/change-pass.png" alt="Confirm Email Address">
                    </a>
                </td>
            </tr>
            <tr>
                <td>
             <span style="font-size: 18px; padding-bottom: 48px; line-height: 27px;">
                 If you didn’t  mean to change your password, you can discard this email; your password will remain as it was.
                 <br/>
                 <br/>
                 Thank you!
                 <br/>
                 The BirdDesk team
             </span>
                </td>
            </tr>
            </tbody>
        </table>
    </div>
</div>