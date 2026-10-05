<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\DespesaCategoria;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Foundation\Auth\User as AuthUser;

class DespesaCategoriaPolicy
{
    use HandlesAuthorization;

    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:DespesaCategoria');
    }

    public function view(AuthUser $authUser, DespesaCategoria $despesaCategoria): bool
    {
        return $authUser->can('View:DespesaCategoria');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:DespesaCategoria');
    }

    public function update(AuthUser $authUser, DespesaCategoria $despesaCategoria): bool
    {
        return $authUser->can('Update:DespesaCategoria');
    }

    public function delete(AuthUser $authUser, DespesaCategoria $despesaCategoria): bool
    {
        return $authUser->can('Delete:DespesaCategoria');
    }

    public function deleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('DeleteAny:DespesaCategoria');
    }

    public function restore(AuthUser $authUser, DespesaCategoria $despesaCategoria): bool
    {
        return $authUser->can('Restore:DespesaCategoria');
    }

    public function forceDelete(AuthUser $authUser, DespesaCategoria $despesaCategoria): bool
    {
        return $authUser->can('ForceDelete:DespesaCategoria');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:DespesaCategoria');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:DespesaCategoria');
    }

    public function replicate(AuthUser $authUser, DespesaCategoria $despesaCategoria): bool
    {
        return $authUser->can('Replicate:DespesaCategoria');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:DespesaCategoria');
    }
}
