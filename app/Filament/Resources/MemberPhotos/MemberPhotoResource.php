<?php

namespace App\Filament\Resources\MemberPhotos;

use App\Filament\Resources\MemberPhotos\Pages\ListMemberPhotos;
use App\Filament\Resources\MemberPhotos\Tables\MemberPhotosTable;
use App\Models\User;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class MemberPhotoResource extends Resource
{
    protected static ?string $model = User::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-user-circle';
    protected static ?string $navigationLabel = 'Mitglieder-Fotos';
    protected static string|\UnitEnum|null $navigationGroup = 'Moderation';
    protected static ?int $navigationSort = 5;

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->whereNotNull('avatar_path');
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->components([]);
    }

    public static function table(Table $table): Table
    {
        return MemberPhotosTable::configure($table);
    }

    public static function canCreate(): bool
    {
        return false;
    }

    public static function getNavigationBadge(): ?string
    {
        $pending = User::where('avatar_status', 'pending')->whereNotNull('avatar_path')->count();
        return $pending > 0 ? (string) $pending : null;
    }

    public static function getPages(): array
    {
        return [
            'index' => ListMemberPhotos::route('/'),
        ];
    }
}
