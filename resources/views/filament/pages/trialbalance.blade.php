<x-filament-panels::page>
    <x-filament::section
        icon="heroicon-m-banknotes"
        icon-size="md"
        style="margin-top: 20px;"
    >
        <x-slot name="heading">
           Trial balance
        </x-slot>

        <x-filament-tables::container>
        <x-filament-tables::table>
            <x-slot:header>
                <x-filament-tables::header-cell>
                   
                </x-filament-tables::header-cell>
                
                <x-filament-tables::header-cell>
                    Credit
                </x-filament-tables::header-cell>
                <x-filament-tables::header-cell>
                    Debit
                </x-filament-tables::header-cell>
            </x-slot:header>
            @php
                $totalcredit = 0;
                $totaldebit = 0;
            @endphp

                <x-filament-tables::row>
                    <x-filament-tables::cell>
                       Supplier Due
                    </x-filament-tables::cell>

                    <x-filament-tables::cell>
                     
                    </x-filament-tables::cell>

                    <x-filament-tables::cell>
                      GHC
                    </x-filament-tables::cell>
                </x-filament-tables::row>


                <x-filament-tables::row>
                    <x-filament-tables::cell>
                       Customer Due
                    </x-filament-tables::cell>

                    <x-filament-tables::cell>
                     GHC
                    </x-filament-tables::cell>

                    <x-filament-tables::cell>
                    </x-filament-tables::cell>
                </x-filament-tables::row>

                <x-filament-tables::row>
                    <x-filament-tables::cell colspan="3">
                      Account balances:
                    </x-filament-tables::cell>
                </x-filament-tables::row>

                @foreach ($this->accounts as $account)
                     <x-filament-tables::row>
                        <x-filament-tables::cell>
                            {{ $account->account_name}}
                        </x-filament-tables::cell>
                        <x-filament-tables::cell>
                            @php
                                $totalcredit +=$account->current_balance;
                            @endphp
                            GHC {{ number_format($account->current_balance,2) }}
                        </x-filament-tables::cell>
                        <x-filament-tables::cell>
                            
                        </x-filament-tables::cell>
                    </x-filament-tables::row>
                @endforeach



                <x-filament-tables::row style="background-color: yellowgreen; color: white;">
                    <x-filament-tables::cell>
                       Total
                    </x-filament-tables::cell>

                    <x-filament-tables::cell>
                     GHC {{ number_format($totalcredit,2) }}
                    </x-filament-tables::cell>

                    <x-filament-tables::cell>
                       GHC {{ number_format($totaldebit,2) }}
                    </x-filament-tables::cell>

                </x-filament-tables::row>
        </x-filament-tables::table>

    </x-filament-tables::container>
</x-filament::section>
</x-filament-panels::page>
