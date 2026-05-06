<?php

namespace App\Enums;

enum OrderStatusEnum: string
{
    case NEW_ORDER = 'New Order';
    case CONFIRMED = 'Confirmed';
    case NUMERO_INCORRECT = 'Numéro incorrect';
    case ANNULE = 'Annulé';
    case RAPPEL = 'Rappel';
    case REPORTER = 'Reporter';
    case OCCUPE = 'Occupé';
    case PAS_REPONSE_1 = 'Pas réponse 1';
    case PAS_REPONSE_2 = 'Pas réponse 2';
    case PAS_REPONSE_3_SMS = 'Pas réponse 3 + SMS';
    case FAKE = 'Fake';
    case DOUBLE = 'Double';

    public function getLabel(): string
    {
        return __($this->value);
    }

    public function getColor(): string
    {
        return match ($this) {
            // New/Pending statuses
            self::NEW_ORDER => 'gray',

            // Positive statuses
            self::CONFIRMED => 'success',

            // Follow-up/Action needed
            self::RAPPEL, self::REPORTER, self::OCCUPE => 'warning',

            // Contact issues
            self::PAS_REPONSE_1, self::PAS_REPONSE_2, self::PAS_REPONSE_3_SMS => 'info',

            // Problems/Issues
            self::NUMERO_INCORRECT, self::ANNULE, self::FAKE, self::DOUBLE => 'danger',
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

    public function isConfirmed(): bool
    {
        return $this === self::CONFIRMED;
    }

    public function isCancelled(): bool
    {
        return in_array($this, [self::ANNULE, self::FAKE, self::DOUBLE]);
    }

    public function needsFollowUp(): bool
    {
        return in_array($this, [
            self::RAPPEL,
            self::REPORTER,
            self::PAS_REPONSE_1,
            self::PAS_REPONSE_2,
            self::PAS_REPONSE_3_SMS,
        ]);
    }

    public function hasContactIssue(): bool
    {
        return in_array($this, [
            self::NUMERO_INCORRECT,
            self::PAS_REPONSE_1,
            self::PAS_REPONSE_2,
            self::PAS_REPONSE_3_SMS,
            self::OCCUPE,
        ]);
    }

    public function isProblematic(): bool
    {
        return in_array($this, [
            self::NUMERO_INCORRECT,
            self::ANNULE,
            self::FAKE,
            self::DOUBLE,
        ]);
    }
}
