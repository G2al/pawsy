<?php

namespace App\Filament\Resources\Exceptions\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\TimePicker;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class ExceptionForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(1)
            ->components([
                Section::make()
                    ->schema([
                        DatePicker::make('date')
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->label('Data')
                            ->native(false)
                            ->displayFormat('d/m/Y')
                            ->minDate(now())
                            ->columnSpanFull(),

                        TextInput::make('reason')
                            ->label('Motivo')
                            ->placeholder('es. Natale, Ferie Estive, Evento Privato...')
                            ->maxLength(255)
                            ->columnSpanFull(),

                        Toggle::make('is_closed')
                            ->label('Chiuso tutto il giorno')
                            ->helperText('Se disattivato, puoi impostare orari speciali')
                            ->reactive()
                            ->default(true)
                            ->columnSpanFull(),

                        TimePicker::make('morning_open')
                            ->label('Apertura Mattina')
                            ->seconds(false)
                            ->hidden(fn ($get) => $get('is_closed'))
                            ->columnSpanFull(),

                        TimePicker::make('morning_close')
                            ->label('Chiusura Mattina')
                            ->seconds(false)
                            ->hidden(fn ($get) => $get('is_closed'))
                            ->columnSpanFull(),

                        TimePicker::make('afternoon_open')
                            ->label('Apertura Pomeriggio')
                            ->seconds(false)
                            ->hidden(fn ($get) => $get('is_closed'))
                            ->columnSpanFull(),

                        TimePicker::make('afternoon_close')
                            ->label('Chiusura Pomeriggio')
                            ->seconds(false)
                            ->hidden(fn ($get) => $get('is_closed'))
                            ->columnSpanFull(),
                    ]),
            ]);
    }
}