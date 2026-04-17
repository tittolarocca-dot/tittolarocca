<?php

namespace App\Filament\Resources\ListingOrders\Pages;

use App\Filament\Resources\ListingOrders\ListingOrderResource;
use Filament\Resources\Pages\CreateRecord;

class CreateListingOrder extends CreateRecord
{
    protected static string $resource = ListingOrderResource::class;
}
