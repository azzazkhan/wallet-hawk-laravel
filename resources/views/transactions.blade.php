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

                <!-- Pagination -->
                <div class="flex items-center justify-between">
                    @php
                        $__classes = [
                            'disabled' => 'inline-flex items-center h-10 px-5 text-sm text-gray-400 bg-gray-100 border border-gray-300 rounded-md cursor-not-allowed',
                            'normal'   => 'inline-flex items-center h-10 px-5 text-sm text-gray-900 transition-colors bg-gray-200 border border-gray-300 rounded-md hover:bg-gray-300',
                        ];

                        // 'previous'   => $previous ?? null, // Previous cursor (opensea)
                        $__params = [
                            'wallet'     => request()->query('wallet'),
                            'event'      => request()->query('event'),
                            'schema'     => request()->query('schema'),
                        ];
                    @endphp

                    {{-- If we are not in pagination view then disable next records button --}}
                    @if ($paginated ?? true)
                        <a role="button" class="{{ $__classes['disabled'] }}">Next</a>
                    @else
                        <a
                            role="button"
                            href="{{
                                route('transactions', array_merge($__params, [
                                    'previous' => $previous ?? null, // Previous cursor (opensea)
                                    'after'   => isset($previous) ? null : $transactions->first()['event_id']
                                ]))
                            }}"
                            class="{{ $__classes['normal'] }}"
                        >
                            Next
                        </a>
                    @endif

                    {{-- If we received fewer records than expected, disable previuos records button --}}
                    @if ($transactions->count() < config('hawk.opensea.event.per_page'))
                        <a role="button" class="{{ $__classes['disabled'] }}">Previous</a>
                    @else
                        <a
                            role="button"
                            href="{{
                                route('transactions', array_merge($__params, [
                                    'next'   => $next ?? null, // Next cursor (opensea)
                                    'before' => isset($next) ? null : $transactions->last()['event_id']
                                ]))
                            }}"
                            class="{{ $__classes['normal'] }}"
                        >
                            Previous
                        </a>
                    @endif
                </div>
            @endif
        </div>
    </x-layout.page>
</x-app>
