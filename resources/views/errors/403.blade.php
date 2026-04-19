<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>403 - Accès Refusé</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-zinc-100 dark:bg-zinc-900 text-gray-800 dark:text-gray-200">
    <div class="min-h-screen flex flex-col items-center justify-center">
        <div class="text-center p-8 max-w-md w-full">
            <img src="{{ asset('404.png') }}" alt="Illustration d'un cadenas pour l'erreur 403" class="w-48 h-48 mx-auto mb-8">

            <h1 class="text-6xl font-extrabold text-gray-800 dark:text-white mb-4">403</h1>
            <h2 class="text-2xl font-semibold text-gray-700 dark:text-gray-300 mb-4">Accès Refusé.</h2>

            <p class="text-gray-600 dark:text-gray-400 mb-8">
                L'erreur 403 signifie que vous n'avez pas les autorisations nécessaires pour accéder à une requête. Si vous pensez que c'est une erreur, veuillez contacter un administrateur.
            </p>

            <div class="flex items-center justify-center gap-4">
                <button onclick="history.back()" class="px-6 py-2 text-sm font-medium text-white bg-blue-600 rounded-lg hover:bg-blue-700 focus:outline-none focus:ring-4 focus:ring-blue-300 dark:focus:ring-blue-800">
                    Retourner en arrière
                </button>
                <a href="{{ url('/') }}" class="px-6 py-2 text-sm font-medium text-gray-700 bg-zinc-200 rounded-lg hover:bg-zinc-300 focus:outline-none focus:ring-4 focus:ring-gray-300 dark:text-gray-300 dark:bg-zinc-700 dark:hover:bg-zinc-600 dark:focus:ring-gray-600">
                    Aller à l'accueil
                </a>
            </div>
        </div>
    </div>
</body>
</html>
