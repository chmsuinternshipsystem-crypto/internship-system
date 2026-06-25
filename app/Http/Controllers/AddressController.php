<?php

namespace App\Http\Controllers;

use App\Models\PhilippineBarangay;
use App\Models\PhilippineCity;
use App\Models\PhilippineProvince;
use Illuminate\Http\Request;

class AddressController extends Controller
{
    public function provinces()
    {
        return PhilippineProvince::orderBy('name')->get(['id', 'code', 'name']);
    }

    public function cities(Request $request, PhilippineProvince $province)
    {
        return $province->cities()->orderBy('name')->get(['id', 'code', 'name', 'type']);
    }

    public function barangays(Request $request, PhilippineCity $city)
    {
        return $city->barangays()->orderBy('name')->get(['id', 'code', 'name']);
    }
}
