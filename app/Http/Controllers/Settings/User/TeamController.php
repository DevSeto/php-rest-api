<?php

namespace App\Http\Controllers\Settings\User;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Team;
use App\Models\TeamUsers;
use App\Helpers\Helper;
use Illuminate\Support\Facades\Lang;
use Response;
use Validator;
use App\Helpers\Crypto;


class TeamController extends Controller
{
    function __construct(Request $request)
    {
        $this->middleware('check_token');
    }

    /**
     * Get all teams of team lead
     *
     * @param  \Illuminate\Http\Request $request
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $user = Helper::getUser($request->header('Authorization'));
        $teams = Team::with('members')->where('lead_id', $user['id'])->get();
        return Response::make(json_encode([
            'success' => true,
            'data' => !empty($teams) ? Crypto::encrypt($teams) : []
        ]), 200);
    }

    /**
     * Create a new team
     *
     * @param  \Illuminate\Http\Request $request
     *
     * @return \Illuminate\Http\Response
     */
    public function create(Request $request)
    {
        $data = json_decode(Crypto::decrypt($request->getContent()), true);
        $teamLead = Helper::getUser($request->header('Authorization'));

        $validationRules = [
            'name' => 'required',
            'description' => 'required'
        ];

        $validator = Validator::make($data, $validationRules);
        if ($validator->fails()) {
            return Response::make(json_encode([
                'success' => false,
                'errors' => Crypto::encrypt(json_encode($validator->errors(), true))
            ]), 422);
        }

        $dataTeam = [
            'lead_id' => $teamLead['id'],
            'name' => $data['name'],
            'description' => $data['description']
        ];

        $newTeam = Team::create($dataTeam);
        $teamMembers = $data['users_ids'];
        if (!empty($teamMembers)) {
            foreach ($teamMembers as $userId) {
                TeamUsers::create([
                    'team_id' => $newTeam->id,
                    'user_id' => $userId
                ]);
            }
        }

        return Response::make(json_encode([
            'success' => true,
            'data' => !empty($newTeam) ? Crypto::encrypt($newTeam) : []
        ]), 200);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Get team and members of team
     *
     * @param  int $teamId
     * @return \Illuminate\Http\Response
     */
    public function show($teamId)
    {
        $team = Team::find($teamId);
        if (empty($team)) {
            return Helper::send_error_response('team_id', Lang::get('teams.wrong_id'), 422);
        }

        return Response::make(json_encode([
            'success' => true,
            'data' => Crypto::encrypt($team->with('members')->first())
        ]), 200);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update team
     *
     * @param  \Illuminate\Http\Request $request
     * @param  int $teamId
     *
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $teamId)
    {
        $data = json_decode(Crypto::decrypt($request->getContent()), true);
        $validationRules = [
            'name' => 'required',
            'description' => 'required'
        ];

        $validator = Validator::make($data, $validationRules);
        if ($validator->fails()) {
            return Response::make(json_encode([
                'success' => false,
                'errors' => Crypto::encrypt(json_encode($validator->errors(), true))
            ]), 422);
        }

        $team = Team::find($teamId);
        if (!empty($team)) {
            $team->update([
                'name' => $data['name'],
                'description' => $data['description']
            ]);

            $teamMembers = TeamUsers::where('team_id', $teamId);
            if (!empty($teamMembers->get()->toArray())) {
                $teamMembers->delete();

                foreach ($data['users_ids'] as $userId) {
                    TeamUsers::create([
                        'user_id' => $userId,
                        'team_id' => $teamId
                    ]);
                }
            }

            return Response::make(json_encode([
                'success' => true,
                'data' => !empty($newTeam) ? Crypto::encrypt($newTeam) : []
            ]), 200);
        }
        return Helper::send_error_response('team_id', Lang::get('teams.wrong_id'), 422);
    }

    /**
     * Delete all teams of team lead
     *
     * @param  \Illuminate\Http\Request $request
     *
     * @return \Illuminate\Http\Response
     */
    public function deleteAll(Request $request)
    {
        $teamLead = Helper::getUser($request->header('Authorization'));
        $allTeams = Team::where('lead_id', $teamLead['id']);
        if (!empty($allTeams->get()->toArray())) {
            // delete team members
            foreach ($allTeams->get()->toArray() as $team) {
                TeamUsers::where('team_id', $team['id'])->delete();
            }
            // delete all teams of team lead
            $allTeams->delete();
            return Response::make(json_encode(['success' => true]), 200);
        }
        return Response::make(json_encode(['success' => false]), 200);
    }

    /**
     * Delete team by Id
     *
     * @param  int $teamId
     *
     * @return \Illuminate\Http\Response
     */
    public function destroy($teamId)
    {
        $team = Team::find($teamId);
        if (!empty($team)) {
            $team->delete();
            TeamUsers::where('team_id', $teamId)->delete();
            return Response::make(json_encode(['success' => true]), 200);
        }
        return Helper::send_error_response('team_id', Lang::get('teams.wrong_id'), 404);
    }

    /**
     * To manage the members of team
     *
     * @param  \Illuminate\Http\Request $request
     * @param integer $teamId
     *
     * @return \Illuminate\Http\Response
     */
    public function manageTeam(Request $request, $teamId)
    {
        $data = json_decode(Crypto::decrypt($request->getContent()), true);
        if (!empty($data['users_ids'])) {
            TeamUsers::where('team_id', $teamId)->delete();
            foreach ($data['users_ids'] as $memberId) {
                TeamUsers::create([
                    'team_id' => $teamId,
                    'user_id' => $memberId
                ]);
            }
        } else {
            TeamUsers::where('team_id', $teamId)->delete();
        }
        return Response::make(json_encode(['success' => true]), 200);
    }
}
