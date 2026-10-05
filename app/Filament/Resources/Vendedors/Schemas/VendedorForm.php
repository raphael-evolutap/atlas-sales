<?php

namespace App\Filament\Resources\Vendedors\Schemas;

use App\Models\Vendedor;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Unique;
use Leandrocfe\FilamentPtbrFormFields\PhoneNumber;

class VendedorForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('user_name')
                    ->label('Nome')
                    ->required()
                    ->maxLength(255),
                TextInput::make('user_email')
                    ->label('Email')
                    ->email()
                    ->required()
                    ->maxLength(255)
                    ->rules([
                        fn (?Vendedor $record): Unique => Rule::unique('users', 'email')->ignore($record?->user_id),
                    ]),
                TextInput::make('user_password')
                    ->label('Senha')
                    ->password()
                    ->revealable()
                    ->confirmed()
                    ->required(fn (string $operation): bool => $operation === 'create')
                    ->dehydrated(fn (?string $state): bool => filled($state))
                    ->minLength(8)
                    ->maxLength(255),
                TextInput::make('user_password_confirmation')
                    ->label('Confirmar Senha')
                    ->password()
                    ->revealable()
                    ->required(fn (string $operation): bool => $operation === 'create')
                    ->dehydrated(false),
                PhoneNumber::make('telefone')
                    ->mask('(99) 99999-9999'),
                Toggle::make('ativo')
                    ->default(true)
                    ->required(),
            ]);
    }
}
