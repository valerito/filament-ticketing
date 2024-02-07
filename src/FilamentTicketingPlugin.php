<?php

declare(strict_types=1);

namespace Sgcomptech\FilamentTicketing;

//use BezhanSalleh\FilamentShield\Support\Utils;
use Filament\Contracts\Plugin;
use Filament\Panel;

class FilamentTicketingPlugin implements Plugin
{
    //use Concerns\CanCustomizeColumns;

    public static function make(): static
    {
        return app(static::class);
    }

    public function getId(): string
    {
        return 'filament-ticketing';
    }

    public function register(Panel $panel): void
    {
        //if (! Utils::isResourcePublished()) {
            $panel->resources([
                Filament\Resources\TicketResource::class,
            ]);
        //}
    }

    public function boot(Panel $panel): void
    {
        //
    }

    public static function get(): static
    {
        /** @var static $plugin */
        $plugin = filament(app(static::class)->getId());

        return $plugin;
    }
}
