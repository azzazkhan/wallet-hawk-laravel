<x-app>
    <div class="px-4 py-14 lg:px-28">
        <div class="flex-col max-w-md mx-auto space-y-4 text-center md:space-y-6 md:max-w-lg lg:max-w-xl">
            <h1 class="text-4xl font-bold md:text-5xl">Start Exploring</h1>
            <p class="text-lg font-light md:text-xl md:leading-relaxed">
                Enter an Ethereum address below to look at transaction history in a human readable format.
            </p>
            <form action="{{ url('/transactions') }}" class="flex pt-4 shadow-2xl lg:pt-0">
                <input
                    type="text"
                    name="wallet"
                    class="flex-1 h-12 px-3 font-medium border-none shadow w-96 rounded-l-md"
                    placeholder="Enter a wallet address here"
                />
                <button type="submit" role="button" class="flex items-center justify-center w-16 h-12 text-white transition-colors bg-blue-500 shadow-md cursor-pointer rounded-r-md hover:bg-blue-600">
                    <i class="text-xl fas fa-search" aria-label="Search"></i>
                </button>
            </form>
            <img
                src="{{ asset('assets/img/hawk.png') }}"
                class="mx-auto w-auto h-[250px]"
                alt="Hawk Image"
            />
        </div>
    </div>
    <x-slot name="styles">
        <link rel="stylesheet" href="{{ asset('dist/css/notus.min.css') }}" />
    </x-slot>
</x-app>
