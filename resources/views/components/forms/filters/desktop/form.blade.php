<form
    {{
        $attributes->merge([
            'class' => 'items-center hidden h-16 px-5 space-x-6 bg-white rounded-lg shadow md:flex'
        ])
    }}>
    @foreach ($filters as $filter)
        <x-forms.filters.desktop.field name="{{ $filter['id'] }}" label="{{ $filter['label'] }}">
            @foreach ($filter['options'] as $option)
                @if (CStr::isValidString($option))
                    <option>{{ $option }}</option>
                @else
                    <option value="{{ $option['value'] }}">{{ $option['label'] }}</option>
                @endif
            @endforeach
        </x-forms.filters.desktop.field>
    @endforeach
    <div class="flex-1"></div>
    <button
        type="submit"
        class="inline-block h-10 px-6 ml-auto text-white transition-colors bg-blue-500 rounded-md hover:bg-blue-600"
    >
        Apply
    </button>
</form>
