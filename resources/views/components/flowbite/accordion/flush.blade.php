<div
    id="{{ CStr::id('unlabelled_accordion') }}"
    data-accordion="collapse"
    {{
        $attributes->merge([
            "data-active-classes"   => "bg-white dark:bg-gray-900 text-gray-900 dark:text-white",
            "data-inactive-classes" => "text-gray-500 dark:text-gray-400 hover:bg-blue-100",
        ])
    }}

>
    {{ $slot }}
</div>
