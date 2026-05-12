<?php

namespace App\Filament\Resources\Tournaments;

use App\Filament\Resources\Tournaments\Pages\CreateTournament;
use App\Filament\Resources\Tournaments\Pages\EditTournament;
use App\Filament\Resources\Tournaments\Pages\ListTournaments;
use App\Filament\Resources\Tournaments\Pages\ManageTournamentTeams;
use App\Filament\Resources\Tournaments\Pages\ViewTournament;
use App\Filament\Resources\Tournaments\Schemas\TournamentForm;
use App\Filament\Resources\Tournaments\Schemas\TournamentInfolist;
use App\Filament\Resources\Tournaments\Tables\TournamentsTable;
use App\Models\Tournament;
use BackedEnum;
use Filament\Pages\Enums\SubNavigationPosition;
use Filament\Resources\Pages\Page;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class TournamentResource extends Resource
{
    protected static ?string $model = Tournament::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $recordTitleAttribute = 'name';

    protected static ?SubNavigationPosition $subNavigationPosition = SubNavigationPosition::Top;

    public static function form(Schema $schema): Schema
    {
        return TournamentForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return TournamentInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return TournamentsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
        ];
    }

    public static function getRecordSubNavigation(Page $page): array
    {
        return $page->generateNavigationItems([
            ViewTournament::class,
            EditTournament::class,
            ManageTournamentTeams::class,
        ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListTournaments::route('/'),
            'create' => CreateTournament::route('/create'),
            'view' => ViewTournament::route('/{record}'),
            'edit' => EditTournament::route('/{record}/edit'),
            'teams' => ManageTournamentTeams::route('/{record}/teams'),
        ];
    }
}
