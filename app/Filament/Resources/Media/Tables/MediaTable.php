<?php

namespace App\Filament\Resources\Media\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class MediaTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('profile.display_name')
                    ->label('Profil')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('type')
                    ->badge()
                    ->color(fn($s) => $s === 'image' ? 'info' : 'warning'),
                TextColumn::make('visibility')
                    ->badge()
                    ->color(fn($s) => $s === 'public' ? 'success' : 'danger'),
                TextColumn::make('status')
                    ->badge()
                    ->color(fn($s) => match($s) {
                        'pending'  => 'warning',
                        'approved' => 'success',
                        'rejected' => 'danger',
                        default    => 'gray',
                    }),
                TextColumn::make('rejection_reason')
                    ->label('Ablehnungsgrund')
                    ->limit(30)
                    ->toggleable(),
                TextColumn::make('created_at')
                    ->label('Hochgeladen')
                    ->date('d.m.Y H:i')
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->options([
                        'pending'  => 'Ausstehend',
                        'approved' => 'Genehmigt',
                        'rejected' => 'Abgelehnt',
                    ]),
                SelectFilter::make('visibility')
                    ->options([
                        'public'  => 'Öffentlich',
                        'private' => 'Privat',
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
