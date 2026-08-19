<?php

namespace App\Filament\Resources\Users\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class UserForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->required(),
                TextInput::make('email')
                    ->label('E-Mail-Adresse')
                    ->email()
                    ->required(),

                // Klarer Schalter statt Datumsfeld – setzt/entfernt email_verified_at.
                Toggle::make('email_verified_at')
                    ->label('E-Mail bestätigt')
                    ->helperText('An = Konto gilt als bestätigt (keine Bestätigungsmail nötig).')
                    ->default(true)
                    ->formatStateUsing(fn ($state) => (bool) $state)
                    ->dehydrateStateUsing(fn ($state) => $state ? now() : null),

                TextInput::make('password')
                    ->password()
                    ->revealable()
                    ->required(fn (string $operation) => $operation === 'create')
                    ->dehydrated(fn ($state) => filled($state))
                    ->helperText('Beim Bearbeiten leer lassen, um das Passwort nicht zu ändern.'),

                Select::make('role')
                    ->label('Rolle')
                    ->options([
                        'member'   => 'Mitglied',
                        'inserent' => 'Inserent:in',
                        'admin'    => 'Admin',
                    ])
                    ->required()
                    ->default('member')
                    ->native(false),

                Select::make('status')
                    ->label('Status')
                    ->options([
                        'active'  => 'Aktiv',
                        'blocked' => 'Gesperrt',
                    ])
                    ->required()
                    ->default('active')
                    ->native(false),

                DateTimePicker::make('blocked_at')
                    ->label('Gesperrt am'),

                // Abrechnungsfelder (optional, i. d. R. leer lassen)
                TextInput::make('stripe_id'),
                TextInput::make('pm_type'),
                TextInput::make('pm_last_four'),
                DateTimePicker::make('trial_ends_at'),
            ]);
    }
}
