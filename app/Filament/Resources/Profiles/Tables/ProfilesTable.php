<?php

namespace App\Filament\Resources\Profiles\Tables;

use App\Models\Profile;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\Action;
use Filament\Forms\Components\Textarea;
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
                    ->color(fn ($state) => match ($state) {
                        'active'  => 'success',
                        'pending' => 'warning',
                        'draft'   => 'gray',
                        'expired' => 'danger',
                        default   => 'gray',
                    }),
                TextColumn::make('verification_status')
                    ->label('Verifikation')
                    ->badge()
                    ->color(fn ($state) => match ($state) {
                        'approved'   => 'success',
                        'pending'    => 'warning',
                        'rejected'   => 'danger',
                        'unverified' => 'gray',
                        default      => 'gray',
                    })
                    ->formatStateUsing(fn ($state) => match ($state) {
                        'approved'   => '✓ Verifiziert',
                        'pending'    => '⏳ Ausstehend',
                        'rejected'   => '✗ Abgelehnt',
                        'unverified' => 'Nicht beantragt',
                        default      => $state,
                    }),
                TextColumn::make('listing_expires_at')
                    ->label('Läuft ab')
                    ->date('d.m.Y')
                    ->sortable(),
                TextColumn::make('total_subscribers')
                    ->label('Abos')
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
                SelectFilter::make('verification_status')
                    ->label('Verifikation')
                    ->options([
                        'unverified' => 'Nicht beantragt',
                        'pending'    => 'Ausstehend',
                        'approved'   => 'Verifiziert',
                        'rejected'   => 'Abgelehnt',
                    ]),
            ])
            ->recordActions([
                Action::make('viewPhoto')
                    ->label('Foto ansehen')
                    ->icon('heroicon-o-photo')
                    ->color('info')
                    ->url(fn (Profile $record) => route('admin.verification.photo', $record))
                    ->openUrlInNewTab()
                    ->visible(fn (Profile $record) => (bool) $record->verification_photo),

                Action::make('approve')
                    ->label('Genehmigen')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->requiresConfirmation()
                    ->modalHeading('Verifikation genehmigen')
                    ->modalDescription('Das Profil erhält ein Verifiziert-Badge.')
                    ->action(fn (Profile $record) => $record->update([
                        'verification_status'      => 'approved',
                        'verification_reviewed_at' => now(),
                    ]))
                    ->visible(fn (Profile $record) => $record->verification_status === 'pending'),

                Action::make('reject')
                    ->label('Ablehnen')
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->form([
                        Textarea::make('reason')
                            ->label('Ablehnungsgrund (wird dem Inserenten angezeigt)')
                            ->required()
                            ->rows(3),
                    ])
                    ->action(fn (Profile $record, array $data) => $record->update([
                        'verification_status'          => 'rejected',
                        'verification_rejected_reason' => $data['reason'],
                        'verification_reviewed_at'     => now(),
                    ]))
                    ->visible(fn (Profile $record) => $record->verification_status === 'pending'),

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
