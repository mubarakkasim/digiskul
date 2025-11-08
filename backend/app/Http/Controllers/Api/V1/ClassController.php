<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\ClassModel;
use Illuminate\Http\Request;

class ClassController extends Controller
{
    public function index(Request $request)
    {
        $schoolId = $request->user()->school_id;
        
        $classes = ClassModel::forSchool($schoolId)->get();

        return response()->json(['data' => $classes]);
    }
}

