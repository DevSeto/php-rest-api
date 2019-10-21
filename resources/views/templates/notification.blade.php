<!DOCTYPE html>
<html>
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
        <title>BirdDesk</title>
        <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    </head>
    <body style="margin: 0; padding: 40px; background-color: #f4f8fb">
        <table align="center"  cellpadding="0" cellspacing="0" width="615px" style="border-collapse: collapse;background-color: rgb(255, 255, 255);margin: 40px auto;-webkit-box-shadow: 0 0 24px 0 rgba(221,232,240,1);-moz-box-shadow: 0 0 24px 0 rgba(221,232,240,1);box-shadow: 0 0 24px 0 rgba(221,232,240,1);">
            <tr>
                <td>
                    <table style="width: 100%;">
                        <tr>
                            <td style="padding: 40px 42px 32px;">

                                <ul style="margin: 0; text-align: center; list-style-type: none; padding: 0">
                                    <li style="vertical-align:middle; display: inline-block"><a href="#" style="font-family:'Montserrat',sans-serif ;font-size: 24px;color: #0a0a0a;text-decoration: none;font-weight: 700"><img src="https://login.birdtest.nl/public/images/logo.png" alt="BirdDesk" style="margin-bottom: -10px;"><span style="margin: 0 0 0 5px;">BirdDesk</span> </a></li>
                                </ul>
                            </td>
                        </tr>
                    </table>
               </td>
            </tr>
            <tr>
                <td>
                    <table style="width: 100%;">
                        <tr>
                            <td style="padding: 0 42px">
                                <h2 style="margin-top: 0; font-size: 16px; color: #0a0a0a; font-family: 'Montserrat', sans-serif; font-weight: bold;"> {!! $data['ticket_data']['subject'] !!} , ticket ID# {!! $data['ticket_data']['id'] !!}, {!! $data['ticket_data']['mailbox']['name'] !!} </h2>
                                <p style="font-size: 13px; line-height: 17px; color: #263238; font-family: 'Montserrat', sans-serif;margin-bottom: 20px;">
                                    {!! $data['last_comment'] !!}
                                </p>
                            </td>
                        </tr>

                    </table>
                </td>
            </tr>
            <tr style="margin-bottom: 15px;">
                <td>
                    <table style="width: 100%;">
                        <tr>
                            <td style="padding: 0 42px;">
                                <p style="font-size: 14px; padding-top: 8px; margin-top: 0; margin-bottom: 0;line-height: 17px; color: #263238; display: inline-block;font-weight: 600; float: left; font-family: 'Montserrat Medium', sans-serif;">Ticket status</p>
                                <p style="float: right;margin-top: 0; margin-bottom: 0;"><a href="#" style=" border-radius: 15px; border: 1px dashed #4dd0e1; text-decoration: none; display: inline-block; padding: 7px 27px; font-size: 14px; line-height: 17px; color: #263238; font-weight: bold; font-family: 'Montserrat Medium', sans-serif;">{!! $data['ticket_data']['status'] !!}</a></p>
                            </td>
                        </tr>
                    </table>
                </td>
            </tr>

            <tr>
                <td>
                    <table style="width: 100%;">
                        <tr>
                            <td style="padding: 0 42px">
                                <p style="font-size: 14px; line-height: 17px; display: inline-block; color: #263238; font-weight: 600; font-family: 'Montserrat Medium', sans-serif;">

                                    @if(!empty($data['ticket_data']['assigned_user']))
                                        Assignee to
                                        <a href="#" style="color: #4dd0e1; padding-left: 6px; text-decoration: none;">
                                            {!! $data['ticket_data']['assigned_user']['first_name']. " " . $data['ticket_data']['assigned_user']['last_name'] !!}
                                        </a>

                                    @else
                                        Not assigned
                                    @endif
                                </p>
                                {{--<p style="min-width: 100px; float:right; display: inline-block;"> Oct 6, 09:56 am</p>--}}
                            </td>
                        </tr>
                    </table>
                </td>
            </tr>
            <tr>
                <td>
                    <table style="width: 100%;">
                        <tr>
                            <td style="padding: 0 42px">
                                @if(!empty($data['ticket_data']['labels']))
                                <p style="font-size: 14px; line-height: 17px; display: inline-block; color: #263238; font-weight: 600; font-family: 'Montserrat Medium', sans-serif;">Ticket Labels
                                    @foreach($data['ticket_data']['labels'] as $label)
                                        <a href="#" style="color: {{$label['color']}};  padding-left: 6px; text-decoration: none;"> {!! $label['body'] !!} </a>
                                    @endforeach
                                </p>
                                @endif
                            </td>
                        </tr>
                    </table>
                </td>
            </tr>

            <tr>
                <td>
                    <table style="width: 100%;">
                        <tr>
                            <td style="padding: 0 42px">
                                @if(count($data['timeline']) > 1)
                                <h2 style="margin-top: 15px; font-size: 14px; color: #263238; font-family: 'Montserrat Medium', sans-serif; font-weight: bold;">Last actions</h2>
                                    @foreach($data['timeline'] as $action)
                                        <div style="border-bottom: 1px solid #b5dee1; padding: 20px 0; font-size: 13px; margin-top: 0;line-height: 17px; color: #263238; font-family: 'Montserrat', sans-serif;">
                                            @if($action['type'] == 'comment')
                                                @if(!empty($action['author']))
                                                    <p>{!! $action['author']['first_name'].' '.$action['author']['last_name'] !!}</p>
                                                @else
                                                    <p>{!! $action['from_name'] !!}</p>
                                                @endif
                                                    <p>{!! $action['body'] !!}</p>
                                            @elseif($action['type'] == 'note')
                                                @if(!empty($action['author']))
                                                    <p>{!! $action['author']['first_name'].' '.$action['author']['last_name'] !!}</p>
                                                    <p>{!! $action['note'] !!}</p>
                                                @endif
                                            @endif

                                        </div>
                                    @endforeach
                                @endif
                            </td>
                        </tr>

                    </table>
                </td>
            </tr>

            <tr>
                <td>
                    <table style="width: 100%;">
                        <tr>
                            <td style="padding: 34px 42px 130px">
                                <a href="{{$data['href']}}" style="text-decoration: none">
                                    <button type="submit" style="padding: 12px 38px; outline: none; border-radius: 15px; border: none; background-color: #4dd0e1; color: white; margin: 0 auto; display: block;text-transform: uppercase;">See ticket</button>
                                </a>
                            </td>
                        </tr>
                    </table>
                </td>
            </tr>
        </table>
    </body>
</html>