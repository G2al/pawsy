<?php

namespace App\Filament\Resources\Services\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;

class ServiceTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                ImageColumn::make('photo')
                    ->disk('public')
                    ->circular()
                    ->label('Foto'),

                TextColumn::make('name')
                    ->searchable()
                    ->sortable()
                    ->label('Nome Servizio'),

                TextColumn::make('duration')
                    ->suffix(' min')
                    ->sortable()
                    ->label('Durata'),

                TextColumn::make('price')
                    ->money('EUR')
                    ->sortable()
                    ->label('Prezzo'),

                ToggleColumn::make('is_active')
                    ->label('Attivo'),

                TextColumn::make('created_at')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->label('Creato il'),

                TextColumn::make('updated_at')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->label('Aggiornato il'),
            ])
            ->filters([
                TernaryFilter::make('is_active')
                    ->label('Stato')
                    ->placeholder('Tutti i servizi')
                    ->trueLabel('Solo attivi')
                    ->falseLabel('Solo disattivati'),
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}