<div class="flex rounded-md relative">
    <div class="flex">
        <div class="px-2 mt-1">
            <div class="h-10 w-10">
                @if ($record['product_image'])
                    <img src="{{ url('/storage/' .$record['product_image']) }}" alt="" role="img" class="h-full w-full rounded-full overflow-hidden shadow object-cover" />
                @else
                <img src="{{ URL::to('images/no-image.jpeg') }}" alt="no image"  role="img" class="h-full w-full rounded-full overflow-hidden shadow object-cover"/>
                @endif
                
            </div>
        </div>
 
        <div class="flex flex-col justify-center pl-3 py-2">
            <p class="text-sm font-bold pb-1">{{ $record['product_name'] }}</p>
        </div>
    </div>
</div>