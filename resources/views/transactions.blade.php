<x-app>
    <x-layout.page>
        <x-forms.search class="md:w-[400px] mr-auto flex-start" small />
        <div class="flex flex-col mt-10 space-y-4 select-none">
            <x-forms.filters.desktop.form action="{{ route('transactions') }}" />
            <x-forms.filters.mobile.form action="{{ route('transactions') }}" />

            @if ($schema === 'ERC20')
                <x-transactions.erc20.table :transactions="$transactions" />
            @else
                <x-transactions.opensea.table :transactions="$transactions" />
                <div class="flex items-center justify-between">
                    <a role="button" class="inline-flex items-center h-10 px-5 text-sm text-gray-400 bg-gray-100 border border-gray-300 rounded-md cursor-not-allowed">
                        Next
                    </a>

                    @if ($transactions->count() >= config('hawk.opensea.event.per_page'))
                        <a
                            role="button"
                            href="{{
                                route('transactions', [
                                    'wallet' => request()->query('wallet'),
                                    'event'  => request()->query('event'),
                                    'schema' => request()->query('schema'),
                                    'before' => $transactions->last()['event_id']
                                ])
                            }}"
                            class="inline-flex items-center h-10 px-5 text-sm text-gray-900 transition-colors bg-gray-200 border border-gray-300 rounded-md hover:bg-gray-300"
                        >
                            Previous
                        </a>
                    @endif
                </div>
            @endif
        </div>
    </x-layout.page>
</x-app>
