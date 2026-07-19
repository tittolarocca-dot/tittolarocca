<?php

namespace App\Filament\Resources\Users\Pages;

use App\Filament\Actions\CreditAdjustActions;
use App\Filament\Resources\Users\UserResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditUser extends EditRecord
{
    protected static string $resource = UserResource::class;

    public function getSubheading(): ?string
    {
        $balance = $this->record->creditsBalance();
        return "Aktueller Credit-Saldo: {$balance}";
    }

    protected function getHeaderActions(): array
    {
        return [
            CreditAdjustActions::add(),
            CreditAdjustActions::remove(),
            DeleteAction::make(),
        ];
    }
}
