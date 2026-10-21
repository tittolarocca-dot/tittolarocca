<?php

namespace App\Filament\Resources\AgeVerifications\Tables;

use App\Models\AdminLog;
use App\Models\AgeVerification;
use Filament\Actions\Action;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class AgeVerificationsTable
{
    /** Vordefinierte Ablehnungsgründe (auswählbar). */
    private const REASONS = [
        'Dokument nicht lesbar'           => 'Dokument nicht lesbar',
        'Gesicht nicht eindeutig sichtbar'=> 'Gesicht nicht eindeutig sichtbar',
        'Angaben stimmen nicht überein'   => 'Angaben stimmen nicht überein',
        'Video unzureichend'              => 'Video unzureichend',
        'Dokument ungültig'               => 'Dokument ungültig',
    ];

    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('profile.display_name')->label('Profil')->searchable()->weight('bold'),
                TextColumn::make('profile.user_id')->label('User-ID')->toggleable(),
                TextColumn::make('first_name')->label('Vorname')->placeholder('—'),
                TextColumn::make('last_name')->label('Nachname')->placeholder('—'),
                TextColumn::make('birth_date')->label('Geburtsdatum')->date('d.m.Y')->placeholder('—'),
                TextColumn::make('age')->label('Alter')
                    ->getStateUsing(fn (AgeVerification $r) => $r->age)
                    ->badge()
                    ->color(fn ($state) => $state === null ? 'gray' : ($state < 18 ? 'danger' : 'success'))
                    ->formatStateUsing(fn ($state) => $state === null ? '—' : $state . ' J.'),
                TextColumn::make('submitted_at')->label('Eingereicht')->dateTime('d.m.Y H:i')->placeholder('—')->sortable(),
                TextColumn::make('status')->label('Status')->badge()
                    ->color(fn ($state) => match ($state) {
                        'verified' => 'success',
                        'pending'  => 'warning',
                        'rejected' => 'danger',
                        default    => 'gray',
                    })
                    ->formatStateUsing(fn ($state) => match ($state) {
                        'verified'      => '✓ 18+ bestätigt',
                        'pending'       => '⏳ Prüfung läuft',
                        'rejected'      => '✗ Abgelehnt',
                        'not_submitted' => 'Entwurf',
                        default         => $state,
                    }),
                TextColumn::make('reviewer.name')->label('Geprüft von')->placeholder('—')->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('status')->options([
                    'not_submitted' => 'Entwurf',
                    'pending'       => 'Prüfung läuft',
                    'verified'      => '18+ bestätigt',
                    'rejected'      => 'Abgelehnt',
                ])->default('pending'),
            ])
            ->recordActions([
                Action::make('viewDocument')
                    ->label('Ausweis')
                    ->icon('heroicon-o-identification')->color('info')
                    ->url(fn (AgeVerification $r) => route('age-verification.document', $r))
                    ->openUrlInNewTab()
                    ->visible(fn (AgeVerification $r) => $r->hasDocument()),

                Action::make('viewVideo')
                    ->label('Video')
                    ->icon('heroicon-o-video-camera')->color('info')
                    ->url(fn (AgeVerification $r) => route('age-verification.video', $r))
                    ->openUrlInNewTab()
                    ->visible(fn (AgeVerification $r) => $r->hasVideo()),

                Action::make('approve')
                    ->label('Bestätigen')
                    ->icon('heroicon-o-check-circle')->color('success')
                    ->requiresConfirmation()
                    ->modalHeading('18+ Identität bestätigen')
                    ->modalDescription('Das Profil gilt danach als 18+ geprüft.')
                    ->action(function (AgeVerification $record) {
                        $retention = config('features.age_verification_retention_days');
                        $record->update([
                            'status'           => AgeVerification::STATUS_VERIFIED,
                            'rejection_reason' => null,
                            'reviewed_at'      => now(),
                            'reviewed_by'      => auth()->id(),
                            'purge_after'      => ($retention !== null && $retention > 0)
                                ? now()->addDays((int) $retention) : null,
                        ]);
                        AdminLog::record('age_verification.approved', 'age_verification', $record->id);
                        Notification::make()->title('18+ Verifizierung bestätigt')->success()->send();
                    })
                    ->visible(fn (AgeVerification $r) => $r->status === AgeVerification::STATUS_PENDING),

                Action::make('reject')
                    ->label('Ablehnen')
                    ->icon('heroicon-o-x-circle')->color('danger')
                    ->schema([
                        Select::make('reason_code')
                            ->label('Ablehnungsgrund')
                            ->options(self::REASONS)
                            ->native(false)
                            ->placeholder('Grund wählen …'),
                        Textarea::make('reason_custom')
                            ->label('Eigener Grund / Ergänzung (optional)')
                            ->rows(2)
                            ->maxLength(500),
                    ])
                    ->action(function (AgeVerification $record, array $data) {
                        $reason = trim((string) ($data['reason_custom'] ?? '')) ?: ($data['reason_code'] ?? null);
                        if (! $reason) {
                            Notification::make()->title('Bitte einen Ablehnungsgrund angeben')->danger()->send();
                            return;
                        }
                        $record->update([
                            'status'           => AgeVerification::STATUS_REJECTED,
                            'rejection_reason' => $reason,
                            'reviewed_at'      => now(),
                            'reviewed_by'      => auth()->id(),
                            'purge_after'      => null,
                        ]);
                        AdminLog::record('age_verification.rejected', 'age_verification', $record->id, $reason);
                        Notification::make()->title('18+ Verifizierung abgelehnt')->send();
                    })
                    ->visible(fn (AgeVerification $r) => $r->status === AgeVerification::STATUS_PENDING),
            ])
            ->defaultSort('submitted_at', 'desc');
    }
}
