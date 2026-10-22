<style>
    @keyframes expandTitleDivider {
        0% { width: 0; opacity: 0; }
        100% { width: 100%; opacity: 1; }
    }
    .animated-title-divider {
        height: 3px;
        background: linear-gradient(90deg, #0ea5e9, transparent);
        border-radius: 2px;
        margin-top: 4px;
        width: 0;
        animation: expandTitleDivider 0.8s cubic-bezier(0.4, 0, 0.2, 1) forwards;
        animation-delay: 0.1s;
    }
</style>
<header class="global-header" style="display: flex; justify-content: space-between; align-items: center; padding: 0 24px 0 0; height: 70px; background: #ffffff; border-bottom: 1px solid #e2e8f0; flex-shrink: 0; z-index: 1050;">
    
    <!-- Bagian Kiri: Title -->
    <div style="display: flex; align-items: center; padding-left: 24px;">
        <div style="display: inline-flex; flex-direction: column;">
            <h1 style="font-size: 1.25rem; font-weight: 600; color: #464748ff; margin: 0;">
                @hasSection('page_title')
                    @yield('page_title')
                @else
                    @yield('title', 'Dashboard')
                @endif
            </h1>
            <div class="animated-title-divider"></div>
        </div>
    </div>

    <!-- Bagian Kanan: Actions & Profile -->
    <div style="display: flex; align-items: center; gap: 16px; margin: 0;">
        @yield('page_actions')

        @hasSection('page_actions')
            <div style="width: 1px; height: 32px; background-color: #e2e8f0;"></div>
        @endif

        <!-- Combo Box Sesi -->
        <form action="{{ route('global.set_session') }}" method="POST" id="globalSessionForm" style="display: flex; align-items: center; gap: 12px; margin: 0;">
            @csrf
            <label for="globalSessionSelect" style="font-size: 0.8rem; font-weight: 700; color: #64748b; margin: 0; text-transform: uppercase; letter-spacing: 0.05em; font-family: 'Inter', sans-serif;">
                Sesi Aktif
            </label>
            <select name="session_id" id="globalSessionSelect" onchange="document.getElementById('globalSessionForm').submit()" style="padding: 6px 32px 6px 12px; border: 1px solid #cbd5e1; border-radius: 6px; background-color: #f8fafc; color: #0f172a; font-weight: 600; font-size: 0.85rem; font-family: 'Inter', sans-serif; outline: none; cursor: pointer; min-width: 180px; appearance: none; background-image: url('data:image/svg+xml;charset=US-ASCII,%3Csvg%20xmlns%3D%22http%3A%2F%2Fwww.w3.org%2F2000%2Fsvg%22%20width%3D%22292.4%22%20height%3D%22292.4%22%3E%3Cpath%20fill%3D%22%23748194%22%20d%3D%22M287%2069.4a17.6%2017.6%200%200%200-13-5.4H18.4c-5%200-9.3%201.8-12.9%205.4A17.6%2017.6%200%200%200%200%2082.2c0%205%201.8%209.3%205.4%2012.9l128%20127.9c3.6%203.6%207.8%205.4%2012.8%205.4s9.2-1.8%2012.8-5.4L287%2095c3.5-3.5%205.4-7.8%205.4-12.8%200-5-1.9-9.2-5.4-12.8z%22%2F%3E%3C%2Fsvg%3E'); background-repeat: no-repeat; background-position: right 10px center; background-size: 10px; transition: border-color 0.2s;">
                <option value="">-- Pilih Sesi --</option>
                @if(isset($globalSessions))
                    @foreach($globalSessions as $s)
                        <option value="{{ $s->id }}" {{ (isset($activeSessionId) && $activeSessionId == $s->id) ? 'selected' : '' }}>
                            {{ $s->session_code }} [{{ $s->status }}]
                        </option>
                    @endforeach
                @endif
            </select>
        </form>

        <!-- Divider -->
        <div style="width: 1px; height: 32px; background-color: #e2e8f0;"></div>
    
    </div>
</header>
