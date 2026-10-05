<?php

namespace App\Filament\Resources\Vendedors\Pages;

use App\Filament\Resources\Vendedors\VendedorResource;
use App\Models\User;
use Filament\Resources\Pages\CreateRecord;

class CreateVendedor extends CreateRecord
{
    protected static string $resource = VendedorResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $user = User::create([
            'name' => $data['user_name'],
            'email' => $data['user_email'],
            'password' => $data['user_password'],
        ]);
        $user->assignRole('Vendedor');

        unset($data['user_name'], $data['user_email'], $data['user_password'], $data['user_password_confirmation']);

        $data['user_id'] = $user->getKey();

        return $data;
    }
}
