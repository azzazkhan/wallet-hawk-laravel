
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
    <link rel="stylesheet" href="{{ mix('assets/dist/tailwind.min.css') }}" />
    <link rel="stylesheet" href="{{ mix('assets/dist/style.min.css') }}" />
    {{ $styles ?? null }}
</head>
<body>
    {{ $slot }}
    <script src="https://unpkg.com/flowbite@1.4.7/dist/flowbite.js"></script>
    <script src="{{ mix('assets/dist/app.min.js') }}" defer></script>
    {{ $scripts ?? null }}
</body>
</html>
