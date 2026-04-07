@props(['padding' => 'p-6', 'hover' => false])

<div {{ $attributes->merge(['class' => 'bg-white rounded-2xl border border-[#E2E8F0] ' . $padding . ($hover ? ' hover:shadow-md transition-shadow duration-200 cursor-pointer' : '')]) }}
     style="box-shadow: 0 4px 20px rgba(0,0,0,0.05)">
    {{ $slot }}
</div>
