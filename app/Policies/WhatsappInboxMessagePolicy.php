<?php

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use App\Models\WhatsappInboxMessage;
use Illuminate\Auth\Access\HandlesAuthorization;

class WhatsappInboxMessagePolicy
{
    use HandlesAuthorization;
    
    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:WhatsappInboxMessage');
    }

    public function view(AuthUser $authUser, WhatsappInboxMessage $whatsappInboxMessage): bool
    {
        return $authUser->can('View:WhatsappInboxMessage');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:WhatsappInboxMessage');
    }

    public function update(AuthUser $authUser, WhatsappInboxMessage $whatsappInboxMessage): bool
    {
        return $authUser->can('Update:WhatsappInboxMessage');
    }

    public function delete(AuthUser $authUser, WhatsappInboxMessage $whatsappInboxMessage): bool
    {
        return $authUser->can('Delete:WhatsappInboxMessage');
    }

    public function deleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('DeleteAny:WhatsappInboxMessage');
    }

    public function restore(AuthUser $authUser, WhatsappInboxMessage $whatsappInboxMessage): bool
    {
        return $authUser->can('Restore:WhatsappInboxMessage');
    }

    public function forceDelete(AuthUser $authUser, WhatsappInboxMessage $whatsappInboxMessage): bool
    {
        return $authUser->can('ForceDelete:WhatsappInboxMessage');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:WhatsappInboxMessage');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:WhatsappInboxMessage');
    }

    public function replicate(AuthUser $authUser, WhatsappInboxMessage $whatsappInboxMessage): bool
    {
        return $authUser->can('Replicate:WhatsappInboxMessage');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:WhatsappInboxMessage');
    }

}