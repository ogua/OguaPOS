<?php

namespace App\Filament\Resources\SalesResource\Pages;

use App\Filament\Resources\SalesResource;
use App\Models\Coupon;
use App\Models\Giftcard;
use App\Models\Payment;
use App\Models\PaymentAccount;
use App\Models\Product;
use App\Models\Product_Warehouse_Inventory;
use App\Models\Productunit;
use App\Models\Stock_History;
use App\Partials\Enums\PaymentStatus;
use App\Partials\Enums\SalesStatus;
use App\Services\SalesForm;
use Filament\Actions;
use Filament\Actions\Action;
use Filament\Forms;
use Filament\Notifications\Notification;
use Filament\Notifications\Actions\Action as NotificationAction;
use Filament\Resources\Pages\EditRecord;

class EditSales extends EditRecord
{
    protected static string $resource = SalesResource::class;
    
    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }


    protected function mutateFormDataBeforeSave(array $data): array
    {
       // $data['user_id'] = auth()->id();
    
        return $data;
    }

    protected function getSavedNotification(): ?Notification
    {
         $record = $this->getRecord();

        return Notification::make()
            ->success()
            ->title('Sales updated')
            ->persistent()
            ->actions([
            NotificationAction::make('print')
            ->url(route('pos-invoice', $record->id), shouldOpenInNewTab: true)
            ->button(),
        ])
        ->body('Sales updated successfully!.');
    }

    protected function beforeSave(): void
    {
        $record = $this->getRecord();

        // Revert the original sale record's effects
        $this->revertOriginalSale($record);

    }

    protected function afterSave(): void
    {
        $record = $this->getRecord();

        // Apply the changes with the updated sale data
        $this->applySaleChanges($record);

        // Recalculate stock history for affected products
        $this->recalculateStockHistory($record);
    }

    protected function revertOriginalSale($sale) {
        // Revert the payment

        foreach ($sale->Payments as $Payment) {
            $amount = $Payment->amount;
            $accid = $Payment->account_id;

            $account = PaymentAccount::where('id',$accid)->first();

            if ($account) {
                $account->current_balance -= $amount;
                $account->save();
            }
        }


       // Payment::where('sale_id', $sale->id)->delete();
       
        // Revert the gift card
        if ($sale->gift_card_id) {
            $giftCard = Giftcard::find($sale->gift_card_id);
            $giftCard->expense -= $sale->paid_amount;
            $giftCard->save();
        }

        // Revert the coupon
        if ($sale->coupon_id) {
            $coupon = Coupon::find($sale->coupon_id);
            $coupon->used -= 1;
            $coupon->available += 1;
            $coupon->save();
        }

        // Revert the stock and stock history
        foreach ($sale->saleitem as $item) {
            $product = Product::find($item->product_id);
            $warehouseId = $sale->warehouse_id;

            $unit = Productunit::where("id",$item->sale_unit_id)->first();

            if ($unit->base_unit) {
                $qty = (int) $unit->operation_value * $item->qty;
            }else{
                $qty = $item->qty;
            }

            if ($product->product_type == "Single") {
                $inventory = Product_Warehouse_Inventory::where('product_id', $product->id)
                    ->where('warehouse_id', $warehouseId)
                    ->first();

                $inventory->qty += $qty;
                $inventory->save();

                Stock_History::where('adjustment_item_id', $sale->id)
                    ->where('type', 'Sales')
                    ->delete();
            } elseif ($product->product_type == "Variation") {
                $inventory = Product_Warehouse_Inventory::where('product_id', $product->id)
                    ->where('warehouse_id', $warehouseId)
                    ->where('variant_id', $item->variant_id)
                    ->first();

                $inventory->qty += $qty;
                $inventory->save();

                Stock_History::where('adjustment_item_id', $sale->id)
                    ->where('type', 'Sales')
                    ->delete();
            }
        }
    }

    protected function applySaleChanges($sale) {
        // Apply new payment
        
        // $paymentinsert = [
        //     'purchase_id' => null,
        //     'account_id' => null,
        //     'amount' => $sale->paid_amount,
        //     'used_points' => 0,
        //     'change' => $this->data['payment']['change'],
        //     'customer_id' => $sale->customer_id,
        //     'customer_stripe_id' => null,
        //     'charge_id' => null,
        //     'paypal_transaction_id' => null,
        //     'paying_method' => $this->data['payment']['paying_method'],
        //     'payment_note' => $this->data['payment_note'],
        // ];

        // $paymentdata = new Payment($paymentinsert);
        // $paymentdata->save();


        // if ($this->data['payment']['paying_method'] == "CHEQUE") {
        //     $paymentdata->cheque_no = $this->data['payment']['cheque_no'];
        //     $paymentdata->save();
        // }

        // if ($this->data['payment']['paying_method'] == "BANK TRANSFER") {
        //     $paymentdata->bankname = $this->data['payment']['bankname'];
        //     $paymentdata->accountnumber = $this->data['payment']['accountnumber'];
        //     $paymentdata->save();
        // }

        // if ($this->data['payment']['paying_method'] == "GIFT CARD") {
        //     $paymentdata->gift_card_id = $this->data['payment']['gift_card_id'] ?? null;
        //     $paymentdata->save();
        // }


        $totalpayments = $sale->payments->sum('amount');

         foreach ($sale->Payments as $Payment) {
            $amount = $Payment->amount;
            $accid = $Payment->account_id;

            $account = PaymentAccount::where('id',$accid)->first();

            if ($account) {
                $account->current_balance += $amount;
                $account->save();
            }

            $Payment->payment_type = "debit";
            $Payment->payment_ref = "SPP-".$Payment->paid_on;
            $Payment->paying_type = "Sales";
            $Payment->balance = $account->current_balance ?? 0;
            $Payment->save();
        }

        $grandtotal = $sale->grand_total;
        $sale->paid_amount = $totalpayments;
        $left = $grandtotal - $totalpayments;
        $sale->balance_amount = $left;

        if($totalpayments == 0){
            $sale->payment_status = PaymentStatus::Due;
        }elseif($left > 0){
            $sale->payment_status = PaymentStatus::Partial;
        }else{
            $sale->payment_status = PaymentStatus::Paid;
        }

        $sale->save();

        $sale->sale_status = SalesStatus::Completed;

        foreach ($sale->payments as $payment) {

            if($payment->paying_method == "CREDIT SALES" && $left < 1){
                $sale->sales_type = "CREDIT SALES PAID";
                $sale->save();
            }
            
             // Update gift card
            if ($sale->gift_card_id && $payment->paying_method == "GIFT CARD") {
                $giftCard = Giftcard::find($sale->gift_card_id);
                $giftCard->expense += $sale->paid_amount;
                $giftCard->save();
            }

        }

        // Update coupon
        if ($sale->coupon_id && $sale->coupon_discount > 0) {
            $coupon = Coupon::find($sale->coupon_id);
            $coupon->used += 1;
            $coupon->available -= 1;
            $coupon->save();
        }
        
        //$sale->payment_status = $sale->paid_amount < $sale->grand_total ? PaymentStatus::Partial : PaymentStatus::Paid;
        // Update stock and stock history
        foreach ($sale->saleitem as $item) {
            $product = Product::find($item->product_id);
            $warehouseId = $sale->warehouse_id;

            $unit = Productunit::where("id",$item->sale_unit_id)->first();

            if ($unit->base_unit) {
                $qty = (int) $unit->operation_value * $item->qty;
            }else{
                $qty = $item->qty;
            }

            if ($product->product_type == "Single") {
                $inventory = Product_Warehouse_Inventory::where('product_id', $product->id)
                    ->where('warehouse_id', $warehouseId)
                    ->first();

                $totalqty = $inventory->qty - $qty;
                $inventory->qty = $totalqty;
                $inventory->save();

                $availableQty = $inventory->qty;

                $stockout = [
                    'product_id' => $product->id,
                    'warehouse_id' => $warehouseId,
                    'adjustment_item_id' => $sale->id,
                    'type' => 'Sales',
                    'qty_change' => "-".$qty,
                    'new_quantity' => $availableQty,
                    'date' => $sale->created_at,
                    'reference' => $sale->reference_number
                ];

                Stock_History::create($stockout);
            } elseif ($product->product_type == "Variation") {
                $inventory = Product_Warehouse_Inventory::where('product_id', $product->id)
                    ->where('warehouse_id', $warehouseId)
                    ->where('variant_id', $item->variant_id)
                    ->first();

                $totalqty = $inventory->qty - $qty;
                $inventory->qty = $totalqty;
                $inventory->save();

                $availableQty = $inventory->qty;

                $stockout = [
                    'product_id' => $product->id,
                    'warehouse_id' => $warehouseId,
                    'variant_id' => $item->variant_id,
                    'adjustment_item_id' => $sale->id,
                    'type' => 'Sales',
                    'qty_change' => "-".$qty,
                    'new_quantity' => $availableQty,
                    'date' => $sale->created_at,
                    'reference' => $sale->reference_number
                ];

                Stock_History::create($stockout);
            }
        }
    }

    protected function recalculateStockHistory($sale) {
        foreach ($sale->saleitem as $item) {
            $productId = $item->product_id;
            $warehouseId = $sale->warehouse_id;
            $variantId = $item->variant_id;

            // Retrieve all stock history records for the product in the warehouse, ordered by date
            $stockHistories = Stock_History::where('product_id', $productId)
                ->where('warehouse_id', $warehouseId)
                ->when($variantId, function ($query, $variantId) {
                    return $query->where('variant_id', $variantId);
                })
                ->orderBy('date','desc')
                ->get();
            
            // Recalculate new quantities
            $inventory = Product_Warehouse_Inventory::where('product_id', $productId)
                ->where('warehouse_id', $warehouseId)
                ->when($variantId, function ($query, $variantId) {
                    return $query->where('variant_id', $variantId);
                })
                ->first();

            $currentQty = $inventory->qty;

            $currentStock = 0;

            $count = 0;

            foreach ($stockHistories as $history) {

                if ($count === 0) {
                    $currentStock+=$currentQty;
                    $count++;
                    continue;
                }


                if ($history->qty_change < 0) {
                    $currentStock -= abs($history->qty_change);
                } else {
                    $currentStock += substr($history->qty_change,1);
                }

                if ($count > 0) {
                    $history->new_quantity = $currentStock;
                    $history->save();
                }


                $count ++;
            }
        }
    }


    

    
}
