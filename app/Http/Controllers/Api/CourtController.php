<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Court;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class CourtController extends Controller
{
    /**
     * Display a listing of courts.
     */
    public function index()
    {
        $courts = Court::all();
        return response()->json($courts);
    }

    /**
     * Store a newly created court.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'location' => 'required|string|max:255',
            'price_per_hour' => 'required|numeric|min:0',
            'status' => 'required|in:available,maintenance,inactive',
            'image' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $court = Court::create($request->all());
        return response()->json($court, 201);
    }

    /**
     * Display the specified court.
     */
    public function show(Court $court)
    {
        return response()->json($court);
    }

    /**
     * Update the specified court.
     */
    public function update(Request $request, Court $court)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'sometimes|required|string|max:255',
            'location' => 'sometimes|required|string|max:255',
            'price_per_hour' => 'sometimes|required|numeric|min:0',
            'status' => 'sometimes|required|in:available,maintenance,inactive',
            'image' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $court->update($request->all());
        return response()->json($court);
    }

    /**
     * Remove the specified court.
     */
    public function destroy(Court $court)
    {
        $court->delete();
        return response()->json(['message' => 'Court deleted successfully']);
    }
}