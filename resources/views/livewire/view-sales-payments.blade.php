<div>
    
    <x-filament::section
    icon="heroicon-m-truck"
    icon-size="md"
>
    <x-slot name="heading">
        {{ $record->reference_number }}
    </x-slot>

        <x-filament-tables::container>

        <x-filament-tables::table>
            <x-slot:header>
                <x-filament-tables::header-cell>
                    Date
                </x-filament-tables::header-cell>
                <x-filament-tables::header-cell>
                    Reference
                </x-filament-tables::header-cell>
                <x-filament-tables::header-cell>
                    Account
                </x-filament-tables::header-cell>
                
                <x-filament-tables::header-cell>
                    
                    @if ($recordtype == "Sales")
                        Paid By
                    @endif
                    
                    @if ($recordtype == "Purchase")
                        Paid To
                    @endif
                </x-filament-tables::header-cell>
                <x-filament-tables::header-cell>
                    Mode of Payment
                </x-filament-tables::header-cell>
                <x-filament-tables::header-cell>
                    Action
                </x-filament-tables::header-cell>
            </x-slot:header>
                @foreach ($record->payments ?? [] as $payment)
                <x-filament-tables::row>
                    <x-filament-tables::cell>
                        {{ date('Y-m-d', strtotime($record->date)) }}
                    </x-filament-tables::cell>

                    <x-filament-tables::cell>
                        @if ($recordtype == "Sales")
                            {{ $record->reference_number }}
                        @endif

                        @if ($recordtype == "Purchase")
                            {{ $record->reference_no }}
                        @endif
                        
                    </x-filament-tables::cell>

                    <x-filament-tables::cell>
                        GHC{{ number_format($payment->amount,2) }}
                    </x-filament-tables::cell>


                    <x-filament-tables::cell>
                        @if ($recordtype == "Sales")
                            {{ $record->customer->name ?? "" }}
                        @endif
                        
                        @if ($recordtype == "Purchase")
                            {{ $record->suplier->fullname ?? "" }}
                        @endif

                        @if ($recordtype == "Sales return")
                            {{ $record->salereturn->sale?->customer?->name }}
                        @endif

                        @if ($recordtype == "Purchase return")
                            {{ $record->salereturn->purchase?->suplier?->fullname }}
                        @endif
                        
                    </x-filament-tables::cell>


                    <x-filament-tables::cell>
                        {{ $payment->paying_method }} 
                    </x-filament-tables::cell>

                    @php
                        $url = App\Filament\Resources\PaymentResource::getUrl('edit',['record' => $payment]);
                    @endphp

                    <x-filament-tables::cell>
                        <div class="flex gap-x-4">
                            <x-filament::button class="w-1/2" href="{{ $url }}"
                            tag="a" color="info">
                                Edit
                            </x-filament::button>

                            <x-filament::button wire:confirm="Are you sure you want to delete this payment?" wire:click="deletepayment('{{ $payment->id }}')" color="danger">
                                Delete
                            </x-filament::button>
                        </div>
                    </x-filament-tables::cell>
                </x-filament-tables::row>
                @endforeach
        </x-filament-tables::table>
    </x-filament-tables::container>
    </x-filament::section>
</div>
