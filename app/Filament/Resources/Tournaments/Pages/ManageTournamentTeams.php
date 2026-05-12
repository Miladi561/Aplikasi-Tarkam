<?php

namespace App\Filament\Resources\Tournaments\Pages;

use App\Filament\Resources\Teams\TeamResource;
use App\Filament\Resources\Tournaments\TournamentResource;
use Filament\Actions\AttachAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DetachAction;
use Filament\Actions\DetachBulkAction;
use Filament\Resources\Pages\ManageRelatedRecords;
use Filament\Tables\Table;

class ManageTournamentTeams extends ManageRelatedRecords
{
    protected static string $resource = TournamentResource::class;

    protected static string $relationship = 'teams';

    protected static ?string $relatedResource = TeamResource::class;

    public function table(Table $table): Table
    {
        return $table
            ->headerActions([
                AttachAction::make()
                    ->preloadRecordSelect()
                    ->recordSelectSearchColumns(['name'])
                    ->multiple(),
                // ->schema(fn(AttachAction $action): array => [
                //     $action->getRecordSelect(),
                // ]),
            ])->recordActions([
                // ...
                DetachAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    // ...
                    DetachBulkAction::make(),
                ]),
            ]);
    }
}
