<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=yes">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Classic Dashboard') - IA</title>
    <link rel="icon" type="image/png" href="{{ asset('images/icon_app_ia.png') }}">
    
    <!-- Font Awesome 6 -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <!-- Remix Icon (Modern & Clean) -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/remixicon@4.3.0/fonts/remixicon.min.css">
    <!-- Tabler Icons (Crisp & Minimalist) -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@tabler/icons-webfont@latest/dist/tabler-icons.min.css">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <!-- Google Fonts: Inter -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Toastify CSS -->
    <link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/toastify-js/src/toastify.min.css">
    
    @vite(['resources/css/app.css']) {{-- Jika pakai Vite --}}
    
    <style>
        :root {
            --bs-body-font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            --bs-font-sans-serif: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
        }
        body, button, input, select, textarea, .btn, .table, .dropdown-menu, .menu-text, .page-title, .content-panel {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif !important;
        }
    </style>
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
    
    <!-- Toastify JS -->
    <script type="text/javascript" src="https://cdn.jsdelivr.net/npm/toastify-js"></script>

    <!-- Global Flash Message Handler -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            @if(session('success'))
                Toastify({
                    text: "{!! addslashes(session('success')) !!}",
                    duration: 3000,
                    close: true,
                    gravity: "top",
                    position: "right",
                    stopOnFocus: true,
                    style: {
                        background: "linear-gradient(135deg, #1ABB9C, #138D75)",
                        borderRadius: "8px",
                        boxShadow: "0 4px 12px rgba(26, 187, 156, 0.2)",
                        fontSize: "0.85rem",
                        fontWeight: "600",
                        fontFamily: "'Inter', sans-serif"
                    }
                }).showToast();
            @endif

            @if(session('error'))
                Toastify({
                    text: "{!! addslashes(session('error')) !!}",
                    duration: 4000,
                    close: true,
                    gravity: "top",
                    position: "right",
                    stopOnFocus: true,
                    style: {
                        background: "linear-gradient(135deg, #E74C3C, #C0392B)",
                        borderRadius: "8px",
                        boxShadow: "0 4px 12px rgba(231, 76, 60, 0.2)",
                        fontSize: "0.85rem",
                        fontWeight: "600",
                        fontFamily: "'Inter', sans-serif"
                    }
                }).showToast();
            @endif
            
            @if(session('info'))
                Toastify({
                    text: "{!! addslashes(session('info')) !!}",
                    duration: 3000,
                    close: true,
                    gravity: "top",
                    position: "right",
                    stopOnFocus: true,
                    style: {
                        background: "linear-gradient(135deg, #3498DB, #2980B9)",
                        borderRadius: "8px",
                        boxShadow: "0 4px 12px rgba(52, 152, 219, 0.2)",
                        fontSize: "0.85rem",
                        fontWeight: "600",
                        fontFamily: "'Inter', sans-serif"
                    }
                }).showToast();
            @endif
        });
    </script>
    
    @vite(['resources/js/app.js'])
    @stack('scripts')
</body>
</html>