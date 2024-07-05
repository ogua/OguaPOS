<?php

namespace App\Partials\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

enum SalesStatus : string implements HasLabel, HasColor {

    case Pending = '1';
    case Completed = '2';

    public function getLabel(): ?string
    {
        //return $this->name;

        return match ($this){
            self::Pending => 'Pending',
            self::Completed => 'Completed',
        };
    }


    public function getColor(): string | array | null {

        return match ($this){
            self::Pending => 'danger',
            self::Completed => 'success',
        };

    }


}