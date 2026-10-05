<?php

namespace App\Filament\Resources\Fornecedors\Schemas;

use Filament\Actions\Action;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Notifications\Notification;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Facades\Http;
use Leandrocfe\FilamentPtbrFormFields\Cep;
use Leandrocfe\FilamentPtbrFormFields\Document;
use Leandrocfe\FilamentPtbrFormFields\Enums\CepFieldMode;
use Leandrocfe\FilamentPtbrFormFields\Providers\ViaCepProvider;

class FornecedorForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(12)
            ->components([
                TextInput::make('nome')
                    ->required()
                    ->maxLength(255)
                    ->columnSpan(4),
                TextInput::make('email')
                    ->email()
                    ->maxLength(255)
                    ->columnSpan(3),
                TextInput::make('telefone')
                    ->maxLength(255)
                    ->columnSpan(3),
                Document::make('cnpj')
                    ->label('CNPJ')
                    ->cnpj()
                    ->live(onBlur: true)
                    ->suffixAction(
                        Action::make('buscarCnpj')
                            ->label('Buscar dados do CNPJ')
                            ->icon(Heroicon::MagnifyingGlass)
                            ->action(function (Get $get, Set $set) {
                                $cnpj = preg_replace('/\D/', '', (string) $get('cnpj'));
                                dump($cnpj);
                                if (strlen($cnpj) !== 14) {
                                    Notification::make()
                                        ->title('CNPJ inválido')
                                        ->body('Informe os 14 dígitos do CNPJ antes de buscar.')
                                        ->danger()
                                        ->send();

                                    return;
                                }

                                $response = Http::get("https://brasilapi.com.br/api/cnpj/v1/{$cnpj}");

                                if ($response->failed()) {
                                    Notification::make()
                                        ->title('CNPJ não encontrado')
                                        ->body('Não foi possível obter os dados na BrasilAPI.')
                                        ->danger()
                                        ->send();

                                    return;
                                }

                                $data = $response->json();

                                $set('nome', $data['razao_social'] ?? null);
                                $logradouro = $data['logradouro'] ?? '';
                                $numero = $data['numero'] ?? '';
                                $set('endereco', trim("{$logradouro}, {$numero}", ', '));
                                $set('cidade', $data['municipio'] ?? null);
                                $set('uf', $data['uf'] ?? null);
                                $set('cep', $data['cep'] ?? null);
                                $set('telefone', $data['ddd_telefone_1'] ?? null);

                                Notification::make()
                                    ->title('Dados do CNPJ carregados')
                                    ->success()
                                    ->send();
                            })
                    )
                    ->columnSpan(3),
                TextInput::make('contato_nome')
                    ->label('Nome do Contato')
                    ->maxLength(255)
                    ->columnSpan(4),
                Cep::make('cep')
                    ->label('CEP')
                    ->mode(CepFieldMode::SUFFIX) // or CepFieldMode::ON_BLUR
                    ->api(ViaCepProvider::class, function (Set $set, ?array $response) {
                        $set('endereco', data_get($response, 'logradouro').' - '.data_get($response, 'bairro'));
                        $set('cidade', data_get($response, 'localidade'));
                        $set('uf', data_get($response, 'uf'));
                    })
                    ->columnStart(1)
                    ->columnSpan(2),
                TextInput::make('endereco')
                    ->maxLength(255)
                    ->columnSpan(6),
                TextInput::make('cidade')
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
            ]);
    }
}
