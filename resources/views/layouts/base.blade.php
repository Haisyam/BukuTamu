<!DOCTYPE html>
<html lang="id" class="h-full">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Sistem Buku Tamu Digital') | Ekraf Majalengka</title>
    <meta name="description" content="Sistem Informasi Buku Tamu Digital Pelayanan Ekraf Majalengka." />

    <!-- Favicon -->
    <link rel="icon" type="image/png" href="{{ asset('ekraf.png') }}" />
    <link rel="shortcut icon" href="{{ asset('ekraf.png') }}" />
    <link rel="apple-touch-icon" href="{{ asset('ekraf.png') }}" />

    <!-- Google Fonts: Inter -->
    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap" rel="stylesheet" />

    <!-- Vite Assets -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <!-- Blocking Theme Script -->
    <script>
        (function() {
            const storedTheme = localStorage.getItem('theme');
            if (storedTheme === 'dark') {
                document.documentElement.classList.add('dark');
            } else {
                document.documentElement.classList.remove('dark');
            }
        })();

        window.toggleAppTheme = function() {
            const isDarkNow = document.documentElement.classList.contains('dark');
            if (isDarkNow) {
                document.documentElement.classList.remove('dark');
                localStorage.setItem('theme', 'light');
            } else {
                document.documentElement.classList.add('dark');
                localStorage.setItem('theme', 'dark');
            }
            window.dispatchEvent(new CustomEvent('theme-changed', { detail: { isDark: !isDarkNow } }));
        };
    </script>

    @stack('styles')
</head>
<body class="h-full font-sans antialiased bg-slate-50 dark:bg-slate-950 text-slate-900 dark:text-slate-100 selection:bg-orange-500 selection:text-white transition-colors duration-200">

    @yield('content')

    @stack('scripts')
</body>
</html>
