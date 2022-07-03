@props(['action' => null, 'label' => 'Details'])
<tr class="bg-white border-b dark:bg-gray-800 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600">
    {{ $slot }}
    @if (CStr::isValidString($action))
        <td class="px-6 py-4 text-right">
            <a
                href="{{ $action ?: '#' }}"
                class="inline-flex items-center h-8 px-3 text-xs font-medium text-blue-500 transition-colors border border-blue-500 rounded-md whitespace-nowrap hover:text-white hover:bg-blue-500"
            >
                {{ $label }}
            </a>
        </td>
    @endif
</tr>
