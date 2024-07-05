<?php

namespace App\Partials\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

enum DeliveryStatus: string implements HasLabel, HasColor{

    case Ordered = 'Ordered';
    case Packed = 'Packed';
    case Shipped = 'Shipped';
    case Delivered = 'Delivered';
    case Cancelled = 'Cancelled';

    public function getLabel(): ?string
    {
        //return $this->name;

        return match ($this){
            self::Ordered => 'Ordered',
            self::Packed => 'Packed',
            self::Shipped => 'Shipped',
            self::Delivered => 'Delivered',
            self::Cancelled => 'Cancelled',
        };
    }


    public function getColor(): string | array | null {

        return match ($this){
            self::Ordered => 'success',
            self::Packed => 'danger',
            self::Shipped => 'info',
            self::Delivered => 'success',
            self::Cancelled => 'danger',
        };

    }








}