<?php

namespace App\Filament\Resources\Reports\Tables;

use App\Models\AdminLog;
use Filament\Actions\Action;
use Filament\Forms\Components\Textarea;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class ReportsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('target_type')->label('Typ')->badge(),
                TextColumn::make('target_id')->label('Ziel-ID'),
                TextColumn::make('reporter.name')->label('Gemeldet von')->searchable()->placeholder('—'),
                TextColumn::make('reason')->label('Grund')->wrap()->limit(120)
                    ->tooltip(fn ($record) => $record->reason),
                TextColumn::make('status')->label('Status')->badge()
                    ->color(fn ($s) => match ($s) {
                        'open'      => 'warning',
                        'resolved'  => 'success',
                        'dismissed' => 'gray',
                        default     => 'gray',
                    }),
                TextColumn::make('admin_note')->label('Notiz')->limit(60)->placeholder('—')->toggleable(),
                TextColumn::make('created_at')->label('Erstellt')->dateTime('d.m.Y H:i')->sortable(),
            ])
            ->filters([
                SelectFilter::make('status')->options([
                    'open'      => 'Offen',
                    'resolved'  => 'Erledigt',
                    'dismissed' => 'Verworfen',
                ]),
            ])
            ->recordActions([
                Action::make('resolve')
                    ->label('Erledigt')
                    ->icon('heroicon-o-check-circle')->color('success')
                    ->visible(fn ($record) => $record->status === 'open')
                    ->schema([
                        Textarea::make('admin_note')->label('Notiz (optional)')->maxLength(500),
                    ])
                    ->action(function (array $data, $record) {
                        $record->update(['status' => 'resolved', 'admin_note' => $data['admin_note'] ?? null]);
                        AdminLog::record('report.resolve', 'report', $record->id, $data['admin_note'] ?? null);
                        Notification::make()->title('Meldung als erledigt markiert')->success()->send();
                    }),
                Action::make('dismiss')
                    ->label('Verwerfen')
                    ->icon('heroicon-o-x-circle')->color('gray')
                    ->visible(fn ($record) => $record->status === 'open')
                    ->requiresConfirmation()
                    ->action(function ($record) {
                        $record->update(['status' => 'dismissed']);
                        AdminLog::record('report.dismiss', 'report', $record->id);
                        Notification::make()->title('Meldung verworfen')->send();
                    }),
            ])
            ->defaultSort('created_at', 'desc');
    }
}
