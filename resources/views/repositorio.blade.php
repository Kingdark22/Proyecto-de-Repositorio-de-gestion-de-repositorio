<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Repositorio Institucional UPTP - Consulta Pública</title>
    <link rel="icon" type="image/png" href="{{ asset('imagenes/uptp-logo.png') }}">
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <script src="https://unpkg.com/lucide@latest"></script>
    <style>
        body { font-family: 'Outfit', sans-serif; }
    </style>
    @livewireStyles
</head>
<body class="bg-slate-50 text-slate-900 antialiased">
    <!-- Navbar -->
    <nav class="bg-slate-900 border-b border-white/5 py-6">
        <div class="container mx-auto px-6 flex items-center justify-between">
            <a href="/" class="flex items-center gap-3">
                <div class="h-8 w-8 bg-blue-600 rounded-lg flex items-center justify-center">
                    <i data-lucide="library" class="w-5 h-5 text-white"></i>
                </div>
                <span class="text-lg font-bold text-white tracking-tight">Repositorio <span class="text-blue-400">UPTP</span></span>
            </a>
            <div class="flex items-center gap-6">
                <a href="/" class="text-slate-300 hover:text-white transition-colors text-sm font-medium">Inicio</a>
                <a href="/login" class="px-5 py-2 bg-white/10 hover:bg-white/20 text-white rounded-xl font-bold text-xs transition-all border border-white/10">Acceso Personal</a>
            </div>
        </div>
    </nav>

    <main class="container mx-auto px-6 py-12">
        <livewire:repositorio-publico />
    </main>

    <footer class="bg-slate-900 py-12 mt-20">
        <div class="container mx-auto px-6 text-center">
            <p class="text-slate-500 text-sm italic font-medium opacity-50">Custodia Digital Institucional - UPTP Juan de Jesús Montilla © 2026</p>
        </div>
    </footer>

    @livewireScripts
    <script>
        lucide.createIcons();
    </script>
</body>
</html>
