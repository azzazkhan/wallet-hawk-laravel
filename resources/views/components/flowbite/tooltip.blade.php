@props(['id' => CStr::id()])
<div
    id="{{ $id }}"
    role="tooltip"
    class="absolute z-10 invisible inline-block px-3 py-2 text-sm font-medium text-white transition-opacity duration-500 bg-gray-900 rounded-lg shadow-sm opacity-0 tooltip dark:bg-gray-700"
>
    <span class="text-xs">{{ $slot }}</span>
    <div class="tooltip-arrow" data-popper-arrow></div>
</div>
