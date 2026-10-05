<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\Despesa;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Foundation\Auth\User as AuthUser;

class DespesaPolicy
{
    use HandlesAuthorization;

    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:Despesa');
    }

    public function view(AuthUser $authUser, Despesa $despesa): bool
    {
        return $authUser->can('View:Despesa');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:Despesa');
    }

    public function update(AuthUser $authUser, Despesa $despesa): bool
    {
        return $authUser->can('Update:Despesa');
    }

    public function delete(AuthUser $authUser, Despesa $despesa): bool
    {
        return $authUser->can('Delete:Despesa');
    }

    public function deleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('DeleteAny:Despesa');
    }

    public function restore(AuthUser $authUser, Despesa $despesa): bool
    {
        return $authUser->can('Restore:Despesa');
    }

    public function forceDelete(AuthUser $authUser, Despesa $despesa): bool
    {
        return $authUser->can('ForceDelete:Despesa');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:Despesa');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:Despesa');
    }

    public function replicate(AuthUser $authUser, Despesa $despesa): bool
    {
        return $authUser->can('Replicate:Despesa');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:Despesa');
    }
}
