<?php

namespace App\Support;

use Illuminate\Database\Eloquent\Model;

final class ProductSearchImage
{
    public static function getOptionString($record): string
    {
        return view('filament.pages.product-select-results', compact('record'))->render();
    }
}