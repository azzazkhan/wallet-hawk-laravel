<div>
    <x-flowbite.table.component :columns="['ID', 'Item', 'In/Out', 'From', 'To', 'Type', 'Event Type', 'Value', 'Time']" editable>
        @if ($events instanceof \Illuminate\Support\Collection && $events->isNotEmpty())
            {{-- `$events` is a non-empty collection, we can iterate over it --}}
            @foreach ($events as $event)
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

    @if ($events instanceof \Illuminate\Support\Collection && $events->isNotEmpty() && $events->count() >= config('hawk.opensea.event.per_page'))
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
