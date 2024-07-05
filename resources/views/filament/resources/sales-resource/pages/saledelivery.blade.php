<x-filament-panels::page>

    <x-filament::section
    icon="heroicon-m-truck"
    icon-size="md"
>
    <x-slot name="heading">
        {{ $this->data->reference_number }}
    </x-slot>

    <form wire:submit="saveshipping">
 
        <div class="flex gap-x-4" style="margin-bottom: 20px;">
            <div class="w-1/2">
                <label for="">Shipping Details</label>
                <x-filament::input.wrapper :valid="! $errors->has('shipping_details')">
                    <textarea 
                        wire:model="shipping_details" 
                        class="block w-full border-none bg-transparent px-3 py-1.5 text-base text-gray-950 placeholder:text-gray-400 focus:ring-0 disabled:text-gray-500 disabled:[-webkit-text-fill-color:theme(colors.gray.500)] disabled:placeholder:[-webkit-text-fill-color:theme(colors.gray.400)] dark:text-white dark:placeholder:text-gray-500 dark:disabled:text-gray-400 dark:disabled:[-webkit-text-fill-color:theme(colors.gray.400)] dark:disabled:placeholder:[-webkit-text-fill-color:theme(colors.gray.500)] sm:text-sm sm:leading-6"
                        rows="5"
                    ></textarea>
                </x-filament::input.wrapper>
            </div>

            <div class="w-1/2">
                <label for="">Shipping Address</label>
                <x-filament::input.wrapper :valid="! $errors->has('shipping_address')">
                    <textarea 
                        wire:model="shipping_address" 
                        class="block w-full border-none bg-transparent px-3 py-1.5 text-base text-gray-950 placeholder:text-gray-400 focus:ring-0 disabled:text-gray-500 disabled:[-webkit-text-fill-color:theme(colors.gray.500)] disabled:placeholder:[-webkit-text-fill-color:theme(colors.gray.400)] dark:text-white dark:placeholder:text-gray-500 dark:disabled:text-gray-400 dark:disabled:[-webkit-text-fill-color:theme(colors.gray.400)] dark:disabled:placeholder:[-webkit-text-fill-color:theme(colors.gray.500)] sm:text-sm sm:leading-6"
                        rows="5"
                    ></textarea>
                </x-filament::input.wrapper>
            </div>
        </div>

        <div
        class="w-full" style="margin-top: 20px;"
        x-data="{ uploading: false, progress: 0 }"
        x-on:livewire-upload-start="uploading = true"
        x-on:livewire-upload-finish="uploading = false"
        x-on:livewire-upload-cancel="uploading = false"
        x-on:livewire-upload-error="uploading = false"
        x-on:livewire-upload-progress="progress = $event.detail.progress"
    >
        <!-- File Input -->

        <label for="">Shipping Documents</label>
             @if ($shipping_file) 
                <img src="{{ $shipping_file->temporaryUrl() }}" style="margin-bottom: 20px;">
            @endif
            <x-filament::input.wrapper>
                <input type="file" wire:model="shipping_file">
            </x-filament::input.wrapper>
 
        <!-- Progress Bar -->
        <div x-show="uploading" class="w-full" style="margin-top: 20px;">
            <progress max="100" x-bind:value="progress"></progress>
        </div>
    </div>


        <div class="flex gap-x-4" style="margin-top: 20px;">
            <div class="w-1/2">
                <label for="">Shipping Status </label>
                <x-filament::input.wrapper :valid="! $errors->has('shipping_status')">
                    <x-filament::input.select wire:model="shipping_status">
                        <option></option>
                        <option value="Ordered">Ordered</option>
                        <option value="Packed">Packed</option>
                        <option value="Shipped">Shipped</option>
                        <option value="Delivered">Delivered</option>
                        <option value="Cancelled">Cancelled</option>
                    </x-filament::input.select>
                </x-filament::input.wrapper>
            </div>

            <div class="w-1/2">
                <label for="">Delivered To</label>
                <x-filament::input.wrapper :valid="! $errors->has('delivered_to')">
                    <x-filament::input
                        type="text"
                        wire:model="delivered_to"
                    />
                </x-filament::input.wrapper>
            </div>
        </div>

        <div class="flex gap-x-4" style="margin-top: 20px;">
            <div class="w-1/2">
                <label for="">Expected Delivery Date</label>
                <x-filament::input.wrapper :valid="! $errors->has('expected')">
                    <x-filament::input
                        type="date"
                        wire:model="expected"
                    />
                </x-filament::input.wrapper>
            </div>

            <div class="w-1/2">
                <label for="">Delivered On</label>
                <x-filament::input.wrapper :valid="! $errors->has('deliveredon')">
                    <x-filament::input
                        type="date"
                        wire:model="deliveredon"
                    />
                </x-filament::input.wrapper>
            </div>
        </div>

        <div class="w-full" style="margin-top: 20px;">
            <label for="">Shipping note:</label>
            <x-filament::input.wrapper :valid="! $errors->has('shipping_note')">
                <textarea 
                    wire:model="shipping_note" 
                    class="block w-full border-none bg-transparent px-3 py-1.5 text-base text-gray-950 placeholder:text-gray-400 focus:ring-0 disabled:text-gray-500 disabled:[-webkit-text-fill-color:theme(colors.gray.500)] disabled:placeholder:[-webkit-text-fill-color:theme(colors.gray.400)] dark:text-white dark:placeholder:text-gray-500 dark:disabled:text-gray-400 dark:disabled:[-webkit-text-fill-color:theme(colors.gray.400)] dark:disabled:placeholder:[-webkit-text-fill-color:theme(colors.gray.500)] sm:text-sm sm:leading-6"
                    rows="5"
                ></textarea>
            </x-filament::input.wrapper>
        </div>


        <x-filament::button icon="heroicon-m-truck" wire:click="saveshipping" outlined  style="margin-top: 20px;">
           Save
        </x-filament::button>

    </form>


</x-filament::section>
 

    <x-filament-tables::container>

    <x-filament-tables::table>
        <x-slot:header>
            <x-filament-tables::header-cell>
                Date
            </x-filament-tables::header-cell>
            <x-filament-tables::header-cell>
                Action
            </x-filament-tables::header-cell>
            <x-filament-tables::header-cell>
                By
            </x-filament-tables::header-cell>
            <x-filament-tables::header-cell>
                Note
            </x-filament-tables::header-cell>
            <x-filament-tables::header-cell>
                Status
            </x-filament-tables::header-cell>
        </x-slot:header>
            @foreach ($this->delivery?->deliveryhistort ?? [] as $delivery)
            <x-filament-tables::row>
                <x-filament-tables::cell>
                    {{ date('Y-m-d', strtotime($delivery->date)) }}
                </x-filament-tables::cell>

                <x-filament-tables::cell>
                    {{ $delivery->action }}
                </x-filament-tables::cell>

                <x-filament-tables::cell>
                    {{ $delivery->user->name ?? "" }}
                </x-filament-tables::cell>


                <x-filament-tables::cell>
                    {{ $delivery->note }}
                </x-filament-tables::cell>


                <x-filament-tables::cell>
                    {{ $delivery->from_statues }} 
                    @if ($delivery->to_statues)
                        - {{ $delivery->to_statues  }}
                    @endif
                </x-filament-tables::cell>
            </x-filament-tables::row>
            @endforeach
    </x-filament-tables::table>
</x-filament-tables::container>

</x-filament-panels::page>
