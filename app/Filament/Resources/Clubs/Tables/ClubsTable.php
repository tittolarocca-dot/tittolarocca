<?php

namespace App\Filament\Resources\Clubs\Tables;

use App\Models\Club;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class ClubsTable
{
    public static function configure(Table $table): Table
    {
        $cantons = collect(config('cantons'))->mapWithKeys(fn ($c, $code) => [$code => $c['name']])->all();

        return $table
            ->columns([
                TextColumn::make('name')->searchable()->sortable()->weight('bold'),
                TextColumn::make('category')->label('Kategorie')->badge(),
                TextColumn::make('city')->label('Stadt')->searchable(),
                TextColumn::make('canton')->label('Kanton')
                    ->formatStateUsing(fn ($state) => config("cantons.{$state}.name", $state))
                    ->sortable(),
                IconColumn::make('is_premium')->label('Premium')->boolean(),
                IconColumn::make('is_verified')->label('Verifiziert')->boolean(),
                TextColumn::make('status')->badge()->color(fn ($state) => match ($state) {
                    'active'   => 'success',
                    'pending'  => 'warning',
                    'inactive' => 'gray',
                    'rejected' => 'danger',
                    default    => 'gray',
                }),
                TextColumn::make('website_clicks')->label('Klicks')->numeric()->sortable()->toggleable(),
                TextColumn::make('profile_views')->label('Aufrufe')->numeric()->sortable()->toggleable(),
                TextColumn::make('created_at')->label('Erstellt')->date('d.m.Y')->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('status')->options([
                    'active' => 'Aktiv', 'pending' => 'Ausstehend', 'inactive' => 'Inaktiv', 'rejected' => 'Abgelehnt',
                ]),
                SelectFilter::make('category')->options(array_combine(Club::CATEGORIES, Club::CATEGORIES)),
                SelectFilter::make('canton')->label('Kanton')->options($cantons),
            ])
            ->recordActions([EditAction::make()])
            ->toolbarActions([
                BulkActionGroup::make([DeleteBulkAction::make()]),
            ])
            ->defaultSort('created_at', 'desc');
    }
}
