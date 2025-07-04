@props([
    'title'      => '',
    'full_title' => '',
    'noHeader' => false,
    'noFooter' => false,
])
@php
    $__page_title = (CStr::isValidString($title) ? $title . ' &mdash; ' : '') . config('app.name', 'Laravel');
    $__full_title = CStr::isValidString($full_title) ? $full_title : $__page_title;
@endphp
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="UTF-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
    <title>{!! $__full_title !!}</title>
    <meta name="description" content="{{ config('app.description') }}" />
    <link rel="shortcut icon" href="{{ asset('favicon.ico') }}" type="image/x-icon" />
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" />

    @vite(['resources/css/tailwind.css', 'resources/scss/app.scss'])

    <!-- FontAwesome 6 CDN -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/fontawesome.min.css" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/solid.min.css" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/brands.min.css" />

    {{ $styles ?? null }}
</head>
<body>
    <div id="root"></div>
    @if (isset($header)) {{ $header }} @elseif (!$noHeader) <x-layout.header /> @endif
    <main class="flex flex-col flex-1">
        {{ $slot }}
    </main>
    @if (isset($footer)) {{ $footer }} @elseif (!$noFooter) <x-layout.footer /> @endif
    <script src="https://unpkg.com/flowbite@1.4.7/dist/flowbite.js"></script>
    @viteReactRefresh
    @vite(['resources/ts/main.ts', 'resources/ts/index.tsx'])
    {{ $scripts ?? null }}
</body>
</html>
