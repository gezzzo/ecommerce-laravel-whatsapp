<?php

namespace App\Enums;

enum DeliveryStatusEnum: string
{
    case ANNULE = 'Annulé';
    case ANNULE_SUIVI = 'Annulé ( SUIVI )';
    case LIVRE = 'Livré';
    case PAS_REPONSE_DEPLACEMENT = 'pas réponse +déplacement';
    case MISE_EN_DISTRIBUTION = 'Mise en distribution';
    case ERREUR_NUMERO = 'Erreur Numero';
    case CLIENT_INTERESSE = 'client intéressé';
    case EN_COURS = 'En cours';
    case PAS_DE_REPONSE_SMS = 'Pas de réponse + SMS';
    case PAS_DE_REPONSE_SUIVI = 'Pas de reponse ( SUIVI )';
    case EN_VOYAGE = 'En Voyage';
    case HORS_ZONE = 'Hors-zone';
    case RAMASSE = 'Ramassé';
    case REPORTE = 'Reporté';
    case REPORTE_SUIVI = 'Reporté ( SUIVI )';
    case PROGRAMME = 'Programmé';
    case RECU = 'Reçu';
    case REFUSE = 'Refusé';
    case REMBOURSE = 'Remboursé';
    case RETOURNE = 'Retourné';
    case EN_RETOUR_PAR_AMANA = 'En retour par AMANA';
    case REPORTE_AUJOURDHUI = 'reporté aujourd\'hui';
    case SANS_ADRESSE = 'sans adresse';
    case EXPEDIE = 'Expédié';
    case EXPEDIER_PAR_AMANA = 'expédier par AMANA';
    case INJOIGNABLE = 'Injoignable';
    case INJOIGNABLE_SUIVI = 'Injoignable ( SUIVI )';
    case BOITE_VOCAL = 'Boite Vocal';
    case BOITE_VOCAL_SUIVI = 'Boite Vocal ( SUIVI )';

    public function getLabel(): string
    {
        return __($this->value);
    }

    public function getColor(): string
    {
        return match ($this) {
            // Delivered/Successful statuses
            self::LIVRE, self::RECU => 'success',

            // In Progress statuses
            self::EN_COURS, self::MISE_EN_DISTRIBUTION, self::EN_VOYAGE,
            self::RAMASSE, self::EXPEDIE, self::PROGRAMME => 'primary',

            // Cancelled/Failed statuses
            self::ANNULE, self::ANNULE_SUIVI, self::REFUSE,
            self::RETOURNE, self::EN_RETOUR_PAR_AMANA => 'danger',

            // Postponed/Delayed statuses
            self::REPORTE, self::REPORTE_SUIVI, self::REPORTE_AUJOURDHUI => 'warning',

            // Contact Issues
            self::PAS_REPONSE_DEPLACEMENT, self::PAS_DE_REPONSE_SMS,
            self::PAS_DE_REPONSE_SUIVI, self::INJOIGNABLE,
            self::INJOIGNABLE_SUIVI, self::BOITE_VOCAL, self::BOITE_VOCAL_SUIVI => 'gray',

            // Address/Zone Issues
            self::ERREUR_NUMERO, self::HORS_ZONE, self::SANS_ADRESSE => 'warning',

            // Other statuses
            self::CLIENT_INTERESSE => 'info',
            self::REMBOURSE => 'success',
            self::EXPEDIER_PAR_AMANA => 'primary',
        };
    }

    public static function getOptions(): array
    {
        $options = [];
        foreach (self::cases() as $case) {
            $options[$case->value] = $case->getLabel();
        }

        return $options;
    }

    public static function getColors(): array
    {
        $colors = [];
        foreach (self::cases() as $case) {
            $colors[$case->value] = $case->getColor();
        }

        return $colors;
    }

    public function isDelivered(): bool
    {
        return in_array($this, [self::LIVRE, self::RECU]);
    }

    public function isFailed(): bool
    {
        return in_array($this, [
            self::ANNULE,
            self::ANNULE_SUIVI,
            self::REFUSE,
            self::RETOURNE,
            self::EN_RETOUR_PAR_AMANA,
        ]);
    }

    public function isInProgress(): bool
    {
        return in_array($this, [
            self::EN_COURS,
            self::MISE_EN_DISTRIBUTION,
            self::EN_VOYAGE,
            self::RAMASSE,
            self::EXPEDIE,
            self::PROGRAMME,
            self::EXPEDIER_PAR_AMANA,
        ]);
    }

    public function isPostponed(): bool
    {
        return in_array($this, [
            self::REPORTE,
            self::REPORTE_SUIVI,
            self::REPORTE_AUJOURDHUI,
        ]);
    }

    public function hasContactIssue(): bool
    {
        return in_array($this, [
            self::PAS_REPONSE_DEPLACEMENT,
            self::PAS_DE_REPONSE_SMS,
            self::PAS_DE_REPONSE_SUIVI,
            self::INJOIGNABLE,
            self::INJOIGNABLE_SUIVI,
            self::BOITE_VOCAL,
            self::BOITE_VOCAL_SUIVI,
        ]);
    }

    public function hasAddressIssue(): bool
    {
        return in_array($this, [
            self::ERREUR_NUMERO,
            self::HORS_ZONE,
            self::SANS_ADRESSE,
        ]);
    }
}
