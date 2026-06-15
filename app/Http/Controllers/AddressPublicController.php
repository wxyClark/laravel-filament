<?php

namespace App\Http\Controllers;

use App\Models\Address;

class AddressPublicController extends Controller
{
    public function index()
    {
        return view('address.index', [
            'total' => Address::count(),
            'provinceCount' => Address::where('level', 'province')->count(),
            'cityCount' => Address::where('level', 'city')->count(),
            'districtCount' => Address::where('level', 'district')->count(),
            'provinces' => Address::with(['children.children' => function($q) {
                $q->orderBy('sort');
            }])->where('level', 'province')->orderBy('sort')->get(['id','name','code']),
        ]);
    }
}
