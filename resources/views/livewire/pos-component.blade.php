<div>
<div class="rounded w-full shadow-md top-0 z-50 bg-red-700 text-white">
<div class="flex justify-between items-center p-1">

<div class="w-1/4">
<p class="ml-3">Location:  {{ $this->pos->warehouse?->name ?? "" }}</p>
</div>

<div class="w-2/4 pr-5">
<div class="flex justify-end space-x-3">

<div class="btn">
<x-filament::button color="success" wire:click="opencashregister" size="xs" icon="heroicon-o-check-circle" tooltip="Register details" outline>
Register details
</x-filament::button>
</div>

<div class="btn">
<x-filament::button color="danger" wire:click="closecashregister" size="xs" icon="heroicon-o-x-circle" tooltip="Close register" outline>
Close register
</x-filament::button>
</div>

<div class="btn">
<?php
use Filament\Facades\Filament;
$panelid = Filament::getCurrentPanel()->getId();
?>
<x-filament::button color="success" size="xs" href="/{{ auth()->user()->role }}" tag="a" icon="heroicon-o-backspace" tooltip="Dashboard">
Dashboard
</x-filament::button>

</div>
</div>
</div>
</div>
</div>
<div class="flex space-x-3 mt-5">
<div class="w-2/3">
<form wire:submit="create">
{{ $this->form }}

<x-filament::modal id="pay-with-cash" width="2xl" slide-over sticky-header class="z-50">
<x-slot name="heading">
Finalise Payment
</x-slot>

<div class="flex space-x-3">
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

<div class="w-1/2"> 
<label for="">Change</label>
<x-filament::input.wrapper :valid="! $errors->has('change')" readonly>
<x-filament::input 
type="text"
wire:model="change"
/>
</x-filament::input.wrapper>
</div> 



</div>

<div class="flex space-x-3">

<div class="w-1/2"> 
<label for="">Paying By</label>
<x-filament::input.wrapper :valid="! $errors->has('paidby')">
<x-filament::input.select wire:model="paidby">
<option>CASH</option>
<option>PAYPAL</option>
<option>CREDIT SALES</option>
<option>CHEQUE</option>
<option>GIFT CARD</option>
<option>CREDIT CARD</option>
<option>DRAFT</option>
<option>BANK TRANSFER</option>
</x-filament::input.select>
</x-filament::input.wrapper>
</div>

<div class="w-1/2"> 
<label for="">Payment Account</label>
<x-filament::input.wrapper :valid="! $errors->has('account_id')">
<x-filament::input.select wire:model="account_id">
<option></option>
@foreach ($this->payingaccount as $payment)
<option value="{{  $payment->id }}">{{  $payment->account_name }}</option>
@endforeach
</x-filament::input.select>
</x-filament::input.wrapper>
</div>

@if ($paidby == "BANK TRANSFER")

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

@if ($paidby == "CHEQUE")
<div class="w-full">   
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

@if ($paidby == "GIFT CARD")
<div class="w-full">

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

@if ($paidby == "PAYPAL")
<div class="w-full">

<x-filament::input.wrapper :valid="! $errors->has('paypal_email')">
<x-slot name="prefix">
Paypal email
</x-slot>

<x-filament::input
type="text"
wire:model="paypal_email"
/>

<x-slot name="suffix">
<x-filament::button color="success" size="xs" icon="heroicon-o-credit-card" wire:click="checkgiftcard">
CHECK OUT
</x-filament::button>
</x-slot>
</x-filament::input.wrapper>



</div>
@endif


<div class="w-full">   
<label for="">Payment Note</label>
<x-filament::input.wrapper :valid="! $errors->has('payment_note')">
<textarea 
wire:model="payment_note" 
class="block w-full border-none bg-transparent px-3 py-1.5 text-base text-gray-950 placeholder:text-gray-400 focus:ring-0 disabled:text-gray-500 disabled:[-webkit-text-fill-color:theme(colors.gray.500)] disabled:placeholder:[-webkit-text-fill-color:theme(colors.gray.400)] dark:text-white dark:placeholder:text-gray-500 dark:disabled:text-gray-400 dark:disabled:[-webkit-text-fill-color:theme(colors.gray.400)] dark:disabled:placeholder:[-webkit-text-fill-color:theme(colors.gray.500)] sm:text-sm sm:leading-6"
rows="5"
></textarea>
</x-filament::input.wrapper>
</div>


<div class="flex space-x-3">
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


<x-slot name="footer">
<x-filament::button wire:click="create">
PAID WITH {{ $paidby }}
</x-filament::button>
</x-slot>
</x-filament::modal>


<!-- OPENING WITH CREDIT SALES MODEL -->

<x-filament::modal id="pay-with-create-sales" width="2xl" slide-over sticky-header class="z-50">
<x-slot name="heading">
Finalise Payment
</x-slot>


<x-filament::input
type="hidden"
wire:model="received"
/> 

<x-filament::input
type="hidden"
wire:model="paying"
/>

<x-filament::input
type="hidden"
wire:model="change"
/>


<x-filament::input
type="hidden"
wire:model="paidby"
/>

<x-filament::input
type="hidden"
wire:model="payment_note"
/>

<x-filament::input
type="hidden"
wire:model="sale_note"
/>

<x-filament::input
type="hidden"
wire:model="staffnote"
/>
<p class="text-center">
{{ $paidby }} <br>
Are you sure you would like to do this?
</p>

<x-slot name="footer">
<x-filament::button wire:click="createcreditsale">
PAID WITH {{ $paidby }}
</x-filament::button>
</x-slot>
</x-filament::modal>

<!-- OPENING WITH CREDIT SALES MODEL END-->


<x-filament::modal id="variation-items" width="3xl">
<x-slot name="heading">
Choose Item
</x-slot>

<section class="grid grid-cols-3 lg:grid-cols-3 md:grid-cols-2 gap-x-3 gap-y-4 mb-20">

@foreach ($variationitems as $key => $product)
<!--   ✅ Product card 1 - Starts Here 👇 -->
<div class="bg-white shadow-md rounded-xl duration-500 hover:scale-105 hover:shadow-xl">

<a href="#" wire:click="addtoitems('{{ $key }}','{{ $product['id'] }}', 'pass')">

<x-filament::loading-indicator wire:loading wire:target="addtoitems('{{ $key }}','{{ $product['id'] }}', 'pass')" class="h-10 w-10 absolute top-16 left-12" />

@if ($product['product_image'])

<img src="{{ asset('storage') }}/{{ $product['product_image'] }}"
alt="{{ $product['product_name'] }}" class="w-23 h-24 object-cover rounded-t-xl m-auto" />
@else

<img src="/images/no-image.jpeg"
alt="{{ $product['product_name'] }}" class="w-23 h-24 object-cover rounded-t-xl m-auto" />

@endif

<div class="px-4 py-3">
<span class="text-gray-400 mr-3 uppercase text-xs">{{ $product['categoryname'] ?? '' }}</span>
<p class="text-lg font-bold text-black text-center block capitalize">{{ $product['product_name'] }}</p>
<!-- <div class="flex items-center">
<p class="text-lg font-semibold text-black cursor-auto my-3">$149</p>
<del>
<p class="text-sm text-gray-600 cursor-auto ml-2">$199</p>
</del>
<div class="ml-auto"><svg xmlns="http://www.w3.org/2000/svg" width="20" height="20"
fill="currentColor" class="bi bi-bag-plus" viewBox="0 0 16 16">
<path fill-rule="evenodd"
d="M8 7.5a.5.5 0 0 1 .5.5v1.5H10a.5.5 0 0 1 0 1H8.5V12a.5.5 0 0 1-1 0v-1.5H6a.5.5 0 0 1 0-1h1.5V8a.5.5 0 0 1 .5-.5z" />
<path
d="M8 1a2.5 2.5 0 0 1 2.5 2.5V4h-5v-.5A2.5 2.5 0 0 1 8 1zm3.5 3v-.5a3.5 3.5 0 1 0-7 0V4H1v10a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2V4h-3.5zM2 5h12v9a1 1 0 0 1-1 1H3a1 1 0 0 1-1-1V5z" />
</svg></div>
</div> -->
</div>
</a>
</div>
<!--   🛑 Product card 1 - Ends Here  -->
@endforeach


</section>

</x-filament::modal>


<x-filament-actions::actions />
</form>

</div>
<div class="w-1/3 h-screen border-gray-500 overflow-y-auto relative">
<div class="absolute p-5 bg-white left-0 right-0">
<div class="flex space-x-3">
<div class="w-1/2"> 
<label for="">Category</label>
<x-filament::input.wrapper :valid="! $errors->has('filtercategory')">
<x-filament::input.select wire:model="filtercategory" wire:change="filterProducts">
<option></option>
@foreach ($this->category as $category)
<option value="{{ $category->id }}">{{ $category->name }}</option>
@endforeach
</x-filament::input.select>
</x-filament::input.wrapper>
</div> 
<div class="w-1/2"> 
<label for="">Brand</label>
<x-filament::input.wrapper :valid="! $errors->has('filterbrand')">
<x-filament::input.select wire:model="filterbrand" wire:change="filterProducts">
<option></option>
@foreach ($this->brand as $brand)
<option value="{{ $brand->id }}">{{ $brand->name }}</option>
@endforeach
</x-filament::input.select>
</x-filament::input.wrapper>
</div> 
</div>
</div>

<!-- SHOW POS SIDE IMAGES -->
@include('livewire.pos-side-image',['allproducts' => $allproducts])
<!-- END SHOW POS SIDE IMAGES -->



<div class="fixed rounded w-full bg-white p-3 shadow-md bottom-0">
<div class="">
<div class="flex space-x-3 justify-left">

<x-filament::button color="success" icon="heroicon-o-credit-card" wire:click="openpaywithcashmodal('CREDIT CARD')" outlined> 
CREDIT CARD
</x-filament::button>

<x-filament::button color="warning" icon="heroicon-o-currency-dollar" wire:click="openpaywithcreditsalesmodal('CREDIT SALES')">
CREDIT SALES
</x-filament::button>

<x-filament::button color="info" icon="heroicon-o-banknotes" wire:click="openpaywithcashmodal('CASH')">
CASH
</x-filament::button>

<x-filament::button color="danger" icon="heroicon-o-newspaper" outlined wire:click="openpaywithcashmodal('CHEQUE')" >
CHEQUE
</x-filament::button>

<x-filament::button color="success" icon="heroicon-o-credit-card" wire:click="openpaywithcashmodal('GIFT CARD')">
GIFT CARD
</x-filament::button>


<x-filament::button color="info" icon="heroicon-o-newspaper" outlined wire:click="openpaywithcashmodal('DRAFT')"> 
DRAFT
</x-filament::button>

<x-filament::button color="success" icon="heroicon-o-home-modern" wire:click="openpaywithcashmodal('BANK TRANSFER')"> 
BANK TRANSFER
</x-filament::button>

<x-filament::button color="danger" icon="heroicon-o-x-circle"  href="/pos"
tag="a" outlined> 
CANCEL
</x-filament::button>
</div>
</div>
</div>

<x-filament-actions::modals />

<!-- Cash register model -->
<x-filament::modal id="cash-register-details" width="4xl" sticky-header class="z-50">
<x-slot name="heading">
Register Details From {{ date('d-M-Y', strtotime($this->cash_register->created_at)) }} To {{ date('d-M-Y', strtotime(now())) }}
</x-slot>
<x-filament-tables::container>
<x-filament-tables::table>
<x-slot:header>
<x-filament-tables::header-cell>
Payment Method
</x-filament-tables::header-cell>
<x-filament-tables::header-cell>
Sales
</x-filament-tables::header-cell>
<x-filament-tables::header-cell>
Expenses
</x-filament-tables::header-cell>
</x-slot:header>

@php
$total = 0;
$totalsale = 0;
@endphp

<x-filament-tables::row>
<x-filament-tables::cell>
Cash in hand:
</x-filament-tables::cell>
<x-filament-tables::cell>
@php
$total += $this->cash_register->cash_in_hand ?? 0;
@endphp
GHC {{ number_format($this->cash_register->cash_in_hand ?? 0,2) }}
</x-filament-tables::cell>
<x-filament-tables::cell>
---
</x-filament-tables::cell>
</x-filament-tables::row>

<x-filament-tables::row>
<x-filament-tables::cell>
Cash payment:
</x-filament-tables::cell>
<x-filament-tables::cell>
@if (isset($this->salesSummary['CASH']))
@php
$total += $this->salesSummary['CASH'];
$totalsale += $this->salesSummary['CASH'];
@endphp
GHC {{ number_format($this->salesSummary['CASH'],2) }}</p>
@else
GHC {{ number_format(0,2) }}
@endif
</x-filament-tables::cell>
<x-filament-tables::cell>
GHC {{ number_format(0,2) }}
</x-filament-tables::cell>
</x-filament-tables::row>

<x-filament-tables::row>
<x-filament-tables::cell>
Cheque payment:
</x-filament-tables::cell>
<x-filament-tables::cell>
@if (isset($this->salesSummary['CHEQUE']))
@php
$total += $this->salesSummary['CHEQUE'];
$totalsale += $this->salesSummary['CHEQUE'];
@endphp
GHC {{ number_format($this->salesSummary['CHEQUE'],2) }}</p>
@else
GHC {{ number_format(0,2) }}
@endif
</x-filament-tables::cell>
<x-filament-tables::cell>
GHC {{ number_format(0,2) }}
</x-filament-tables::cell>
</x-filament-tables::row>

<x-filament-tables::row>
<x-filament-tables::cell>
Paypal payment:
</x-filament-tables::cell>
<x-filament-tables::cell>
@if (isset($this->salesSummary['PAYPAL']))
@php
$total += $this->salesSummary['PAYPAL'];
$totalsale += $this->salesSummary['PAYPAL'];
@endphp
GHC {{ number_format($this->salesSummary['PAYPAL'],2) }}</p>
@else
GHC {{ number_format(0,2) }}
@endif
</x-filament-tables::cell>
<x-filament-tables::cell>
GHC {{ number_format(0,2) }}
</x-filament-tables::cell>
</x-filament-tables::row>

<x-filament-tables::row>
<x-filament-tables::cell>
Gift card payment:
    </x-filament-tables::cell>
    <x-filament-tables::cell>
    @if (isset($this->salesSummary['GIFT CARD']))
    @php
    $total += $this->salesSummary['GIFT CARD'];
    $totalsale += $this->salesSummary['GIFT CARD'];
    @endphp
    GHC {{ number_format($this->salesSummary['GIFT CARD'],2) }}</p>
    @else
    GHC {{ number_format(0,2) }}
    @endif
    </x-filament-tables::cell>
    <x-filament-tables::cell>
    GHC {{ number_format(0,2) }}
    </x-filament-tables::cell>
    </x-filament-tables::row>
    
    <x-filament-tables::row>
    <x-filament-tables::cell>
    Credit card payment:
    </x-filament-tables::cell>
    <x-filament-tables::cell>
    @if (isset($this->salesSummary['CREDIT CARD']))
    @php
    $total += $this->salesSummary['CREDIT CARD'];
    $totalsale += $this->salesSummary['CREDIT CARD'];
    @endphp
    GHC {{ number_format($this->salesSummary['CREDIT CARD'],2) }}</p>
    @else
    GHC {{ number_format(0,2) }}
    @endif
    </x-filament-tables::cell>
    <x-filament-tables::cell>
    GHC {{ number_format(0,2) }}
    </x-filament-tables::cell>
    </x-filament-tables::row>
    
    <x-filament-tables::row>
    <x-filament-tables::cell>
    Bank transfer payment:
    </x-filament-tables::cell>
    <x-filament-tables::cell>
    @if (isset($this->salesSummary['BANK TRANSFER']))
    @php
    $total += $this->salesSummary['BANK TRANSFER'];
    $totalsale += $this->salesSummary['BANK TRANSFER'];
    @endphp
    GHC {{ number_format($this->salesSummary['BANK TRANSFER'],2) }}</p>
    @else
    GHC {{ number_format(0,2) }}
    @endif
    </x-filament-tables::cell>
    <x-filament-tables::cell>
    GHC {{ number_format(0,2) }}
    </x-filament-tables::cell>
    </x-filament-tables::row>
    
    <x-filament-tables::row style="background-color: #5cb85c; color: white;">
    <x-filament-tables::cell>
    Total Sales:
    </x-filament-tables::cell>
    
    <x-filament-tables::cell colspan="2">
    GHC {{ number_format($total,2) }}
    </x-filament-tables::cell>
    </x-filament-tables::row>
    
    </x-filament-tables::table>
    
    <hr style="margin-top: 20px; margin-bottom: 20px;">
    
    <x-filament-tables::table>
    
    <x-filament-tables::row style="background-color: #337ab7; color: white;">
    <x-filament-tables::cell>
    Total Refund:
    </x-filament-tables::cell>
    <x-filament-tables::cell>
    GHC {{ number_format(0,2) }}
    </x-filament-tables::cell>
    </x-filament-tables::row>
    
    <x-filament-tables::row style="background-color: #5cb85c; color: white;">
    <x-filament-tables::cell>
    Total Payments:
    </x-filament-tables::cell>
    <x-filament-tables::cell>
    GHC {{ number_format($totalsale,2) }}
    </x-filament-tables::cell>
    </x-filament-tables::row>
    
    <x-filament-tables::row style="background-color: #c9302c; color: white;">
    <x-filament-tables::cell>
    Total Expenses:
    </x-filament-tables::cell>
    <x-filament-tables::cell>
    GHC {{ number_format(0,2) }}
    </x-filament-tables::cell>
    </x-filament-tables::row>
    </x-filament-tables::table>
    </x-filament-tables::container>
    
    <hr style="margin-top: 20px; margin-bottom: 20px;">
    
    <x-filament::section
    icon="heroicon-m-truck"
    icon-size="md"
    >
    <x-slot name="heading">
    Details of product sold
    </x-slot>
    
    <x-filament-tables::container>
    <x-filament-tables::table>
    <x-slot:header>
    <x-filament-tables::header-cell>
    Brand
    </x-filament-tables::header-cell>
    <x-filament-tables::header-cell>
    Quantity
    </x-filament-tables::header-cell>
    <x-filament-tables::header-cell>
    Total Amount
    </x-filament-tables::header-cell>
    </x-slot:header>
    @php
    $totqty = 0;
    $totpx = 0;
    @endphp
    
    @foreach ($this->salesbrandSummary ?? [] as $brand => $totalQty)
    <x-filament-tables::row>
    <x-filament-tables::cell>
    {{ $brand }}
    </x-filament-tables::cell>
    <x-filament-tables::cell>
    @php
    $tot = explode(",",$totalQty);
    $totqty += $tot[0];
    $totpx += $tot[1];
    @endphp
    {{ $tot[0] }}
    </x-filament-tables::cell>
    <x-filament-tables::cell>
    GHC {{ number_format($tot[1],2) }}
    </x-filament-tables::cell>
    </x-filament-tables::row>
    @endforeach
    
    <x-filament-tables::row style="background-color: red;color: white;"> 
    <x-filament-tables::cell>
    #
    </x-filament-tables::cell>
    <x-filament-tables::cell>
    {{ $totqty }}
    </x-filament-tables::cell>
    <x-filament-tables::cell>
    GHC {{ number_format($totpx,2) }}
    </x-filament-tables::cell>
    </x-filament-tables::row>
    </x-filament-tables::table>
    </x-filament-tables::container>
    </x-filament::section>
    
    <x-slot name="footer">
        <div class="flex gap-x-5">
            
            <a href="{{ route('print-cash-register', $this->cash_register->id) }}"
                target="_blank"
                style="--c-400:var(--success-400);--c-500:var(--success-500);--c-600:var(--success-600);" class="fi-btn relative grid-flow-col items-center justify-center font-semibold outline-none transition duration-75 focus-visible:ring-2 rounded-lg fi-color-custom fi-btn-color-success fi-color-success fi-size-md fi-btn-size-md gap-1.5 px-3 py-2 text-sm inline-grid shadow-sm bg-custom-600 text-white hover:bg-custom-500 focus-visible:ring-custom-500/50 dark:bg-custom-500 dark:hover:bg-custom-400 dark:focus-visible:ring-custom-400/50">
                Print
            </a>

            @if ($this->registertype == "close")

                <x-filament::button wire:click="closeregister" color="danger" icon="heroicon-o-x-circle" outline>
                    Close register
                </x-filament::button>
                
            @endif
        </div>
    </x-slot>
    </x-filament::modal>
    
    
    
    
    
    
    </div>