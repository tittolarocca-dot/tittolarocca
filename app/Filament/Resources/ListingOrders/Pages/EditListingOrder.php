<?php

namespace App\Filament\Resources\ListingOrders\Pages;

use App\Filament\Resources\ListingOrders\ListingOrderResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditListingOrder extends EditRecord
{
    protected static string $resource = ListingOrderResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
