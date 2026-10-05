<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\Movimentacao;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Foundation\Auth\User as AuthUser;

class MovimentacaoPolicy
{
    use HandlesAuthorization;

    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:Movimentacao');
    }

    public function view(AuthUser $authUser, Movimentacao $movimentacao): bool
    {
        return $authUser->can('View:Movimentacao');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:Movimentacao');
    }

    public function update(AuthUser $authUser, Movimentacao $movimentacao): bool
    {
        return $authUser->can('Update:Movimentacao');
    }

    public function delete(AuthUser $authUser, Movimentacao $movimentacao): bool
    {
        return $authUser->can('Delete:Movimentacao');
    }

    public function deleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('DeleteAny:Movimentacao');
    }

    public function restore(AuthUser $authUser, Movimentacao $movimentacao): bool
    {
        return $authUser->can('Restore:Movimentacao');
    }

    public function forceDelete(AuthUser $authUser, Movimentacao $movimentacao): bool
    {
        return $authUser->can('ForceDelete:Movimentacao');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:Movimentacao');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:Movimentacao');
    }

    public function replicate(AuthUser $authUser, Movimentacao $movimentacao): bool
    {
        return $authUser->can('Replicate:Movimentacao');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:Movimentacao');
    }
}
