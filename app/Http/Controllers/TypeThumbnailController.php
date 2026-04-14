<?php

namespace App\Http\Controllers;

use App\Models\TypeThumbnail;
use Illuminate\Http\Request;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\DB;

class TypeThumbnailController extends Controller
{
    use AuthorizesRequests;
    /**
     * Display a listing of the resource.
     */
    public function index()
    {

        return response()->json(TypeThumbnail::all());
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(TypeThumbnail $typeThumbnail)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, TypeThumbnail $typeThumbnail)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(TypeThumbnail $typeThumbnail)
    {
        //
    }
}
