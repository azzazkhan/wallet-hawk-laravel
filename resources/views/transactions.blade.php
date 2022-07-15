<x-app>
    <x-layout.page>
        <x-forms.search class="md:w-[400px] mr-auto flex-start" small />

        <div class="flex flex-col mt-10 space-y-4 select-none">
            <x-forms.filters.desktop.form action="{{ route('transactions') }}" />
            <x-forms.filters.mobile.form action="{{ route('transactions') }}" />

            @if (request()->has('schema') && strtolower(request()->query('schema')) == 'erc20')
                <livewire:etherscan-table />
            @else
                <livewire:opensea-table />
            @endif
        </div>

    </x-layout.page>
</x-app>
