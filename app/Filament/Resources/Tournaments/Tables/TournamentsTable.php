<?php

namespace App\Filament\Resources\Tournaments\Tables;

use App\Models\Tournament;
use App\Services\Bracket\GenerateBracketService;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class TournamentsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->searchable(),
                TextColumn::make('year'),
                TextColumn::make('status')
                    ->badge(),
                TextColumn::make('teams_count')
                    ->counts('teams')
                    ->label('Teams'),
                TextColumn::make('champion.name')
                    ->label('Champion')
                    ->placeholder('-'),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                Action::make('generateBracket')
                    ->label('Generate Bracket')
                    ->requiresConfirmation()
                    ->action(function (Tournament $record, GenerateBracketService $service): void {
                        $service->generate($record, true);

                        Notification::make()
                            ->title('Bracket berhasil dibuat')
                            ->success()
                            ->send();
                    }),
                Action::make('viewBracket')
                    ->label('View Bracket')
                    ->url(fn (Tournament $record): string => route('filament.admin.resources.tournaments.bracket', $record)),
                ViewAction::make(),
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
