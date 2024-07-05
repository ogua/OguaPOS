<x-filament-panels::page>

    <h1>{{ $this->data->product_name }}</h1>

    @foreach ($this->data->warehouses as $warehouse)
    <div class="relative overflow-x-auto">
    <table class="w-full text-sm text-left rtl:text-right text-gray-500 dark:text-gray-400">
        <thead class="text-xs text-gray-700 uppercase bg-gray-50 dark:bg-gray-700 dark:text-gray-400">
            <tr style="background-color: rgb(74 222 128);">
                <th scope="col" colspan="7" class="uppercase px-6 py-3 text-center text-white">Location: {{ $warehouse->name }}</th>
            </tr>
            <tr>
                <th scope="col" class="px-6 py-3">
                    Code
                </th>
                <th scope="col" class="px-6 py-3">
                    Product
                </th>
            
                <th scope="col" class="px-6 py-3">
                    Quantity
                </th>
                <th scope="col" class="px-6 py-3">
                    
                </th>
                <th scope="col" class="px-6 py-3">
                    Unit Cost
                </th>
                <th scope="col" class="px-6 py-3">
                   Exp Date
                </th>
                <th scope="col" class="px-6 py-3">
                    Subtotal (BEFORE TAX)
                </th>
            </tr>
        </thead>
        <tbody>
            @php
                $inventory = App\Models\Product_Warehouse_Inventory::where('product_id',$warehouse->pivot->product_id)
                ->where('warehouse_id',$warehouse->pivot->warehouse_id)->get();
            @endphp

            @foreach ($inventory as $row)

            <tr class="bg-white border-b dark:bg-gray-800 dark:border-gray-700">
                <td class="px-6 py-4">
                     {{ $row->variant->item_code ?? $row->product->product_code }}
                </td>
                <td class="px-6 py-4">
                     {{ $row->product->product_name }}
                </td>

                <td class="px-6 py-4">
                    <input type="number" wire:model.defer="inventoryData.{{ $row->id }}.qty" wire:keyup.debounce.1000ms="InventoryData('qty', {{ $row->id }})" class="qty" id="qty_{{ $row->id }}" style="width: 100px" /> 
                </td>

                 <td class="px-6 py-4">
                    <input type="text" readonly value="({{ $row->product->unit->code ?? '' }})" style="width: 50px;font-size: 10px;" /> 
                </td>

                <td class="px-6 py-4">
                     <input type="number" wire:model.defer="inventoryData.{{ $row->id }}.price" wire:keyup.debounce.1000ms="InventoryData('price', {{ $row->id }})" style="width: 150px"  />
                </td>

                <td class="px-6 py-4">
                    {{ $row->product->product_expiry_date }}
                </td>

                <td class="px-6 py-4">
                    {{ $this->inventoryData[$row->id]['subtotal'] }}
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endforeach

<x-filament::button wire:click="saveopeningstock" size="lg" outlined>
    Save
</x-filament::button>

</x-filament-panels::page>