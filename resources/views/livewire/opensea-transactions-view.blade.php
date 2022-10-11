<div class="relative mt-10 overflow-x-auto shadow-md sm:rounded-lg">
    <table class="w-full text-sm text-left text-gray-500">
        <thead class="text-sm text-gray-700 capitalize bg-gray-50">
            <tr>
                @php
                    $__columns = [
                        'Name', 'Asset ID', 'Event ID', 'Sender', 'Receiver',
                        'Scheme', 'Event Type', 'Value', 'Time', ''
                    ];
                @endphp

                @foreach ($__columns as $label)
                    <th scope="col" class="px-6 py-3">{{ $label }}</th>
                @endforeach
            </tr>
        </thead>

        <tbody>
            @foreach ($events as $event)
                @php
                    $data = static::prepare_event_for_preview($wallet, $event);

                    $name        = $data->get('name');
                    $asset_id    = $data->get('asset_id');
                    $image       = $data->get('animation') ?: $data->get('image');
                    $direction   = $data->get('direction');
                    $event_id    = $data->get('event_id');
                    $from        = optional($data->get('from'), fn (array $account) => $account['address']);
                    $to          = optional($data->get('to'), fn (array $account) => $account['address']);
                    $schema      = $data->get('schema');
                    $event_type  = $data->get('event_type');
                    $value       = $data->get('value') ? sprintf('%s ETH', $data->get('value')) : null;
                    $time_ago    = $data->get('timestamp')->diffForHumans();
                    $event_time  = $data->get('timestamp')->format('D jS M Y \a\t g:i:s A');
                @endphp

                <tr class="bg-white border-b">
                    <th scope="row" class="px-6 py-4 font-semibold text-gray-900 whitespace-nowrap">
                        {{ $data->get('name') }}
                    </th>
                    <td class="px-6 py-4"></td>
                    <td class="px-6 py-4">{{ $data->get('event_id') }}</td>
                    <td class="px-6 py-4">{{ collect($data->get('from'))->get('address') }}</td>
                    <td class="px-6 py-4">{{ collect($data->get('to'))->get('address') }}</td>
                    <td class="px-6 py-4 uppercase">{{ $data->get('schema') }}</td>
                    <td class="px-6 py-4 uppercase">{{ $data->get('event_type') }}</td>
                    <td class="px-6 py-4">{{ $data->get('value') }}</td>
                    <td class="px-6 py-4">{{ $data->get('timestamp')->diffForHumans() }}</td>
                    <td class="px-6 py-4"></td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>
