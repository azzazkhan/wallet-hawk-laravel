@php
    $background = optional($event->media, function (array $media): ?string {
        return optional($media['image'], function (array $image): ?string {
            return $image['original']
                    ?? $image['url']
                    ?? $image['thumbnail']
                    ?? null;
        });
    });

    $name       = $event->asset['name'];

    $to       = $event->accounts['to'];
    $from     = $event->accounts['from'];
    $seller   = $event->accounts['seller'];
    $winner   = $event->accounts['winner'];

    function gweiToEth(int $gwei): float {
        return $gwei / 1000000000000000000;
    }
@endphp

<x-app>
    <div
        class="relative flex flex-col w-full min-h-[calc(100vh-12rem)] bg-fixed h-full bg-cover"
        style="background-image: url({{ $background }})"
    >
        <div class="flex flex-col flex-1 w-full sm:py-10 sm:px-2.5 md:py-20 md:px-10 backdrop-blur-xl">
            <div class="flex-1 w-full h-full overflow-hidden bg-white md:py-10 md:px-5 lg:p-10 sm:rounded-2xl">
                <div class="grid grid-cols-1 md:grid-cols-6 md:gap-x-10 lg:gap-x-0">
                    <!-- Image wrapper -->
                    <div class="flex col-span-2">
                        <img
                            src="{{ $background }}"
                            class="object-cover h-full min-h-[24rem] md:shadow-md md:rounded-xl w-full md:w-72"
                            alt="{{ $name }}"
                        />
                    </div>
                    <div class="flex flex-col flex-1 col-span-4 px-5 py-4 text-sm md:px-0">
                        <h1 class="py-2 text-3xl font-semibold">{{ $name }}</h1>
                        <x-custom.pills.wrapper label="Contract Address:">
                            <x-custom.pills.item class="overflow-hidden text-ellipsis whitespace-nowrap md:w-auto">
                                {{ $event->contract['address'] }}
                            </x-custom.pills.item>
                        </x-custom.pills.wrapper>

                        <x-custom.pills.wrapper noPush>
                            @if ($event->value)
                                <x-custom.pills.item
                                    tooltip="NFT Price"
                                    style="color: white; background-color: black;"
                                    icon="fab fa-ethereum"
                                >
                                    {{ number_format(gweiToEth((int) $event->value), 4) }}
                                </x-custom.pills.item>
                            @endif
                            <x-custom.pills.item
                                tooltip="Token ID"
                                style="background-color: #2563eb; color: white;"
                                icon="fas fa-hashtag"
                            >
                                {{ $event->asset['id'] }}
                            </x-custom.pills.item>
                            <x-custom.pills.item
                                tooltip="Token Standard"
                                style="color: white; background-color: #eab308;"
                                icon="fab fa-gg-circle"
                            >
                                {{ $event->schema }}
                            </x-custom.pills.item>
                        </x-custom.pills.wrapper>

                        <x-custom.pills.wrapper noPush>
                            @if ($from || $seller)
                                @if ($from)
                                    <x-custom.pills.item
                                        style="color: white; background-color: #0ea5e9;"
                                        icon="fas fa-pen-nib"
                                        tooltip="{{ $from['address'] }}"
                                    >
                                        Creator: {{ $from['user']['username'] ?? 'Unknown' }}
                                    </x-custom.pills.item>
                                @elseif ($seller)
                                    <x-custom.pills.item
                                        style="color: white; background-color: #0ea5e9;"
                                        icon="fas fa-pen-nib"
                                        tooltip="{{ $seller['address'] }}"
                                    >
                                        Creator: {{ $seller['user']['username'] ?? 'Unknown' }}
                                    </x-custom.pills.item>
                                @endif
                            @endif
                            @if ($to || $winner)
                                @if ($to)
                                    <x-custom.pills.item
                                        style="color: white; background-color: #ef4444;"
                                        tooltip="{{ $to['address'] }}"
                                    >
                                        Winner: {{ $to['user']['username'] ?? 'Unknown' }}
                                    </x-custom.pills.item>
                                @endif
                                @if ($winner)
                                    <x-custom.pills.item
                                    style="color: white; background-color: #ef4444;"
                                        tooltip="{{ $winner['address'] }}"
                                    >
                                        Winner: {{ $winner['user']['username'] ?? 'Unknown' }}
                                    </x-custom.pills.item>
                                @endif
                            @endif
                        </x-custom.pills.wrapper>

                        <p class="pt-4 font-medium leading-relaxed">{{ $event->asset['description'] }}</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app>
