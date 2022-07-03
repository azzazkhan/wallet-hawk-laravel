@props(['columns' => [], 'editable' => false])
<div class="relative overflow-x-auto shadow-md sm:rounded-lg">
    <table class="w-full text-sm text-left text-gray-500 dark:text-gray-400">
        <thead class="text-xs text-gray-700 uppercase bg-gray-50 dark:bg-gray-700 dark:text-gray-400">
            @if (CStr::isValidArray($columns))
                <tr>
                    @foreach($columns as $column)
                        @if (CStr::isValidString($column))
                            <th scope="col" class="px-6 py-3">{{ $column }}</th>
                        @endif
                    @endforeach
                    @if ($editable)
                        <th scope="col" class="px-6 py-3">
                            <span class="sr-only">Edit</span>
                        </th>
                    @endif
                </tr>
            @endif
        </thead>
        <tbody>
            {{ $slot }}
        </tbody>
    </table>
</div>
