<?php

namespace App\Filament\Resources\Services\Schemas;

use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class ServiceForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(1)
            ->components([
                Section::make()
                    ->schema([
                        TextInput::make('name')
                            ->required()
                            ->maxLength(255)
                            ->label('Nome Servizio')
                            ->placeholder('es. Bagno completo')
                            ->columnSpanFull(),

                        Textarea::make('description')
                            ->rows(3)
                            ->maxLength(1000)
                            ->label('Descrizione')
                            ->placeholder('Descrivi il servizio...')
                            ->columnSpanFull(),

                        FileUpload::make('photo')
                            ->image()
                            ->imageEditor()
                            ->disk('public')
                            ->visibility('public') 
                            ->directory('services')
                            ->maxSize(2048)
                            ->label('Foto Servizio')
                            ->columnSpanFull(),

                        TextInput::make('duration')
                            ->required()
                            ->numeric()
                            ->minValue(5)
                            ->maxValue(480)
                            ->suffix('min')
                            ->label('Durata (minuti)')
                            ->columnSpanFull(),

                        TextInput::make('price')
                            ->required()
                            ->numeric()
                            ->minValue(0)
                            ->prefix('€')
                            ->step(0.01)
                            ->label('Prezzo')
                            ->columnSpanFull(),

                        Toggle::make('is_active')
                            ->label('Servizio Attivo')
                            ->default(true)
                            ->columnSpanFull(),
                    ]),
            ]);
    }
}