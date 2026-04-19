<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>404 - Page non trouvée</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-zinc-100 dark:bg-zinc-900 text-gray-800 dark:text-gray-200">
    <div class="min-h-screen flex flex-col items-center justify-center">
        <div class="text-center p-8 max-w-md w-full">
            <img src="{{ asset('404.png') }}" alt="Illustration d'un fantôme pour l'erreur 404" class="w-48 h-48 mx-auto mb-8">

            <h1 class="text-6xl font-extrabold text-gray-800 dark:text-white mb-4">404</h1>
            <h2 class="text-2xl font-semibold text-gray-700 dark:text-gray-300 mb-4">Oups ! Page non trouvée.</h2>

            <p class="text-gray-600 dark:text-gray-400 mb-8">
                L'erreur 404 signifie que la page que vous essayez de joindre n'existe pas ou a été déplacée. Vérifiez l'URL ou retournez à la page d'accueil.
            </p>

            <div class="flex items-center justify-center gap-4">
                
                <flux:button onclick="history.back()" variant="primary">
                    Retourner en arrière
                </flux:button>
                <flux:button href="{{ url('/') }}">
                    Aller à l'accueil
                </flux:button>
            </div>
        </div>
    </div>
</body>
</html>
