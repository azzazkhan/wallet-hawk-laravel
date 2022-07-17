@php
    $background = $event->media['image']['original'];
    $name       = $event->asset['name'];

    $to       = $event->accounts['to'];
    $from     = $event->accounts['from'];
    $seller   = $event->accounts['seller'];
    $winner   = $event->accounts['winner'];
@endphp

<x-app>
    <div
        class="relative flex flex-col w-full min-h-[calc(100vh-12rem)] bg-fixed h-full bg-cover"
        style="background-image: url({{ $background }})"
    >
        <div class="flex flex-col flex-1 w-full py-10 px-2.5 md:p-20 backdrop-blur-xl">
            <div class="flex-1 w-full h-full p-5 bg-white md:p-10 rounded-2xl">
                <div class="grid grid-cols-1 md:grid-cols-6">
                    <!-- Image wrapper -->
                    <div class="flex col-span-2">
                        <img
                            src="{{ $background }}"
                            class="object-cover h-full min-h-[24rem] shadow-md rounded-xl w-full md:w-72"
                            alt="{{ $name }}"
                        />
                    </div>
                    <div class="flex flex-col flex-1 col-span-4 py-4 text-sm">
                        <h1 class="py-2 text-3xl font-semibold">{{ $name }}</h1>
                        <x-custom.pills.wrapper label="Contract Address:">
                            <x-custom.pills.item class="!w-[56px] text-ellipsis md:w-auto">{{ $event->contract['address'] }}</x-custom.pills.item>
                        </x-custom.pills.wrapper>

                        <x-custom.pills.wrapper noPush>
                            @if ($event->payment_token)
                                <x-custom.pills.item tooltip="NFT Price" classes="text-white bg-black" icon="fab fa-ethereum">
                                    {{ number_format($event->payment_token['eth'], 4) }}
                                </x-custom.pills.item>
                                <x-custom.pills.item tooltip="Current ETH Price" classes="text-white bg-green-500" icon="fas fa-dollar-sign">
                                    {{ number_format($event->payment_token['usd'], 2) }}
                                </x-custom.pills.item>
                            @endif
                            <x-custom.pills.item tooltip="Token ID" style="background-color: #2563eb; color: white;" icon="fas fa-hashtag">
                                {{ $event->asset['id'] }}
                            </x-custom.pills.item>
                            <x-custom.pills.item tooltip="Token Standard" classes="text-white bg-yellow-400" icon="fab fa-gg-circle">
                                {{ $event->schema }}
                            </x-custom.pills.item>
                        </x-custom.pills.wrapper>

                        <x-custom.pills.wrapper noPush>
                            @if ($from || $seller)
                                @if ($from)
                                    <x-custom.pills.item classes="bg-sky-500 text-white" icon="fas fa-pen-nib" tooltip="{{ $from['address'] }}">Creator: {{ $from['user']['username'] ?? 'Unknown' }}</x-custom.pills.item>
                                @elseif ($seller)
                                    <x-custom.pills.item classes="bg-sky-500 text-white" icon="fas fa-pen-nib" tooltip="{{ $seller['address'] }}">Creator: {{ $seller['user']['username'] ?? 'Unknown' }}</x-custom.pills.item>
                                @endif
                            @endif
                            @if ($to || $winner)
                                @if ($to)
                                    <x-custom.pills.item classes="text-white bg-orange-600" tooltip="{{ $to['address'] }}">Winner: {{ $to['user']['username'] ?? 'Unknown' }}</x-custom.pills.item>
                                @endif
                                @if ($winner)
                                    <x-custom.pills.item classes="text-white bg-orange-600" tooltip="{{ $winner['address'] }}">Winner: {{ $winner['user']['username'] ?? 'Unknown' }}</x-custom.pills.item>
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
