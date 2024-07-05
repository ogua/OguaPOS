<x-filament-panels::page>

<div class="flex gap-x-4">

  <div class="w-32">
     @if ($this->data->product_image)
        <img  class="rounded-lg shadow-sm" src="{{ asset('storage') }}/{{ $this->data->product_image }}" alt="{{ $this->data->product_name }}" style="height: 200px;">
      @else
          <img class="rounded-lg shadow-sm" src="{{ URL::to('images/no-image.jpeg') }}" alt="no image" style="height: 200px;">
      @endif

      <h1 style="margin-top: 10px;">{{ $this->data->product_name }}</h1>

  </div>



  <div class="w-1/2">
    <div class="flex">
      <div class="w-1/2" style="margin-right: 20px;">
        <x-filament::input.wrapper>
            <x-filament::input.select wire:model="warehouse" wire:change="changewarehouse">
            @foreach ($this->data->warehouses as $warehouse)
            <option value="{{ $warehouse->id }}">{{ $warehouse->name }}</option>
            @endforeach
            </x-filament::input.select>
        </x-filament::input.wrapper>
      </div>
      <div class="w-1/2">
        @if ($this->data->product_type == "Variation")
            <x-filament::input.wrapper>
            <x-filament::input.select wire:model="variation" wire:change="changevariation">
            @foreach ($this->data->variationitems as $row)
            <option value="{{ $row->id }}">{{ $row->item_code }}</option>
            @endforeach
            </x-filament::input.select>
            </x-filament::input.wrapper>
        @endif
      </div>
    </div>
  </div>
</div>

<div class="relative overflow-x-auto">
<table class="w-full text-sm text-left rtl:text-right text-gray-500 dark:text-gray-400">
<thead class="text-xs text-gray-700 uppercase bg-gray-50 dark:bg-gray-700 dark:text-gray-400">
<tr style="background-color: rgb(74 222 128);">
<th scope="col" colspan="6" class="uppercase px-6 py-3 text-center text-white">Product History</th>
</tr>
<tr>
<th scope="col" class="px-6 py-3">
Type
</th>
<th scope="col" class="px-6 py-3">
Quantity Change
</th>

<th scope="col" class="px-6 py-3">
New Quantity
</th>
<th scope="col" class="px-6 py-3">
Date
</th>
<th scope="col" class="px-6 py-3">
Reference No
</th>
</tr>
</thead>
<tbody>
@foreach ($this->history as $row)

<tr class="bg-white border-b dark:bg-gray-800 dark:border-gray-700">
<td class="px-6 py-4">
{{ $row->type }}
</td>
<td class="px-6 py-4">
{{ $row->qty_change }}
</td>

<td class="px-6 py-4">
{{ $row->new_quantity }}
</td>


<td class="px-6 py-4">
{{ $row->date }}
</td>

<td class="px-6 py-4">
{{ $row->reference }}
</td>
</tr>
@endforeach
</tbody>
</table>
</div>

</x-filament-panels::page>