<?php

namespace App\Enums;

enum InventoryMovementType: string
{
    case Import = 'import';
    case Return = 'return';
    case Sale = 'sale';
    case Adjustment = 'adjustment';

    /**
     * @return string
     */
    public function label(): string
    {
        return match ($this) {
            self::Import => __('Import'),
            self::Return => __('Return'),
            self::Sale => __('Sale'),
            self::Adjustment => __('Adjustment'),
        };
    }

    /**
     * @return string
     */
    public function color(): string
    {
        return match ($this) {
            self::Import => 'success',
            self::Return => 'warning',
            self::Sale => 'danger',
            self::Adjustment => 'info',
        };
    }

    /**
     * @return string
     */
    public function icon(): string
    {
        return match ($this) {
            self::Import => 'heroicon-o-arrow-down-tray',
            self::Return => 'heroicon-o-arrow-uturn-left',
            self::Sale => 'heroicon-o-shopping-cart',
            self::Adjustment => 'heroicon-o-wrench-screwdriver',
        };
    }
}
