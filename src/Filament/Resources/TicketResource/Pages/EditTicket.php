<?php

namespace Sgcomptech\FilamentTicketing\Filament\Resources\TicketResource\Pages;

use Filament\Pages\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;
use Sgcomptech\FilamentTicketing\Events\NewAssignment;
use Sgcomptech\FilamentTicketing\Models\Ticket;

class EditTicket extends EditRecord
{
    public $prev_assigned_to;

    public static function getResource(): string
    {
        return config('filament-ticketing.ticket-resource');
    }

    protected function getActions(): array
    {
        return [DeleteAction::make()];
    }

    public function getTitle(): string
    {
        $interacted = $this->record?->ticketable;

        return __('Ticket') . ($interacted ? ' [' . $interacted?->{$interacted?->model_name()} . ']' : '');
    }

    protected function afterFill()
    {
        $this->prev_assigned_to = Ticket::find($this->record->id)->assigned_to()->get();
    }

    protected function afterSave()
    {
        if(Ticket::find($this->record->id)->assigned_to()->get()->diff($this->prev_assigned_to)->isNotEmpty()){
            NewAssignment::dispatch(Ticket::find($this->record->id)->assigned_to()->get()->diff($this->prev_assigned_to), $this->record);
        }
        $this->prev_assigned_to = Ticket::find($this->record->id)->assigned_to()->get();
    }
}
