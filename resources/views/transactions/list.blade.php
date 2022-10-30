<x-app>
    <x-layout.page>
        <x-forms.search class="md:w-[400px] mr-auto flex-start" small />

        @if (Str::lower(request()->query('schema', 'none')) == 'erc20')
            <div id="etherscan_module"></div>
        @else
            <div id="opensea_module"></div>
        @endif
    </x-layout.page>
</x-app>
