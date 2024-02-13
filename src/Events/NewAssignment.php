<?php

namespace Sgcomptech\FilamentTicketing\Events;

use App\Models\User;
use Filament\Notifications\Events\DatabaseNotificationsSent;
use Filament\Notifications\Notification;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Sgcomptech\FilamentTicketing\Models\Ticket;

class NewAssignment
{
    use Dispatchable;
    use InteractsWithSockets;
    use SerializesModels;

    /**
     * Create a new event instance.
     *
     * @param  Sgcomptech\FilamentTicketing\Models\Ticket  $ticket
     * @return void
     */
    public function __construct(public Collection $users, public Ticket $ticket)
    {
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return \Illuminate\Broadcasting\Channel|array
     */
    public function broadcastOn()
    {
        return new PrivateChannel(config('filament-ticketing.event_broadcast_channel'));
    }
}
