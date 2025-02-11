<?php

namespace Sgcomptech\FilamentTicketing\Filament\Resources;

use Filament\Forms\Components\Actions\Action;
use Filament\Forms\Components\Card;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables\Table;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Illuminate\Database\Eloquent\Builder;
use Sgcomptech\FilamentTicketing\Filament\Resources\TicketResource\RelationManagers\CommentsRelationManager;
use Sgcomptech\FilamentTicketing\Filament\Resources\TicketResource\RelationManagers\UsersRelationManager;
use Sgcomptech\FilamentTicketing\Models\Category;
use Sgcomptech\FilamentTicketing\Models\Ticket;
use BezhanSalleh\FilamentShield\Contracts\HasShieldPermissions;
use Filament\Forms\Components\RichEditor;


class TicketResource extends Resource implements HasShieldPermissions
{
    protected static ?string $model = Ticket::class;

    protected static ?string $navigationIcon = 'heroicon-o-ticket';

    public static function getNavigationLabel(): string
    {
        return __('Ticket');
    }

    public static function getNavigationGroup(): ?string
    {
        return __(config('filament-ticketing.navigation.group'));
    }

    public static function getNavigationSort(): ?int
    {
        return config('filament-ticketing.navigation.sort');
    }

        public static function getPermissionPrefixes(): array
    {
        return [
            'view',
            'view_any',
            'create',
            'update',
            'restore',
            'restore_any',
            'recorder',
            'delete',
            'delete_any',
            'force_delete',
            'force_delete_any',
            'manageAllTickets',
            'manageAssignedTickets',
            'assignTickets',
        ];
    }

    public static function form(Form $form): Form
    {
        $user = auth()->user();
        if (config('filament-ticketing.use_authorization')) {
            $cannotManageAllTickets = $user->cannot('manageAllTickets_ticket');
            $cannotManageAssignedTickets = $user->cannot('manageAssignedTickets_ticket', Ticket::class);
            $cannotAssignTickets = $user->cannot('assignTickets_ticket', Ticket::class);
        } else {
            $cannotManageAllTickets = false;
            $cannotManageAssignedTickets = false;
            $cannotAssignTickets = false;
        }

        $statuses = array_map(fn ($e) => __($e), config('filament-ticketing.statuses'));
        $priorities = array_map(fn ($e) => __($e), config('filament-ticketing.priorities'));

        return $form
            ->schema([
                Card::make([
                    Placeholder::make('User Name')
                        ->label(__('User Name'))
                        ->content(fn ($record) => $record?->user->name)
                        ->hiddenOn('create'),
                    Placeholder::make('User Email')
                        ->label(__('User Email'))
                        ->content(fn ($record) => $record?->user->email)
                        ->hiddenOn('create'),
                    TextInput::make('title')
                        ->translateLabel()
                        ->required()
                        ->maxLength(255)
                        ->columnSpan(2)
                        ->disabledOn('edit'),
                    RichEditor::make('content')
                        ->translateLabel()
                        ->required()
                        ->columnSpan(2)
                        ->disabledOn('edit'),
                    Select::make('status')
                        ->translateLabel()
                        ->options($statuses)
                        ->required()
                        ->disabled(fn ($record) => (
                            $cannotManageAllTickets &&
                            ($cannotManageAssignedTickets || !$record->assigned_to()->contains($user->id))
                        ))
                        ->hiddenOn('create'),
                    Select::make('priority')
                        ->translateLabel()
                        ->options($priorities)
                        ->disabledOn('edit')
                        ->required(),
                    Select::make('category_id')
                        ->translateLabel()
                        ->options(
                            collect(Category::all())
                                ->pluck('name', 'id')
                                ->toArray()
                        )
                        ->createOptionAction(
                            fn (Action $action) => $action->hidden(function () {
                                $roles = auth()->user()->roles->pluck('name');
                                return !$roles->contains('super_admin');
                            })
                        )
                        ->createOptionForm([
                            TextInput::make('name')
                                ->required()
                                ->maxLength(255),
                            Select::make('users')
                                ->translateLabel()
                                ->options(
                                    collect(config('filament-ticketing.user-model')::all())
                                        ->pluck('name', 'id')
                                        ->toArray()
                                )
                                ->multiple()
                                ->required(),
                        ])
                        ->createOptionUsing(function ($data) {
                            //create category and relate to user
                            $category = Category::create([
                                'name' => $data['name'],
                            ]);
                            $category->users()->attach($data['users']);
                            return $category->id;
                        })
                        ->disabledOn('edit')
                        ->required(),
                    Select::make('assigned_to')
                        ->label(__('Assign Ticket To'))
                        ->hint(__('Key in 3 or more characters to begin search'))
                        ->searchable()
                        ->getSearchResultsUsing(function ($search) {
                            if (strlen($search) < 3) {
                                return [];
                            }

                            return config('filament-ticketing.user-model')::where('name', 'like', "%{$search}%")
                                ->limit(50)
                                ->get()
                                ->filter(fn ($user) => $user->can('manageAssignedTickets_ticket'))
                                ->pluck('name', 'id');
                        })
                        ->multiple()
                        ->relationship('assigned_to', 'name')
                        ->getOptionLabelUsing(fn ($value): ?string => config('filament-ticketing.user-model')::find($value)?->name)
                        ->disabled($cannotAssignTickets)
                        ->hiddenOn('create'),
                ])->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        $user = auth()->user();
        if (config('filament-ticketing.use_authorization')) {
            $canManageAllTickets = $user->can('manageAllTickets_ticket', Ticket::class);
            $canManageAssignedTickets = $user->can('manageAssignedTickets_ticket', Ticket::class);
        } else {
            $canManageAllTickets = true;
            $canManageAssignedTickets = true;
        }

        $statuses = array_map(fn ($e) => __($e), config('filament-ticketing.statuses'));
        $priorities = array_map(fn ($e) => __($e), config('filament-ticketing.priorities'));

        return $table
            ->columns([
                TextColumn::make('identifier')
                    ->translateLabel()
                    ->searchable(),
                TextColumn::make('user.name')
                    ->translateLabel()
                    ->sortable()
                    ->searchable(),
                TextColumn::make('title')
                    ->translateLabel()
                    ->searchable()
                    ->words(8),
                TextColumn::make('status')
                    ->translateLabel()
                    ->formatStateUsing(fn ($record) => $statuses[$record->status] ?? '-'),
                TextColumn::make('priority')
                    ->translateLabel()
                    ->formatStateUsing(fn ($record) => $priorities[$record->priority] ?? '-')
                    ->color(fn ($record) => $record->priorityColor()),
                TextColumn::make('category.name')
                    ->translateLabel()
                    ->searchable(),
                TextColumn::make('assigned_to.name')
                    ->translateLabel()
                    ->visible($canManageAllTickets || $canManageAssignedTickets),
            ])
            ->filters([
                Filter::make('filters')
                    ->form([
                        Select::make('status')
                            ->translateLabel()
                            ->options($statuses),
                        Select::make('priority')
                            ->translateLabel()
                            ->options($priorities),
                    ])
                    ->query(
                        fn (Builder $query, array $data) => $query
                        ->when($data['status'], fn ($query, $status) => $query->where('status', $status))
                        ->when($data['priority'], fn ($query, $priority) => $query->where('priority', $priority))
                    ),
            ])
            ->actions([
                // ViewAction::make(),
                EditAction::make()
                    ->visible($canManageAllTickets || $canManageAssignedTickets),
            ])
            ->bulkActions([
                // DeleteBulkAction::make(),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            CommentsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => TicketResource\Pages\ListTicket::route('/'),
            'create' => TicketResource\Pages\CreateTicket::route('/create'),
            'edit' => TicketResource\Pages\EditTicket::route('/{record}/edit'),
        ];
    }
}
