<?php

namespace App\Filament\Resources\Profiles\Tables;

use App\Models\CreditTransaction;
use App\Models\Profile;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\Action;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

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
                    ->label('Foto')
                    ->badge()
                    ->color(fn ($state) => match ($state) {
                        'approved'   => 'success',
                        'pending'    => 'warning',
                        'rejected'   => 'danger',
                        'unverified' => 'gray',
                        default      => 'gray',
                    })
                    ->formatStateUsing(fn ($state) => match ($state) {
                        'approved'   => '✓ Foto bestätigt',
                        'pending'    => '⏳ Ausstehend',
                        'rejected'   => '✗ Abgelehnt',
                        'unverified' => 'Nicht beantragt',
                        default      => $state,
                    }),
                TextColumn::make('identity_verification_status')
                    ->label('Identität (Veriff)')
                    ->badge()
                    ->placeholder('Nicht beantragt')
                    ->color(fn ($state) => match ($state) {
                        'approved' => 'success',
                        'pending'  => 'warning',
                        'rejected' => 'danger',
                        default    => 'gray',
                    })
                    ->formatStateUsing(fn ($state) => match ($state) {
                        'approved' => '✓ ID & Alter',
                        'pending'  => '⏳ Ausstehend',
                        'rejected' => '✗ Abgelehnt',
                        default    => 'Nicht beantragt',
                    }),
                TextColumn::make('listing_expires_at')
                    ->label('Läuft ab')
                    ->date('d.m.Y')
                    ->sortable()
                    ->color(fn (Profile $record) =>
                        $record->listing_expires_at && $record->listing_expires_at->isPast()
                            ? 'danger' : null
                    )
                    ->description(fn (Profile $record) =>
                        $record->listing_expires_at && $record->listing_expires_at->isPast()
                            ? '⚠ Inserat abgelaufen' : null
                    ),
                TextColumn::make('total_subscribers')
                    ->label('Abos')
                    ->numeric()
                    ->sortable()
                    ->toggleable(),
                IconColumn::make('has_private_media')
                    ->label('Privat')
                    ->boolean()
                    ->getStateUsing(fn (Profile $record) => $record->privateMedia()->exists()),
                IconColumn::make('launch_gallery_free')
                    ->label('Launch-Galerie')
                    ->boolean(),
                TextColumn::make('push_count')
                    ->label('Pushs')
                    ->badge()
                    ->color('info')
                    ->getStateUsing(fn (Profile $record) => CreditTransaction::where('profile_id', $record->id)
                        ->where('type', 'profile_push')->count())
                    ->toggleable(),
                TextColumn::make('pushed_at')
                    ->label('Letzter Push')
                    ->dateTime('d.m.Y H:i')
                    ->placeholder('—')
                    ->sortable()
                    ->toggleable(),
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
                Filter::make('listing_expired')
                    ->label('Inserat abgelaufen')
                    ->query(fn (Builder $query) => $query->where(
                        fn ($q) => $q->whereNull('listing_expires_at')
                                     ->orWhere('listing_expires_at', '<=', now())
                    )),
            ])
            ->recordActions([
                Action::make('reactivate')
                    ->label('Reaktivieren')
                    ->icon('heroicon-o-arrow-path')
                    ->color('warning')
                    ->form([
                        Select::make('days')
                            ->label('Verlängerung um')
                            ->options([
                                7  => '7 Tage',
                                14 => '14 Tage',
                                30 => '30 Tage',
                                60 => '60 Tage',
                                90 => '90 Tage',
                            ])
                            ->default(30)
                            ->required(),
                    ])
                    ->modalHeading('Inserat reaktivieren')
                    ->modalDescription('Das Ablaufdatum wird ab heute verlängert.')
                    ->action(fn (Profile $record, array $data) => $record->update([
                        'listing_expires_at' => now()->addDays((int) $data['days']),
                        'status'             => 'active',
                    ]))
                    ->visible(fn (Profile $record) =>
                        ! $record->listing_expires_at || $record->listing_expires_at->isPast()
                    ),

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

                // ── Identität & Alter (Veriff) manuell moderieren ──────────
                Action::make('approveIdentity')
                    ->label('ID genehmigen')
                    ->icon('heroicon-o-identification')
                    ->color('success')
                    ->requiresConfirmation()
                    ->modalHeading('Identität & Alter bestätigen')
                    ->modalDescription('Das Profil erhält ein „ID & Alter verifiziert"-Badge.')
                    ->action(fn (Profile $record) => $record->update([
                        'identity_verification_status' => 'approved',
                        'identity_verified_at'         => now(),
                        'age_verified_at'              => now(),
                        'identity_rejected_reason'     => null,
                    ]))
                    ->visible(fn (Profile $record) => $record->identity_verification_status !== 'approved'),

                Action::make('rejectIdentity')
                    ->label('ID ablehnen')
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->form([
                        Textarea::make('reason')
                            ->label('Ablehnungsgrund (intern)')
                            ->required()
                            ->rows(3),
                    ])
                    ->action(fn (Profile $record, array $data) => $record->update([
                        'identity_verification_status' => 'rejected',
                        'identity_rejected_reason'     => $data['reason'],
                    ]))
                    ->visible(fn (Profile $record) => $record->identity_verification_status === 'pending'),

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
