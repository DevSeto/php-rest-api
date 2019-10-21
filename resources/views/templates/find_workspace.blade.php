

<table border="0" cellpadding="0" cellspacing="0"
       style="margin:0 auto; padding:0; width: 600px; background-color: #FAFAFA">
    <tr>
        <td style="padding: 45px 58px 29px 58px;">
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
                                style="margin:0; padding:0; font-weight: 600; font-family: Arial, Helvetica, sans-serif; font-size: 48px; text-align: center;">
                            Your companies
                        </h1>
                    </td>
                </tr>
                <tr>
                    <td>
                        <p
                                style="margin:0; padding:0; font-family: Arial, Helvetica, sans-serif; font-size: 18px; line-height: 24px; text-align: center; padding-bottom: 32px">
                            You are already have these BirdDesk companies.
                        </p>
                    </td>
                </tr>
                <tr>
                    <td>
                        <table border="0" cellpadding="0" cellspacing="0" style="margin:0 auto; padding:0; width: 100%;">
                            @if(!empty($workspaces))
                                <tr>
                                    <td colspan="3" style="padding-bottom: 32px">
                                        <div style="height: 1px; width: 87%; margin: 0 auto; background-color: #E7E7E7;"></div>
                                    </td>
                                </tr>
                                @foreach($workspaces as $workspace)

                                    <tr>
                                        <td style="width: 47.5%; vertical-align: baseline;">
                                            <img src="https://api.birdtest.nl/uploads/email/logo-mail.png" style="height: 36px; width: auto; vertical-align: bottom;"
                                                 alt="Birddesk">
                                            <span
                                                    style="margin:0; padding:0; font-family: Arial, Helvetica, sans-serif; font-weight: bold; padding-left: 32px; font-size: 18px; line-height: 18px; color: #1D1D1D;">{{$workspace['company']['company_url']}}</span>
                                        </td>
                                        <td style="width: 5%; vertical-align: baseline;"></td>
                                        <td style="width: 47.5%; text-align: right;">
                                            <a href="https://{{$workspace['company']['company_url'].$url."/login"}}" style="display: inline-block; height: 40px;">
                                                <img src="https://api.birdtest.nl/uploads/email/launch.png" height="40" width="auto" alt="Launch">
                                            </a>
                                        </td>
                                    </tr>

                                    <tr>
                                        <td colspan="3" style="padding: 32px 0">
                                            <div style="height: 1px; width: 87%; margin: 0 auto; background-color: #E7E7E7;"></div>
                                        </td>
                                    </tr>
                                @endforeach

                            @endif


                        </table>
                    </td>
                </tr>
                <tr>
                    <td style="padding-bottom: 26px">
                        <p
                                style="margin:0; padding:0; font-family: Arial, Helvetica, sans-serif; font-size: 16px; color: #929395; line-height: 24px; text-align: center;">
                            Looking for different company? You can try <a href="#" style="color: #02BFF3">another email address</a>.
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
