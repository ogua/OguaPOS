<x-filament-panels::page x-data="printpreview">

<x-filament::section
    icon="heroicon-o-tag"
    icon-color="info"
>
    <x-slot name="heading">
        Add products to generate Labels
    </x-slot>

<x-filament::input.wrapper>
    <x-filament::input
        type="text"
        wire:model="search"
         wire:keyup="search_product_for_label"
         placeholder="Enter products name to print labels"
         autofocus=""
         autocomplete="off"
    />
</x-filament::input.wrapper>
<div style="margin-top: 10px;border-color: rgb(156 163 175); max-height: 150px; overflow-y: auto;">
    <ul class="w-full divide-y divide-gray-300 dark:divide-gray-700 rounded-md">
    @foreach ($searchresults as $row)
    
        <a href="#" class="pb-3 sm:pb-4 hover:bg-yellow-500 hover:text-white w-full block" wire:click="additems({{ $row['id'] }}, '{{ $row['product_name'] }}', '{{ $row['product_code'] }}', '{{ $row['cost_price'] }}', '{{ $row['selling_price'] }}','{{ $row['size'] }}', '{{ $row['include_tax'] }}', '{{ $row['exclude_tax'] }}', '{{ $row['company_name'] }}', '{{ $row['barcode'] }}')" >
            <li class="p-4 text-sm font-medium text-gray-900 dark:text-white">
                {{ $row['product_name'] }}
            </li>
        </a>
    
    @endforeach
</ul>


    </ul>
</div>

    <br><br>

    <div class="relative overflow-x-auto">
    <table class="w-full text-sm text-left rtl:text-right text-gray-500 dark:text-gray-400">
        <thead class="text-xs text-gray-700 uppercase bg-gray-50 dark:bg-gray-700 dark:text-gray-400">
            <tr>
                <th scope="col" class="px-6 py-3">
                    Product
                </th>
                <th scope="col" class="px-6 py-3">
                    No. of labels
                </th>
                <th scope="col" class="px-6 py-3">
                    EXP Date
                </th>
                <th scope="col" class="px-6 py-3">
                    Packing Date
                </th>
                <th></th>
            </tr>
        </thead>
        <tbody>
            @foreach ($selecteditems as $index => $row)
            <tr class="bg-white border-b dark:bg-gray-800 dark:border-gray-700">
                <td class="px-6 py-4">
                     {{ $row['product_name'] }}
                </td>
                <td class="px-6 py-4">
                     <input type="number" wire:model="selecteditems.{{ $index }}.product_label" style="width: 80px" />
                </td>
                <td class="px-6 py-4">
                    <input type="date" wire:model="selecteditems.{{ $index }}.product_expiry_date"  /> 
                </td>

                <td class="px-6 py-4">
                    <input type="date" wire:model="selecteditems.{{ $index }}.product_packing_date"  /> 
                </td>
                <td>
                    <x-filament::button color="warning" wire:click="removeItem({{ $index }} )" size="xs" icon="heroicon-m-trash" tooltip="Remove Item">Del</x-filament::button>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>

<br><br>

    <x-filament::section
    icon="heroicon-o-information-circle"
    icon-color="info"
>
    <x-slot name="heading">
        Information to show in Labels
    </x-slot>
 
    <div class="flex gap-x-2">
        <label class="w-1/2">
            <x-filament::input.checkbox wire:model="ProductName" wire:change="Productnameupdate" />
            <span>
                Product Name
            </span>
        </label>

        <label class="w-1/2">
            <x-filament::input.checkbox wire:model="variation" wire:change="variationupdate" />
            <span>
                Product Variation (recommended)
            </span>
        </label>

    </div>

    <div class="flex gap-x-2">

        <label class="w-1/2">
            <x-filament::input.checkbox wire:model="productPrice" wire:change="productPriceupdate" />
            <span>
                Product Price
            </span>
        </label>

        <label class="w-1/2">
            <x-filament::input.checkbox wire:model="incTax" wire:change="incTaxupdate" />
            <span>
                Inc. Tax
            </span>
        </label>

        <label class="w-1/2">
            <x-filament::input.checkbox wire:model="exTax" wire:change="exTaxupdate"/>
            <span>
                Ex. Tax
            </span>
        </label>

        <label class="w-1/2">
            <x-filament::input.checkbox wire:model="businessname" wire:change="businessnameupdate"/>
            <span>
                Business name
            </span>
        </label>

    </div>

    <div class="flex gap-x-2">

        <label class="w-1/2">
            <x-filament::input.checkbox wire:model="expirydate" wire:change="expirydateupdate" />
            <span>
                Print expiry date
            </span>
        </label>

        <label class="w-1/2">
            <x-filament::input.checkbox wire:model="packingdate" wire:change="packingdateupdate" />
            <span>
                Print packing date
            </span>
        </label>
    </div>

    <br>
    <p>Barcode Settings</p>
    <div class="w-full">
        <x-filament::input.wrapper :valid="! $errors->has('barcode_setting')">
            <x-filament::input.select wire:model="barcode_setting">
                <option value="1">20 Labels per Sheet, Sheet Size: 8.5" x 11", Label Size: 4" x 1", Labels per sheet: 20</option>
                <option value="2">30 Labels per sheet, Sheet Size: 8.5" x 11", Label Size: 2.625" x 1", Labels per sheet: 30</option>
                <option value="3">32 Labels per sheet, Sheet Size: 8.5" x 11", Label Size: 2" x 1.25", Labels per sheet: 32</option>
                <option value="4">40 Labels per sheet, Sheet Size: 8.5" x 11", Label Size: 2" x 1", Labels per sheet: 40</option>
                <option value="5">50 Labels per Sheet, Sheet Size: 8.5" x 11", Label Size: 1.5" x 1", Labels per sheet: 50</option>
                <option value="6">Continuous Rolls - 31.75mm x 25.4mm, Label Size: 31.75mm x 25.4mm, Gap: 3.18mm</option>
            </x-filament::input.select>
        </x-filament::input.wrapper>
    </div>

    <br><br>

    <x-filament::button  style="float: right;margin-right: 40px;" @click="previewLabels">
       Preview
    </x-filament::button>

    

</x-filament::section>



</x-filament::section>
</x-filament-panels::page>

<script>
    document.addEventListener('alpine:init', () => {
        Alpine.data('printpreview', () => ({
            selecteditems: @entangle('selecteditems'),
            ProductName: @entangle('ProductName'),
            variation: @entangle('variation'),
            productPrice: @entangle('productPrice'),
            exTax: @entangle('exTax'),
            businessname: @entangle('businessname'),
            expirydate: @entangle('expirydate'),
            packingdate: @entangle('packingdate'),
            incTax: @entangle('incTax'),
            barcode_setting: @entangle('barcode_setting'),

            previewLabels() {
                const selectedItemsEncoded = encodeURIComponent(JSON.stringify(this.selecteditems));
                const newWindow = window.open(`/print-label?barcode_setting=${this.barcode_setting}&productname=${this.ProductName}&variation=${this.variation}&productPrice=${this.productPrice}&exTax=${this.exTax}&businessname=${this.businessname}&expirydate=${this.expirydate}&packingdate=${this.packingdate}&incTax=${this.incTax}&data=${selectedItemsEncoded}`,'_blank');
                if (newWindow) {
                    newWindow.focus();
                } else {
                    alert('Please allow popups for this website');
                }
            }
        }));
    });
</script>
