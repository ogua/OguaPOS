<?php

namespace App\Partials\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

enum PurchaseStatus: string implements HasLabel, HasColor{

    case Received = 'Received';
    case Pending = 'Pending';
    case Ordered = 'Ordered';

    public function getLabel(): ?string
    {
        //return $this->name;

        return match ($this){
            self::Received => 'Received',
            self::Pending => 'Pending',
            self::Ordered => 'Ordered',
        };
    }


    public function getColor(): string | array | null {

        return match ($this){
            self::Received => 'success',
            self::Pending => 'danger',
            self::Ordered => 'warning',
        };

    }








}