<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\EventCategory;
use Illuminate\Http\Request;

class EventCategoryController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $categories = EventCategory::all();
        return $categories;
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
    public function show(int $id)
    {
        $category = EventCategory::find($id);
        if (!$category) {
            return response()->json(['message' => 'Kategori bulunamadı!'], 404);
        }
        return $category;
    }

    public function eventCategoryEvents(int $id)
    {
        $events = EventCategory::find($id)->events()->paginate(10);
        $events->load('club', 'club.manager', 'club.manager.socialMediaLink', 'category');
        return $events;
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }

    public function searchEventCategory(string $search)
    {
        $categories = EventCategory::where('name', 'like', '%' . $search . '%')->get();
        return $categories;
    } 
}
