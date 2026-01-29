<?php

namespace App\Filament\Resources\BusinessHours\Tables;

use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class BusinessHoursTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('day_of_week')
                    ->label('Giorno')
                    ->formatStateUsing(fn ($state) => match($state) {
                        0 => 'Domenica',
                        1 => 'Lunedì',
                        2 => 'Martedì',
                        3 => 'Mercoledì',
                        4 => 'Giovedì',
                        5 => 'Venerdì',
                        6 => 'Sabato',
                        default => 'Sconosciuto',
                    })
                    ->sortable()
                    ->weight('bold'),

                IconColumn::make('is_closed')
                    ->label('Stato')
                    ->boolean()
                    ->trueIcon('heroicon-o-x-circle')
                    ->falseIcon('heroicon-o-check-circle')
                    ->trueColor('danger')
                    ->falseColor('success')
                    ->alignCenter(),

                TextColumn::make('morning_open')
                    ->label('Mattina Apertura')
                    ->time('H:i')
                    ->placeholder('-'),

                TextColumn::make('morning_close')
                    ->label('Mattina Chiusura')
                    ->time('H:i')
                    ->placeholder('-'),

                TextColumn::make('afternoon_open')
                    ->label('Pomeriggio Apertura')
                    ->time('H:i')
                    ->placeholder('-'),

                TextColumn::make('afternoon_close')
                    ->label('Pomeriggio Chiusura')
                    ->time('H:i')
                    ->placeholder('-'),
            ])
            ->filters([])
            ->recordActions([
                EditAction::make(),
            ])
            ->defaultSort('day_of_week', 'asc')
            ->paginated(false);
    }
}