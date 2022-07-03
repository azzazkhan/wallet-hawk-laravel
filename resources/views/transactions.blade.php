<x-app>
    <x-layout.page>
        <x-forms.search class="md:w-[400px] mr-auto flex-start" small />
        <div class="flex flex-col mt-10 space-y-4 select-none">
            <x-forms.filters.desktop.form action="{{ url('/transactions') }}" />
            <x-forms.filters.mobile.form action="{{ url('/transactions') }}" />
            <x-transactions.erc20.table :transactions="$transactions" />
        </div>
    </x-layout.page>
</x-app>
