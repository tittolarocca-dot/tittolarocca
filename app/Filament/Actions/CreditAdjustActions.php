<?php

namespace App\Filament\Actions;

use App\Exceptions\InsufficientCreditsException;
use App\Models\CreditBalance;
use App\Models\User;
use App\Services\CreditService;
use Filament\Actions\Action;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Notifications\Notification;

/**
 * Wiederverwendbare Admin-Aktionen zum manuellen Gutschreiben / Abziehen von Credits.
 * Funktioniert sowohl auf User-Records als auch auf CreditBalance-Records.
 */
class CreditAdjustActions
{
    private static function resolveUser($record): ?User
    {
        if ($record instanceof User) {
            return $record;
        }
        if ($record instanceof CreditBalance) {
            return $record->user;
        }
        return null;
    }

    public static function add(): Action
    {
        return Action::make('addCredits')
            ->label('Credits +')
            ->icon('heroicon-o-plus-circle')
            ->color('success')
            ->modalHeading('Credits gutschreiben')
            ->form([
                TextInput::make('amount')
                    ->label('Anzahl Credits')
                    ->numeric()
                    ->minValue(1)
                    ->required(),
                Textarea::make('note')
                    ->label('Grund / Notiz')
                    ->required()
                    ->rows(2),
            ])
            ->action(function ($record, array $data) {
                $user = self::resolveUser($record);
                if (! $user) {
                    return;
                }
                app(CreditService::class)->grant($user, (int) $data['amount'], 'manual_admin_credit', [
                    'description' => $data['note'],
                    'admin_id'    => auth()->id(),
                ]);
                Notification::make()
                    ->title("+{$data['amount']} Credits gutgeschrieben")
                    ->success()
                    ->send();
            });
    }

    public static function remove(): Action
    {
        return Action::make('removeCredits')
            ->label('Credits −')
            ->icon('heroicon-o-minus-circle')
            ->color('danger')
            ->modalHeading('Credits abziehen')
            ->form([
                TextInput::make('amount')
                    ->label('Anzahl Credits')
                    ->numeric()
                    ->minValue(1)
                    ->required(),
                Textarea::make('note')
                    ->label('Grund / Notiz')
                    ->required()
                    ->rows(2),
                Toggle::make('force')
                    ->label('Negativen Saldo erlauben (bewusst erzwingen)')
                    ->default(false),
            ])
            ->action(function ($record, array $data) {
                $user = self::resolveUser($record);
                if (! $user) {
                    return;
                }
                try {
                    app(CreditService::class)->spend($user, (int) $data['amount'], 'manual_admin_debit', null, [
                        'description' => $data['note'],
                        'admin_id'    => auth()->id(),
                        'force'       => (bool) ($data['force'] ?? false),
                    ]);
                    Notification::make()
                        ->title("−{$data['amount']} Credits abgezogen")
                        ->success()
                        ->send();
                } catch (InsufficientCreditsException $e) {
                    Notification::make()
                        ->title('Nicht genügend Credits')
                        ->body('Aktiviere „Negativen Saldo erlauben", um es bewusst zu erzwingen.')
                        ->danger()
                        ->send();
                }
            });
    }
}
