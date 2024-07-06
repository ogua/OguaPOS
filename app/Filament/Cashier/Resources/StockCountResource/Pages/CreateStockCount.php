<?php

namespace App\Filament\Cashier\Resources\StockCountResource\Pages;

use App\Filament\Cashier\Resources\StockCountResource;
use App\Models\Product_Warehouse_Inventory;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;
use Filament\Notifications\Notification;


class CreateStockCount extends CreateRecord
{
    protected static string $resource = StockCountResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function getSavedNotification(): ?Notification
    {
         $record = $this->getRecord();

        return Notification::make()
            ->success()
            ->title('Counted successfully!')
            ->body('Stock counted successfully!.');
    }

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        
        $products = Product_Warehouse_Inventory::query()
        ->when($this->data['category_id'], function ($query) {
            return $query->whereHas('product', function ($query) {
                $query->where('product_category_id', $this->data['category_id']);
            });
        })
        
        ->when($this->data['brand_id'], function ($query) {
            return $query->whereHas('product', function ($query) {
                $query->where('brand_id', $this->data['brand_id']);
            });
        })
        ->where('warehouse_id',$this->data['warehouse_id'])
        ->get();

        //dd($products);

       // $this->halt();


        if (count($products)) {
            $csvData = array('Warehouse, Product Name, Product Code, Batch Number, Expected, Counted');
            foreach ($products as $item) {

                $productype = $item->product->product_type;

                if ($productype == "Variation") {
                    $code = $item->variant?->item_code;
                    $name = $item->product->product_name."(".$code.")";
                }elseif ($productype == "Single") {
                    $name = $item->product->product_name;
                }



                $csvData[] =  $item->warehouse?->name.','.$name. ',' . $item->product->product_code . ',' . $item->product->product_batch_number . ',' . $item->qty . ',';
            }

            $filename = date('Ymd') . '-' . date('His') . ".csv";
            $filePath = storage_path('app/public/stock_count/' . $filename);

            // Ensure the directory exists
            if (!file_exists(dirname($filePath))) {
                mkdir(dirname($filePath), 0755, true);
            }

            $file = fopen($filePath, "w+");
            foreach ($csvData as $cellData) {
                fputcsv($file, explode(',', $cellData));
            }
            fclose($file);

            $data['intital_file'] = $filename;
            $data['is_adjusted'] = 0;
        }
        
        return $data;
    }

    protected function beforeCreate(): void
    {
        

    }
}
