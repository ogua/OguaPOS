<x-filament-widgets::widget>
    <x-filament::section>
         <x-slot name="heading">
           Top Best Seller {{ date('M') }}
        </x-slot>
        <x-filament-tables::container>
            <x-filament-tables::table>
                <x-slot:header>
                    <x-filament-tables::header-cell>
                        S.N
                    </x-filament-tables::header-cell>
                    <x-filament-tables::header-cell>
                        Product name
                    </x-filament-tables::header-cell>
                    <x-filament-tables::header-cell>
                        Qty
                    </x-filament-tables::header-cell>
                </x-slot:header>
                @php
                    $count = 1;
                @endphp
                @foreach ($this->topseller as $topproduct)
                    <x-filament-tables::row>
                    <x-filament-tables::cell>
                        {{ $count }}
                    </x-filament-tables::cell>
                    <x-filament-tables::cell>
                        {{ $topproduct->product_name }}
                    </x-filament-tables::cell>
                    <x-filament-tables::cell>
                        {{ $topproduct->sold_qty }}
                    </x-filament-tables::cell>
                </x-filament-tables::row>
                @php
                    $count++;
                @endphp
                @endforeach
            </x-filament-tables::table>
        </x-filament-tables::container>
    </x-filament::section>
</x-filament-widgets::widget>
