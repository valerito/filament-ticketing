<?php

namespace Sgcomptech\FilamentTicketing\Listeners;

use Filament\Notifications\Actions\Action;
use Filament\Notifications\Notification;
use Sgcomptech\FilamentTicketing\Events\NewAssignment;
use Sgcomptech\FilamentTicketing\Filament\Resources\TicketResource;

class NewAssignmentListener
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
    public function handle(NewAssignment $event): void
    {
        //each user of assigned ticket
        foreach ($event->users as $user) {
            //send a notification to the user
            Notification::make()
                ->title('New Ticket Assignment: ' . $event->ticket->title)
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
