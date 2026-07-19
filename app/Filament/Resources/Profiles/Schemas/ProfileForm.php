<?php

namespace App\Filament\Resources\Profiles\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
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
                Select::make('verification_status')
                    ->label('Verifikationsstatus')
                    ->options([
                        'unverified' => 'Nicht beantragt',
                        'pending'    => 'Ausstehend',
                        'approved'   => 'Verifiziert',
                        'rejected'   => 'Abgelehnt',
                    ]),
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
                Toggle::make('launch_gallery_free')
                    ->label('Private Galerie im Launch kostenlos freigeben')
                    ->helperText('Registrierte Mitglieder erhalten im Launch-Modus kostenlosen Zugang zur privaten Galerie.')
                    ->columnSpanFull(),
                Textarea::make('description')->label('Beschreibung')->columnSpanFull(),
                Textarea::make('verification_rejected_reason')
                    ->label('Ablehnungsgrund')
                    ->columnSpanFull()
                    ->rows(2),
            ]);
    }
}
