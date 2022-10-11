@php $__modal_id = CStr::id('filter_modal'); @endphp
<div class="flex flex-col mt-10 space-y-6">

    {{-- Desktop filters bar --}}
    <div class="sticky z-50 items-center hidden h-16 px-5 space-x-6 bg-white rounded-lg shadow top-4 md:flex">
        <!-- Asset token type selection -->
        <div class="flex items-stretch h-10 overflow-hidden border border-gray-200 rounded-md">
            <a href="#" class="flex items-center px-3 text-sm text-gray-500 bg-gray-200 cursor-not-allowed pointer-events-none">ERC1155/ERC721</a>
            <a href="{{ route('transactions', ['schema' => 'erc20', 'wallet' => $wallet]) }}" class="flex items-center px-3 text-sm transition-colors hover:bg-blue-600 hover:text-white">ERC20</a>
        </div>

        <!-- Event type filter -->
        <div class="flex items-center space-x-2">
            @php $id = CStr::id('filter_field') @endphp

            <label for="{{ $id }}" class="text-sm font-medium">Event</label>

            <select
                name="type"
                id="{{ $id }}"
                class="h-10 text-sm bg-white border border-gray-200 rounded-md"
                wire:model="type"
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
                name="start"
                wire:model="start"
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
                name="end"
                wire:model="end"
            />
        </div>

        <!-- Submit button -->
        <div class="flex-1"></div>
        <button
            type="submit"
            class="inline-block h-10 px-6 ml-auto text-white transition-colors bg-blue-500 rounded-md hover:bg-blue-600"
            wire:loading.class="cursor-wait pointer-events-none opacity-60"
            wire:loading.attr="disabled"
            wire:click="apply_filters"
            wire:target="apply_filters"
        >
            <span wire:target="apply_filters" wire:loading.remove>Apply</span>
            <span wire:target="apply_filters" wire:loading>Filtering</span>
        </button>
    </div>

    {{-- Mobile filters modal trigger --}}
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

    {{-- Mobile filter modal --}}
    <x-flowbite.modal.popup id="{{ $__modal_id }}">
        <div class="flex flex-col space-y-4">
            <!-- Asset token type selection -->
            <div class="flex items-stretch h-10 overflow-hidden border border-gray-200 rounded-md max-w-max">
                <a
                    href="#"
                    class="flex items-center px-3 text-sm text-gray-500 bg-gray-200 cursor-not-allowed pointer-events-none"
                >
                    ERC1155/ERC721
                </a>
                <a
                    href="{{ route('transactions', ['schema' => 'erc20', 'wallet' => $wallet]) }}"
                    class="flex items-center px-3 text-sm transition-colors hover:bg-blue-600 hover:text-white"
                >
                    ERC20
                </a>
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
                    wire:modal="type"
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
                    name="start"
                    class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500"
                    wire:modal="start"
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
                    name="end"
                    class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500"
                    wire:modal="end"
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
                wire:click="apply_filters"
                wire:target="apply_filters"
            >
                <span wire:target="apply_filters" wire:loading.remove>Apply</span>
                <span wire:target="apply_filters" wire:loading>Filtering</span>
            </button>

            <x-flowbite.modal.cancel id="{{ $__modal_id }}">
                Cancel
            </x-flowbite.modal.cancel>
        </div>
    </x-flowbite.modal.popup>


    {{-- Data table --}}
    <div class="relative mt-10 overflow-x-auto shadow-md sm:rounded-lg">
        <div
            class="absolute top-0 bottom-0 left-0 right-0 z-20 bg-white bg-opacity-40 backdrop-blur-lg"
            wire:loading
        >
            <div class="absolute text-4xl font-medium transform -translate-x-1/2 -translate-y-1/2 top-1/2 left-1/2">
                Loading...
            </div>
        </div>
        <table class="w-full text-sm text-left text-gray-500">
            <thead class="text-sm text-gray-700 capitalize bg-gray-100">
                <tr>
                    @php
                        $__columns = [
                            'Item', 'Direction', 'From', 'To', 'Scheme',
                            'Event Type', 'Value', 'Occurred'
                        ];
                    @endphp

                    @foreach ($__columns as $label)
                        <th scope="col" class="px-6 py-4">{{ $label }}</th>
                    @endforeach
                    <th scope="col" class="px-6 py-4"></th>
                </tr>
            </thead>
            <tbody>
                @if ($events instanceof Illuminate\Support\Collection && $events->isNotEmpty())
                    @foreach ($events->sortByDesc('event_timestamp') as $event)
                        @php
                            $data = static::prepare_event_for_preview(Str::lower($wallet), $event);

                            $name        = $data->get('name');
                            $token_id    = $data->get('token_id');
                            $asset_id    = $data->get('asset_id');
                            $image       = $data->get('image');
                            $direction   = $data->get('direction');
                            $event_id    = $data->get('event_id');
                            $from        = optional($data->get('from'), fn (array $account) => $account['address']);
                            $to          = optional($data->get('to'), fn (array $account) => $account['address']);
                            $schema      = $data->get('schema');
                            $event_type  = $data->get('event_type');
                            $value       = $data->get('value');
                            $time_ago    = $data->get('timestamp')->diffForHumans();
                            $event_time  = $data->get('timestamp')->format('D jS M Y \a\t g:i:s A');
                        @endphp

                        <tr class="bg-white border-b">
                            <th
                                scope="row"
                                class="flex items-center px-4 py-3 space-x-5 font-semibold text-gray-900 whitespace-nowrap"
                            >
                                @if ($name || $image)
                                    @if ($image)
                                        <img
                                            src="{{ $image }}"
                                            class="flex-shrink-0 w-10 h-10 rounded-lg"
                                            alt="{{ $name }}"
                                        />
                                    @else
                                        <div class="flex-shrink-0 block w-10 h-10 rounded-lg"></div>
                                    @endif

                                    @if ($name)
                                        <span>{{ $name }}</span>
                                    @else
                                        <span class="block font-bold text-center">--</span>
                                    @endif

                                @else
                                    <span class="block font-bold text-center">--</span>
                                @endif
                            </th>

                            {{-- Event direction --}}
                            <td class="px-4 py-3">
                                @switch($direction)
                                    @case('out')
                                        <span class="text-red-600">
                                            <i class="fas fa-arrow-up" aria-hidden="true"></i> OUT
                                        </span>
                                        @break

                                    @case('in')
                                        <span class="text-green-600">
                                            <i class="fas fa-arrow-down" aria-hidden="true"></i> IN
                                        </span>
                                        @break

                                    @default
                                        <span class="block font-bold text-center">--</span>
                                @endswitch
                            </td>

                            {{-- Sender/from address --}}
                            <td class="px-4 py-3">
                                @if ($from)
                                    @php
                                        $address = sprintf(
                                            '%s....%s',
                                            substr($from, 0, 4),
                                            substr(
                                                $from,
                                                strlen($from) - 4,
                                                strlen($from) - 1
                                            )
                                        );
                                    @endphp

                                    <span title="{{ $from }}">{{ $address }}</span>
                                @else
                                    <span class="block font-bold text-center">--</span>
                                @endif
                            </td>

                            {{-- Receiver/to address --}}
                            <td class="px-4 py-3">
                                @if ($to)
                                    @php
                                        $address = sprintf(
                                            '%s....%s',
                                            substr($to, 0, 4),
                                            substr(
                                                $to,
                                                strlen($to) - 4,
                                                strlen($to) - 1
                                            )
                                        );
                                    @endphp

                                    <span title="{{ $to }}">{{ $address }}</span>
                                @else
                                    <span class="block font-bold text-center">--</span>
                                @endif
                            </td>

                            <td class="px-4 py-3 uppercase">{{ $data->get('schema') }}</td>
                            <td class="px-4 py-3 capitalize">{{ Str::title($data->get('event_type')) }}</td>

                            {{-- ETH value of token --}}
                            <td class="px-4 py-3">
                                @if ($value)
                                    <span class="whitespace-nowrap">{{ $value }} ETH</span>
                                @else
                                    <span class="block font-bold text-center">--</span>
                                @endif
                            </td>

                            <td class="px-4 py-3">
                                <span title="{{ $event_time }}">{{ $time_ago }}</span>
                            </td>

                            <td class="px-4 py-3">
                                <a
                                    href="{{ route('transactions.single', ['wallet' => $wallet, 'event_id' => $event_id]) }}"
                                    class="inline-flex items-center h-8 px-3 text-xs font-medium text-blue-500 transition-colors border border-blue-500 rounded-md whitespace-nowrap hover:text-white hover:bg-blue-500"
                                >
                                    Details
                                </a>
                            </td>
                        </tr>
                    @endforeach
                @else
                    <tr colspan="9" class="">
                        <th colspan="9" class="px-4 py-4 font-medium text-center text-gray-500 whitespace-nowrap">
                            No events were found for provided wallet address!
                        </th>
                    </tr>
                @endif
            </tbody>
        </table>
    </div>

    {{-- Pagination and other controls --}}
    @if ($events instanceof Illuminate\Support\Collection && $events->isNotEmpty())
        <div class="flex items-center justify-between px-2">
            <span class="text-sm text-gray-500">
                Showing {{ $events->count() }} events
            </span>

            @php
                $__ids = $events
                    ->map(function ($event) {
                        return $event instanceof App\Models\Opensea ? $event->id : $event['id'];
                    })
                    ->join(',');
            @endphp
                <a
                    href="{{ route('transactions.download.opensea') }}?ids={{ $__ids }}"
                    class="px-3 py-1.5 rounded hover:bg-gray-300 transition-colors font-medium text-sm"
                >
                    Downloads CSV
                </a>
        </div>

        <div class="flex justify-center">
            @if ($cursor)
                <button
                    type="button"
                    class="flex items-center h-10 px-5 mx-auto my-10 font-medium text-white transition-all bg-blue-500 rounded-md cursor-pointer hover:bg-blue-700"
                    wire:click="load_more_events"
                    wire:loading.class="cursor-wait pointer-events-none opacity-60"
                    wire:loading.attr="disabled"
                    wire:target="load_more_events"
                >
                    <span wire:target="load_more_events" wire:loading.remove>Load More</span>
                    <span wire:target="load_more_events" wire:loading>Loading</span>
                </button>
            @else
                <button
                    type="button"
                    class="flex items-center h-10 px-5 mx-auto my-10 font-medium text-white bg-blue-400 rounded-md"
                >
                    Load More
                </button>
            @endif
        </div>
    @endif
</div>
