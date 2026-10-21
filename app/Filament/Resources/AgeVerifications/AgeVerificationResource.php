<?php

namespace App\Filament\Resources\AgeVerifications;

use App\Filament\Resources\AgeVerifications\Pages\ListAgeVerifications;
use App\Filament\Resources\AgeVerifications\Tables\AgeVerificationsTable;
use App\Models\AgeVerification;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;

class AgeVerificationResource extends Resource
{
    protected static ?string $model = AgeVerification::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-identification';
    protected static ?string $navigationLabel = '18+ Verifizierungen';
    protected static ?string $modelLabel = '18+ Verifizierung';
    protected static ?string $pluralModelLabel = '18+ Verifizierungen';
    protected static string|\UnitEnum|null $navigationGroup = 'Moderation';
    protected static ?int $navigationSort = 2;

    public static function form(Schema $schema): Schema
    {
        return $schema->components([]);
    }

    public static function table(Table $table): Table
    {
        return AgeVerificationsTable::configure($table);
    }

    public static function canCreate(): bool
    {
        return false;
    }

    public static function getNavigationBadge(): ?string
    {
        $open = AgeVerification::where('status', AgeVerification::STATUS_PENDING)->count();
        return $open > 0 ? (string) $open : null;
    }

    public static function getPages(): array
    {
        return [
            'index' => ListAgeVerifications::route('/'),
        ];
    }
}
