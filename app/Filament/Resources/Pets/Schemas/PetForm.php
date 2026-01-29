<?php

namespace App\Filament\Resources\Pets\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Schema;

class PetForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('user_id')
                    ->relationship('user', 'name')
                    ->searchable()
                    ->preload()
                    ->required()
                    ->label('Proprietario'),
                
                TextInput::make('name')
                    ->required()
                    ->maxLength(255)
                    ->label('Nome'),
                
                Select::make('sex')
                    ->options([
                        'maschio' => 'Maschio',
                        'femmina' => 'Femmina',
                        'non_specificato' => 'Non specificato'
                    ])
                    ->label('Sesso'),
                
                TextInput::make('breed')
                    ->maxLength(255)
                    ->label('Razza'),
                
                Select::make('size')
                    ->options([
                        'piccola' => 'Piccola',
                        'media' => 'Media',
                        'grande' => 'Grande'
                    ])
                    ->label('Taglia'),
                
                DatePicker::make('birth_date')
                    ->label('Data di nascita')
                    ->maxDate(now()),
                
                FileUpload::make('photo')
                    ->image()
                    ->directory('pets')
                    ->disk('public')                    
                    ->visibility('public') 
                    ->label('Foto'),
                
                Textarea::make('notes')
                    ->rows(3)
                    ->label('Note')
                    ->helperText('Allergie, comportamento, paure, ecc.')
                    ->columnSpanFull(),
            ]);
    }
}