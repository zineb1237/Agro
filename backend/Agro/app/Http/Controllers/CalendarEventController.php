<?php

namespace App\Http\Controllers;

use App\Models\CalendarEvent;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class CalendarEventController extends Controller
{
    public function index()
    {
        $userId = Auth::id();
        if (!$userId) {
            return response()->json(['message' => 'Non authentifié'], 401);
        }
        $events = CalendarEvent::where('proprietaire_id', $userId)->get();
        return response()->json($events);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'parcelle_id' => 'nullable|exists:parcelle,id',
            'title' => 'required|string',
            'start_date' => 'required|date',
            'end_date' => 'nullable|date',
            'type' => 'string',
            'description' => 'nullable|string',
            'background_color' => 'nullable|string',
        ]);

        $data['proprietaire_id'] = Auth::id();
        $event = CalendarEvent::create($data);
        return response()->json($event, 201);
    }

    public function update(Request $request, $id)
    {
        $event = CalendarEvent::where('proprietaire_id', Auth::id())->findOrFail($id);
        $data = $request->validate([
            'title' => 'sometimes|string',
            'start_date' => 'sometimes|date',
            'end_date' => 'nullable|date',
            'type' => 'sometimes|string',
            'description' => 'nullable|string',
            'background_color' => 'nullable|string',
        ]);
        $event->update($data);
        return response()->json($event);
    }

    public function destroy($id)
    {
        $event = CalendarEvent::where('proprietaire_id', Auth::id())->findOrFail($id);
        $event->delete();
        return response()->noContent();
    }

    // Pour les notifications : événements d'aujourd'hui et de demain (notif J-1) non notifiés
    public function upcomingEvents()
    {
        $today = now()->toDateString();
        $tomorrow = now()->addDay()->toDateString();
        
        $events = CalendarEvent::where('proprietaire_id', Auth::id())
            ->whereIn(\DB::raw('DATE(start_date)'), [$today, $tomorrow])
            ->whereNull('notified_at')
            ->get();
            
        return response()->json($events);
    }

    // Marquer comme notifié
    public function markNotified($id)
    {
        $event = CalendarEvent::where('proprietaire_id', Auth::id())->findOrFail($id);
        $event->update(['notified_at' => now()]);
        return response()->noContent();
    }
}