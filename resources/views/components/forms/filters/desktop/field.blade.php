@props(['name', 'label' => null])
@php $id = CStr::id('filter_field') @endphp
<div class="flex items-center space-x-2">
    @if (CStr::isValidString($label))
        <label for="{{ $id }}" class="text-sm font-medium">{{ $label }}</label>
    @endif
    <select
        id="{{ $id }}"
        name="{{ $name }}"
        {{ $attributes->merge(['class' => 'h-10 text-sm bg-white border border-gray-200 rounded-md']) }}
    >
        {{ $slot }}
    </select>
</div>
