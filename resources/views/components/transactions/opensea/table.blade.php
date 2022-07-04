@props(['transactions'])
<x-flowbite.table.component
    :columns="['Item', 'In/Out', 'From', 'To', 'Type', 'Event Type', 'Value', 'Time']"
    editable
>
    @php $__wallet = request()->query('wallet') @endphp
    @foreach ($transactions as $transaction)
        @php
            $__animation = $transaction['media']['animation'];
            $__image     = $transaction['media']['image'];
            $__thumbnail = ($__image['thumbnail'] ?: $__image['url']) ?: $__image['original'];
            $__name      = $transaction['asset']['name'];
            $__outgoing  = ($transaction['accounts']['from'] ?: $transaction['accounts']['seller']) === $__wallet;
            $__from      = $transaction['accounts']['from']  ?: $transaction['accounts']['seller'];
            $__to        = $transaction['accounts']['to']    ?: $transaction['accounts']['winner'];
            $__value     = $transaction['payment_token'] ?
                                sprintf(
                                    '%s ETH, %s USD',
                                    substr($transaction['payment_token']['eth'], 0, 6),
                                    substr($transaction['payment_token']['usd'], 0, 6)
                                ) :
                                null;
        @endphp
        <x-flowbite.table.row
            action="{{ sprintf('/transactions/%s/%d/details', $__wallet, $transaction['event_id']) }}"
        >
            <!-- Asset Name -->
            <th scope="row" class="px-6 py-4 font-medium text-gray-900 dark:text-white whitespace-nowrap">
                <div class="flex items-center space-x-2">
                    <img src="{{ $__thumbnail }}" class="inline-block w-10 h-10 rounded" alt="{{ $__name }}" />
                    <span>{{ $__name }}</span>
                </div>
            </th>

            <!-- Direction -->
            <td class="px-6 py-4 {{ $__outgoing ? 'text-red-600' : 'text-green-600' }}">
                @if ($__outgoing)
                    <i class="fas fa-arrow-up" aria-hidden="true"></i>
                @else
                    <i class="fas fa-arrow-down" aria-hidden="true"></i>
                @endif
                {{ $__outgoing ? 'OUT' : 'IN' }}
            </td>

            <!-- From -->
            <td class="px-6 py-4">
                @if ($__from)
                    @php $__id = CStr::id('recepient_address') @endphp
                    <x-flowbite.tooltip id="{{ $__id }}">{{ $__from }}</x-flowbite.tooltip>
                    <div data-tooltip-target="{{ $__id }}">
                        {{ substr($__from, 0, 4) }}...{{ substr($__from, strlen($__from) - 4, strlen($__from) - 1) }}
                    </div>
                @else
                    <div class="font-bold text-center">--</div>
                @endif
            </td>

            <!-- To -->
            <td class="px-6 py-4">
                @if ($__to)
                    @php $__id = CStr::id('recepient_address') @endphp
                    <x-flowbite.tooltip id="{{ $__id }}">{{ $__to }}</x-flowbite.tooltip>
                    <div data-tooltip-target="{{ $__id }}">
                        {{ substr($__to, 0, 4) }}...{{ substr($__to, strlen($__to) - 4, strlen($__to) - 1) }}
                    </div>
                @else
                    <div class="font-bold text-center">--</div>
                @endif
            </td>

            <!-- Contract Schema -->
            <td class="px-6 py-4">
                {{ strtoupper($transaction['schema']) }}
            </td>

            <!-- Event Type -->
            <td class="px-6 py-4">
                {{ ucfirst($transaction['event_type']) }}
            </td>

            <!-- Value -->
            <td class="px-6 py-4 whitespace-nowrap">
                @if ($__value) {{ $__value }} @else <div class="font-bold text-center">--</div> @endif
            </td>

            <!-- Timestamp -->
            <td class="px-6 py-4 whitespace-nowrap">
                @php
                    $__id = CStr::id('transaction_address');
                @endphp
                <x-flowbite.tooltip id="{{ $__id }}">
                    {{ $transaction['event_timestamp']->format('D jS M Y \a\t g:i:s A') }}
                </x-flowbite.tooltip>
                <div data-tooltip-target="{{ $__id }}">{{ $transaction['event_timestamp']->format('c') }}</div>
            </td>
        </x-flowbite.table.row>
    @endforeach
</x-flowbite.table.component>
