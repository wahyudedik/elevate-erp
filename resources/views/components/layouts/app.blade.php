<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Elevate Present Check</title>

    <link rel="stylesheet" href="https://unpkg.com/leaflet/dist/leaflet.css" />

    <script src="https://cdn.tailwindcss.com"></script>

    <style>
        #map {
            height: 400px;
        }
    </style>

    @livewireStyles
</head>

<body>
    {{ $slot }}

    @livewireScripts
</body>

</html>
