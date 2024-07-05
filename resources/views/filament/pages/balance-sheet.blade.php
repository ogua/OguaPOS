<x-filament-panels::page>
    <x-filament::section
        icon="heroicon-m-banknotes"
        icon-size="md"
        style="margin-top: 20px;"
    >
        <x-slot name="heading">
           Balance sheet
        </x-slot>

        <x-filament-tables::container>
        <x-filament-tables::table>
            <x-slot:header>
                <x-filament-tables::header-cell>
                   Liability
                </x-filament-tables::header-cell>
                <x-filament-tables::header-cell>
                </x-filament-tables::header-cell>
                
                <x-filament-tables::header-cell>
                    Asset
                </x-filament-tables::header-cell>
                <x-filament-tables::header-cell>
                </x-filament-tables::header-cell>
            </x-slot:header>
            @php
                $totalliability = 0;
                $totalasset = 0;
            @endphp

                <x-filament-tables::row>
                    <x-filament-tables::cell>
                       Supplier Due
                    </x-filament-tables::cell>
                    <x-filament-tables::cell>
                        GHC
                    </x-filament-tables::cell>
                    <x-filament-tables::cell>
                       Customer Due
                    </x-filament-tables::cell>
                    <x-filament-tables::cell>
                        GHC
                    </x-filament-tables::cell>
                </x-filament-tables::row>

                <x-filament-tables::row>
                    <x-filament-tables::cell colspan="2">
                    </x-filament-tables::cell>
                    <x-filament-tables::cell>
                       Closing Stock
                    </x-filament-tables::cell>
                    <x-filament-tables::cell>
                        GHC
                    </x-filament-tables::cell>
                </x-filament-tables::row>

                <x-filament-tables::row>
                    <x-filament-tables::cell colspan="2">
                    </x-filament-tables::cell>
                    <x-filament-tables::cell colspan="2">
                       Account balances:
                    </x-filament-tables::cell>
                </x-filament-tables::row>

                @foreach ($this->accounts as $account)
                    @php
                        $totalasset +=$account->current_balance;
                    @endphp
                     <x-filament-tables::row>
                        <x-filament-tables::cell colspan="2">
                        </x-filament-tables::cell>
                        <x-filament-tables::cell>
                            {{ $account->account_name}}
                        </x-filament-tables::cell>
                        <x-filament-tables::cell>
                            GHC {{ number_format($account->current_balance,2) }}
                        </x-filament-tables::cell>
                    </x-filament-tables::row>
                @endforeach

               <x-filament-tables::row style="background-color: yellowgreen; color: white;">
                    <x-filament-tables::cell>
                       Total Liability:
                    </x-filament-tables::cell>
                    <x-filament-tables::cell>
                        GHC {{ number_format($totalliability,2) }}
                    </x-filament-tables::cell>

                    <x-filament-tables::cell>
                        Total Assets
                    </x-filament-tables::cell>

                    <x-filament-tables::cell>
                        GHC {{ number_format($totalasset,2) }}
                    </x-filament-tables::cell>
                </x-filament-tables::row>



        </x-filament-tables::table>

    </x-filament-tables::container>
</x-filament::section>
</x-filament-panels::page>
