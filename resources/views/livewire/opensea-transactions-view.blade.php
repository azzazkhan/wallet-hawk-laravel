<div class="flex flex-col space-y-2">
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
                                {{-- {{ $data->get('timestamp')->format('D, d M Y H:i:s') }} --}}
                            </td>
                            <td class="px-4 py-3"></td>
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
