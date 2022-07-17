@props(['tooltip' => null, 'classes' => '', 'icon' => null])
@php
    $__id = CStr::id('pill_item');
    $__classes = "flex items-center h-8 px-3 text-sm font-medium transition-colors bg-transparent rounded-full cursor-pointer select-none";
@endphp

@if (CStr::isValidString($tooltip))
    <x-flowbite.tooltip id="{{ $__id }}">
        {{ $tooltip }}
    </x-flowbite.tooltip>
@endif

<span
    {{
        $attributes->merge([
            'class' => sprintf('%s %s', $__classes,  $classes ?: 'hover:bg-black hover:text-white')
        ])
    }}
    @if(CStr::isValidString($tooltip)) data-tooltip-target="{{ $__id }}" @endif
>
    @if (CStr::isValidString($icon))
        <i class="inline-block h-4 mr-2 {{ $icon }}" aria-hidden="true"></i>
    @endif

    {{ $slot }}
</span>
