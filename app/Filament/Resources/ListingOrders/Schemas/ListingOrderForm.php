<?php

namespace App\Filament\Resources\ListingOrders\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class ListingOrderForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('user_id')
                    ->required()
                    ->numeric(),
                TextInput::make('profile_id')
                    ->required()
                    ->numeric(),
                TextInput::make('listing_package_id')
                    ->required()
                    ->numeric(),
                TextInput::make('amount_chf')
                    ->required()
                    ->numeric(),
                TextInput::make('currency')
                    ->required()
                    ->default('CHF'),
                TextInput::make('status')
                    ->required()
                    ->default('pending'),
                TextInput::make('stripe_payment_intent_id'),
                DateTimePicker::make('paid_at'),
                DateTimePicker::make('expires_at'),
            ]);
    }
}
