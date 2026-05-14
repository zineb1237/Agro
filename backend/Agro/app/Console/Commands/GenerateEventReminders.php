<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\CalendarEvent;
use App\Models\Notification;

class GenerateEventReminders extends Command
{
    // 👇 DONNEZ UN NOM À LA COMMANDE (ex: generate:event-reminders)
    protected $signature = 'generate:event-reminders';

    protected $description = 'Génère des rappels in-app pour les événements du lendemain';

    public function handle()
    {
        $tomorrow = now()->addDay()->toDateString();
        $events = CalendarEvent::whereDate('start_date', $tomorrow)->get();

        foreach ($events as $event) {
            Notification::create([
                'proprietaire_id' => $event->proprietaire_id,
                'title' => 'Rappel action agricole',
                'message' => "📅 Demain : {$event->title}",
                'type' => 'rappel',
                'data' => ['event_id' => $event->id],
            ]);
        }

        $this->info('Notifications de rappel générées.');
    }
}