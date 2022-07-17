@php $__modal_id = CStr::id('filter_modal') @endphp
<div>
    <div class="flex flex-col mt-10 space-y-4 select-none">
        <!-- Desktop filters -->
        <div class="sticky z-50 items-center hidden h-16 px-5 space-x-6 bg-white rounded-lg shadow top-4 md:flex">
            <!-- Asset token type selection -->
            <div class="flex items-stretch h-10 overflow-hidden border border-gray-200 rounded-md">
                <a href="{{ route('transactions', ['wallet' => request()->query('wallet')]) }}" class="flex items-center px-3 text-sm transition-colors hover:bg-blue-600 hover:text-white">ERC1155/ERC721</a>
                <a href="#" class="flex items-center px-3 text-sm text-gray-500 bg-gray-200 cursor-not-allowed pointer-events-none">ERC20</a>
            </div>

            <!-- Block direction -->
            <div class="flex items-center space-x-2">
                @php $id = CStr::id('filter_field') @endphp

                <label for="{{ $id }}" class="text-sm font-medium">Direction</label>

                <select
                    name="direction"
                    id="{{ $id }}"
                    class="h-10 text-sm bg-white border border-gray-200 rounded-md"
                    wire:model="direction"
                >
                    <option value="both">Both</option>
                    <option value="in">IN</option>
                    <option value="out">OUT</option>
                </select>
            </div>

            <!-- Start Date Filter -->
            <div class="flex items-center space-x-2">
                @php $id = CStr::id('filter_field') @endphp

                <label for="{{ $id }}" class="text-sm font-medium">Start</label>

                <input
                    type="date"
                    id="{{ $id }}"
                    class="h-10 text-sm bg-white border border-gray-200 rounded-md"
                    wire:model="start_date"
                />
            </div>

            <!-- End Date Filter -->
            <div class="flex items-center space-x-2">
                @php $id = CStr::id('filter_field') @endphp

                <label for="{{ $id }}" class="text-sm font-medium">End</label>

                <input
                    type="date"
                    id="{{ $id }}"
                    class="h-10 text-sm bg-white border border-gray-200 rounded-md"
                    wire:model="end_date"
                />
            </div>

            <!-- Submit button -->
            <div class="flex-1"></div>
            <button
                type="submit"
                class="inline-block h-10 px-6 ml-auto text-white transition-colors bg-blue-500 rounded-md hover:bg-blue-600"
                wire:loading.class="cursor-wait pointer-events-none opacity-60"
                wire:loading.attr="disabled"
                wire:click="filterTransactions"
                wire:target="filterTransactions"
            >
                <span wire:target="filterTransactions" wire:loading.remove>Apply</span>
                <span wire:target="filterTransactions" wire:loading>Filtering</span>
            </button>
        </div>

        <!-- Mobile filters trigger -->
        <div class="flex items-center justify-end mb-4 md:hidden">
            <button
                type="button"
                data-modal-toggle="{{ $__modal_id }}"
                class="h-10 px-6 text-sm font-medium text-white transition-colors bg-blue-500 rounded-md cursor-pointer hover:bg-blue-600"
            >
                <i class="inline-block mr-1 text-xs fas fa-filter" aria-hidden="true"></i>
                Filters
            </button>
        </div>

        <!-- Mobile filters modal -->
        <x-flowbite.modal.popup id="{{ $__modal_id }}">
            <div class="flex flex-col space-y-4">
                <!-- Asset token type selection -->
                <div class="flex items-stretch h-10 overflow-hidden border border-gray-200 rounded-md max-w-max">
                    <a href="{{ route('transactions', ['wallet' => request()->query('wallet')]) }}" class="flex items-center px-3 text-sm transition-colors hover:bg-blue-600 hover:text-white">ERC1155/ERC721</a>
                    <a href="#" class="flex items-center px-3 text-sm text-gray-500 bg-gray-200 cursor-not-allowed pointer-events-none">ERC20</a>
                </div>

                <!-- Direction filter -->
                <div class="flex flex-col space-y-1">
                    @php $id = CStr::id('filter_field') @endphp
                    <label for="{{ $id }}" class="block mb-2 text-sm font-medium text-gray-900 dark:text-gray-300">
                        Direction
                    </label>
                    <select
                        id="{{ $id }}"
                        name="direction"
                        class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500"
                        wire:modal="direction"
                    >
                        <option value="both">Both</option>
                        <option value="in">IN</option>
                        <option value="out">OUT</option>
                    </select>
                </div>

                <!-- Start date filter -->
                <div class="flex flex-col space-y-1">
                    @php $id = CStr::id('filter_field') @endphp
                    <label for="{{ $id }}" class="block mb-2 text-sm font-medium text-gray-900 dark:text-gray-300">
                        Start Date
                    </label>
                    <input
                        type="date"
                        id="{{ $id }}"
                        name="start_date"
                        class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500"
                        wire:modal="start_date"
                    />
                </div>

                <!-- End date filter -->
                <div class="flex flex-col space-y-1">
                    @php $id = CStr::id('filter_field') @endphp
                    <label for="{{ $id }}" class="block mb-2 text-sm font-medium text-gray-900 dark:text-gray-300">
                        End Date
                    </label>
                    <input
                        type="date"
                        id="{{ $id }}"
                        name="end_date"
                        class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500"
                        wire:modal="end_date"
                    />
                </div>
            </div>

            <!-- Filter apply and cancel buttons -->
            <div class="flex mt-6 space-x-2">
                <button
                    data-modal-toggle="{{ $__modal_id }}"
                    type="submit"
                    class="text-white bg-blue-500 hover:bg-blue-600 font-medium rounded-lg text-sm inline-flex items-center px-5 py-2.5 text-center"
                    wire:loading.class="cursor-wait pointer-events-none opacity-60"
                    wire:loading.attr="disabled"
                    wire:click="filterTransactions"
                    wire:target="filterTransactions"
                >
                    <span wire:target="filterTransactions" wire:loading.remove>Apply</span>
                    <span wire:target="filterTransactions" wire:loading>Filtering</span>
                </button>
                <x-flowbite.modal.cancel id="{{ $__modal_id }}">
                    Cancel
                </x-flowbite.modal.cancel>
            </div>
        </x-flowbite.modal.popup>

        @php
            function calculateQuantity(int $value, ?int $decimals = 0): float {
                if (!$value || !$decimals) return 0;

                return round($value / (pow(10, $decimals)), 3);
            }

            $gweiToEth = fn (int $gwei): float => $gwei / 1000000000;
        @endphp

        <x-flowbite.table.component :columns="['Item', 'In/Out', 'Quantity', 'From', 'To', 'Txn Fee', 'Time']">
            @if ($transactions instanceof \Illuminate\Support\Collection && $transactions->isNotEmpty())
                {{-- `$transactions` is a non-empty collection, we can iterate over it --}}
                @foreach ($transactions->sortBy('block_number') as $transaction)
                    @php
                        $transaction->quantity = calculateQuantity(
                            $transaction->value,
                            $transaction->token['decimals']
                        );

                        $transaction->fee = round($gweiToEth($transaction->gas['price']), 3);
                    @endphp
                    <x-flowbite.table.row>
                        <!-- Asset Name -->
                        <th scope="row" class="px-6 py-4 font-medium text-gray-900 dark:text-white whitespace-nowrap">
                            {{ $transaction->token['name'] }}
                        </th>

                        <!-- Direction -->
                        <td class="px-6 py-4 {{ $transaction->direction == 'OUT' ? 'text-red-600' : 'text-green-600' }}">
                            @if ($transaction->direction == 'OUT')
                                <i class="fas fa-arrow-up" aria-hidden="true"></i>
                            @else
                                <i class="fas fa-arrow-down" aria-hidden="true"></i>
                            @endif
                            {{ $transaction->direction }}
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
                                {{ $__timestamp->format('d-m-Y h:i:s A') }}
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


        @if ($transactions instanceof \Illuminate\Support\Collection && $transactions->isNotEmpty())
            <div class="flex items-center justify-end">
                @php $__ids = $transactions->map(fn ($transaction) => $transaction->id)->join(','); @endphp
                <a
                    href="{{ route('transactions.download.etherscan') }}?ids={{ $__ids }}"
                    class="px-3 py-1.5 rounded hover:bg-gray-300 transition-colors font-medium text-sm"
                >
                    Downloads CSV
                </a>
            </div>
        @endif
    </div>

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
