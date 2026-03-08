<?php

namespace App\Http\Controllers\project;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class projectController extends Controller
{
    public function index()
    {
        return view('project.index');
    }

    public function kota()
    {
        return view('project.kota');
    }
}
