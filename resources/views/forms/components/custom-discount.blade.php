<div x-data="{ state: $wire.$entangle('{{ $getStatePath() }}') }">
    <p> {{ $getAction('SetDiscount') }}</p>
</div>