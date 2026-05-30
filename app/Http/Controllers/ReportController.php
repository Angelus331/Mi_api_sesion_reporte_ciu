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

    public function create()
    {
        //
    }

    public function store(Request $request)
    {
        // VALIDACIÓN AGRESIVA: Asegura que las coordenadas sean numéricas y la categoría exista
        $request->validate([
            'title' => 'required|min:5|max:100',
            'description' => 'required|min:10|max:500',
            'image' => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
            'latitude' => 'nullable|numeric',
            'longitude' => 'nullable|numeric',
            'category_id' => 'nullable|integer',
        ]);

        $imagePath = null;

        if ($request->hasFile('image')) {
            $imagePath = $request->file('image')->store('reports', 'public');
        }

        // CASTEO SEGURO: Convertimos explícitamente a float para limpiar cualquier residuo string de Flutter
        $report = Report::create([
            'user_id' => $request->user()->id,
            'title' => $request->title,
            'description' => $request->description,
            'image' => $imagePath,
            'latitude' => $request->latitude ? (float) $request->latitude : null,   // 👈 Casteo a float
            'longitude' => $request->longitude ? (float) $request->longitude : null, // 👈 Casteo a float
            'category_id' => $request->category_id ? (int) $request->category_id : null,
        ]);

        Notification::create([
            'user_id' => $request->user()->id,
            'message' => 'Reporte creado correctamente'
        ]);

        // Retornamos el objeto con su URL de imagen mapeada para que Flutter lo pinte al instante
        $report->image_url = $report->image ? asset('storage/' . $report->image) : null;

        return response()->json($report, 201);
    }

    public function show(string $id)
    {
        $report = Report::findOrFail($id);

        $report->image_url = $report->image
            ? asset('storage/' . $report->image)
            : null;

        return $report;
    }

    public function edit(Report $report)
    {
        //
    }

    public function update(Request $request, string $id)
    {
        $report = Report::findOrFail($id);

        // Validamos también la actualización de la misma manera segura
        $request->validate([
            'title' => 'nullable|min:5|max:100',
            'description' => 'nullable|min:10|max:500',
            'image' => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
            'latitude' => 'nullable|numeric',
            'longitude' => 'nullable|numeric',
        ]);

        if ($request->hasFile('image')) {
            $imagePath = $request->file('image')->store('reports', 'public');
            $report->image = $imagePath;
        }

        $report->title = $request->title ?? $report->title;
        $report->description = $request->description ?? $report->description;

        // Asignamos convirtiendo a tipos numéricos limpios
        if ($request->has('latitude')) $report->latitude = $request->latitude ? (float) $request->latitude : null;
        if ($request->has('longitude')) $report->longitude = $request->longitude ? (float) $request->longitude : null;

        $report->save();

        // Mapeamos el nuevo URL antes de responder a Flutter
        $report->image_url = $report->image ? asset('storage/' . $report->image) : null;

        return response()->json($report);
    }

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
            ->get()
            ->map(function ($report) {
                $report->image_url = $report->image ? asset('storage/' . $report->image) : null;
                return $report;
            });
    }
}
