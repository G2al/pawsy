<?php

namespace App\Filament\Resources\Exceptions\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class ExceptionsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('date')
                    ->label('Data')
                    ->date('d/m/Y')
                    ->sortable()
                    ->weight('bold'),

                TextColumn::make('reason')
                    ->label('Motivo')
                    ->searchable()
                    ->placeholder('-')
                    ->limit(50),

                IconColumn::make('is_closed')
                    ->label('Stato')
                    ->boolean()
                    ->trueIcon('heroicon-o-x-circle')
                    ->falseIcon('heroicon-o-clock')
                    ->trueColor('danger')
                    ->falseColor('warning')
                    ->alignCenter(),

                TextColumn::make('morning_open')
                    ->label('Mattina')
                    ->time('H:i')
                    ->placeholder('-')
                    ->formatStateUsing(fn ($record) => 
                        $record->is_closed 
                            ? 'Chiuso' 
                            : ($record->morning_open && $record->morning_close 
                                ? $record->morning_open->format('H:i') . ' - ' . $record->morning_close->format('H:i')
                                : '-')
                    ),

                TextColumn::make('afternoon_open')
                    ->label('Pomeriggio')
                    ->time('H:i')
                    ->placeholder('-')
                    ->formatStateUsing(fn ($record) => 
                        $record->is_closed 
                            ? 'Chiuso' 
                            : ($record->afternoon_open && $record->afternoon_close 
                                ? $record->afternoon_open->format('H:i') . ' - ' . $record->afternoon_close->format('H:i')
                                : '-')
                    ),

                TextColumn::make('created_at')
                    ->label('Creato il')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('is_closed')
                    ->label('Stato')
                    ->options([
                        true => 'Chiuso',
                        false => 'Orari Speciali',
                    ]),
            ])
            ->recordActions([
                EditAction::make(),
                DeleteBulkAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('date', 'desc');
    }
}