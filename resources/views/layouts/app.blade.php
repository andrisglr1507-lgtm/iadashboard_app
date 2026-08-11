<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=yes">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Classic Dashboard') - Laravel</title>
    
    <!-- Font Awesome 6 -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:opsz,wght@14..32,300;14..32,400;14..32,500;14..32,600;14..32,700&display=swap" rel="stylesheet">
    
    @vite(['resources/css/app.css']) {{-- Jika pakai Vite --}}
    @stack('styles')
</head>
<body>
    <div class="app-container">
        @include('partials.sidebar')
        
        <main class="main-content">
            @include('partials.header')
            @yield('content')
        </main>
    </div>

    @vite(['resources/js/app.js'])
    @stack('scripts')
</body>
</html>