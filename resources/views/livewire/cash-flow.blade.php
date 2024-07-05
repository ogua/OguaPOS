<div>
    
        
    <x-filament::section
        icon="heroicon-m-banknotes"
        icon-size="md"
        style="margin-top: 20px;"
    >
        <x-slot name="heading">
           Cash Flow
        </x-slot>

        <x-filament-tables::container>
        <x-filament-tables::table>
            <x-slot:header>
                <x-filament-tables::header-cell>
                    Paid on
                </x-filament-tables::header-cell>
                <x-filament-tables::header-cell>
                    Description
                </x-filament-tables::header-cell>
                <x-filament-tables::header-cell>
                    Action By
                </x-filament-tables::header-cell>
                <x-filament-tables::header-cell>
                    Credit
                </x-filament-tables::header-cell>
                <x-filament-tables::header-cell>
                    Debit
                </x-filament-tables::header-cell>
                <x-filament-tables::header-cell>
                    Balance
                </x-filament-tables::header-cell>
            </x-slot:header>
            @php
                $balance = 0;
            @endphp

             @foreach ($accountdetails ?? [] as $account)
                <x-filament-tables::row>
                    <x-filament-tables::cell>
                       {{ $account->paid_on }}
                    </x-filament-tables::cell>

                    <x-filament-tables::cell>
                       @if ($account->purchase_id)
                         {{$account->paying_type}} <br>
                         Supplier: {{$account->purchase?->suplier?->fullname}} <br>
                         Reference No.: {{$account->purchase?->reference_no}} <br>
                         Pay reference no.: {{$account->payment_ref}} <br>
                         Payment For: {{$account->purchase?->suplier?->fullname}}
                       @endif

                    @if ($account->sale_id)
                        {{$account->paying_type}} <br>
                        Customer: {{$account->sale?->customer?->name}} <br>
                        Reference No.: {{$account->sale?->reference_number}} <br>
                        Pay reference no.: {{$account->payment_ref}} <br>
                        Payment For: {{$account->sale?->customer?->name}}                    
                    @endif

                    @if ($account->sale_return_id) 
                         Pay reference no.: {{$account->salereturn?->reference_no}} <br>
                         Payment For: {{$account->salereturn?->sale?->customer?->name}}
                    @endif

                    @if ($account->purchase_return_id) 
                         Pay reference no.: {{$account->purchasereturn?->reference_no}} <br>
                         Payment For: {{$account->purchasereturn?->purchase?->suplier?->fullname}}
                    @endif
                    </x-filament-tables::cell>
                    
                    <x-filament-tables::cell>
                        {{ $account->user?->name ?? "" }}
                    </x-filament-tables::cell>


                    <x-filament-tables::cell>
                       {{ $account->payment_type == "credit" ? "GHC ".number_format($account->amount,2) : "" }}
                    </x-filament-tables::cell>

                    <x-filament-tables::cell>
                       {{ $account->payment_type == "debit" ? "GHC ".number_format($account->amount,2) : "" }}
                    </x-filament-tables::cell>

                    <x-filament-tables::cell>
                    
                            <!-- $credit = $account->payment_type == "credit" ? $account->amount : 0;
                            $debit = $account->payment_type == "debit" ? $account->amount : 0;
                            $current_balance = $this->current_balance + $credit - $debit; -->

                        GHC {{ number_format($account->balance,2) }}
                       
                    </x-filament-tables::cell>

                </x-filament-tables::row>
            @endforeach                  
        </x-filament-tables::table>

         <x-filament::pagination
            :paginator="$accountdetails"
            :page-options="[5, 10, 20, 50, 100, 'all']"
            :current-page-option-property="$perPage"
        />

       

    </x-filament-tables::container>
</x-filament::section>
</div>
