<?php

namespace Sgcomptech\FilamentTicketing\Listeners;

use Filament\Notifications\Actions\Action;
use Filament\Notifications\Notification;
use Sgcomptech\FilamentTicketing\Events\NewResponse;
use Sgcomptech\FilamentTicketing\Filament\Resources\TicketResource;

class NewResponseListener
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
    public function handle(NewResponse $event): void
    {
        //send a notification to the user
        Notification::make()
            ->title('New comment on ticket: ' . $event->response->ticket->title)
            ->icon('heroicon-o-ticket')
            ->body($event->response->content)
            ->actions([
                Action::make('View Ticket')
                    ->button()
                    ->url(TicketResource::getUrl('edit', ['record' => $event->response->ticket->id])),
            ])
            ->sendToDatabase($event->response->ticket->user);
    }
}
