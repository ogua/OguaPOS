<div class="relative overflow-x-auto">
    <table class="w-full text-sm text-left rtl:text-right text-gray-500 dark:text-gray-400">
        <tbody>
            <tr style="background-color: rgb(253 224 71);">
                <th scope="col" colspan="4" class="uppercase px-6 py-3 text-center" style="color: white;">Product Information</th>
            </tr>
            <tr class="bg-white border-b dark:bg-gray-800 dark:border-gray-700">
                <th scope="row" class="px-6 py-4 font-medium text-gray-900 whitespace-nowrap dark:text-white">
                    Product name: {{ $getRecord()->product_name }}
                </th>
                <td class="px-6 py-4">
                    Code: {{ $getRecord()->product_code }}
                </td>
                <td class="px-6 py-4">
                    Barcode: {{ $getRecord()->barcode_symbology }}
                </td>
                <td class="px-6 py-4" rowspan="3">
                    @if ($getRecord()->product_image)
                    123
                        <img src="{{ $getRecord()->product_image }}" alt="">
                    @else
                        <img src="{{ URL::to('images/no-image.jpeg') }}" alt="no image" width="600">
                    @endif
                    
                </td>
            </tr>
            <tr class="bg-white border-b dark:bg-gray-800 dark:border-gray-700">
                <th scope="row" class="px-6 py-4 font-medium text-gray-900 whitespace-nowrap dark:text-white">
                    Brand: {{ $getRecord()->brand?->name ?? "" }}
                </th>
                <td class="px-6 py-4">
                    Category: {{ $getRecord()->category?->name ?? "" }}
                </td>
                <td class="px-6 py-4">
                    Unit: {{ $getRecord()->unit->code }}
                </td>

            </tr>
            <tr class="bg-white dark:bg-gray-800">
                <th scope="row" class="px-6 py-4 font-medium text-gray-900 whitespace-nowrap dark:text-white">
                    Alert: {{ $getRecord()->alert_quantity }}
                </th>
                <td class="px-6 py-4">
                    Tax: {{ $getRecord()->product_tax }}
                </td>
                <td class="px-6 py-4">
                    Taxes: @foreach ($getRecord()->taxes as $tax)
                        {{ $tax->name }},
                    @endforeach
                </td>
            </tr>
            <tr class="bg-white dark:bg-gray-800">
                <th scope="row" class="px-6 py-4 font-medium text-gray-900 whitespace-nowrap dark:text-white">
                    Expiry: {{ $getRecord()->product_expiry_date }}
                </th>
                <td class="px-6 py-4" colspan="2">
                    Batch no: {{ $getRecord()->product_batch_number }}
                </td>
            </tr>
            <tr class="bg-white dark:bg-gray-800">
                <td class="px-6 py-4" colspan="4">
                    Details: {{ $getRecord()->product_details }}
                </td>
            </tr>
        </tbody>
    </table>
</div>

<hr style="margin-top: 20px; margin-bottom: 20px;">

<div class="relative overflow-x-auto">
    <table class="w-full text-sm text-left rtl:text-right text-gray-500 dark:text-gray-400">
        <thead class="text-xs text-gray-700 uppercase bg-gray-50 dark:bg-gray-700 dark:text-gray-400">
            <tr style="background-color: rgb(253 224 71);">
                <th scope="col" colspan="5" class="uppercase px-6 py-3 text-center">Variations</th>
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
            @foreach ($getRecord()->variationitems as $row)
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

<div class="relative overflow-x-auto">
    <table class="w-full text-sm text-left rtl:text-right text-gray-500 dark:text-gray-400">
        <thead class="text-xs text-gray-700 uppercase bg-gray-50 dark:bg-gray-700 dark:text-gray-400">
            <tr style="background-color: rgb(74 222 128);">
                <th scope="col" colspan="9" class="uppercase px-6 py-3 text-center">Product Stock Details</th>
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
            @foreach ($getRecord()->inventory as $row)
            <tr class="bg-white border-b dark:bg-gray-800 dark:border-gray-700">
                <td class="px-6 py-4">
                     {{ $row->variant->item_code }}
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
                    Unit sold
                </td>

                <td class="px-6 py-4">
                    Unit transfered
                </td>

                <td class="px-6 py-4">
                    Unit adjusted
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>
