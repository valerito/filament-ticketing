<?php

namespace Sgcomptech\FilamentTicketing\Filament\Resources\Widgets;

use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class TicketOverview extends BaseWidget
{
    //columns
    public function getColumns(): int
    {
        return 4;
    }

    protected function getStats(): array
    {
        //get tickets of this week
        $ticketsThisWeek = \Sgcomptech\FilamentTicketing\Models\Ticket::whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()])->get();
        //count tickets each day of this week
        $ticketsThisWeekByDay = $ticketsThisWeek->groupBy(function($date) {
            return $date->created_at->format('l');
        });
        //array of tickets each day of this week
        $ticketsThisWeekByDayCount = $ticketsThisWeekByDay->map(function($item, $key) {
            return $item->count();
        });
        //each day of this week
        $days = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'];
        //each day put number of tickets in array
        $ticketsThisWeekByDayArray = [];
        foreach($days as $day) {
            if(isset($ticketsThisWeekByDayCount[$day])) {
                $ticketsThisWeekByDayArray[] = $ticketsThisWeekByDayCount[$day];
            } else {
                $ticketsThisWeekByDayArray[] = 0;
            }
        }

        //filter only opened tickets
        $ticketsOpenedThisWeekByDayArray = [];
        foreach($days as $day) {
            if(isset($ticketsThisWeekByDay[$day])) {
                $ticketsOpenedThisWeekByDayArray[] = $ticketsThisWeekByDay[$day]->map(function($ticket) {
                    return $ticket->status == 1;
                })->count();
            } else {
                $ticketsOpenedThisWeekByDayArray[] = 0;
            }
        }

        //filter only closed tickets
        $ticketsClosedThisWeekByDayArray = [];
        foreach($days as $day) {
            if(isset($ticketsThisWeekByDay[$day])) {
                $ticketsClosedThisWeekByDayArray[] = $ticketsThisWeekByDay[$day]->map(function($ticket) {
                    return $ticket->status == 4;
                })->count();
            } else {
                $ticketsClosedThisWeekByDayArray[] = 0;
            }
        }

        //get all comments of this week
        $commentsThisWeek = \Sgcomptech\FilamentTicketing\Models\Comment::whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()])->get();
        //count comments each day of this week
        $commentsThisWeekByDay = $commentsThisWeek->groupBy(function($date) {
            return $date->created_at->format('l');
        });
        //each day put number of comments in array
        $commentsThisWeekByDayArray = [];
        foreach($days as $day) {
            if(isset($commentsThisWeekByDay[$day])) {
                $commentsThisWeekByDayArray[] = $commentsThisWeekByDay[$day]->count();
            } else {
                $commentsThisWeekByDayArray[] = 0;
            }
        }

        return [
            Stat::make('Total Tickets', $ticketsThisWeek->count())
            ->chart($ticketsThisWeekByDayArray)->extraAttributes(['class' => 'md:col-span-2'])->description('last 7 days'),
            Stat::make('Total Comments', $commentsThisWeek->count())
            ->chart($commentsThisWeekByDayArray)->extraAttributes(['class' => 'md:col-span-2'])->description('last 7 days'),
            Stat::make('Opened Tickets', $ticketsThisWeek->filter(function($ticket) {
                return $ticket->status == 1;
            })->count())
            ->chart($ticketsOpenedThisWeekByDayArray)->extraAttributes(['class' => 'md:col-span-1']),
            Stat::make('Closed Tickets', $ticketsThisWeek->filter(function($ticket) {
                return $ticket->status == 4;
            })->count())
            ->chart($ticketsClosedThisWeekByDayArray)->extraAttributes(['class' => 'md:col-span-1']),
        ];
    }
}
