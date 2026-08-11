<header class="global-header" style="display: flex; justify-content: space-between; align-items: center; padding: 12px 20px; background: white; border-bottom: 1px solid var(--border); border-radius: 12px; margin-bottom: 16px; box-shadow: 0 1px 3px rgba(0,0,0,0.03);">
    
    <!-- Bagian Kiri: Title & Actions -->
    <div class="page-header-left" style="display: flex; align-items: center; gap: 16px;">
        <h1 style="font-size: 1.1rem; font-weight: 600; color: var(--dark); margin: 0;">@yield('page_title', 'Dashboard')</h1>
        @yield('page_actions')
    </div>

    <!-- Bagian Kanan: Combo Box Sesi -->
    <form action="{{ route('global.set_session') }}" method="POST" id="globalSessionForm" style="display: flex; align-items: center; gap: 12px; margin: 0;">
        @csrf
        <label for="globalSessionSelect" style="font-size: 0.8rem; font-weight: 600; color: var(--text-secondary); margin: 0;">
            Sesi Aktif:
        </label>
        <select name="session_id" id="globalSessionSelect" onchange="document.getElementById('globalSessionForm').submit()" style="padding: 6px 32px 6px 12px; border: 1px solid var(--border); border-radius: 6px; background-color: #f8fafc; color: var(--dark); font-weight: 500; font-size: 0.8rem; outline: none; cursor: pointer; min-width: 180px; appearance: none; background-image: url('data:image/svg+xml;charset=US-ASCII,%3Csvg%20xmlns%3D%22http%3A%2F%2Fwww.w3.org%2F2000%2Fsvg%22%20width%3D%22292.4%22%20height%3D%22292.4%22%3E%3Cpath%20fill%3D%22%2364748b%22%20d%3D%22M287%2069.4a17.6%2017.6%200%200%200-13-5.4H18.4c-5%200-9.3%201.8-12.9%205.4A17.6%2017.6%200%200%200%200%2082.2c0%205%201.8%209.3%205.4%2012.9l128%20127.9c3.6%203.6%207.8%205.4%2012.8%205.4s9.2-1.8%2012.8-5.4L287%2095c3.5-3.5%205.4-7.8%205.4-12.8%200-5-1.9-9.2-5.4-12.8z%22%2F%3E%3C%2Fsvg%3E'); background-repeat: no-repeat; background-position: right 10px center; background-size: 10px;">
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
</header>
