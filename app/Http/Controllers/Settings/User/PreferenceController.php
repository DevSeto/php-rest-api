<?php

namespace App\Http\Controllers\Settings\User;

use App\Http\Controllers\Controller;
use App\Models\UserPreferences;
use App\Helpers\Crypto;
use App\Helpers\Helper;
use App\Services\UserPreferenceService;
use Illuminate\Http\Request;
use Response;
use Validator;
use File;
use Lang;

class PreferenceController extends Controller
{
    protected $preferenceService;

    function __construct(UserPreferenceService $preferenceService)
    {
        $this->middleware('check_token');
        $this->preferenceService = $preferenceService;
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $user = Helper::$user;
        $userPreferences = UserPreferences::where('user_id', $user['id'])->first();
        return Response::make(json_encode([
            'success' => true,
            'data' => (!empty($userPreferences)) ? $userPreferences : []
        ]), 200);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  int $userId
     * @return \Illuminate\Http\Response
     */
    public static function show($userId)
    {
        $userPreferences = UserPreferences::where('user_id', $userId)->first();
        return Response::make(json_encode([
            'success' => true,
            'data' => (!empty($userPreferences)) ? $userPreferences : []
        ]), 200);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }

    /**
     * Change user preferences
     *
     * @param  \Illuminate\Http\Request $request
     *
     * @return \Illuminate\Http\Response
     */
    public function changePreferences(Request $request)
    {
        $user = Helper::$user;
        $preferencesData = $request->all();
        unset($preferencesData['q']);
        UserPreferences::where('user_id', $user['id'])->update($preferencesData);

        return Response::make(json_encode(['success' => true]), 200);
    }
}
