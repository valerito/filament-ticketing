<?php

namespace Sgcomptech\FilamentTicketing\Models;

use Illuminate\Database\Eloquent\Model;

class Ticket extends Model
{
    protected $guarded = [];

    public function fill(array $attributes)
    {
        if (array_key_exists('assigned_to', $attributes)) {
            unset($attributes['assigned_to']);
        }

        return parent::fill($attributes);
    }

    protected static function boot()
    {
        parent::boot();

        static::created(function ($ticket) {
            $ticket->assigned_to()->sync($ticket->category->users);
        });

    }

    public function ticketable()
    {
        return $this->morphTo();
    }

    public function user()
    {
        return $this->belongsTo(config('filament-ticketing.user-model'));
    }

    public function comments()
    {
        return $this->hasMany(Comment::class);
    }

    public function assigned_to()
    {
        return $this->belongsToMany(config('filament-ticketing.user-model'), 'ticket_user');
    }

    public function priorityColor()
    {
        $colors = [1 => 'success', 2 => 'primary', 3 => 'warning', 4 => 'danger'];

        return $colors[$this->priority] ?? 'danger';
    }

    public function category()
    {
        return $this->hasOne(Category::class, 'id', 'category_id');
    }
}
