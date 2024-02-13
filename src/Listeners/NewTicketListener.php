<?php

namespace Sgcomptech\FilamentTicketing\Listeners;

use Filament\Notifications\Actions\Action;
use Filament\Notifications\Notification;
use Sgcomptech\FilamentTicketing\Events\NewTicket;
use Sgcomptech\FilamentTicketing\Filament\Resources\TicketResource;

class NewTicketListener
{
    /**
     * Create the event listener.
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     */
    public function handle(NewTicket $event): void
    {
        foreach ($event->ticket->assigned_to()->get() as $user) {
            //send a notification to the user
            Notification::make()
                ->title('New ticket: ' . $event->ticket->title)
                ->icon('heroicon-o-ticket')
                ->body($event->ticket->content)
                ->actions([
                    Action::make('View Ticket')
                        ->button()
                        ->url(TicketResource::getUrl('edit', ['record' => $event->ticket->id])),
                ])
                ->sendToDatabase($user);
        }
    }
}
