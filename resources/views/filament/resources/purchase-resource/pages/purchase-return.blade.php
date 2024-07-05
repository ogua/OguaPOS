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

    <p class="text-center text-xl font-semibold">Purchase Return</p>

    <div class="mt-4" style="margin-top: 20px;">
        <b>Date:</b> {{ $record->returndate }} <br>
        <b>Reference:</b> {{ $record->reference_no }} <br>
        <b>Warehouse:</b> {{ $record->sale?->warehouse?->name ?? "" }}
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
            {{ $record->sale?->suplier?->fullname }} <br>
            @if ($record->sale?->suplier?->contact)
                 {{ $record->sale?->suplier?->contact }} <br>
            @endif

            @if ($record->sale?->suplier?->email)
                {{ $record->sale?->suplier?->email }} <br>
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
                @foreach ($record->returnitems as $item)
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
                        GHC {{ $record->order_tax }}
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
                        Return Note:
                    </x-filament-tables::cell>
                    <x-filament-tables::cell colspan="5">
                        {{ $record->return_note }}
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