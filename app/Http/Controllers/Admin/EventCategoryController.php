<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Event;
use App\Models\EventCategory;
use Illuminate\Http\Request;

class EventCategoryController extends Controller
{
    public function index()
    {
        $categories = EventCategory::all();

        return response()->json($categories, 200);
    }
    

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|unique:event_categories,name',
        ]);

        $category = EventCategory::create($request->all());
        return response()->json($category, 201);
    }

    public function show(int $id)
    {
        $category = EventCategory::find($id);
        if (!$category) {
            return response()->json(['message' => 'Kategori bulunamadı!'], 404);
        }
        return $category;
    }

    public function update(Request $request, int $id)
    {
        $request->validate([
            'name' => 'required|string|unique:event_categories,name,' . $id,
        ]);

        $category = EventCategory::find($id);
        if (!$category) {
            return response()->json(['message' => 'Kategori bulunamadı!'], 404);
        }
        $category->update($request->all());
        return response()->json($category, 200);
    }

    public function destroy(int $id)
    {
        $category = EventCategory::find($id);
        if (!$category) {
            return response()->json(['message' => 'Kategori bulunamadı!'], 404);
        }
        $category->delete();
        return response()->json(['message' => 'Kategori silindi!'], 200);
    }

    public function restore(int $id)
    {
        $category = EventCategory::onlyTrashed()->find($id);
        if (!$category) {
            return response()->json(['message' => 'Kategori bulunamadı!'], 404);
        }
        $category->restore();
        return response()->json(['message' => 'Kategori geri yüklendi!'], 200);
    }


    public function deletedEventCategories()
    {
        $categories = EventCategory::onlyTrashed()->paginate(10);
        return $categories;
    }


    public function search(string $search)
    {
        $events = EventCategory::where('name', 'LIKE', '%' . $search . '%')->get();
        return $events;
    }
}
