@php $__modal_id = CStr::id('filter_modal') @endphp
<div>
    @if ($error && strlen($error) > 0)
        <div class="fixed z-50 flex items-center py-3 text-sm font-medium text-center text-white transform -translate-x-1/2 translate-y-2 bg-red-600 rounded-md bottom-10 left-1/2 px-7">
            {{ $error }}
        </div>
    @endif
    @if ($message && strlen($message) > 0)
        <div class="fixed z-50 flex items-center py-3 text-sm font-medium text-center text-white transform -translate-x-1/2 translate-y-2 bg-blue-600 rounded-md bottom-10 left-1/2 px-7">
            {{ $message }}
        </div>
    @endif

    <div class="relative flex flex-col mt-10 space-y-4 select-none">
        <!-- Desktop filters -->
        <div class="sticky top-0 items-center hidden h-16 px-5 space-x-6 bg-white rounded-lg shadow md:flex">
            <!-- Asset token type selection -->
            <div class="flex items-stretch h-10 overflow-hidden border border-gray-200 rounded-md">
                <a href="#" class="flex items-center px-3 text-sm text-gray-500 bg-gray-200 cursor-not-allowed pointer-events-none">ERC1155/ERC721</a>
                <a href="{{ route('transactions', ['schema' => 'erc20', 'wallet' => request()->query('wallet')]) }}" class="flex items-center px-3 text-sm transition-colors hover:bg-blue-600 hover:text-white">ERC20</a>
            </div>

            <!-- Event type filter -->
            <div class="flex items-center space-x-2">
                @php $id = CStr::id('filter_field') @endphp

                <label for="{{ $id }}" class="text-sm font-medium">Event</label>

                <select
                    name="event_type"
                    id="{{ $id }}"
                    class="h-10 text-sm bg-white border border-gray-200 rounded-md"
                    wire:model="event_type"
                >
                    <option value="all">All</option>
                    <option value="created">Created</option>
                    <option value="successful">Sale</option>
                    <option value="cancelled">Cancelled</option>
                    <option value="bid_entered">Bid Entered</option>
                    <option value="bid_withdrawn">Bid Withdrawn</option>
                    <option value="transfer">Transfer</option>
                    <option value="offer_ended">Offer Ended</option>
                    <option value="approved">Approved</option>
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
                wire:click="filterEvents"
                wire:target="filterEvents"
            >
                <span wire:target="filterEvents" wire:loading.remove>Apply</span>
                <span wire:target="filterEvents" wire:loading>Filtering</span>
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
                    <a href="#" class="flex items-center px-3 text-sm text-gray-500 bg-gray-200 cursor-not-allowed pointer-events-none">ERC1155/ERC721</a>
                    <a href="{{ route('transactions', ['schema' => 'erc20', 'wallet' => request()->query('wallet')]) }}" class="flex items-center px-3 text-sm transition-colors hover:bg-blue-600 hover:text-white">ERC20</a>
                </div>

                <!-- Event type filter -->
                <div class="flex flex-col space-y-1">
                    @php $id = CStr::id('filter_field') @endphp
                    <label for="{{ $id }}" class="block mb-2 text-sm font-medium text-gray-900 dark:text-gray-300">
                        Event Type
                    </label>
                    <select
                        id="{{ $id }}"
                        name="event_type"
                        class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500"
                        wire:modal="event_type"
                    >
                        <option value="all">All</option>
                        <option value="created">Created</option>
                        <option value="successful">Sale</option>
                        <option value="cancelled">Cancelled</option>
                        <option value="bid_entered">Bid Entered</option>
                        <option value="bid_withdrawn">Bid Withdrawn</option>
                        <option value="transfer">Transfer</option>
                        <option value="offer_ended">Offer Ended</option>
                        <option value="approved">Approved</option>
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
                    wire:click="filterEvents"
                    wire:target="filterEvents"
                >
                    <span wire:target="filterEvents" wire:loading.remove>Apply</span>
                    <span wire:target="filterEvents" wire:loading>Filtering</span>
                </button>
                <x-flowbite.modal.cancel id="{{ $__modal_id }}">
                    Cancel
                </x-flowbite.modal.cancel>
            </div>
        </x-flowbite.modal.popup>

        <x-flowbite.table.component
            :columns="['Item', 'In/Out', 'From', 'To', 'Type', 'Event Type', 'Value', 'Time']"
            class="overflow-y-auto"
            style="height: 40px;"
            editable
        >
            @if ($events instanceof \Illuminate\Support\Collection && $events->isNotEmpty())
                {{-- `$events` is a non-empty collection, we can iterate over it --}}
                @foreach ($events->sortByDesc('event_timestamp') as $event)
                    @php
                        $__wallet         = strtolower($wallet);
                        $image            = $event['media']['image'];
                        $event->thumbnail = ($image['thumbnail'] ?: $image['url']) ?: $image['original'];
                        $event->name      = $event->asset['name'];
                        $event->timestamp = new \Illuminate\Support\Carbon($event->event_timestamp);



                        // Event direction and seller computation
                        if (is_array($event->accounts['from'])) {
                            $event->direction = $event->accounts['from']['address'] == strtolower($__wallet) ? 'OUT' : 'IN';
                            $event->from = $event->accounts['from']['address'];
                        }
                        elseif (is_array($event->accounts['seller'])) {
                            $event->direction = $event->accounts['seller']['address'] == strtolower($__wallet) ? 'OUT' : 'IN';
                            $event->from = $event->accounts['seller']['address'];
                        }
                        else {
                            $event->direction = 'OUT';
                            $event->from = null;
                        }


                        // Event winner/to computation
                        if (is_array($event->accounts['to']))
                            $event->to = $event->accounts['to']['address'];
                        elseif (is_array($event->accounts['winner']))
                            $event->to = $event->accounts['winner']['address'];
                        else $event->to = null;


                        // Even asset value calculations
                        if ($event->payment_token && is_array($event->payment_token))
                            $event->value = sprintf(
                                '%s ETH, %s USD',
                                number_format((int) $event->payment_token['eth'], 0, 6),
                                number_format((int) $event->payment_token['usd'], 0, 6)
                            );
                        else $event->value = '0 ETH, 0 USD';
                    @endphp
                    <x-flowbite.table.row
                        action="{{
                            route('transactions.single', [
                                'wallet' => $wallet,
                                'event_id' => $event->event_id
                            ])
                        }}"
                    >
                        <!-- Asset Name -->
                        <th scope="row" class="px-6 py-4 font-medium text-gray-900 dark:text-white whitespace-nowrap">
                            <div class="flex items-center space-x-2">
                                <img src="{{ $event->thumbnail }}" class="inline-block w-10 h-10 rounded" alt="{{ $event->name }}" />
                                <span>{{ $event->name }}</span>
                            </div>
                        </th>

                        <!-- Direction -->
                        <td class="px-6 py-4 {{ $event->direction === 'OUT' ? 'text-red-600' : 'text-green-600' }}">
                            @if ($event->direction === 'OUT')
                                <i class="fas fa-arrow-up" aria-hidden="true"></i>
                            @else
                                <i class="fas fa-arrow-down" aria-hidden="true"></i>
                            @endif
                            {{ $event->direction }}
                        </td>

                        <!-- From -->
                        <td class="px-6 py-4">
                            @php
                                $__id = CStr::id('recepient_address');
                                $__address = $event->from;
                            @endphp
                            @if ($__address)
                                <x-flowbite.tooltip id="{{ $__id }}">{{ $__address }}</x-flowbite.tooltip>
                                <div data-tooltip-target="{{ $__id }}">
                                    {{ substr($__address, 0, 4) }}...{{ substr($__address, strlen($__address) - 4, strlen($__address) - 1) }}
                                </div>
                            @else
                                <div class="font-bold text-center">--</div>
                            @endif
                        </td>

                        <!-- To -->
                        <td class="px-6 py-4">
                            @php
                                $__id = CStr::id('recepient_address');
                                $__address = $event->to;
                            @endphp
                            @if ($__address)
                                <x-flowbite.tooltip id="{{ $__id }}">{{ $__address }}</x-flowbite.tooltip>
                                <div data-tooltip-target="{{ $__id }}">
                                    {{ substr($__address, 0, 4) }}...{{ substr($__address, strlen($__address) - 4, strlen($__address) - 1) }}
                                </div>
                            @else
                                <div class="font-bold text-center">--</div>
                            @endif
                        </td>

                        <!-- Contract Schema -->
                        <td class="px-6 py-4">
                            {{ strtoupper($event->schema) }}
                        </td>

                        <!-- Event Type -->
                        <td class="px-6 py-4">
                            {{ ucfirst(preg_replace('/(successful)/', 'sale', strtolower($event->event_type))) }}
                        </td>

                        <!-- Asset Value -->
                        <td class="px-6 py-4">
                            {{ $event->value }}
                        </td>

                        <!-- Timestamp -->
                        <td class="px-6 py-4">
                            @php
                                $__id = CStr::id('transaction_address');
                                $__timestamp = new \Illuminate\Support\Carbon($event->event_timestamp);
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
                    <th colspan="9" class="py-3 font-semibold text-center text-gray-500">
                        No transactions were found
                    </th>
                </x-flowbite.table.row>
            @endif
        </x-flowbite.table.component>
    </div>

    @if ($events instanceof \Illuminate\Support\Collection && $events->isNotEmpty() && $events->count() >= config('hawk.opensea.event.per_page'))
        <button
            type="button"
            class="flex items-center h-10 px-5 mx-auto my-10 font-medium text-white transition-all bg-blue-500 rounded-md cursor-pointer hover:bg-blue-700"
            wire:click="loadMoreEvents"
            wire:loading.class="cursor-wait pointer-events-none opacity-60"
            wire:loading.attr="disabled"
            wire:target="loadMoreEvents"
        >
            <span wire:target="loadMoreEvents" wire:loading.remove>Load More</span>
            <span wire:target="loadMoreEvents" wire:loading>Loading</span>
        </button>
    @endif
</div>
