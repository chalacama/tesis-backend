<?php

namespace App\Http\Controllers;

use App\Models\EducationalUnit;
use Illuminate\Http\Request;

class EducationalUnitController extends Controller
{
    public function index()
    {
        $educationalUnits = EducationalUnit::all();
        return response()->json($educationalUnits);
    }

    public function store(Request $request)
    {
        $educationalUnit = EducationalUnit::create($request->all());
        return response()->json($educationalUnit, 201);
    }

    public function show(EducationalUnit $educationalUnit)
    {
        return response()->json($educationalUnit);
    }

    public function update(Request $request, EducationalUnit $educationalUnit)
    {
        $educationalUnit->update($request->all());
        return response()->json($educationalUnit);
    }

    public function destroy(EducationalUnit $educationalUnit)
    {
        $educationalUnit->delete();
        return response()->json(null, 204);
    }
}
