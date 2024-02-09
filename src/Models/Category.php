<?php

namespace Sgcomptech\FilamentTicketing\Models;

use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    protected $guarded = [];

    public function tickets()
    {
        return $this->belongsToMany(Ticket::class);
    }

    public function users()
    {
        return $this->belongsToMany(config('filament-ticketing.user-model'), 'category_user');
    }
}
