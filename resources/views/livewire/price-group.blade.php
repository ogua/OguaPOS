<div>
    <div class="w-132">
        @if ($this->data->product_image)
            <img  class="rounded-lg shadow-sm" src="{{ asset('storage') }}/{{ $this->data->product_image }}" alt="{{ $this->data->product_name }}" style="height: 200px;">
        @else
            <img class="rounded-lg shadow-sm" src="{{ URL::to('images/no-image.jpeg') }}" alt="no image" style="height: 200px;">
        @endif

        <h1 style="margin-top: 10px;">{{ $this->data->product_name }}</h1>

    </div>

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
                    Selling Price
                </th>
        
                <th scope="col" class="px-6 py-3">
                    Wholesale Cost
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
                     <input type="number" wire:model.lazy="inventoryData.{{ $row->id }}.price" style="width: 150px"  />
                </td>

                <td class="px-6 py-4">
                     <input type="number" wire:model.lazy="inventoryData.{{ $row->id }}.wholesale" style="width: 150px"  />
                </td>

            </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endforeach

<br>
<div style="float: right; margin-right: 20px;">
    <x-filament::button wire:click="saveopeningstock" size="lg" outlined>
        Save
    </x-filament::button>
</div>

</div>
