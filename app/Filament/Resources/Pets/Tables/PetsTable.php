<?php

namespace App\Filament\Resources\Pets\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class PetsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                ImageColumn::make('photo')
                    ->circular()
                    ->disk('public')  
                    ->label('Foto'),
                
                TextColumn::make('name')
                    ->searchable()
                    ->sortable()
                    ->label('Nome'),
                
                TextColumn::make('user.name')
                    ->searchable()
                    ->sortable()
                    ->label('Proprietario'),
                
                TextColumn::make('sex')
                    ->badge()
                    ->colors([
                        'primary' => 'maschio',
                        'danger' => 'femmina',
                        'secondary' => 'non_specificato',
                    ])
                    ->label('Sesso'),
                
                TextColumn::make('breed')
                    ->searchable()
                    ->label('Razza'),
                
                TextColumn::make('size')
                    ->badge()
                    ->colors([
                        'success' => 'piccola',
                        'warning' => 'media',
                        'danger' => 'grande',
                    ])
                    ->label('Taglia'),
                
                TextColumn::make('birth_date')
                    ->date('d/m/Y')
                    ->sortable()
                    ->label('Data di nascita'),
                
                TextColumn::make('created_at')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->label('Creato il'),
            ])
            ->filters([
                SelectFilter::make('user_id')
                    ->relationship('user', 'name')
                    ->label('Proprietario'),
                
                SelectFilter::make('sex')
                    ->options([
                        'maschio' => 'Maschio',
                        'femmina' => 'Femmina',
                        'non_specificato' => 'Non specificato',
                    ])
                    ->label('Sesso'),
                
                SelectFilter::make('size')
                    ->options([
                        'piccola' => 'Piccola',
                        'media' => 'Media',
                        'grande' => 'Grande',
                    ])
                    ->label('Taglia'),
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