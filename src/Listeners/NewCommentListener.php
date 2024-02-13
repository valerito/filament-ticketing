<?php

namespace Sgcomptech\FilamentTicketing\Listeners;

use Filament\Notifications\Actions\Action;
use Filament\Notifications\Notification;
use Sgcomptech\FilamentTicketing\Events\NewComment;
use Sgcomptech\FilamentTicketing\Filament\Resources\TicketResource;

class NewCommentListener
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
    public function handle(NewComment $event): void
    {
        //each user of assigned ticket
        foreach ($event->comment->ticket->assigned_to()->get() as $user) {
            //send a notification to the user
            Notification::make()
                ->title('New comment on ticket: ' . $event->comment->ticket->title)
                ->icon('heroicon-o-ticket')
                ->body($event->comment->content)
                ->actions([
                    Action::make('View Ticket')
                        ->button()
                        ->url(TicketResource::getUrl('edit', ['record' => $event->comment->ticket->id])),
                ])
                ->sendToDatabase($user);
        }
    }
}
