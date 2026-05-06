<?php

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use App\Models\WhatsappCampaign;
use Illuminate\Auth\Access\HandlesAuthorization;

class WhatsappCampaignPolicy
{
    use HandlesAuthorization;
    
    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:WhatsappCampaign');
    }

    public function view(AuthUser $authUser, WhatsappCampaign $whatsappCampaign): bool
    {
        return $authUser->can('View:WhatsappCampaign');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:WhatsappCampaign');
    }

    public function update(AuthUser $authUser, WhatsappCampaign $whatsappCampaign): bool
    {
        return $authUser->can('Update:WhatsappCampaign');
    }

    public function delete(AuthUser $authUser, WhatsappCampaign $whatsappCampaign): bool
    {
        return $authUser->can('Delete:WhatsappCampaign');
    }

    public function deleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('DeleteAny:WhatsappCampaign');
    }

    public function restore(AuthUser $authUser, WhatsappCampaign $whatsappCampaign): bool
    {
        return $authUser->can('Restore:WhatsappCampaign');
    }

    public function forceDelete(AuthUser $authUser, WhatsappCampaign $whatsappCampaign): bool
    {
        return $authUser->can('ForceDelete:WhatsappCampaign');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:WhatsappCampaign');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:WhatsappCampaign');
    }

    public function replicate(AuthUser $authUser, WhatsappCampaign $whatsappCampaign): bool
    {
        return $authUser->can('Replicate:WhatsappCampaign');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:WhatsappCampaign');
    }

}