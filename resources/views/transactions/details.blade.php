@php
    $data = App\Traits\HandlesOpenseaEvents::prepare_event_for_preview($wallet, $event);

    $name        = $data->get('name');
    $token_id    = $data->get('token_id');
    $asset_id    = $data->get('asset_id');
    $image       = $data->get('image');
    $direction   = $data->get('direction');
    $event_id    = $data->get('event_id');
    $from        = $data->get('from_account');
    $to          = $data->get('to_account');
    $seller      = $data->get('seller_account');
    $owner       = $data->get('owner_account');
    $winner      = $data->get('winner_account');
    $schema      = $data->get('schema');
    $event_type  = $data->get('event_type');
    $value       = $data->get('value');
    $time_ago    = $data->get('timestamp')->diffForHumans();
    $event_time  = $data->get('timestamp')->format('D jS M Y \a\t g:i:s A');
@endphp

<x-app>
    <div
        class="relative flex flex-col w-full min-h-[calc(100vh-12rem)] bg-fixed h-full bg-cover"
        style="background-image: url({{ $image }})"
    >
        <div class="flex flex-col flex-1 w-full sm:py-10 sm:px-2.5 md:py-20 md:px-10 backdrop-blur-xl">
            <div class="flex-1 w-full h-full overflow-hidden bg-white md:py-10 md:px-5 lg:p-10 sm:rounded-2xl">
                <div class="grid grid-cols-1 md:grid-cols-6 md:gap-x-10 lg:gap-x-0">
                    <!-- Image wrapper -->
                    <div class="flex col-span-2">
                        <img
                            src="{{ $image }}"
                            class="object-cover h-full min-h-[24rem] md:shadow-md md:rounded-xl w-full md:w-72"
                            alt="{{ $name }}"
                        />
                    </div>
                    <div class="flex flex-col flex-1 col-span-4 px-5 py-4 text-sm md:px-0">
                        <h1 class="py-2 text-3xl font-semibold">{{ $name }}</h1>
                        <x-custom.pills.wrapper label="Contract Address:">
                            <x-custom.pills.item class="overflow-hidden text-ellipsis whitespace-nowrap md:w-auto">
                                {{ $event->contract->get('address') }}
                            </x-custom.pills.item>
                        </x-custom.pills.wrapper>

                        <x-custom.pills.wrapper noPush>
                            @if ($event->value)
                                <x-custom.pills.item
                                    tooltip="NFT Price"
                                    style="color: white; background-color: black;"
                                    icon="fab fa-ethereum"
                                >
                                    {{ $value }}
                                </x-custom.pills.item>
                            @endif
                            <x-custom.pills.item
                                tooltip="Token ID"
                                style="background-color: #2563eb; color: white;"
                                icon="fas fa-hashtag"
                            >
                                {{ $asset_id }}
                            </x-custom.pills.item>
                            <x-custom.pills.item
                                tooltip="Token Standard"
                                style="color: white; background-color: #eab308;"
                                icon="fab fa-gg-circle"
                            >
                                <span class="uppercase">{{ $schema }}</span>
                            </x-custom.pills.item>

                            <x-custom.pills.item
                                tooltip="Event Type"
                                style="color: white; background-color: #15803d;"
                            >
                                {{ Str::title($event_type) }}
                            </x-custom.pills.item>
                            <x-custom.pills.item
                                tooltip="Event Type"
                                style="color: white; background-color: #4f46e5;"
                            >
                                {{ $token_id }}
                            </x-custom.pills.item>
                        </x-custom.pills.wrapper>

                        <x-custom.pills.wrapper noPush>
                            @if ($from)
                                <x-custom.pills.item
                                    style="color: white; background-color: #0ea5e9;"
                                    icon="fas fa-pen-nib"
                                    tooltip="{{ $from['address'] }}"
                                >
                                    From: {{ $from['user']['username'] ?? 'Unknown' }}
                                </x-custom.pills.item>
                            @endif

                            @if ($owner)
                                <x-custom.pills.item
                                    style="color: white; background-color: #0ea5e9;"
                                    icon="fas fa-pen-nib"
                                    tooltip="{{ $owner['address'] }}"
                                >
                                    Owner: {{ $owner['user']['username'] ?? 'Unknown' }}
                                </x-custom.pills.item>
                            @endif

                            @if ($seller)
                                <x-custom.pills.item
                                    style="color: white; background-color: #0ea5e9;"
                                    icon="fas fa-pen-nib"
                                    tooltip="{{ $seller['address'] }}"
                                >
                                    Seller: {{ $seller['user']['username'] ?? 'Unknown' }}
                                </x-custom.pills.item>
                            @endif

                            @if ($to)
                                <x-custom.pills.item
                                    style="color: white; background-color: #ef4444;"
                                    icon="fas fa-pen-nib"
                                    tooltip="{{ $to['address'] }}"
                                >
                                    To: {{ $to['user']['username'] ?? 'Unknown' }}
                                </x-custom.pills.item>
                            @endif

                            @if ($winner)
                                <x-custom.pills.item
                                    style="color: white; background-color: #ef4444;"
                                    icon="fas fa-pen-nib"
                                    tooltip="{{ $winner['address'] }}"
                                >
                                    Winner: {{ $winner['user']['username'] ?? 'Unknown' }}
                                </x-custom.pills.item>
                            @endif
                        </x-custom.pills.wrapper>

                        <p class="pt-4 font-medium leading-relaxed">
                            {{ $event->asset->get('description') }}
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app>
