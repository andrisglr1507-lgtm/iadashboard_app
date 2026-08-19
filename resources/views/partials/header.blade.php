<header class="global-header" style="display: flex; justify-content: space-between; align-items: center; padding: 0 24px 0 20px; height: 70px; background: rgba(255, 255, 255, 0.95); backdrop-filter: blur(8px); border-bottom: 1px solid var(--phoenix-border-color); flex-shrink: 0; z-index: 1050;">
    
    <div style="display: flex; align-items: center;">
        <!-- Brand Logo -->
        <div style="display: flex; align-items: center; gap: 12px; width: 230px;">
            <i class="fas fa-chalkboard-user" style="font-size: 24px; color: var(--phoenix-primary);"></i>
            <span style="font-weight: 800; font-size: 1.25rem; color: var(--phoenix-dark); letter-spacing: -0.5px;">{{ config('app.name', 'Laravel') }}</span>
        </div>
        
        <!-- Toggle Btn -->
        <button id="toggleSidebarBtn" style="background: transparent; border: none; cursor: pointer; color: var(--phoenix-text-muted); padding: 8px 16px; margin-right: 24px; font-size: 1.1rem; transition: color 0.2s;" onmouseover="this.style.color='var(--phoenix-primary)'" onmouseout="this.style.color='var(--phoenix-text-muted)'">
            <i class="fas fa-bars" id="toggleIcon"></i>
        </button>

        <!-- Bagian Kiri: Title & Actions -->
        <div class="page-header-left" style="display: flex; align-items: center; gap: 16px;">
            <h1 style="font-size: 1.15rem; font-weight: 800; color: var(--phoenix-dark); margin: 0; letter-spacing: -0.02em;">@yield('page_title', 'Dashboard')</h1>
            @yield('page_actions')
        </div>
    </div>

    <!-- Bagian Kanan: Actions & Profile -->
    <div style="display: flex; align-items: center; gap: 20px; margin: 0;">
        <!-- Combo Box Sesi -->
        <form action="{{ route('global.set_session') }}" method="POST" id="globalSessionForm" style="display: flex; align-items: center; gap: 12px; margin: 0;">
            @csrf
            <label for="globalSessionSelect" style="font-size: 0.8rem; font-weight: 700; color: var(--phoenix-text-muted); margin: 0; text-transform: uppercase; letter-spacing: 0.05em;">
                Sesi Aktif
            </label>
            <select name="session_id" id="globalSessionSelect" onchange="document.getElementById('globalSessionForm').submit()" style="padding: 6px 32px 6px 12px; border: 1px solid var(--phoenix-border-color); border-radius: var(--phoenix-border-radius); background-color: var(--phoenix-bg-main); color: var(--phoenix-dark); font-weight: 600; font-size: 0.85rem; outline: none; cursor: pointer; min-width: 180px; appearance: none; background-image: url('data:image/svg+xml;charset=US-ASCII,%3Csvg%20xmlns%3D%22http%3A%2F%2Fwww.w3.org%2F2000%2Fsvg%22%20width%3D%22292.4%22%20height%3D%22292.4%22%3E%3Cpath%20fill%3D%22%23748194%22%20d%3D%22M287%2069.4a17.6%2017.6%200%200%200-13-5.4H18.4c-5%200-9.3%201.8-12.9%205.4A17.6%2017.6%200%200%200%200%2082.2c0%205%201.8%209.3%205.4%2012.9l128%20127.9c3.6%203.6%207.8%205.4%2012.8%205.4s9.2-1.8%2012.8-5.4L287%2095c3.5-3.5%205.4-7.8%205.4-12.8%200-5-1.9-9.2-5.4-12.8z%22%2F%3E%3C%2Fsvg%3E'); background-repeat: no-repeat; background-position: right 10px center; background-size: 10px; transition: border-color 0.2s;">
                <option value="">-- Pilih Sesi --</option>
                @if(isset($globalSessions))
                    @foreach($globalSessions as $s)
                        <option value="{{ $s->session_id }}" {{ (isset($activeSessionId) && $activeSessionId == $s->session_id) ? 'selected' : '' }}>
                            {{ $s->session_id }} - {{ $s->branch_id }}
                        </option>
                    @endforeach
                @endif
            </select>
        </form>

        <!-- Divider -->
        <div style="width: 1px; height: 32px; background-color: var(--phoenix-border-color);"></div>

        <!-- User Profile & Logout -->
        @auth
        <div style="display: flex; align-items: center; gap: 12px;">
            <div style="display: flex; align-items: center; gap: 12px; cursor: pointer;">
                <div style="width: 32px; height: 32px; border-radius: 50%; background: var(--phoenix-primary); color: white; display: flex; align-items: center; justify-content: center; font-weight: 800; font-size: 0.9rem; box-shadow: 0 0 0 2px white, 0 0 0 3px var(--phoenix-border-color);">
                    {{ strtoupper(substr(Auth::user()->full_name ?? Auth::user()->username, 0, 1)) }}
                </div>
                <div style="display: flex; flex-direction: column;">
                    <span style="font-size: 0.85rem; font-weight: 700; color: var(--phoenix-dark); line-height: 1.2;">{{ Auth::user()->full_name ?? Auth::user()->username }}</span>
                    <span style="font-size: 0.75rem; color: var(--phoenix-text-muted); line-height: 1.2;">{{ Auth::user()->role }}</span>
                </div>
            </div>
            <form action="{{ route('logout') }}" method="POST" style="margin: 0; margin-left: 8px;">
                @csrf
                <button type="submit" style="background: none; border: none; color: var(--phoenix-text-muted); cursor: pointer; padding: 6px; border-radius: 6px; display: flex; align-items: center; justify-content: center; transition: all 0.2s;" onmouseover="this.style.color='var(--phoenix-danger)'" onmouseout="this.style.color='var(--phoenix-text-muted)'" title="Logout">
                    <i class="fas fa-sign-out-alt"></i>
                </button>
            </form>
        </div>
        @endauth
    </div>
</header>
