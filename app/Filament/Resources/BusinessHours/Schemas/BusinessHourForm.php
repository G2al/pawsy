<?php

namespace App\Filament\Resources\BusinessHours\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TimePicker;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class BusinessHourForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(1)
            ->components([
                Section::make()
                    ->schema([
                        Select::make('day_of_week')
                            ->required()
                            ->disabled()
                            ->label('Giorno')
                            ->options([
                                0 => 'Domenica',
                                1 => 'Lunedì',
                                2 => 'Martedì',
                                3 => 'Mercoledì',
                                4 => 'Giovedì',
                                5 => 'Venerdì',
                                6 => 'Sabato',
                            ])
                            ->columnSpanFull(),

                        Toggle::make('is_closed')
                            ->label('Chiuso')
                            ->helperText('Attiva se il locale è chiuso questo giorno')
                            ->reactive()
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