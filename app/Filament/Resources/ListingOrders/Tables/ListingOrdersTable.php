<?php

namespace App\Filament\Resources\ListingOrders\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class ListingOrdersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('profile.display_name')
                    ->label('Profil')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('user.email')
                    ->label('E-Mail')
                    ->searchable(),
                TextColumn::make('listingPackage.name')
                    ->label('Paket'),
                TextColumn::make('amount_chf')
                    ->label('Betrag')
                    ->money('CHF')
                    ->sortable(),
                TextColumn::make('status')
                    ->badge()
                    ->color(fn($s) => match($s) {
                        'paid'    => 'success',
                        'pending' => 'warning',
                        'failed'  => 'danger',
                        default   => 'gray',
                    }),
                TextColumn::make('paid_at')
                    ->label('Bezahlt am')
                    ->date('d.m.Y H:i')
                    ->sortable(),
                TextColumn::make('created_at')
                    ->label('Erstellt')
                    ->date('d.m.Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->options(['pending' => 'Ausstehend', 'paid' => 'Bezahlt', 'failed' => 'Fehlgeschlagen']),
            ])
            ->toolbarActions([
                BulkActionGroup::make([DeleteBulkAction::make()]),
            ])
            ->defaultSort('created_at', 'desc');
    }
}
