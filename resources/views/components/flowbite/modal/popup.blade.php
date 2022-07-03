@props(['id' => CStr::id()])
<div
  id="{{ $id }}"
  tabindex="-1"
  class="fixed top-0 left-0 right-0 z-50 hidden overflow-x-hidden overflow-y-auto md:inset-0 h-modal md:h-full"
>
    <div class="relative w-full h-full max-w-md p-4 md:h-auto">
        <div class="relative bg-white rounded-lg shadow dark:bg-gray-700">
            <!-- Close button -->
            <x-flowbite.modal.close id="{{ $id }}" />
            <div class="p-6">
                {{ $slot }}
            </div>
        </div>
    </div>
</div>
