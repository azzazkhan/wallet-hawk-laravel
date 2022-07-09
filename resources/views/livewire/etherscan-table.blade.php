<x-flowbite.table.component :columns="['Item', 'In/Out', 'Quantity', 'From', 'To', 'Txn Fee', 'Time']" editable>
    @if (isset($transactions) && $transactions instanceof \Illuminate\Support\Collection && !$transactions->empty())
        {{-- `$transactions` is a non-empty collection, we can iterate over it --}}
        @foreach ($transactions as $transaction)
            <x-flowbite.table.row
                action="{{ sprintf('/transactions/0x%s/details', $transaction['hash']) }}">
                <!-- Asset Name -->
                <th scope="row" class="px-6 py-4 font-medium text-gray-900 dark:text-white whitespace-nowrap">
                    {{ $transaction['item'] }}
                </th>

                <!-- Direction -->
                <td class="px-6 py-4 {{ $transaction['direction'] == 'IN' ? 'text-green-600' : 'text-red-600' }}">
                    @if ($transaction['direction'] == 'IN')
                        <i class="fas fa-arrow-down" aria-hidden="true"></i>
                    @else
                        <i class="fas fa-arrow-up" aria-hidden="true"></i>
                    @endif
                    {{ $transaction['direction'] }}
                </td>

                <!-- Quantity -->
                <td class="px-6 py-4">{{ $transaction['quantity'] }}</td>

                <!-- From -->
                <td class="px-6 py-4">
                    @php
                        $__id = CStr::id('recepient_address');
                        $__address = $transaction['from'];
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
                        $__address = $transaction['to'];
                    @endphp
                    <x-flowbite.tooltip id="{{ $__id }}">{{ $__address }}</x-flowbite.tooltip>
                    <div data-tooltip-target="{{ $__id }}">
                        {{ substr($__address, 0, 4) }}...{{ substr($__address, strlen($__address) - 4, strlen($__address) - 1) }}
                    </div>
                </td>

                <!-- Txn Fee -->
                <td class="px-6 py-4">
                    {{ $transaction['fee']['amount'] }} {{ $transaction['fee']['symbol'] }}
                </td>

                <!-- Timestamp -->
                <td class="px-6 py-4">
                    @php $__id = CStr::id('transaction_address') @endphp
                    <x-flowbite.tooltip id="{{ $__id }}">
                        {{ now()->format('D jS M Y \a\t g:i:s A') }}
                    </x-flowbite.tooltip>

                    <div data-tooltip-target="{{ $__id }}">
                        {{ now()->format('c') }}
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
