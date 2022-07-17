@props(['label' => null, 'noPush' => false])
<div {{ $attributes->merge(['class' => 'flex py-2 space-x-2']) }}>
    @if (CStr::isValidString($label))
        <span class="mt-1.5 font-medium">{{ $label }}</span>
    @endif
    @php
        $__classes = CStr::classes([
            'flex flex-wrap flex-1 space-x-2' => true,
            '-ml-3' => $noPush
        ]);
    @endphp
    <div class="{{ $__classes }}">
        {{ $slot }}
    </div>
</div>
