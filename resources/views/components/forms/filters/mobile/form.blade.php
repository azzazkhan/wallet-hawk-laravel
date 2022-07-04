@props(['action' => '#'])
@php $__modal_id = CStr::id('filter_modal') @endphp
<div class="flex items-center justify-end mb-4 md:hidden">
    <button
        type="button"
        data-modal-toggle="{{ $__modal_id }}"
        class="h-10 px-6 text-sm font-medium text-white transition-colors bg-blue-500 rounded-md cursor-pointer hover:bg-blue-600"
    >
        <i class="inline-block mr-1 text-xs fas fa-filter" aria-hidden="true"></i>
        Filters
    </button>

    <x-flowbite.modal.popup id="{{ $__modal_id }}">
        <form action="{{ route('transactions') }}">
            <input type="hidden" name="wallet" value="{{ request()->query('wallet') }}" />
            <div class="flex flex-col space-y-4">
                @foreach ($filters as $filter)
                    <x-forms.filters.mobile.field name="{{ $filter['id'] }}" label="{{ $filter['label'] }}">
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
                    </x-forms.filters.mobile.field>
                @endforeach
            </div>

            <div class="flex mt-6 space-x-2">
                <button
                    data-modal-toggle="{{ $__modal_id }}"
                    type="submit"
                    class="text-white bg-blue-500 hover:bg-blue-600 font-medium rounded-lg text-sm inline-flex items-center px-5 py-2.5 text-center"
                >
                    Apply
                </button>
                <x-flowbite.modal.cancel id="{{ $__modal_id }}">
                    Cancel
                </x-flowbite.modal.cancel>
            </div>
        </form>
    </x-flowbite.modal.popup>
</div>
