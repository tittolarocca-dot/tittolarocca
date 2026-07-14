<?php

namespace App\Filament\Resources\Reviews\Tables;

use App\Models\AdminLog;
use App\Notifications\ReplyModeratedNotification;
use App\Notifications\ReviewModeratedNotification;
use Filament\Actions\Action;
use Filament\Forms\Components\Textarea;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class ReviewsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('profile.display_name')
                    ->label('Profil')->searchable()->sortable(),
                TextColumn::make('reviewer.name')
                    ->label('Von')->searchable(),
                TextColumn::make('stars')
                    ->label('Sterne')
                    ->formatStateUsing(fn ($state) => str_repeat('★', (int) $state) . str_repeat('☆', 5 - (int) $state)),
                TextColumn::make('comment')
                    ->label('Kommentar')->wrap()->limit(120)->tooltip(fn ($record) => $record->comment),
                TextColumn::make('status')
                    ->label('Status')->badge()
                    ->color(fn ($s) => match ($s) {
                        'pending'  => 'warning',
                        'approved' => 'success',
                        'rejected' => 'danger',
                        default    => 'gray',
                    }),
                TextColumn::make('inserent_reply')
                    ->label('Antwort')->wrap()->limit(80)->placeholder('—')
                    ->tooltip(fn ($record) => $record->inserent_reply),
                TextColumn::make('reply_status')
                    ->label('Antwort-Status')->badge()->placeholder('—')
                    ->color(fn ($s) => match ($s) {
                        'pending'  => 'warning',
                        'approved' => 'success',
                        'rejected' => 'danger',
                        default    => 'gray',
                    }),
                TextColumn::make('created_at')
                    ->label('Erstellt')->dateTime('d.m.Y H:i')->sortable(),
            ])
            ->filters([
                SelectFilter::make('status')->label('Status')->options([
                    'pending'  => 'Ausstehend',
                    'approved' => 'Freigeschaltet',
                    'rejected' => 'Abgelehnt',
                ]),
                SelectFilter::make('reply_status')->label('Antwort-Status')->options([
                    'pending'  => 'Ausstehend',
                    'approved' => 'Freigeschaltet',
                    'rejected' => 'Abgelehnt',
                ]),
            ])
            ->recordActions([
                // ── Bewertung ──────────────────────────────────────────
                Action::make('approve')
                    ->label('Freischalten')
                    ->icon('heroicon-o-check-circle')->color('success')
                    ->visible(fn ($record) => $record->status === 'pending')
                    ->requiresConfirmation()
                    ->action(function ($record) {
                        $record->update([
                            'status' => 'approved', 'rejection_reason' => null, 'moderated_at' => now(),
                        ]);
                        AdminLog::record('review.approve', 'review', $record->id);
                        self::notify($record->reviewer, new ReviewModeratedNotification($record, 'approved'));
                        Notification::make()->title('Bewertung freigeschaltet')->success()->send();
                    }),

                Action::make('reject')
                    ->label('Ablehnen')
                    ->icon('heroicon-o-x-circle')->color('danger')
                    ->visible(fn ($record) => in_array($record->status, ['pending', 'approved'], true))
                    ->schema([
                        Textarea::make('rejection_reason')
                            ->label('Ablehnungsgrund (intern)')->required()->maxLength(500),
                    ])
                    ->action(function (array $data, $record) {
                        $record->update([
                            'status' => 'rejected',
                            'rejection_reason' => $data['rejection_reason'],
                            'moderated_at' => now(),
                        ]);
                        AdminLog::record('review.reject', 'review', $record->id, $data['rejection_reason']);
                        self::notify($record->reviewer, new ReviewModeratedNotification($record, 'rejected'));
                        Notification::make()->title('Bewertung abgelehnt')->danger()->send();
                    }),

                // ── Inserenten-Antwort ─────────────────────────────────
                Action::make('approveReply')
                    ->label('Antwort freischalten')
                    ->icon('heroicon-o-chat-bubble-left-right')->color('success')
                    ->visible(fn ($record) => filled($record->inserent_reply) && $record->reply_status === 'pending')
                    ->requiresConfirmation()
                    ->action(function ($record) {
                        $record->update([
                            'reply_status' => 'approved',
                            'reply_rejection_reason' => null,
                            'reply_moderated_at' => now(),
                        ]);
                        AdminLog::record('reply.approve', 'review', $record->id);
                        self::notify($record->profile->user, new ReplyModeratedNotification($record, 'approved'));
                        Notification::make()->title('Antwort freigeschaltet')->success()->send();
                    }),

                Action::make('rejectReply')
                    ->label('Antwort ablehnen')
                    ->icon('heroicon-o-x-circle')->color('danger')
                    ->visible(fn ($record) => filled($record->inserent_reply) && in_array($record->reply_status, ['pending', 'approved'], true))
                    ->schema([
                        Textarea::make('reply_rejection_reason')
                            ->label('Ablehnungsgrund (intern)')->required()->maxLength(500),
                    ])
                    ->action(function (array $data, $record) {
                        $record->update([
                            'reply_status' => 'rejected',
                            'reply_rejection_reason' => $data['reply_rejection_reason'],
                            'reply_moderated_at' => now(),
                        ]);
                        AdminLog::record('reply.reject', 'review', $record->id, $data['reply_rejection_reason']);
                        self::notify($record->profile->user, new ReplyModeratedNotification($record, 'rejected'));
                        Notification::make()->title('Antwort abgelehnt')->danger()->send();
                    }),
            ])
            ->defaultSort('created_at', 'desc');
    }

    private static function notify(?object $notifiable, $notification): void
    {
        if (! $notifiable) {
            return;
        }
        try {
            $notifiable->notify($notification);
        } catch (\Throwable $e) {
            // Zustellung nicht kritisch für die Moderationsaktion
        }
    }
}
