<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Report;
use App\Models\Notification;

class ReportController extends Controller
{
    public function index(Request $request)
    {
        $query = Report::with('category');

        if ($request->status) {
            $query->where('status', $request->status);
        }

        if ($request->category_id) {
            $query->where('category_id', $request->category_id);
        }

        return $query->latest()->get()->map(function ($report) {

            $report->image_url = $report->image
                ? asset('storage/' . $report->image)
                : null;

            return $report;
        });
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }
    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|min:5|max:100',
            'description' => 'required|min:10|max:500',
            'image' => 'nullable|image|mimes:jpg,jpeg,png|max:2048'
        ]);

        $imagePath = null;

        if ($request->hasFile('image')) {
            $imagePath = $request->file('image')->store('reports', 'public');
        }

        $report = Report::create([
            'user_id' => $request->user()->id, // Obtiene el usuario directamente de la petición
            'title' => $request->title,
            'description' => $request->description,
            'image' => $imagePath,
            'latitude' => $request->latitude,
            'longitude' => $request->longitude,
            'category_id' => $request->category_id,
        ]);

        Notification::create([
            'user_id' => $request->user()->id,
            'message' => 'Reporte creado correctamente'
        ]);

        return response()->json($report, 201);
    }
    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $report =  Report::findOrFail($id);

        $report->image_url = $report->image
            ? asset('storage/' . $report->image)
            : null;

        return $report;
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Report $report)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $report = Report::findOrFail($id);

        if ($request->hasFile('image')) {
            $imagePath = $request->file('image')->store('reports', 'public');

            $report->image = $imagePath;
        }

        $report->title = $request->title ?? $report->title;
        $report->description = $request->description ?? $report->description;
        $report->latitude = $request->latitude ?? $report->latitude;
        $report->longitude = $request->longitude ?? $report->longitude;

        $report->save();

        return response()->json($report);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $report = Report::findOrFail($id);

        $report->delete();

        return response()->json([
            'message' => 'Reporte eliminado'
        ]);
    }

    public function myReports(Request $request)
    {
        return Report::where('user_id', $request->user()->id)
            ->latest()
            ->get();
    }
}
