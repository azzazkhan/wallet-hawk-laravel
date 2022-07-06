<!-- next, previous, paginated, transactions, date_filtered -->
@php
    $__schema       = request()->has('schema') && strtolower(request()->query('schema')) == 'erc20' ? 'ERC20' : 'Opensea';
    $__has_before   = !($transactions->count() < config('hawk.opensea.event.per_page'));
    $__dated        = CStr::isValidBoolean($date_filtered);
    $__has_cursors  = request()->has('previous') || request()->has('next');
    $__has_after    = CStr::isValidBoolean($paginated) || ($__dated && $__has_cursors);
@endphp
<x-app>
    <x-layout.page>
        <x-forms.search class="md:w-[400px] mr-auto flex-start" small />
        <div class="flex flex-col mt-10 space-y-4 select-none">
            <x-forms.filters.desktop.form action="{{ route('transactions') }}" />
            <x-forms.filters.mobile.form action="{{ route('transactions') }}" />

            @if ($__schema == 'ERC20')
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
                    @if ($__has_after)
                        <a
                            role="button"
                            href="{{
                                route('transactions', array_merge($__params, [
                                    'previous' => $previous ?? null, // Previous cursor (opensea)
                                    'after'    => $transactions->first()['event_id']
                                ]))
                            }}"
                            class="{{ $__classes['normal'] }}"
                        >
                            Next
                        </a>
                    @else
                        <span></span>
                    @endif

                    {{-- If we received fewer records than expected, disable previuos records button --}}
                    @if ($__has_before)
                        <a
                            role="button"
                            href="{{
                                route('transactions', array_merge($__params, [
                                    'next'   => $next ?? null, // Next cursor (opensea)
                                    'before' => $transactions->last()['event_id']
                                ]))
                            }}"
                            class="{{ $__classes['normal'] }}"
                        >
                            Previous
                        </a>
                    @else
                        <span></span>
                    @endif
                </div>
            @endif
        </div>
    </x-layout.page>
</x-app>
