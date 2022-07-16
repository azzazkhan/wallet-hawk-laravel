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

    <div class="flex flex-col mt-10 space-y-4 select-none">
        <div class="items-center hidden h-16 px-5 space-x-6 bg-white rounded-lg shadow md:flex">
            <!-- Asset token type selection -->
            <div class="flex items-stretch h-10 overflow-hidden border border-gray-200 rounded-md">
                <a href="#" class="flex items-center px-3 text-sm text-gray-500 bg-gray-200 cursor-not-allowed pointer-events-none">ERC1155/ERC721</a>
                <a href="{{ route('transactions', ['schema' => 'erc20', 'wallet' => request()->query('wallet')]) }}" class="flex items-center px-3 text-sm transition-colors hover:bg-blue-600 hover:text-white">ERC20</a>
            </div>

            <!-- Event type filter -->
            <div class="flex items-center space-x-2">
                @php $id = CStr::id('filter_field') @endphp

                <label for="{{ $id }}" class="text-sm font-medium">Type</label>

                <select
                    name="event_type"
                    id="{{ $id }}"
                    class="h-10 text-sm bg-white border border-gray-200 rounded-md"
                    wire:model="event_type"
                >
                    <option value="all">All</option>
                    <option value="created">Created</option>
                    <option value="successful">Successful</option>
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

        <x-flowbite.table.component :columns="['ID', 'Item', 'In/Out', 'From', 'To', 'Type', 'Event Type', 'Value', 'Time']" editable>
            @if ($events instanceof \Illuminate\Support\Collection && $events->isNotEmpty())
                {{-- `$events` is a non-empty collection, we can iterate over it --}}
                @foreach ($events as $event)
                    @php
                        $__wallet         = strtolower($wallet);
                        $image            = $event['media']['image'];
                        $event->thumbnail = ($image['thumbnail'] ?: $image['url']) ?: $image['original'];
                        $event->name      = $event->asset['name'];
                        $event->direction = $event->accounts['to'] == $__wallet || $event->accounts['winner'] == $__wallet
                            ? 'IN' : 'OUT';
                        $event->from      = $event->accounts['from'] ?: $event->accounts['seller'];
                        $event->to        = $event->accounts['to'] ?: $event->accounts['winner'];
                        $event->value     = '0 ETH, 0 USD';
                        $event->timestamp = new \Illuminate\Support\Carbon($event->event_timestamp);

                        if ($event->payment_token && is_array($event->payment_token))
                            $event->value = sprintf(
                                '%s ETH, %s USD',
                                substr($event->payment_token['eth'], 0, 6),
                                substr($event->payment_token['usd'], 0, 6)
                            );
                    @endphp
                    <x-flowbite.table.row
                        action="{{ route('transactions.single', $event->event_id) }}"
                    >
                        <td class="text-center">{{ $event->id }}</td>

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
                            {{ $event->event_type }}
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
                    <th colspan="10" class="py-3 font-semibold text-center text-gray-500">
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
