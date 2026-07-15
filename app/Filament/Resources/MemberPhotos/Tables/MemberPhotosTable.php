<?php

namespace App\Filament\Resources\MemberPhotos\Tables;

use App\Models\AdminLog;
use App\Notifications\AvatarModeratedNotification;
use Filament\Actions\Action;
use Filament\Forms\Components\Textarea;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class MemberPhotosTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                ImageColumn::make('avatar_path')
                    ->label('Foto')
                    ->getStateUsing(fn ($record) => route('mitglied.avatar', $record->id) . '?v=' . ($record->updated_at?->timestamp ?? 1))
                    ->size(64),
                TextColumn::make('name')->label('Mitglied')->searchable()->sortable(),
                TextColumn::make('avatar_status')
                    ->label('Status')->badge()
                    ->color(fn ($s) => match ($s) {
                        'pending'  => 'warning',
                        'approved' => 'success',
                        'rejected' => 'danger',
                        default    => 'gray',
                    }),
                TextColumn::make('avatar_rejection_reason')
                    ->label('Ablehnungsgrund')->limit(40)->placeholder('—')->toggleable(),
                TextColumn::make('avatar_moderated_at')
                    ->label('Moderiert')->dateTime('d.m.Y H:i')->placeholder('—')->sortable(),
            ])
            ->filters([
                SelectFilter::make('avatar_status')->label('Status')->options([
                    'pending'  => 'Ausstehend',
                    'approved' => 'Freigeschaltet',
                    'rejected' => 'Abgelehnt',
                ])->default('pending'),
            ])
            ->recordActions([
                Action::make('approveAvatar')
                    ->label('Freischalten')
                    ->icon('heroicon-o-check-circle')->color('success')
                    ->visible(fn ($record) => $record->avatar_status === 'pending')
                    ->requiresConfirmation()
                    ->action(function ($record) {
                        $record->update([
                            'avatar_status' => 'approved',
                            'avatar_rejection_reason' => null,
                            'avatar_moderated_at' => now(),
                        ]);
                        AdminLog::record('avatar.approve', 'user', $record->id);
                        self::notify($record, 'approved');
                        Notification::make()->title('Profilfoto freigeschaltet')->success()->send();
                    }),

                Action::make('rejectAvatar')
                    ->label('Ablehnen')
                    ->icon('heroicon-o-x-circle')->color('danger')
                    ->visible(fn ($record) => in_array($record->avatar_status, ['pending', 'approved'], true))
                    ->schema([
                        Textarea::make('reason')->label('Ablehnungsgrund (intern)')->required()->maxLength(500),
                    ])
                    ->action(function (array $data, $record) {
                        $record->update([
                            'avatar_status' => 'rejected',
                            'avatar_rejection_reason' => $data['reason'],
                            'avatar_moderated_at' => now(),
                        ]);
                        AdminLog::record('avatar.reject', 'user', $record->id, $data['reason']);
                        self::notify($record, 'rejected');
                        Notification::make()->title('Profilfoto abgelehnt')->danger()->send();
                    }),
            ])
            ->defaultSort('avatar_moderated_at', 'asc');
    }

    private static function notify($user, string $decision): void
    {
        try {
            $user->notify(new AvatarModeratedNotification($decision));
        } catch (\Throwable $e) {
            // Zustellung nicht kritisch für die Moderationsaktion
        }
    }
}
