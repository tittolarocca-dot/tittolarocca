<?php

namespace App\Filament\Resources\CreditBalances\Tables;

use App\Filament\Actions\CreditAdjustActions;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class CreditBalancesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('user.name')
                    ->label('Benutzer')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('user.email')
                    ->label('E-Mail')
                    ->searchable()
                    ->toggleable(),
                TextColumn::make('user.role')
                    ->label('Rolle')
                    ->badge()
                    ->color(fn ($s) => match ($s) {
                        'admin'     => 'danger',
                        'moderator' => 'warning',
                        'inserent'  => 'info',
                        default     => 'gray',
                    }),
                TextColumn::make('balance')
                    ->label('Saldo')
                    ->badge()
                    ->color('success')
                    ->sortable(),
                TextColumn::make('total_granted')->label('Erhalten')->numeric()->sortable(),
                TextColumn::make('total_spent')->label('Verbraucht')->numeric()->sortable(),
                TextColumn::make('total_bonus')->label('Bonus')->numeric()->toggleable(),
                TextColumn::make('total_purchased')->label('Gekauft')->numeric()->toggleable(),
                TextColumn::make('updated_at')
                    ->label('Letzte Änderung')
                    ->dateTime('d.m.Y H:i')
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('user_role')
                    ->label('Rolle')
                    ->options([
                        'member'    => 'Mitglied',
                        'inserent'  => 'Inserent',
                        'moderator' => 'Moderator',
                        'admin'     => 'Admin',
                    ])
                    ->query(fn (Builder $query, array $data) => $data['value']
                        ? $query->whereHas('user', fn ($q) => $q->where('role', $data['value']))
                        : $query),
            ])
            ->recordActions([
                CreditAdjustActions::add(),
                CreditAdjustActions::remove(),
            ])
            ->defaultSort('balance', 'desc');
    }
}
