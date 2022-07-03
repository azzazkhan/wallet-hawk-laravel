@props(['small' => false])
<form action="{{ route('transactions') }}" {{ $attributes->merge(['class' => 'flex']) }}>
    <input
        type="text"
        name="wallet"
        class="{{ CStr::classes([
            'flex-1 px-3 font-medium rounded-l-md' => true,
            'h-12 border-none shadow' => !$small,
            'h-10 text-sm border border-gray-200' => $small,
        ]) }}"
        placeholder="Enter a wallet address here"
    />
    <button type="submit" role="button" class="{{ CStr::classes([
        'flex items-center justify-center w-16 text-white transition-colors bg-blue-500 shadow-md cursor-pointer rounded-r-md hover:bg-blue-600' => true,
        'h-12' => !$small,
        'h-10' => $small
    ]) }}">
        <i class="fas fa-search {{ $small ? "text-lg" : "text-xl" }}" aria-label="Search"></i>
    </button>
</form>
