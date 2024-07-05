<div class="relative overflow-x-auto">
    <table class="w-full text-sm text-left rtl:text-right text-gray-500 dark:text-gray-400">
        <tbody>
            <tr style="background-color: rgb(253 224 71);">
                <th scope="col" colspan="4" class="uppercase px-6 py-3 text-center text-white">Product Information</th>
            </tr>
            <tr class="bg-white border-b dark:bg-gray-800 dark:border-gray-700">
                <th scope="row" class="px-6 py-4 font-medium text-gray-900 whitespace-nowrap dark:text-white">
                    Product name: {{ $record->product_name }}
                </th>
                <th class="px-6 py-4">
                    Code: {{ $record->product_code }}
                </th>
                <th class="px-6 py-4">
                    Barcode: {{ $record->barcode_symbology }}
                </th>
                <th class="px-6 py-4" rowspan="4">
                    @if ($record->product_image)
                        <img  class="rounded-lg shadow-sm" src="{{ asset('storage') }}/{{ $record->product_image }}" alt="{{ $record->product_name }}" style="height: 300px;">
                    @else
                        <img class="rounded-lg shadow-sm" src="{{ URL::to('images/no-image.jpeg') }}" alt="no image" style="height: 300px;">
                    @endif
                    
                </th>
            </tr>
            <tr class="bg-white border-b dark:bg-gray-800 dark:border-gray-700">
                <th scope="row" class="px-6 py-4 font-medium text-gray-900 whitespace-nowrap dark:text-white">
                    Brand: {{ $record->brand?->name ?? "" }}
                </th>
                <th class="px-6 py-4">
                    Category: {{ $record->category?->name ?? "" }}
                </th>
                <th class="px-6 py-4">
                    Unit: {{ $record->unit->code }}
                </th>

            </tr>
            <tr class="bg-white dark:bg-gray-800">
                <th scope="row" class="px-6 py-4 font-medium text-gray-900 whitespace-nowrap dark:text-white">
                    Alert: {{ $record->alert_quantity }}
                </th>
                <td class="px-6 py-4">
                    Tax: {{ $record->product_tax }}
                </td>
                <th class="px-6 py-4">
                    Taxes: @foreach ($record->taxes as $tax)
                        {{ $tax->name }},
                    @endforeach
                </th>
            </tr>
            <tr class="bg-white dark:bg-gray-800">
                <th scope="row" class="px-6 py-4 font-medium text-gray-900 whitespace-nowrap dark:text-white">
                    Expiry: {{ $record->product_expiry_date }}
                </th>
                <th class="px-6 py-4" colspan="2">
                    Batch no: {{ $record->product_batch_number }}
                </th>
            </tr>
            <tr class="bg-white dark:bg-gray-800">
                <th class="px-6 py-4" colspan="4">
                    Details: {{ $record->product_details }}
                </th>
            </tr>
        </tbody>
    </table>
</div>

<hr style="margin-top: 20px; margin-bottom: 20px;">

@if ($record->product_type == "Variation")

<div class="relative overflow-x-auto">
    <table class="w-full text-sm text-left rtl:text-right text-gray-500 dark:text-gray-400">
        <thead class="text-xs text-gray-700 uppercase bg-gray-50 dark:bg-gray-700 dark:text-gray-400">
            <tr style="background-color: rgb(253 224 71);">
                <th scope="col" colspan="5" class="uppercase px-6 py-3 text-center text-white">Variations</th>
            </tr>
            <tr>
                <th scope="col" class="px-6 py-3">
                    Product
                </th>
                <th scope="col" class="px-6 py-3">
                    Position
                </th>
                <th scope="col" class="px-6 py-3">
                    Code
                </th>
                <th scope="col" class="px-6 py-3">
                    Cost price
                </th>
                <th scope="col" class="px-6 py-3">
                    Selling price
                </th>
            </tr>
        </thead>
        <tbody>
            @foreach ($record->variationitems as $row)
            <tr class="bg-white border-b dark:bg-gray-800 dark:border-gray-700">
                <td class="px-6 py-4">
                     {{ $row->product->product_name }}
                </td>
                <td class="px-6 py-4">
                     {{ $row->position }}
                </td>
                <td class="px-6 py-4">
                    {{ $row->item_code }}
                </td>

                <td class="px-6 py-4">
                    GHC{{ $row->cost_price }}
                </td>

                <td class="px-6 py-4">
                    GHC{{ $row->selling_price }}
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>

<hr style="margin-top: 20px; margin-bottom: 20px;">

@endif

<div class="relative overflow-x-auto">
    <table class="w-full text-sm text-left rtl:text-right text-gray-500 dark:text-gray-400">
        <thead class="text-xs text-gray-700 uppercase bg-gray-50 dark:bg-gray-700 dark:text-gray-400">
            <tr style="background-color: rgb(74 222 128);">
                <th scope="col" colspan="9" class="uppercase px-6 py-3 text-center text-white">Product Stock Details</th>
            </tr>
            <tr>
                <th scope="col" class="px-6 py-3">
                    Code
                </th>
                <th scope="col" class="px-6 py-3">
                    Product
                </th>
                <th scope="col" class="px-6 py-3">
                    Location
                </th>
                <th scope="col" class="px-6 py-3">
                    Unit price
                </th>
                <th scope="col" class="px-6 py-3">
                    Current stock
                </th>
                <th scope="col" class="px-6 py-3">
                    Stock value
                </th>
                <th scope="col" class="px-6 py-3">
                    Total unit sold
                </th>
                <th scope="col" class="px-6 py-3">
                    Total unit transfered
                </th>
                <th scope="col" class="px-6 py-3">
                    Total unit adjusted
                </th>
            </tr>
        </thead>
        <tbody>
            @foreach ($record->inventory as $row)
            <tr class="bg-white border-b dark:bg-gray-800 dark:border-gray-700">
                <td class="px-6 py-4">
                     {{ $row->variant->item_code ?? $row->product->product_code }}
                </td>
                <td class="px-6 py-4">
                     {{ $row->product->product_name }}
                </td>
                <td class="px-6 py-4">
                    {{ $row->warehouse->name }}
                </td>

                <td class="px-6 py-4">
                    GHC{{ $row->selling_price }}
                </td>

                <td class="px-6 py-4">
                    {{ $row->qty }}
                </td>

                <td class="px-6 py-4">
                    {{ $row->selling_price *  $row->qty }}
                </td>

                <td class="px-6 py-4">
                    @if ($row->product->product_type == "Single")
                        GHC {{ $row->singleitems->sum('total') }}
                    @else
                       GHC {{ $row->variantitems->sum('total') }}
                    @endif
                </td>

                <td class="px-6 py-4">
                     @if ($row->product->product_type == "Single")
                         {{ $row->singleunittransfer->sum('qty') }}
                    @else
                        {{ $row->variantunittransfer->sum('qty') }}
                    @endif
                </td>

                <td class="px-6 py-4">
                    @if ($row->product->product_type == "Single")
                         {{ $row->singleunitadjusted->sum('qty') }}
                    @else
                        {{ $row->variantunitadjusted->sum('qty') }}
                    @endif
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>
