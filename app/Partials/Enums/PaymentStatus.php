<?php

namespace App\Partials\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

enum PaymentStatus : string implements HasLabel, HasColor {

    case Pending = '1';
    case Due = '2';
    case Partial = '3';
    case Paid = '4';

    public function getLabel(): ?string
    {
        //return $this->name;

        return match ($this){
            self::Pending => 'Pending',
            self::Due => 'Due',
            self::Partial => 'Partial',
            self::Paid => 'Paid',
        };
    }


    public function getColor(): string | array | null {

        return match ($this){
            self::Pending => 'info',
            self::Due => 'danger',
            self::Partial => 'warning',
            self::Paid => 'success',
        };

    }


}