<?php

namespace App\Filament\Resources\Vendedors\Pages;

use App\Filament\Resources\Vendedors\VendedorResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditVendedor extends EditRecord
{
    protected static string $resource = VendedorResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }

    protected function mutateFormDataBeforeFill(array $data): array
    {
        $data['user_name'] = $this->getRecord()->user?->name;
        $data['user_email'] = $this->getRecord()->user?->email;

        return $data;
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        $user = $this->getRecord()->user;
        $user->name = $data['user_name'];
        $user->email = $data['user_email'];

        if (filled($data['user_password'] ?? null)) {
            $user->password = $data['user_password'];
        }

        $user->save();

        unset($data['user_name'], $data['user_email'], $data['user_password'], $data['user_password_confirmation']);

        return $data;
    }
}
