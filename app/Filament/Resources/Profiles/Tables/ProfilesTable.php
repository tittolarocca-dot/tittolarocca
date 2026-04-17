<?php

namespace App\Filament\Resources\Profiles\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\Action;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class ProfilesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('display_name')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),
                TextColumn::make('user.email')
                    ->label('E-Mail')
                    ->searchable(),
                TextColumn::make('city.name')
                    ->label('Stadt')
                    ->sortable(),
                TextColumn::make('category.name')
                    ->label('Kategorie'),
                TextColumn::make('status')
                    ->badge()
                    ->color(fn($state) => match($state) {
                        'active'  => 'success',
                        'pending' => 'warning',
                        'draft'   => 'gray',
                        'expired' => 'danger',
                        default   => 'gray',
                    }),
                TextColumn::make('listing_expires_at')
                    ->label('Läuft ab')
                    ->date('d.m.Y')
                    ->sortable(),
                TextColumn::make('total_subscribers')
                    ->label('Abos')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('total_views')
                    ->label('Aufrufe')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('created_at')
                    ->label('Erstellt')
                    ->date('d.m.Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->options([
                        'draft'   => 'Entwurf',
                        'pending' => 'Ausstehend',
                        'active'  => 'Aktiv',
                        'expired' => 'Abgelaufen',
                        'blocked' => 'Gesperrt',
                    ]),
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
    }
}
