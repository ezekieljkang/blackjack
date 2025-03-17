<!DOCTYPE html>
<html lang="en">
<head>
    <title>Blackjack Game</title>
    @livewireStyles
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-green-900 text-white min-h-screen flex items-center justify-center">
    <div class="container p-6 rounded-lg shadow-lg bg-green-800">
        @yield('content')
    </div>

    @livewireScripts
</body>
</html>
