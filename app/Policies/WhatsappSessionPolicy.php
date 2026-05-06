<?php

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use App\Models\WhatsappSession;
use Illuminate\Auth\Access\HandlesAuthorization;

class WhatsappSessionPolicy
{
    use HandlesAuthorization;
    
    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:WhatsappSession');
    }

    public function view(AuthUser $authUser, WhatsappSession $whatsappSession): bool
    {
        return $authUser->can('View:WhatsappSession');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:WhatsappSession');
    }

    public function update(AuthUser $authUser, WhatsappSession $whatsappSession): bool
    {
        return $authUser->can('Update:WhatsappSession');
    }

    public function delete(AuthUser $authUser, WhatsappSession $whatsappSession): bool
    {
        return $authUser->can('Delete:WhatsappSession');
    }

    public function deleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('DeleteAny:WhatsappSession');
    }

    public function restore(AuthUser $authUser, WhatsappSession $whatsappSession): bool
    {
        return $authUser->can('Restore:WhatsappSession');
    }

    public function forceDelete(AuthUser $authUser, WhatsappSession $whatsappSession): bool
    {
        return $authUser->can('ForceDelete:WhatsappSession');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:WhatsappSession');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:WhatsappSession');
    }

    public function replicate(AuthUser $authUser, WhatsappSession $whatsappSession): bool
    {
        return $authUser->can('Replicate:WhatsappSession');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:WhatsappSession');
    }

}