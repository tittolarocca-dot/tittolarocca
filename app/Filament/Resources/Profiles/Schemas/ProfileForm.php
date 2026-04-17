<?php

namespace App\Filament\Resources\Profiles\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Schema;

class ProfileForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('display_name')->required()->columnSpan(2),
                Select::make('status')
                    ->options([
                        'draft'   => 'Entwurf',
                        'pending' => 'Ausstehend',
                        'active'  => 'Aktiv',
                        'expired' => 'Abgelaufen',
                        'blocked' => 'Gesperrt',
                    ])
                    ->required(),
                DateTimePicker::make('listing_expires_at')->label('Läuft ab'),
                Select::make('city_id')
                    ->label('Stadt')
                    ->relationship('city', 'name')
                    ->required(),
                Select::make('category_id')
                    ->label('Kategorie')
                    ->relationship('category', 'name')
                    ->required(),
                TextInput::make('age')->numeric(),
                TextInput::make('subscription_price_chf')->label('Abo-Preis (CHF)')->numeric(),
                Textarea::make('description')->label('Beschreibung')->columnSpanFull(),
            ]);
    }
}
