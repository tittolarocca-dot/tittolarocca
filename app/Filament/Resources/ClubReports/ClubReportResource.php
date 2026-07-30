<?php

namespace App\Filament\Resources\ClubReports;

use App\Filament\Resources\ClubReports\Pages\ListClubReports;
use App\Filament\Resources\ClubReports\Tables\ClubReportsTable;
use App\Models\ClubReport;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;

class ClubReportResource extends Resource
{
    protected static ?string $model = ClubReport::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-flag';
    protected static ?string $navigationLabel = 'Club-Meldungen';
    protected static string|\UnitEnum|null $navigationGroup = 'Clubs';
    protected static ?int $navigationSort = 2;

    public static function form(Schema $schema): Schema
    {
        return $schema->components([]);
    }

    public static function table(Table $table): Table
    {
        return ClubReportsTable::configure($table);
    }

    public static function canCreate(): bool
    {
        return false;
    }

    public static function getNavigationBadge(): ?string
    {
        $open = ClubReport::where('status', 'open')->count();
        return $open > 0 ? (string) $open : null;
    }

    public static function getPages(): array
    {
        return [
            'index' => ListClubReports::route('/'),
        ];
    }
}
