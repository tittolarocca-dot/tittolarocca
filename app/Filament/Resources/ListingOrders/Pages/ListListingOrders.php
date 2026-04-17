<?php

namespace App\Filament\Resources\ListingOrders\Pages;

use App\Filament\Resources\ListingOrders\ListingOrderResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListListingOrders extends ListRecords
{
    protected static string $resource = ListingOrderResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
