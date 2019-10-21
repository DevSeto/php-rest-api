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

<div style="margin: 0 auto; width: 600px; padding: 40px; background-color: #fff;">

    <div
            style="margin:0 0 22px 0px; padding:0; font-family: Arial, Helvetica, sans-serif;font-size: 14px;color: #929395; line-height: 1">
        {!! $data['ticket_data']['mailbox']['name'] !!}: Received a new conversation <span style="color: #09B0E2">#{!! $data['ticket_data']['id'] !!}</span>
    </div>

    <table border="0" cellpadding="0" cellspacing="0" style="margin:0; padding:0; width: 100%;">
        <tr style="vertical-align: baseline;">
            <td style="padding-bottom: 24px; border-bottom: 1px solid #E7E7E7; width: 415px;">
                <h1
                        style="margin:0 0 16px 0px; padding:0; font-family: Arial, Helvetica, sans-serif;font-size: 22px;color: #000000; line-height: 1; font-weight: 300;">
                    {!! $data['ticket_data']['subject'] !!}
                </h1>
            </td>
            <td style="text-align: right; padding-bottom: 24px; border-bottom: 1px solid #E7E7E7; width: 101px;">
                <a href="#"
                   style="margin:0 0 16px 0px; padding:0; font-family: Arial, Helvetica, sans-serif;font-size: 16px;color: #D0021B; line-height: 1">
                    {!! $data['ticket_data']['status'] !!}
                </a>
            </td>
        </tr>

        <tr style="padding-top: 24px;">
            <td style="width: 415px; padding-top: 24px; padding-bottom: 8px;">
          <span
                  style="margin:0 0 16px 0px; padding:0 16px 0 0; font-family: Arial, Helvetica, sans-serif;font-size: 18px;color: #1D1D1D; line-height: 1; font-weight: 600;">
            {!! $data['ticket_data']['author'] !!}
          </span>
                <span
                        style="margin:0 0 16px 0px; paddiёng:0; font-family: Arial, Helvetica, sans-serif;font-size: 14px;color: #929395; line-height: 1">
            started the conversation
          </span>
            </td>
            <td style="text-align: right; width: 111px; padding-top: 24px; padding-bottom: 8px;">
          <span
                  style="margin:0 0 16px 0px; padding:0; font-family: Arial, Helvetica, sans-serif;font-size: 14px;color: #929395; line-height: 1">
            {!! date('F m,h:iA ', strtotime($data['ticket_data']['created_at'])) !!}
          </span>
            </td>
        </tr>

        <tr>
            <td colspan="2">
          <span
                  style="margin:0 0 16px 0px; padding:0; font-family: Arial, Helvetica, sans-serif;font-size: 14px;color: #929395; line-height: 1">Assigned:
              @if(!empty($data['ticket_data']['assigned_user']))
                  Assignee to
                  <a href="#" style="color: #4dd0e1; padding-left: 6px; text-decoration: none;">
                                            {!! $data['ticket_data']['assigned_user']['first_name']. " " . $data['ticket_data']['assigned_user']['last_name'] !!}
                                        </a>
              @else
                  Anyone
              @endif

          </span>
            </td>
        </tr>

        <tr>
            <td colspan="2" style="padding-top: 32px; padding-bottom: 40px;">
          <span
                  style="margin:0 0 16px 0px; padding:0; font-family: Arial, Helvetica, sans-serif;font-size: 16px; line-height: 24px; color: #000000;">
              {!! $data['ticket_data']['body'] !!}
          </span>
            </td>
        </tr>




        @if(count($data['timeline']) > 1)
            <h2 style="margin-top: 15px; font-size: 14px; color: #263238; font-family: 'Montserrat Medium', sans-serif; font-weight: bold;">Last actions</h2>
            @foreach($data['timeline'] as $action)

                <tr>
                    <td style="padding-bottom: 24px;">
          <span style="margin:0 0 16px 0px; padding:0; font-family: Arial, Helvetica, sans-serif;font-size: 16px; line-height: 24px; color: #000000;">
                        {!! date('F m,h:iA ', strtotime($action['created_at'])) !!}

          </span>
                        <table border="0" cellpadding="0" cellspacing="0" style="margin:0; padding:0; width: 100%;">
                            <tr>
                                <td style="width: 36px; border-left: 4px solid #E7E7E7"></td>
                                <td>

                                    @if($action['type'] == 'comment')
                                        @if(!empty($action['author']))
                                            <p>{!! $action['author']['first_name'].' '.$action['author']['last_name'] !!}</p>
                                        @else
                                            <p>{!! $action['from_name'] !!}</p>
                                        @endif
                                        <span style="margin:0 0 16px 0px; padding:0; font-family: Arial, Helvetica, sans-serif;font-size: 16px; line-height: 24px; color: #000000;">
                                        {!! $action['body'] !!}
                                    </span>
                                    @elseif($action['type'] == 'note')
                                        @if(!empty($action['author']))
                                            <p>{!! $action['author']['first_name'].' '.$action['author']['last_name'] !!}</p>
                                            <span style="margin:0 0 16px 0px; padding:0; font-family: Arial, Helvetica, sans-serif;font-size: 16px; line-height: 24px; color: #000000;">
                                        {!! $action['note'] !!}
                                    </span>
                                        @endif
                                    @endif


                                </td>
                            </tr>
                        </table>
                    </td>
                </tr>







            @endforeach
        @endif



<!--
        <tr>
            <td style="padding-bottom: 24px;">
          <span style="margin:0 0 16px 0px; padding:0; font-family: Arial, Helvetica, sans-serif;font-size: 16px; line-height: 24px; color: #000000;">
            Op 20-1-2019 om 12:00
          </span>
            </td>
        </tr>

        <table border="0" cellpadding="0" cellspacing="0" style="margin:0; padding:0; width: 100%;">
            <tr>
                <td style="width: 36px; border-left: 4px solid #E7E7E7"></td>
                <td>
            <span style="margin:0 0 16px 0px; padding:0; font-family: Arial, Helvetica, sans-serif;font-size: 16px; line-height: 24px; color: #000000;">
              But of the many celestial phenomenons, there is probably none as exciting as that time you see your first asteroid on the move in the heavens. To call asteroids the “rock stars” of astronomy is simultaneously a bad joke but an accurate depiction of how astronomy fans view them. Unlike suns, planets and moons, asteroids are on the move, ever changing and, if they appear in the night sky, exciting and dynamic.
            </span>
                </td>
            </tr>
        </table>
  -->  </table>

</div>

</body>

</html>