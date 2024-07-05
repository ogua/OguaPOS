@php
    $company = App\Models\Companyinfo::first();
@endphp
<div>
    <div class="flex gap-x-3">
        <x-filament::button color="info" icon="heroicon-o-printer" outlined> 
            Print
        </x-filament::button>

        <x-filament::button color="success" icon="heroicon-o-envelope" outlined> 
            Email
        </x-filament::button>
    </div>

    <p class="text-center text-2xl font-bold">{{ $company->name }} </p>

    <p class="text-center text-xl font-semibold">Sales Information</p>

    <div class="mt-4" style="margin-top: 20px;">
        <b>Date:</b> {{ $record->transaction_date }} <br>
        <b>Reference:</b> {{ $record->reference_number }} <br>
        <b>Warehouse:</b> {{ $record->warehouse?->name ?? "" }}
    </div>

    <hr style="margin-top: 20px;">

    <div class="flex gap-x-4" style="margin-top: 20px;">
        <div class="w-1/2">
            <b>From:</b> <br>
            {{ $company->name }} <br>
            {{ $company->address }} <br>
            {{ $company->location }} <br>
            {{ $company->email1 }} <br>
            {{ $company->phone }}
        </div>
        <div class="w-1/2">
            <b>To:</b> <br>
            {{ $record->customer?->name }} <br>
            @if ($record->customer?->company_name)
                 {{ $record->customer?->company_name }} <br>
            @endif

            @if ($record->customer?->address)
                {{ $record->customer?->address }} <br>
            @endif

            @if ($record->customer?->email)
                {{ $record->customer?->email }} <br>
            @endif

            @if ($record->customer?->phone_number)
                {{ $record->customer?->phone_number }}
            @endif
        </div>
    </div>

    <div class="w-full" style="margin-top: 20px;">
        <x-filament-tables::container>
            <x-filament-tables::table>
                <x-slot:header>
                    <x-filament-tables::header-cell>
                        #
                    </x-filament-tables::header-cell>
                    <x-filament-tables::header-cell>
                        Product
                    </x-filament-tables::header-cell>
                    <x-filament-tables::header-cell class="text-center">
                        Quantity
                    </x-filament-tables::header-cell>
                    <x-filament-tables::header-cell>
                        Unit price
                    </x-filament-tables::header-cell>
                    <x-filament-tables::header-cell>
                        Tax
                    </x-filament-tables::header-cell>
                    <x-filament-tables::header-cell>
                        Subtotal
                    </x-filament-tables::header-cell>
                </x-slot:header>
                @foreach ($record->saleitem as $item)
                    <x-filament-tables::row>
                        <x-filament-tables::cell>
                            {{ $loop->iteration }}
                        </x-filament-tables::cell>
                        <x-filament-tables::cell>
                            {{ $item->product_name }}
                        </x-filament-tables::cell>
                        <x-filament-tables::cell>
                            {{ $item->qty }}
                        </x-filament-tables::cell>
                        <x-filament-tables::cell>
                            {{ $item->unit_price }}
                        </x-filament-tables::cell>
                        <x-filament-tables::cell>
                            GHC {{ $item->tax }}
                        </x-filament-tables::cell>
                        <x-filament-tables::cell>
                            GHC {{ $item->total }}
                        </x-filament-tables::cell>
                   </x-filament-tables::row>
                @endforeach
                <x-filament-tables::row>
                    <x-filament-tables::cell colspan="5">
                        Order Tax
                    </x-filament-tables::cell>
                    <x-filament-tables::cell>
                        GHC {{ $record->order_tax_value }}
                    </x-filament-tables::cell>
                </x-filament-tables::row>

                <x-filament-tables::row>
                    <x-filament-tables::cell colspan="5">
                        Discount
                    </x-filament-tables::cell>
                    <x-filament-tables::cell>
                        GHC {{ $record->order_discount_value }}
                    </x-filament-tables::cell>
                </x-filament-tables::row>

                <x-filament-tables::row>
                    <x-filament-tables::cell colspan="5">
                        Coupon
                    </x-filament-tables::cell>
                    <x-filament-tables::cell>
                        GHC {{ $record->coupon_discount }}
                    </x-filament-tables::cell>
                </x-filament-tables::row>


                <x-filament-tables::row>
                    <x-filament-tables::cell colspan="5">
                        Shipping cost
                    </x-filament-tables::cell>
                    <x-filament-tables::cell>
                        GHC {{ $record->shipping_cost }}
                    </x-filament-tables::cell>
                </x-filament-tables::row>

                <x-filament-tables::row>
                    <x-filament-tables::cell colspan="5">
                        Grand Total
                    </x-filament-tables::cell>
                    <x-filament-tables::cell>
                        GHC {{ $record->grand_total }}
                    </x-filament-tables::cell>
                </x-filament-tables::row>

                <x-filament-tables::row>
                    <x-filament-tables::cell>
                        Sales Note:
                    </x-filament-tables::cell>
                    <x-filament-tables::cell colspan="5">
                        {{ $record->sale_note }}
                    </x-filament-tables::cell>
                </x-filament-tables::row>

                <x-filament-tables::row>
                    <x-filament-tables::cell>
                        Staff Note:
                    </x-filament-tables::cell>
                    <x-filament-tables::cell colspan="5">
                        {{ $record->staff_note }}
                    </x-filament-tables::cell>
                </x-filament-tables::row>
            </x-filament-tables::table>
        </x-filament-tables::container>
    </div>

    <p style="margin-top: 20px;"><b>Created By:</b> {{ $record->user?->name }}</p>


</div>