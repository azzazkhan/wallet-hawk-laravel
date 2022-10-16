<header class="flex items-center justify-between h-20 px-4 bg-white shadow-lg space-x-28 lg:pl-28 lg:pr-36 shadow-gray-300">
    <!-- Site logo -->
    <a href="/" class="inline-flex items-center">
        <img
            src="{{ asset('assets/img/logo.png') }}"
            class="h-10"
            alt="{{ config('app.name') }}"
        />
    </a>

    <!-- Info links -->
    <div class="flex items-center justify-end space-x-10">
        <a href="{{ route('faqs') }}">FAQ</a>

        <a
            href="https://twitter.com/wallethawk"
            class="flex items-center justify-center w-10 h-10 text-lg transition-colors transform rounded-lg hover:bg-gray-100 hover:text-blue-500"
            target="_blank"
        >
            <i class="fa-brands fa-twitter"></i>
        </a>
    </div>
</header>
