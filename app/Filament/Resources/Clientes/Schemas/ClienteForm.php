<?php

namespace App\Filament\Resources\Clientes\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Leandrocfe\FilamentPtbrFormFields\Cep;
use Leandrocfe\FilamentPtbrFormFields\Document;
use Leandrocfe\FilamentPtbrFormFields\Enums\CepFieldMode;
use Leandrocfe\FilamentPtbrFormFields\PhoneNumber;
use Leandrocfe\FilamentPtbrFormFields\Providers\ViaCepProvider;

class ClienteForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Grid::make(12)
                    ->schema([
                        Select::make('grupo_id')
                            ->relationship('grupo', 'nome')
                            ->required()
                            ->searchable()
                            ->preload()
                            ->columnSpan(3),
                    ])->columnSpanFull(),
                TextInput::make('nome')
                    ->required()
                    ->maxLength(255)
                    ->columnSpan(3),
                TextInput::make('email')
                    ->email()
                    ->maxLength(255)
                    ->columnSpan(3),
                PhoneNumber::make('telefone')
                    ->mask('(99) 99999-9999')
                    ->columnSpan(3),
                Document::make('cpf_cnpj')
                    ->label('CPF ou CNPJ')
                    ->dynamic()
                    ->columnSpan(3),
                Cep::make('cep')
                    ->label('CEP')
                    ->mode(CepFieldMode::SUFFIX) // or CepFieldMode::ON_BLUR
                    ->api(ViaCepProvider::class, function (Set $set, ?array $response) {
                        $set('endereco', data_get($response, 'logradouro').' - '.data_get($response, 'bairro'));
                        $set('cidade', data_get($response, 'localidade'));
                        $set('uf', data_get($response, 'uf'));
                    })
                    ->maxLength(10)
                    ->columnSpan(2),
                TextInput::make('endereco')
                    ->label('Endereço')
                    ->maxLength(255)
                    ->columnSpan(6),
                TextInput::make('cidade')
                    ->label('Cidade')
                    ->maxLength(255)
                    ->columnSpan(2),
                Select::make('uf')
                    ->label('Estado')
                    ->options([
                        'AC' => 'Acre',
                        'AL' => 'Alagoas',
                        'AP' => 'Amapá',
                        'AM' => 'Amazonas',
                        'BA' => 'Bahia',
                        'CE' => 'Ceará',
                        'DF' => 'Distrito Federal',
                        'ES' => 'Espírito Santo',
                        'GO' => 'Goiás',
                        'MA' => 'Maranhão',
                        'MT' => 'Mato Grosso',
                        'MS' => 'Mato Grosso do Sul',
                        'MG' => 'Minas Gerais',
                        'PA' => 'Pará',
                        'PB' => 'Paraíba',
                        'PR' => 'Paraná',
                        'PE' => 'Pernambuco',
                        'PI' => 'Piauí',
                        'RJ' => 'Rio de Janeiro',
                        'RN' => 'Rio Grande do Norte',
                        'RS' => 'Rio Grande do Sul',
                        'RO' => 'Rondônia',
                        'RR' => 'Roraima',
                        'SC' => 'Santa Catarina',
                        'SP' => 'São Paulo',
                        'SE' => 'Sergipe',
                        'TO' => 'Tocantins',
                    ])
                    ->columnSpan(2),

                Textarea::make('observacoes')
                    ->columnSpanFull(),
                Toggle::make('ativo')
                    ->default(true)
                    ->required(),
            ])->columns(12);
    }
}
