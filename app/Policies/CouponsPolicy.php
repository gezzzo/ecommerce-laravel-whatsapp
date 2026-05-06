<?php

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use App\Models\Coupons;
use Illuminate\Auth\Access\HandlesAuthorization;

class CouponsPolicy
{
    use HandlesAuthorization;
    
    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:Coupons');
    }

    public function view(AuthUser $authUser, Coupons $coupons): bool
    {
        return $authUser->can('View:Coupons');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:Coupons');
    }

    public function update(AuthUser $authUser, Coupons $coupons): bool
    {
        return $authUser->can('Update:Coupons');
    }

    public function delete(AuthUser $authUser, Coupons $coupons): bool
    {
        return $authUser->can('Delete:Coupons');
    }

    public function deleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('DeleteAny:Coupons');
    }

    public function restore(AuthUser $authUser, Coupons $coupons): bool
    {
        return $authUser->can('Restore:Coupons');
    }

    public function forceDelete(AuthUser $authUser, Coupons $coupons): bool
    {
        return $authUser->can('ForceDelete:Coupons');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:Coupons');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:Coupons');
    }

    public function replicate(AuthUser $authUser, Coupons $coupons): bool
    {
        return $authUser->can('Replicate:Coupons');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:Coupons');
    }

}