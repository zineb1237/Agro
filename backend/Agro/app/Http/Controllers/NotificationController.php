<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
 // app/Http/Controllers/NotificationController.php
use App\Models\Notification;
class NotificationController extends Controller
{
  

public function index()
{
    $notifications = Notification::where('proprietaire_id', auth()->id())
        ->orderBy('created_at', 'desc')
        ->get();
    return response()->json($notifications);
}

public function markAsRead($id)
{
    $notif = Notification::where('proprietaire_id', auth()->id())->where('id', $id)->firstOrFail();
    $notif->update(['read_at' => now()]);
    return response()->noContent();
}
}
