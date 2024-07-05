<?php

namespace App\Filament\Resources\ProductPromotionResource\Pages;

use App\Filament\Resources\ProductPromotionResource;
use App\Models\ProductPromotion;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Redirect;
use Filament\Notifications\Notification;

class CreateProductPromotion extends CreateRecord
{
    protected static string $resource = ProductPromotionResource::class;
    
    protected function mutateFormDataBeforeCreate(array $data): array
    {        
        return $data;
    }
    
    protected function beforeCreate(): void
    {
        $fdata = $this->data['promotionitems'] ?? [];
        
        foreach ($fdata as $row) {
            
            if (!$row['product_name']) {
                continue;
            }
            
            if ($row['variant_id']) {
                $data = [
                    'product_id' => $row['product_id'],
                    'product_name' => $row['product_name'],
                    'variant_id' => $row['variant_id'] ?? NULL,
                    'promotion_price' => $row['promotion_price'],
                    'start_date' => $row['start_date'],
                    'status' => $row['status'],
                    'end_date' => $row['end_date'],
                    'warehouse_id' => $this->data['warehouse_id'],
                ];
            }else{
                $data = [
                    'product_id' => $row['product_id'],
                    'product_name' => $row['product_name'],
                    'promotion_price' => $row['promotion_price'],
                    'start_date' => $row['start_date'],
                    'status' => $row['status'],
                    'end_date' => $row['end_date'],
                    'warehouse_id' => $this->data['warehouse_id'],
                ];
            }
            
            
            
            logger($data);
            
            // $this->halt();
            
            ProductPromotion::create($data);
            
        }
        
        Notification::make()
        ->title('Promotions added succesfully')
        ->success()
        ->send();
        
        $resources = static::getResource();
        $this->redirect($resources::getUrl('index'));
        
        $this->halt();
    }
}
