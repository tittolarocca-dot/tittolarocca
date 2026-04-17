<?php

namespace App\Filament\Resources\ListingOrders;

use App\Filament\Resources\ListingOrders\Pages\CreateListingOrder;
use App\Filament\Resources\ListingOrders\Pages\EditListingOrder;
use App\Filament\Resources\ListingOrders\Pages\ListListingOrders;
use App\Filament\Resources\ListingOrders\Schemas\ListingOrderForm;
use App\Filament\Resources\ListingOrders\Tables\ListingOrdersTable;
use App\Models\ListingOrder;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class ListingOrderResource extends Resource
{
    protected static ?string $model = ListingOrder::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedCreditCard;
    protected static ?string $navigationLabel = 'Zahlungen';
    protected static string|\UnitEnum|null $navigationGroup = 'Finanzen';
    protected static ?int $navigationSort = 1;

    public static function form(Schema $schema): Schema
    {
        return ListingOrderForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ListingOrdersTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListListingOrders::route('/'),
            'create' => CreateListingOrder::route('/create'),
            'edit' => EditListingOrder::route('/{record}/edit'),
        ];
    }
}
