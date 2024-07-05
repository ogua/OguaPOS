<form wire:submit="dispatchfees">
    
    {{ $this->form }}

    <div style="margin-top:15px;"></div>

    <x-filament::button type="submit">
        <span wire:loading.remove> RECORD SALES </span> <span wire:loading wire:target="dispatchfees">Please wait...</span>
    </x-filament::button>

</form>
