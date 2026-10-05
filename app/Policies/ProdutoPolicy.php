<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\Produto;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Foundation\Auth\User as AuthUser;

class ProdutoPolicy
{
    use HandlesAuthorization;

    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:Produto');
    }

    public function view(AuthUser $authUser, Produto $produto): bool
    {
        return $authUser->can('View:Produto');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:Produto');
    }

    public function update(AuthUser $authUser, Produto $produto): bool
    {
        return $authUser->can('Update:Produto');
    }

    public function delete(AuthUser $authUser, Produto $produto): bool
    {
        return $authUser->can('Delete:Produto');
    }

    public function deleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('DeleteAny:Produto');
    }

    public function restore(AuthUser $authUser, Produto $produto): bool
    {
        return $authUser->can('Restore:Produto');
    }

    public function forceDelete(AuthUser $authUser, Produto $produto): bool
    {
        return $authUser->can('ForceDelete:Produto');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:Produto');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:Produto');
    }

    public function replicate(AuthUser $authUser, Produto $produto): bool
    {
        return $authUser->can('Replicate:Produto');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:Produto');
    }
}
