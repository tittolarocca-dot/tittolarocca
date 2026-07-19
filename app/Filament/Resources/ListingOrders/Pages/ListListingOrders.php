<?php

namespace App\Filament\Resources\ListingOrders\Pages;

use App\Filament\Resources\ListingOrders\ListingOrderResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListListingOrders extends ListRecords
{
    protected static string $resource = ListingOrderResource::class;

    public function getSubheading(): ?string
    {
        return config('features.launch_mode')
            ? 'Im Launch-Modus sind echte Zahlungen deaktiviert.'
            : null;
    }

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
