@props(['zebra' => false])

<tr {{ $attributes->merge(['class' => 'border-b border-[#F1F5F9] transition-colors duration-75 hover:bg-[#F8FAFC]' . ($zebra ? ' even:bg-[#FAFBFC]' : '')]) }}>
    {{ $slot }}
</tr>
