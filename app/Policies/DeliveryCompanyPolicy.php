<?php

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use App\Models\DeliveryCompany;
use Illuminate\Auth\Access\HandlesAuthorization;

class DeliveryCompanyPolicy
{
    use HandlesAuthorization;
    
    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:DeliveryCompany');
    }

    public function view(AuthUser $authUser, DeliveryCompany $deliveryCompany): bool
    {
        return $authUser->can('View:DeliveryCompany');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:DeliveryCompany');
    }

    public function update(AuthUser $authUser, DeliveryCompany $deliveryCompany): bool
    {
        return $authUser->can('Update:DeliveryCompany');
    }

    public function delete(AuthUser $authUser, DeliveryCompany $deliveryCompany): bool
    {
        return $authUser->can('Delete:DeliveryCompany');
    }

    public function deleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('DeleteAny:DeliveryCompany');
    }

    public function restore(AuthUser $authUser, DeliveryCompany $deliveryCompany): bool
    {
        return $authUser->can('Restore:DeliveryCompany');
    }

    public function forceDelete(AuthUser $authUser, DeliveryCompany $deliveryCompany): bool
    {
        return $authUser->can('ForceDelete:DeliveryCompany');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:DeliveryCompany');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:DeliveryCompany');
    }

    public function replicate(AuthUser $authUser, DeliveryCompany $deliveryCompany): bool
    {
        return $authUser->can('Replicate:DeliveryCompany');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:DeliveryCompany');
    }

}