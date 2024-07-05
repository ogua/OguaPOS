<div>
    <x-filament::section
        icon="heroicon-m-truck"
        icon-size="md"
    >
        <x-slot name="heading">
            {{ $this->record->reference_number }}
        </x-slot>

        <form wire:submit="updatepayment">

            <div class="flex w-full gap-x-4">
            <div class="w-1/2">   
            <label for="">Received Amount</label>
            <x-filament::input.wrapper :valid="! $errors->has('received')">
            <x-filament::input 
            type="text"
            wire:model="received" wire:keyup="calculatechange"
            />
            </x-filament::input.wrapper>
            </div> 
            <div class="w-1/2"> 
                <label for="">Paying Amount</label>
                    <x-filament::input.wrapper :valid="! $errors->has('paying')">
                    <x-filament::input 
                    type="text"
                    wire:model="paying" wire:keyup="calculatechange" readonly
                    />
                    </x-filament::input.wrapper>
            </div>

                <x-filament::input 
                type="hidden"
                wire:model="change"
                />

            </div>

            <div class="flex gap-x-4" style="margin-top: 20px;">

            <div class="w-1/2"> 
            <label for="">Paying By</label>
            <x-filament::input.wrapper :valid="! $errors->has('paidby')">
            <x-filament::input.select wire:model="paidby" wire:change="changepayment">
            <option></option>
            <option>CASH</option>
            <option>PAYPAL</option>
            <option>CHEQUE</option>
            <option>GIFT CARD</option>
            <option>CREDIT CARD</option>
            <option>DRAFT</option>
            <option>BANK TRANSFER</option>
            </x-filament::input.select>
            </x-filament::input.wrapper>
            </div>

            @if ($this->paidby == "BANK TRANSFER")

            <div class="w-1/2"> 
                <label for="">Bank name</label>
                <x-filament::input.wrapper :valid="! $errors->has('bankname')" readonly>
                <x-filament::input 
                type="text"
                wire:model="bankname"
                />
                </x-filament::input.wrapper>
            </div>

            <div class="w-1/2"> 
                <label for="">Account number</label>
                <x-filament::input.wrapper :valid="! $errors->has('accountnumber')" readonly>
                <x-filament::input 
                type="text"
                wire:model="accountnumber"
                />
                </x-filament::input.wrapper>
            </div>

            @endif

            @if ($this->paidby == "CHEQUE")
                <div class="w-1/2">   
                    <label for="">Cheque number</label>
                    <x-filament::input.wrapper :valid="! $errors->has('cheque_no')">
                    <x-filament::input 
                    type="text"
                    wire:model="cheque_no"
                    />
                    </x-filament::input.wrapper>
                </div>
            @endif

            </div>

            @if ($this->paidby == "GIFT CARD")
                <div class="w-full" style="margin-top: 25px;">

                <x-filament::input
                    type="hidden"
                    wire:model="gift_card_id"
                />

                    <x-filament::input.wrapper :valid="! $errors->has('gift_card')">
                        <x-slot name="prefix">
                            {{ $gift_card_id ? 'Card Available' : 'Enter Card Code'}}
                        </x-slot>
                    
                        <x-filament::input
                            type="text"
                            wire:model="gift_card"
                        />
                    
                        <x-slot name="suffix">
                        <x-filament::button color="success" size="xs" icon="{{ $gift_card_id ? 'heroicon-o-check-circle' : 'heroicon-o-credit-card'}}" wire:click="checkgiftcard">
                            CHECK CARD
                            </x-filament::button>
                        </x-slot>
                </x-filament::input.wrapper>



                </div>
            @endif

            @if ($this->paidby == "PAYPAL")
                <div class="w-full">

                </div>
            @endif

            <div class="w-1/2" style="margin-top: 20px;">
            <label for="">Payment Account</label>
            <x-filament::input.wrapper :valid="! $errors->has('account_id')">
            <x-filament::input.select wire:model="account_id">
            <option></option>
            @foreach ($this->payingaccount as $account)
                <option value="{{ $account->id }}">{{ $account->account_name }}</option>
            @endforeach
            </x-filament::input.select>
            </x-filament::input.wrapper>
            </div>


            <div class="w-full" style="margin-top: 20px;">   
            <label for="">Payment Note</label>
            <x-filament::input.wrapper :valid="! $errors->has('payment_note')">
                <textarea 
                    wire:model="payment_note" 
                    class="block w-full border-none bg-transparent px-3 py-1.5 text-base text-gray-950 placeholder:text-gray-400 focus:ring-0 disabled:text-gray-500 disabled:[-webkit-text-fill-color:theme(colors.gray.500)] disabled:placeholder:[-webkit-text-fill-color:theme(colors.gray.400)] dark:text-white dark:placeholder:text-gray-500 dark:disabled:text-gray-400 dark:disabled:[-webkit-text-fill-color:theme(colors.gray.400)] dark:disabled:placeholder:[-webkit-text-fill-color:theme(colors.gray.500)] sm:text-sm sm:leading-6"
                    rows="5"
                ></textarea>
            </x-filament::input.wrapper>
            </div>


            <div class="flex gap-x-4" style="margin-top: 20px;">
            <div class="w-1/2">   
            <label for="">Sale Note</label>
            <x-filament::input.wrapper :valid="! $errors->has('sale_note')">
            <textarea 
                    wire:model="sale_note" 
                    class="block w-full border-none bg-transparent px-3 py-1.5 text-base text-gray-950 placeholder:text-gray-400 focus:ring-0 disabled:text-gray-500 disabled:[-webkit-text-fill-color:theme(colors.gray.500)] disabled:placeholder:[-webkit-text-fill-color:theme(colors.gray.400)] dark:text-white dark:placeholder:text-gray-500 dark:disabled:text-gray-400 dark:disabled:[-webkit-text-fill-color:theme(colors.gray.400)] dark:disabled:placeholder:[-webkit-text-fill-color:theme(colors.gray.500)] sm:text-sm sm:leading-6"
                    rows="5"
                ></textarea>
            </x-filament::input.wrapper>
            </div> 
            <div class="w-1/2"> 
            <label for="">Staff Note</label>
            <x-filament::input.wrapper :valid="! $errors->has('staffnote')">
            <textarea 
                    wire:model="staffnote" 
                    class="block w-full border-none bg-transparent px-3 py-1.5 text-base text-gray-950 placeholder:text-gray-400 focus:ring-0 disabled:text-gray-500 disabled:[-webkit-text-fill-color:theme(colors.gray.500)] disabled:placeholder:[-webkit-text-fill-color:theme(colors.gray.400)] dark:text-white dark:placeholder:text-gray-500 dark:disabled:text-gray-400 dark:disabled:[-webkit-text-fill-color:theme(colors.gray.400)] dark:disabled:placeholder:[-webkit-text-fill-color:theme(colors.gray.500)] sm:text-sm sm:leading-6"
                    rows="5"
                ></textarea>
            </x-filament::input.wrapper>
            </div> 
            </div>

            <x-filament::button wire:click="updatepayment" style="margin-top: 20px;">
                Save Payment
            </x-filament::button>


        </form>

    </x-filament::section>
</div>