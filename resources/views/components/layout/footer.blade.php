@php $__donate_modal_id = CStr::id('donate_modal') @endphp
<footer class="flex items-center justify-between px-5 mt-auto bg-white drop-shadow-2xl md:px-28 sm:px-12 h-28">
    <form class="md:min-w-[300px]">
        <label htmlFor="newsletterEmailInput" class="text-xs font-medium">
            Sign up for our emails!
        </label>
        <div class="flex">
            <input
                type="text"
                id="newsletterEmailInput"
                class="flex-1 w-full px-2 text-xs border-gray-200 h-9 rounded-l-md"
                placeholder="Enter your email address"
            />
            <button class="flex items-center justify-center px-3 text-xs text-white transition-colors bg-blue-500 cursor-pointer h-9 rounded-r-md hover:bg-blue-600">
                Sign Up
            </button>
        </div>
    </form>

    <div class="block">
        <button
            type="button"
            data-modal-toggle="{{ $__donate_modal_id }}"
            class="block px-3 text-xs font-medium transition-colors border border-gray-900 rounded-md hover:bg-gray-100 h-9"
        >
            Donate with ETH
        </button>
    </div>
</footer>
<x-flowbite.modal.popup id="{{ $__donate_modal_id }}">
    <div class="flex flex-col py-6 space-y-10">
        <img
            src="{{ asset('assets/img/donation-wallet-qr-code.png') }}"
            class="w-48 h-48 mx-auto"
            alt="ETH wallet QR Code"
        />
        <p class="px-6 text-gray-700">
            If you're able, please donate to us! Anything helps to maintain operating costs (i.e. API integrations). This is our ETH wallet address.
        </p>
    </div>
</x-flowbite.modal.popup>
