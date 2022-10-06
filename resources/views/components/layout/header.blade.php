<header class="flex items-center justify-between h-20 px-4 bg-white shadow-lg space-x-28 lg:pl-28 lg:pr-36 shadow-gray-300">
    <!-- Site logo -->
    <a href="/" class="inline-flex items-center">
        <img
            src="{{ asset('assets/img/logo.png') }}"
            class="h-10"
            alt="{{ config('app.name') }}"
        />
    </a>

    <!-- Available crypto items -->
    <div class="items-center justify-end flex-1 hidden space-x-3 md:flex">
        {{-- @foreach ($cryptos as $crypto)
            @php
                $__available = CStr::isValidBoolean($crypto["available"]) && $crypto["available"];
                $__url = CStr::isValidString($crypto["url"]) ? $crypto["url"] : "#";
                $__tooltip_id = CStr::id('tooltip');
            @endphp

            @if ($__available)
                <a
                    href="{{ $__url }}"
                    class="inline-flex items-center h-8 text-sm text-white bg-blue-500 px-7"
                >
                    {{ $crypto['name'] }}
                </a>
            @endif
            {{-- <a
                class="inline-flex items-center h-8 text-sm text-white px-7 {{ $__available ? "bg-blue-500" : "bg-blue-400" }}"
                href="{{ $__url }}"
                @unless ($__available) data-tooltip-target="{{ $__tooltip_id }}" @endunless
                data-tooltip-target="tooltip-dark"
            >
                {{ $crypto['name'] }}
            </a>
            @unless($__available)
                <x-flowbite.tooltip id="{{ $__tooltip_id }}">Coming Soon</x-flowbite.tooltip>
            @endunless -}}
        @endforeach --}}
    </div>

    <!-- Info links -->
    <div class="flex items-center justify-end space-x-4">
        <a href="{{ route('faqs') }}">FAQ</a>

        <a
            href="https://twitter.com/wallethawk"
            class="flex items-center justify-center w-10 h-10 text-lg transition-colors transform rounded-lg hover:bg-gray-100 hover:text-blue-500"
        >
            <i class="fa-brands fa-twitter"></i>
        </a>
    </div>
</header>
