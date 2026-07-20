<?php

namespace App\Http\Controllers;

use App\Models\Address;

class AddressPublicController extends Controller
{
    public function index()
    {
        $levelCounts = Address::query()
            ->whereIn('level', ['province', 'city', 'district'])
            ->groupBy('level')
            ->selectRaw('level, count(*) as total')
            ->pluck('total', 'level');

        return view('address.index', [
            'total' => Address::count(),
            'provinceCount' => $levelCounts->get('province', 0),
            'cityCount' => $levelCounts->get('city', 0),
            'districtCount' => $levelCounts->get('district', 0),
            'provinces' => Address::with(['children.children' => function ($q) {
                $q->orderBy('sort');
            }])->where('level', 'province')->orderBy('sort')->get(['id', 'name', 'code']),
        ]);
    }
}
