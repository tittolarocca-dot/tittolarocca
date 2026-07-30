<?php

namespace App\Filament\Resources\ClubReports\Tables;

use App\Models\ClubReport;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class ClubReportsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('created_at')->label('Datum')->dateTime('d.m.Y H:i')->sortable(),
                TextColumn::make('club.name')->label('Club')->searchable()->sortable(),
                TextColumn::make('type')->label('Art')->badge()
                    ->formatStateUsing(fn ($s) => $s === 'claim' ? 'Beanspruchen' : 'Änderung')
                    ->color(fn ($s) => $s === 'claim' ? 'warning' : 'info'),
                TextColumn::make('message')->label('Nachricht')->wrap()->limit(120),
                TextColumn::make('email')->label('E-Mail')->placeholder('—')->toggleable(),
                TextColumn::make('status')->badge()->color(fn ($s) => $s === 'handled' ? 'success' : 'gray'),
            ])
            ->filters([
                SelectFilter::make('status')->options(['open' => 'Offen', 'handled' => 'Erledigt']),
                SelectFilter::make('type')->options(['report' => 'Änderung', 'claim' => 'Beanspruchen']),
            ])
            ->recordActions([
                Action::make('toggleHandled')
                    ->label(fn (ClubReport $r) => $r->status === 'handled' ? 'Als offen' : 'Erledigt')
                    ->icon('heroicon-o-check-circle')
                    ->color(fn (ClubReport $r) => $r->status === 'handled' ? 'gray' : 'success')
                    ->action(fn (ClubReport $r) => $r->update([
                        'status' => $r->status === 'handled' ? 'open' : 'handled',
                    ])),
            ])
            ->toolbarActions([
                BulkActionGroup::make([DeleteBulkAction::make()]),
            ])
            ->defaultSort('created_at', 'desc');
    }
}
