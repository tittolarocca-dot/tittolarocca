<?php

namespace App\Filament\Resources\CreditBalances;

use App\Filament\Resources\CreditBalances\Pages\ListCreditBalances;
use App\Filament\Resources\CreditBalances\Tables\CreditBalancesTable;
use App\Models\CreditBalance;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;

class CreditBalanceResource extends Resource
{
    protected static ?string $model = CreditBalance::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-wallet';
    protected static ?string $navigationLabel = 'Credits';
    protected static string|\UnitEnum|null $navigationGroup = 'Finanzen';
    protected static ?int $navigationSort = 2;

    public static function form(Schema $schema): Schema
    {
        return $schema->components([]);
    }

    public static function table(Table $table): Table
    {
        return CreditBalancesTable::configure($table);
    }

    public static function canCreate(): bool
    {
        return false;
    }

    public static function getPages(): array
    {
        return [
            'index' => ListCreditBalances::route('/'),
        ];
    }
}
