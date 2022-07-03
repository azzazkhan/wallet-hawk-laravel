@props(['label' => 'Unlabelled Accordion Item'])
@php
    $__content_id = CStr::id('accordion_body');
    $__label_id = CStr::id('accordion_body');
@endphp
<h2 id="{{ $__label_id }}">
    <button
        type="button"
        class="flex items-center justify-between w-full p-5 font-medium text-left text-gray-500 border border-b-0 border-gray-200 rounded-t-xl focus:ring-4 focus:ring-blue-200 dark:focus:ring-blue-800 dark:border-gray-700 dark:text-gray-400 hover:bg-blue-100 dark:hover:bg-gray-800"
        data-accordion-target="#{{ $__content_id }}"
        aria-expanded="true"
        aria-controls="{{ $__content_id }}"
    >
        <span>{{ $label }}</span>
        <svg
            data-accordion-icon
            class="w-6 h-6 rotate-180 shrink-0"
            fill="currentColor"
            viewBox="0 0 20 20"
            xmlns="http://www.w3.org/2000/svg">
                <path
                    fill-rule="evenodd"
                    d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z"
                    clip-rule="evenodd"
                ></path>
            </svg>
    </button>
</h2>
<div
    id="{{ $__content_id }}"
    class="hidden"
    aria-labelledby="{{ $__label_id }}"
>
    <div class="py-5 border-b border-gray-200 dark:border-gray-700">
        {{ $slot }}
    </div>
</div>
