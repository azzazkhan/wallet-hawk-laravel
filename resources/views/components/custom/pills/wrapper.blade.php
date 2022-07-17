@props(['label' => null, 'noPush' => false])
<div {{ $attributes->merge(['class' => 'flex flex-col space-y-2 lg:flex-row py-2 lg:space-y-0 lg:space-x-2']) }}>
    @if (CStr::isValidString($label))
        <span class="mt-1.5 font-medium">{{ $label }}</span>
    @endif
    @php
        $__classes = CStr::classes([
            'flex flex-wrap flex-1 space-x-2 space-y-2 -ml-3 lg:ml-0' => true,
            '!-ml-3' => $noPush
        ]);
    @endphp
    <div class="{{ $__classes }}">
        {{ $slot }}
    </div>
</div>
