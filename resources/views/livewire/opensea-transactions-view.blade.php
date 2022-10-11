<div class="relative mt-10 overflow-x-auto shadow-md sm:rounded-lg">
    <table class="w-full text-sm text-left text-gray-500">
        <thead class="text-sm text-gray-700 capitalize bg-gray-50">
            <tr>
                @php
                    $__columns = [
                        'Name', 'Direction', 'From', 'To', 'Scheme',
                        'Event Type', 'Value', 'Occurred'
                    ];
                @endphp

                @foreach ($__columns as $label)
                    <th scope="col" class="px-6 py-3">{{ $label }}</th>
                @endforeach
                <th scope="col" class="px-6 py-3"></th>
            </tr>
        </thead>

        @if ($events instanceof Illuminate\Support\Collection && $events->isNotEmpty())
            <tbody>
                @foreach ($events as $event)
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
                            {{-- {{ $event->timestamp }} --}}
                            {{-- @if ($name || $image)
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
                            @endif --}}
                            <span class="block font-bold text-center">{{ $data->get('timestamp') }}</span>
                        </th>

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
                            {{-- <span title="{{ $event_time }}">{{ $time_ago }}</span> --}}
                            {{ $data->get('timestamp')->format('D, d M Y H:i:s') }}
                        </td>
                        <td class="px-4 py-3"></td>
                    </tr>
                @endforeach
            </tbody>
        @endif
    </table>
</div>
