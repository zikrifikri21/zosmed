<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="lg:h-full">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="title" content="zosmed">
    <meta name="description" content="zomed adalah tempat dimana anda dapat saling terhubung ">
    <meta name="keywords" content="zosmed, sosial media, saling terhubung">
    <meta name="robots" content="index, follow">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
    <meta name="language" content="English">
    <meta name="revisit-after" content="10 days">
    <meta name="author" content="zikrifikri21">
    <title inertia>{{ config('app.name', 'Laravel') }}</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

    <!-- Scripts -->
    @routes
    @vite(['resources/js/app.js', "resources/js/Pages/{$page['component']}.vue"])
    @inertiaHead
</head>

<body class="font-sans antialiased bg-gray-100 dark:bg-gray-900 lg:overflow-hidden lg:h-full">
    @inertia
</body>
@if (Route::currentRouteName() != 'chat')
    <script data-name="BMC-Widget" data-cfasync="false" src="https://cdnjs.buymeacoffee.com/1.0.0/widget.prod.min.js"
        data-id="iniakunku3c" data-description="Support me on Buy me a coffee!" data-message="" data-color="#5F7FFF"
        data-position="Right" data-x_margin="18" data-y_margin="18"></script>
@endif

</html>
