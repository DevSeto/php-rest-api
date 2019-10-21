<?php

namespace App\Http\Controllers\SuperAdmin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Subdomains;
use App\Helpers\Helper;
use Response;
use App\Helpers\Crypto;

class SuperAdminController extends Controller
{
    /**
     * Check Sub domain
     *
     * @return \Illuminate\Http\Response
     */
    public function checkSubDomain()
    {
        $subDomainOfUrl = Helper::getSubDomain();
        $checkSubDomain = Subdomains::where('company_url', $subDomainOfUrl)->first();

        return Response::make(json_encode([
            'success' => !empty($checkSubDomain) ? true : false,
            'data' => !empty($checkSubDomain) ? Crypto::encrypt($checkSubDomain) : []
        ]), !empty($checkSubDomain) ? 200 : 451);
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
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
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
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
}
