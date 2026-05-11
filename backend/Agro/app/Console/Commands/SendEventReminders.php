<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\CalendarEvent;
use App\Models\Utilisateur;
use App\Models\Notification;
use Illuminate\Support\Facades\Mail;
use App\Mail\EventReminderMail;

class SendEventReminders extends Command
{
    protected $signature = 'reminders:send';
    protected $description = 'Envoyer les rappels pour les événements de demain';

    public function handle()
    {
        $tomorrow = now()->addDay()->toDateString();
        $events = CalendarEvent::with('proprietaire')->whereDate('start_date', $tomorrow)->get();

        foreach ($events as $event) {
            $user = $event->proprietaire;
            if (!$user) continue;

            // 1. Notification in-app (base de données)
            Notification::create([
                'user_id' => $user->id,
                'title' => 'Rappel : ' . $event->title,
                'message' => "Votre événement \"{$event->title}\" est prévu pour demain ({$event->start_date}).",
            ]);

            // 2. Envoi d'email (optionnel, commentez si vous ne voulez que l'email)
            Mail::to($user->email)->send(new EventReminderMail($event));
        }

        $this->info('Rappels envoyés pour ' . $events->count() . ' événement(s).');
    }
}