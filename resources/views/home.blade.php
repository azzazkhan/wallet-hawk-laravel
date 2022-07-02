<x-app>
    <x-layout.page>
        <div class="flex-col max-w-md mx-auto space-y-4 text-center md:space-y-6 md:max-w-lg lg:max-w-xl">
            <h1 class="text-4xl font-bold md:text-5xl">Start Exploring</h1>
            <p class="text-lg font-light md:text-xl md:leading-relaxed">
                Enter an Ethereum address below to look at transaction history in a human readable format.
            </p>
            <x-forms.search class="pt-4 shadow-2xl lg:pt-0" />
            <img
                src="{{ asset('assets/img/hawk.png') }}"
                class="mx-auto w-auto h-[250px]"
                alt="Hawk Image"
            />
        </div>
    </x-layout.page>
    <x-slot name="styles">
        <link rel="stylesheet" href="{{ asset('dist/css/notus.min.css') }}" />
    </x-slot>
</x-app>
