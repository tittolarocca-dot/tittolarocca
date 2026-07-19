<?php

namespace App\Filament\Resources\CreditTransactions\Tables;

use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class CreditTransactionsTable
{
    private const TYPE_LABELS = [
        'initial_launch_bonus'         => 'Launch-Bonus',
        'manual_admin_credit'          => 'Admin-Gutschrift',
        'manual_admin_debit'           => 'Admin-Abzug',
        'profile_push'                 => 'Push',
        'bonus_private_gallery_launch' => 'Galerie-Bonus',
        'refund'                       => 'Rückerstattung',
        'purchase'                     => 'Kauf',
    ];

    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('created_at')
                    ->label('Datum')
                    ->dateTime('d.m.Y H:i')
                    ->sortable(),
                TextColumn::make('user.name')
                    ->label('Benutzer')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('profile.display_name')
                    ->label('Profil')
                    ->placeholder('—')
                    ->toggleable(),
                TextColumn::make('amount')
                    ->label('Betrag')
                    ->badge()
                    ->color(fn ($state) => $state >= 0 ? 'success' : 'danger')
                    ->formatStateUsing(fn ($state) => ($state >= 0 ? '+' : '') . $state),
                TextColumn::make('type')
                    ->label('Typ')
                    ->badge()
                    ->formatStateUsing(fn ($state) => self::TYPE_LABELS[$state] ?? $state),
                TextColumn::make('description')
                    ->label('Beschreibung')
                    ->wrap()
                    ->toggleable(),
                TextColumn::make('admin.name')
                    ->label('Admin')
                    ->placeholder('—')
                    ->toggleable(),
                TextColumn::make('reference')
                    ->label('Referenz')
                    ->placeholder('—')
                    ->getStateUsing(fn ($record) => $record->reference_type
                        ? $record->reference_type . '#' . $record->reference_id
                        : null)
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('type')
                    ->label('Typ')
                    ->options(self::TYPE_LABELS),
            ])
            ->defaultSort('created_at', 'desc');
    }
}
