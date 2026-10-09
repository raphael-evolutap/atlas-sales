<?php

declare(strict_types=1);

namespace App\Policies;

use App\Enums\StatusVenda;
use App\Models\Venda;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Foundation\Auth\User as AuthUser;

class VendaPolicy
{
    use HandlesAuthorization;

    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:Venda');
    }

    public function view(AuthUser $authUser, Venda $venda): bool
    {
        return $authUser->can('View:Venda')
            || $venda->vendedor_id === $authUser->vendedor?->getKey();
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:Venda');
    }

    public function update(AuthUser $authUser, Venda $venda): bool
    {
        return $authUser->can('Update:Venda')
            || $venda->vendedor_id === $authUser->vendedor?->getKey();
    }

    public function delete(AuthUser $authUser, Venda $venda): bool
    {
        // Admin (super admin do Shield) não passa por aqui e também exclui canceladas.
        return $venda->status === StatusVenda::Aberta
            && ($authUser->can('Delete:Venda') || $venda->vendedor_id === $authUser->vendedor?->getKey());
    }

    public function deleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('DeleteAny:Venda');
    }

    public function restore(AuthUser $authUser, Venda $venda): bool
    {
        return $authUser->can('Restore:Venda');
    }

    public function forceDelete(AuthUser $authUser, Venda $venda): bool
    {
        return $authUser->can('ForceDelete:Venda');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:Venda');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:Venda');
    }

    public function replicate(AuthUser $authUser, Venda $venda): bool
    {
        return $authUser->can('Replicate:Venda');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:Venda');
    }

    public function fechar(AuthUser $authUser, Venda $venda): bool
    {
        return $authUser->can('Fechar:Venda') && $venda->vendedor_id === $authUser->vendedor?->getKey();
    }

    public function cancelar(AuthUser $authUser, Venda $venda): bool
    {
        return $authUser->can('Cancelar:Venda') && $venda->vendedor_id === $authUser->vendedor?->getKey();
    }

    public function enviarComprovantes(AuthUser $authUser, Venda $venda): bool
    {
        return $authUser->can('EnviarComprovantes:Venda') && $venda->vendedor_id === $authUser->vendedor?->getKey();
    }
}
