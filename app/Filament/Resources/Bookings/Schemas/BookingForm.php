<?php

namespace App\Filament\Resources\Bookings\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TimePicker;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class BookingForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(1)
            ->components([
                Section::make('Dati Prenotazione')
                    ->schema([
                        Select::make('user_id')
                            ->relationship('user', 'name')
                            ->searchable()
                            ->preload()
                            ->required()
                            ->label('Cliente')
                            ->columnSpanFull(),

                        Select::make('pet_id')
                            ->relationship('pet', 'name')
                            ->searchable()
                            ->preload()
                            ->required()
                            ->label('Animale')
                            ->columnSpanFull(),

                        Select::make('service_id')
                            ->relationship('service', 'name')
                            ->searchable()
                            ->preload()
                            ->required()
                            ->label('Servizio')
                            ->reactive()
                            ->afterStateUpdated(function ($state, callable $set) {
                                if ($state) {
                                    $service = \App\Models\Service::find($state);
                                    if ($service) {
                                        $set('duration', $service->duration);
                                        $set('price', $service->price);
                                    }
                                }
                            })
                            ->columnSpanFull(),

                        DatePicker::make('booking_date')
                            ->required()
                            ->native(false)
                            ->displayFormat('d/m/Y')
                            ->minDate(now())
                            ->label('Data Prenotazione')
                            ->columnSpanFull(),

                        TimePicker::make('time_slot')
                            ->required()
                            ->seconds(false)
                            ->label('Orario')
                            ->columnSpanFull(),

                        TextInput::make('duration')
                            ->required()
                            ->numeric()
                            ->suffix('min')
                            ->label('Durata')
                            ->helperText('Compilato automaticamente dal servizio')
                            ->columnSpanFull(),

                        TextInput::make('price')
                            ->required()
                            ->numeric()
                            ->prefix('€')
                            ->step(0.01)
                            ->label('Prezzo')
                            ->helperText('Compilato automaticamente dal servizio')
                            ->columnSpanFull(),
                    ]),

                Section::make('Stato')
                    ->schema([
                        Select::make('status')
                            ->options([
                                'pending' => 'In Attesa',
                                'confirmed' => 'Confermata',
                                'in_progress' => 'In Corso',
                                'completed' => 'Completata',
                                'cancelled' => 'Cancellata',
                                'no_show' => 'Non Presentato',
                            ])
                            ->default('pending')
                            ->required()
                            ->label('Stato Prenotazione')
                            ->columnSpanFull(),

                        Select::make('payment_status')
                            ->options([
                                'unpaid' => 'Non Pagato',
                                'paid' => 'Pagato',
                            ])
                            ->default('unpaid')
                            ->required()
                            ->label('Stato Pagamento')
                            ->columnSpanFull(),
                    ]),

                Section::make('Note')
                    ->schema([
                        Textarea::make('notes')
                            ->rows(3)
                            ->label('Note Cliente')
                            ->placeholder('Note o richieste del cliente...')
                            ->columnSpanFull(),

                        Textarea::make('admin_notes')
                            ->rows(3)
                            ->label('Note Private Admin')
                            ->placeholder('Note interne non visibili al cliente...')
                            ->columnSpanFull(),
                    ]),
            ]);
    }
}