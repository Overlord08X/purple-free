<?php

namespace App\Http\Controllers\project;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class wilayahController extends Controller
{
    public function index()
    {
        return view('project.wilayah');
    }

    public function provinsi()
    {
        return response()->json(
            DB::table('reg_provinces')->get()
        );
    }

    public function kota($id)
    {
        return response()->json(
            DB::table('reg_regencies')
                ->where('province_id', $id)
                ->get()
        );
    }

    public function kecamatan($id)
    {
        return response()->json(
            DB::table('reg_districts')
                ->where('regency_id', $id)
                ->get()
        );
    }

    public function kelurahan($id)
    {
        return response()->json(
            DB::table('reg_villages')
                ->where('district_id', $id)
                ->get()
        );
    }
}
