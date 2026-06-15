<?php

namespace App\Filament\Resources\Tournaments\Pages;

use App\Filament\Resources\Tournaments\TournamentResource;
use Filament\Resources\Pages\Page;
use Filament\Resources\Pages\Concerns\InteractsWithRecord;

class TournamentBracketPage extends Page
{
    use InteractsWithRecord;

    protected static string $resource = TournamentResource::class;

    protected string $view = 'filament.resources.tournaments.pages.tournament-bracket-page';

    protected static ?string $title = 'Bracket';

    public function mount(int|string $record): void
    {
        $this->record = $this->resolveRecord($record);
    }
}
