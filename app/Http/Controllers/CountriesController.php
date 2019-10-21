<?php

namespace App\Http\Controllers;

use App\Models\CountryCodes;
use Response;
use App\Helpers\Crypto;

class CountriesController extends Controller
{

    /**
     * Get all countries
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $data = CountryCodes::get();
        return Response::make(json_encode([
            'success' => true,
            'data' => !empty($data->toArray()) ? $data : []
        ]), 200);
    }

    /**
     * Get all countries
     *
     * @param int $id
     *
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $data = CountryCodes::find($id);
        return Response::make(json_encode([
            'success' => true,
            'data' => !empty($data) ? $data : []
        ]), 200);
    }
}
