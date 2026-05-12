<?php

namespace App\Filament\Resources\Tournaments\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class TournamentForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->required(),
                TextInput::make('year'),
                Select::make('status')
                    ->options(['draft' => 'Draft', 'ongoing' => 'Ongoing', 'finished' => 'Finished'])
                    ->default('draft')
                    ->required(),
            ]);
    }
}
