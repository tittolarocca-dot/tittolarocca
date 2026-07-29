<?php

namespace App\Filament\Resources\Clubs\Schemas;

use App\Models\Club;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class ClubForm
{
    public static function configure(Schema $schema): Schema
    {
        $cantons = collect(config('cantons'))
            ->mapWithKeys(fn ($c, $code) => [$code => "{$c['name']} ({$code})"])
            ->all();

        return $schema
            ->components([
                TextInput::make('name')->required()->maxLength(120)->columnSpan(2),
                TextInput::make('slug')
                    ->maxLength(140)
                    ->helperText('Leer lassen für automatische Erzeugung aus dem Namen.'),
                Select::make('category')
                    ->label('Kategorie')
                    ->options(array_combine(Club::CATEGORIES, Club::CATEGORIES))
                    ->required(),
                Select::make('canton')
                    ->label('Kanton')
                    ->options($cantons)
                    ->searchable()
                    ->required(),
                TextInput::make('city')->label('Stadt')->required(),
                TextInput::make('postal_code')->label('PLZ')->maxLength(10),
                TextInput::make('address')->label('Adresse')->columnSpan(2),
                TextInput::make('latitude')->label('Breitengrad')->numeric(),
                TextInput::make('longitude')->label('Längengrad')->numeric(),
                TextInput::make('phone')->label('Telefon')->tel(),
                TextInput::make('email')->label('E-Mail')->email(),
                TextInput::make('website_url')->label('Website-URL')->url()->columnSpan(2),
                Textarea::make('description')->label('Beschreibung')->rows(4)->columnSpanFull(),

                Repeater::make('opening_hours')
                    ->label('Öffnungszeiten')
                    ->helperText('Pro Zeile ein Tag. Für 24h denselben Wert bei „von" und „bis" eintragen (z. B. 00:00 / 00:00).')
                    ->schema([
                        Select::make('day')->label('Tag')->options([
                            'mon' => 'Montag', 'tue' => 'Dienstag', 'wed' => 'Mittwoch',
                            'thu' => 'Donnerstag', 'fri' => 'Freitag', 'sat' => 'Samstag', 'sun' => 'Sonntag',
                        ])->required(),
                        TextInput::make('from')->label('von')->placeholder('18:00'),
                        TextInput::make('to')->label('bis')->placeholder('04:00'),
                    ])
                    ->columns(3)
                    ->columnSpanFull()
                    ->default([]),

                Select::make('status')
                    ->options(array_combine(Club::STATUSES, ['Aktiv', 'Ausstehend', 'Inaktiv', 'Abgelehnt']))
                    ->default('active')
                    ->required(),
                Toggle::make('is_premium')->label('Premium'),
                Toggle::make('is_verified')->label('Verifiziert'),
                TextInput::make('rating_average')->label('Bewertung Ø')->numeric()->step('0.1')->minValue(0)->maxValue(5),
                TextInput::make('rating_count')->label('Anzahl Bewertungen')->numeric()->default(0),
            ]);
    }
}
