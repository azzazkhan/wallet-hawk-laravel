<div>
    <x-flowbite.table.component :columns="['ID', 'Item', 'In/Out', 'Quantity', 'From', 'To', 'Txn Fee', 'Time']">
        @if ($transactions instanceof \Illuminate\Support\Collection && $transactions->isNotEmpty())
            {{-- `$transactions` is a non-empty collection, we can iterate over it --}}
            @foreach ($transactions as $transaction)
                <x-flowbite.table.row>
                    <td class="text-center">{{ $loop->index + 1 }}</td>

                    <!-- Asset Name -->
                    <th scope="row" class="px-6 py-4 font-medium text-gray-900 dark:text-white whitespace-nowrap">
                        {{ $transaction->token['name'] }}
                    </th>

                    <!-- Direction -->
                    @php $__outwards = strtolower($transaction->accounts['from']) == strtolower($wallet) @endphp
                    <td class="px-6 py-4 {{ $__outwards ? 'text-red-600' : 'text-green-600' }}">
                        @if ($__outwards)
                            <i class="fas fa-arrow-up" aria-hidden="true"></i>
                        @else
                            <i class="fas fa-arrow-down" aria-hidden="true"></i>
                        @endif
                        {{ $__outwards ? 'OUT' : 'IN' }}
                    </td>

                    <!-- Quantity -->
                    <td class="px-6 py-4">{{ $transaction->quantity }}</td>

                    <!-- From -->
                    <td class="px-6 py-4">
                        @php
                            $__id = CStr::id('recepient_address');
                            $__address = $transaction->accounts['from'];
                        @endphp
                        <x-flowbite.tooltip id="{{ $__id }}">{{ $__address }}</x-flowbite.tooltip>
                        <div data-tooltip-target="{{ $__id }}">
                            {{ substr($__address, 0, 4) }}...{{ substr($__address, strlen($__address) - 4, strlen($__address) - 1) }}
                        </div>
                    </td>

                    <!-- To -->
                    <td class="px-6 py-4">
                        @php
                            $__id = CStr::id('recepient_address');
                            $__address = $transaction->accounts['to'];
                        @endphp
                        <x-flowbite.tooltip id="{{ $__id }}">{{ $__address }}</x-flowbite.tooltip>
                        <div data-tooltip-target="{{ $__id }}">
                            {{ substr($__address, 0, 4) }}...{{ substr($__address, strlen($__address) - 4, strlen($__address) - 1) }}
                        </div>
                    </td>

                    <!-- Txn Fee -->
                    <td class="px-6 py-4">{{ $transaction->fee }}</td>

                    <!-- Timestamp -->
                    <td class="px-6 py-4">
                        @php
                            $__id = CStr::id('transaction_address');
                            $__timestamp = new \Illuminate\Support\Carbon($transaction->block_timestamp);
                        @endphp
                        <x-flowbite.tooltip id="{{ $__id }}">
                            {{ $__timestamp->format('D jS M Y \a\t g:i:s A') }}
                        </x-flowbite.tooltip>

                        <div data-tooltip-target="{{ $__id }}">
                            {{ $__timestamp->format('c') }}
                        </div>
                    </td>
                </x-flowbite.table.row>
            @endforeach

        @else
            <!-- No transaction records available :( -->
            <x-flowbite.table.row>
                <th colspan="8" class="py-3 font-semibold text-center text-gray-500">
                    No transactions were found
                </th>
            </x-flowbite.table.row>
        @endif
    </x-flowbite.table.component>

    @if ($transactions instanceof \Illuminate\Support\Collection && $transactions->isNotEmpty() && $transactions->count() >= config('hawk.etherscan.blocks.per_page'))
        <button
            type="button"
            class="flex items-center h-10 px-5 mx-auto my-10 font-medium text-white transition-all bg-blue-500 rounded-md cursor-pointer hover:bg-blue-700"
            wire:click="loadMoreTransactions"
            wire:loading.class="cursor-wait pointer-events-none opacity-60"
            wire:loading.attr="disabled"
        >
            <span wire:loading.remove>Load More</span>
            <span wire:loading>Loading</span>
        </button>
    @endif
</div>
