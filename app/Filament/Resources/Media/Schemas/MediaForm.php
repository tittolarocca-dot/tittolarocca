<?php

namespace App\Filament\Resources\Media\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class MediaForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('status')
                    ->options([
                        'pending'  => 'Ausstehend',
                        'approved' => 'Genehmigt',
                        'rejected' => 'Abgelehnt',
                    ])
                    ->required(),
                TextInput::make('rejection_reason')
                    ->label('Ablehnungsgrund')
                    ->placeholder('Nur bei Ablehnung ausfüllen'),
                Select::make('visibility')
                    ->options(['public' => 'Öffentlich', 'private' => 'Privat'])
                    ->required(),
            ]);
    }
}
