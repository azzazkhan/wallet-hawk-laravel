<form
    {{
        $attributes->merge([
            'class' => 'items-center hidden h-16 px-5 space-x-6 bg-white rounded-lg shadow md:flex'
        ])
    }}>
    <input type="hidden" name="wallet" value="{{ request()->query('wallet') }}" />
    <div class="flex items-stretch h-10 overflow-hidden border border-gray-200 rounded-md">
        @if (request()->has('schema') && strtolower(request()->query('schema')) == 'erc20')
            <a href="{{ route('transactions', ['wallet' => request()->query('wallet')]) }}" class="flex items-center px-3 text-sm transition-colors hover:bg-blue-600 hover:text-white">ERC1155/ERC721</a>
            <a href="#" class="flex items-center px-3 text-sm text-gray-500 bg-gray-200 cursor-not-allowed pointer-events-none">ERC20</a>
        @else
            <a href="#" class="flex items-center px-3 text-sm text-gray-500 bg-gray-200 cursor-not-allowed pointer-events-none">ERC1155/ERC721</a>
            <a href="{{ route('transactions', ['schema' => 'erc20', 'wallet' => request()->query('wallet')]) }}" class="flex items-center px-3 text-sm transition-colors hover:bg-blue-600 hover:text-white">ERC20</a>
        @endif
    </div>
    @unless (request()->has('schema') && strtolower(request()->query('schema')) == 'erc20')
        @foreach ($filters as $filter)
            <x-forms.filters.desktop.field name="{{ $filter['id'] }}" label="{{ $filter['label'] }}">
                @foreach ($filter['options'] as $option)
                    @if (CStr::isValidString($option))
                        <option>{{ $option }}</option>
                    @else
                        <option
                            value="{{ $option['value'] }}"
                            @selected($option['value'] == request()->query($filter['id']))
                        >
                            {{ $option['label'] }}
                        </option>
                    @endif
                @endforeach
            </x-forms.filters.desktop.field>
        @endforeach
    @endif
    <div class="flex-1"></div>
    <button
        type="submit"
        class="inline-block h-10 px-6 ml-auto text-white transition-colors bg-blue-500 rounded-md hover:bg-blue-600"
    >
        Apply
    </button>
</form>
