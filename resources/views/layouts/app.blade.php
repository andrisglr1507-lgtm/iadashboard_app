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
    
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    
    @vite(['resources/css/app.css']) {{-- Jika pakai Vite --}}
    @stack('styles')
</head>
<body>
    <div class="app-layout" style="display: flex; flex-direction: row; height: 100vh; overflow: hidden;">
        @include('partials.sidebar')
        
        <div style="display: flex; flex-direction: column; flex: 1; overflow: hidden; background: var(--phoenix-bg-main);">
            @include('partials.header')
            
            <main class="main-content" style="flex: 1; overflow-y: auto;">
                <div class="content-wrapper" style="padding: 10px 20px;">
                    @yield('content')
                </div>
            </main>
        </div>
    </div>
    <!-- Bootstrap 5 JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    
    @vite(['resources/js/app.js'])
    @stack('scripts')
</body>
</html>