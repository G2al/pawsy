<?php

namespace App\Filament\Resources\Bookings\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class BookingsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('booking_date')
                    ->label('Data')
                    ->date('d/m/Y')
                    ->sortable()
                    ->weight('bold'),

                TextColumn::make('time_slot')
                    ->label('Orario')
                    ->time('H:i')
                    ->sortable(),

                TextColumn::make('user.name')
                    ->label('Cliente')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('pet.name')
                    ->label('Animale')
                    ->searchable(),

                TextColumn::make('service.name')
                    ->label('Servizio')
                    ->searchable()
                    ->limit(30),

                TextColumn::make('duration')
                    ->label('Durata')
                    ->suffix(' min')
                    ->alignCenter(),

                TextColumn::make('price')
                    ->label('Prezzo')
                    ->money('EUR')
                    ->sortable(),

                TextColumn::make('status')
                    ->label('Stato')
                    ->badge()
                    ->formatStateUsing(fn ($state) => match($state) {
                        'pending' => 'In Attesa',
                        'confirmed' => 'Confermata',
                        'in_progress' => 'In Corso',
                        'completed' => 'Completata',
                        'cancelled' => 'Cancellata',
                        'no_show' => 'Non Presentato',
                        default => $state,
                    })
                    ->colors([
                        'warning' => 'pending',
                        'success' => 'confirmed',
                        'info' => 'in_progress',
                        'success' => 'completed',
                        'danger' => 'cancelled',
                        'danger' => 'no_show',
                    ]),

                TextColumn::make('payment_status')
                    ->label('Pagamento')
                    ->badge()
                    ->formatStateUsing(fn ($state) => match($state) {
                        'unpaid' => 'Non Pagato',
                        'paid' => 'Pagato',
                        default => $state,
                    })
                    ->colors([
                        'danger' => 'unpaid',
                        'success' => 'paid',
                    ]),

                TextColumn::make('created_at')
                    ->label('Creato il')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->label('Stato')
                    ->options([
                        'pending' => 'In Attesa',
                        'confirmed' => 'Confermata',
                        'in_progress' => 'In Corso',
                        'completed' => 'Completata',
                        'cancelled' => 'Cancellata',
                        'no_show' => 'Non Presentato',
                    ]),

                SelectFilter::make('payment_status')
                    ->label('Pagamento')
                    ->options([
                        'unpaid' => 'Non Pagato',
                        'paid' => 'Pagato',
                    ]),

                SelectFilter::make('service_id')
                    ->label('Servizio')
                    ->relationship('service', 'name'),
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
            ->defaultSort('booking_date', 'desc');
    }
}