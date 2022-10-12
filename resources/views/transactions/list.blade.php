<x-app>
    <x-layout.page>
        <x-forms.search class="md:w-[400px] mr-auto flex-start" small />

        @if (request()->has('schema') && strtolower(request()->query('schema')) == 'erc20')
            {{-- <livewire:etherscan-table /> --}}
            <div id="root"></div>
        @else
            <livewire:opensea-transactions-view />
        @endif

    </x-layout.page>
</x-app>
