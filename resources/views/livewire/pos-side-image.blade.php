<section class="grid grid-cols-3 lg:grid-cols-3 md:grid-cols-2 gap-x-3 mt-40 mb-20 m-r-4 gap-y-4">

@foreach ($allproducts as $key => $product)
<!--   ✅ Product card 1 - Starts Here 👇 -->
<div class="bg-gray-50 shadow-md rounded-xl duration-500 hover:scale-105 hover:shadow-xl relative">

<a href="#" wire:click.prevent="addtoitems('{{ $key }}','{{ $product['id'] }}', 'check')">

<x-filament::loading-indicator class="h-10 w-10 absolute top-16 left-12" wire:loading wire:target="addtoitems('{{ $key }}','{{ $product['id'] }}', 'check')" />


    @if ($product['product_image'])

    <img src="{{ asset('storage') }}/{{ $product['product_image'] }}"
    alt="{{ $product['product_name'] }}" class="w-23 h-24 object-cover rounded-t-xl m-auto" />
    @else

    <img src="/images/no-image.jpeg"
    alt="{{ $product['product_name'] }}" class="w-24 h-24 object-cover rounded-t-xl m-auto" />

    @endif

<div class="px-4 py-3">
<span class="text-gray-400 mr-3 uppercase text-xs">{{ $product['categoryname'] ?? '' }}</span>
<p class="text-sm font-bold text-black text-left block capitalize">{{ $product['product_name'] }}</p>
</div>
</a>
</div>
<!--   🛑 Product card 1 - Ends Here  -->
@endforeach


</section>

</div>
</div>