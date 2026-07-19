<?php

namespace App\Filament\Resources\Users\Tables;

use App\Filament\Actions\CreditAdjustActions;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class UsersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')->searchable()->sortable(),
                TextColumn::make('email')->searchable(),
                TextColumn::make('role')
                    ->badge()
                    ->color(fn($s) => match($s) {
                        'admin'     => 'danger',
                        'moderator' => 'warning',
                        'inserent'  => 'info',
                        default     => 'gray',
                    }),
                TextColumn::make('status')
                    ->badge()
                    ->color(fn($s) => $s === 'active' ? 'success' : 'danger'),
                TextColumn::make('creditBalance.balance')
                    ->label('Credits')
                    ->badge()
                    ->color('success')
                    ->placeholder('0'),
                TextColumn::make('creditBalance.total_granted')
                    ->label('Erhalten')
                    ->numeric()
                    ->placeholder('0')
                    ->toggleable(),
                TextColumn::make('creditBalance.total_spent')
                    ->label('Verbraucht')
                    ->numeric()
                    ->placeholder('0')
                    ->toggleable(),
                TextColumn::make('blocked_at')
                    ->label('Gesperrt am')
                    ->date('d.m.Y')
                    ->sortable()
                    ->toggleable(),
                TextColumn::make('created_at')
                    ->label('Registriert')
                    ->date('d.m.Y')
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('role')
                    ->options([
                        'member'    => 'Mitglied',
                        'inserent'  => 'Inserent',
                        'moderator' => 'Moderator',
                        'admin'     => 'Admin',
                    ]),
                SelectFilter::make('status')
                    ->options(['active' => 'Aktiv', 'blocked' => 'Gesperrt']),
            ])
            ->recordActions([
                CreditAdjustActions::add(),
                CreditAdjustActions::remove(),
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([DeleteBulkAction::make()]),
            ])
            ->defaultSort('created_at', 'desc');
    }
}
